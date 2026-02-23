<?php
// config.php
$host = "localhost";
$user = "root";       // Default XAMPP user
$pass = "";           // Default XAMPP password (empty)
$dbname = "inventory_db"; // Make sure to create this DB in phpMyAdmin

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>