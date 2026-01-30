<?php
// ====== Generate 5000 Fake Customers for OpenCart ======

// Database connection
$host = 'localhost';
$user = 'root';
$pass = 'nidha';
$db   = 'openshop';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Predefined sample first and last names
$first_names = [
    'John', 'Jane', 'Mike', 'Alice', 'Bob', 'Chris', 'Emma', 'David', 'Sophia', 'Liam',
    'Olivia', 'Noah', 'Ava', 'Ethan', 'Isabella', 'Mason', 'Mia', 'Logan', 'Amelia', 'Lucas',
    // Indian first names
    'Aarav', 'Ishita', 'Rohan', 'Priya', 'Arjun', 'Diya', 'Vikram', 'Neha', 'Raj', 'Ananya',
    'Karan', 'Sanya', 'Rahul', 'Meera', 'Aditya', 'Sneha', 'Varun', 'Kavya', 'Nikhil', 'Pooja'
];

$last_names = [
    'Doe', 'Smith', 'Brown', 'Johnson', 'Williams', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Martinez',
    'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
    // Indian last names
    'Sharma', 'Patel', 'Reddy', 'Nair', 'Gupta', 'Iyer', 'Kumar', 'Joshi', 'Mehta', 'Das',
    'Rao', 'Chopra', 'Menon', 'Bhat', 'Naidu', 'Singh', 'Pandey', 'Verma', 'Jain', 'Mishra'
];

// Common password hash: 'password123' (MD5)
$password_hash = md5('password123');

for ($i = 1; $i <= 5000; $i++) {
    $first = $first_names[array_rand($first_names)];
    $last = $last_names[array_rand($last_names)];
    $email = strtolower($first . $last . $i . '@example.com');
    $phone = '9' . rand(100000000, 999999999);
    $newsletter = rand(0, 1);
    $status = 1;
    $ip = '127.0.0.1';

    $sql = "INSERT INTO oc_customer 
        (firstname, lastname, email, password, customer_group_id, newsletter, status, date_added, telephone, ip)
        VALUES 
        ('$first', '$last', '$email', '$password_hash', 1, $newsletter, $status, NOW(), '$phone', '$ip')";
    
    $conn->query($sql);

    if ($i % 500 == 0) {
        echo "Inserted $i customers so far";
    }
}

$conn->close();
echo "Done inserting 5000 fake customers";