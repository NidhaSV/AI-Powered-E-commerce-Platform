<?php
// ====== Generate 50,000 Fake Orders ======

// Database connection
$host = 'localhost';
$user = 'root';
$pass = 'nidha';
$db   = 'openshop';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===== Load existing customers dynamically =====
$customers = [];
$result = $conn->query("SELECT customer_id AS id, firstname AS first, lastname AS last, email, telephone AS phone FROM oc_customer");
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
$result->free();

if (count($customers) == 0) {
    die("No customers found in oc_customer. Please insert customers first.\n");
}

// ===== Define sample products =====
$products = [
    ['id'=>28,'name'=>'HTC Touch HD','price'=>500],
    ['id'=>29,'name'=>'Palm Treo Pro','price'=>200],
    ['id'=>30,'name'=>'Canon EOS 5D','price'=>300],
    ['id'=>31,'name'=>'Nikon D300','price'=>150],
    ['id'=>32,'name'=>'iPod Touch','price'=>150],
    ['id'=>33,'name'=>'Samsung SyncMaster 941BW','price'=>300],
    ['id'=>34,'name'=>'iPod Shuffle','price'=>200],
    ['id'=>35,'name'=>'iwatch','price'=>150],
    ['id'=>36,'name'=>'iPod Nano','price'=>100],
    ['id'=>40,'name'=>'iPhone','price'=>101],
    ['id'=>41,'name'=>'iMac','price'=>100],
    ['id'=>42,'name'=>'Apple Cinema 30&quot;','price'=>100],
    ['id'=>43,'name'=>'MacBook','price'=>500],
    ['id'=>44,'name'=>'MacBook Air','price'=>1000],
    ['id'=>45,'name'=>'MacBook Pro','price'=>2000],
    ['id'=>46,'name'=>'Sony VAIO','price'=>1000],
    ['id'=>47,'name'=>'HP LP3065','price'=>100],
    ['id'=>48,'name'=>'iPod Classic','price'=>100],
    ['id'=>49,'name'=>'Samsung Galaxy Tab 10.1','price'=>199.99],
];

// ===== Order setup =====
$order_statuses = [1, 2, 3, 5]; // Pending, Processing, Shipped, Complete
$payment_method_json = '{"name":"Cash On Delivery","code":"cod.cod"}';
$shipping_method = 'Flat Shipping';
$comment = 'Auto-inserted demo order';

// ===== Generate orders =====
$total_orders = 50000;
$start_date = strtotime('2023-01-01');
$end_date = strtotime('2025-12-31');

for ($i = 1; $i <= $total_orders; $i++) {
    $customer = $customers[array_rand($customers)];
    $num_products = rand(1, 3);

    $order_products = [];
    $subtotal = 0;

    for ($p = 0; $p < $num_products; $p++) {
        $prod = $products[array_rand($products)];
        $qty = rand(1, 2);
        $total = $prod['price'] * $qty;
        $subtotal += $total;

        $order_products[] = [
            'product_id' => $prod['id'],
            'name' => $prod['name'],
            'model' => $prod['name'],
            'qty' => $qty,
            'price' => $prod['price'],
            'total' => $total
        ];
    }

    $shipping_cost = 50.00;
    $total = $subtotal + $shipping_cost;
    $order_status = $order_statuses[array_rand($order_statuses)];

    // Random date between 2023 and 2025
    $timestamp = rand($start_date, $end_date);
    $order_date = date('Y-m-d H:i:s', $timestamp);

    // Insert into oc_order
    $sql_order = "
        INSERT INTO oc_order 
        (store_id, store_name, store_url, customer_id, customer_group_id, firstname, lastname, email, telephone,
         payment_firstname, payment_lastname, payment_company, payment_address_1, payment_address_2, payment_city,
         payment_postcode, payment_country, payment_country_id, payment_zone, payment_zone_id, payment_method,
         shipping_firstname, shipping_lastname, shipping_company, shipping_address_1, shipping_address_2, shipping_city,
         shipping_postcode, shipping_country, shipping_country_id, shipping_zone, shipping_zone_id, shipping_method,
         comment, total, order_status_id, currency_code, currency_value, ip, user_agent, date_added, date_modified)
        VALUES
        (0,'Your Store','http://localhost/openshop/',{$customer['id']},1,
         '{$customer['first']}','{$customer['last']}','{$customer['email']}','{$customer['phone']}',
         '{$customer['first']}','{$customer['last']}','','Demo Address','','Demo City','000000','India',99,'Kerala',1490,'$payment_method_json',
         '{$customer['first']}','{$customer['last']}','','Demo Address','','Demo City','000000','India',99,'Kerala',0,'$shipping_method',
         '$comment',$total,$order_status,'USD',1.0,'127.0.0.1',
         'Mozilla/5.0 (Windows NT 10.0; Win64; x64)','$order_date','$order_date')";

    $conn->query($sql_order);
    $order_id = $conn->insert_id;

    // Insert order products
    foreach ($order_products as $op) {
        $conn->query("INSERT INTO oc_order_product (order_id, product_id, name, model, quantity, price, total)
            VALUES ($order_id, {$op['product_id']}, '{$op['name']}', '{$op['model']}', {$op['qty']}, {$op['price']}, {$op['total']})");
    }

    // Insert order totals
    $conn->query("INSERT INTO oc_order_total (order_id, code, title, value, sort_order)
        VALUES 
        ($order_id,'sub_total','Sub-Total',$subtotal,1),
        ($order_id,'shipping','Flat Shipping',$shipping_cost,2),
        ($order_id,'total','Total',$total,3)");

    // Insert order history
    $conn->query("INSERT INTO oc_order_history (order_id, order_status_id, notify, comment, date_added)
        VALUES ($order_id,$order_status,0,'$comment','$order_date')");

    if ($i % 500 == 0) {
        echo "Inserted $i / $total_orders orders so far...\n";
    }
}

$conn->close();
echo "✅ Done inserting $total_orders fake orders from 2023 to 2025.\n";
