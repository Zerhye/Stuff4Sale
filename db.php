<?php
$host = "localhost"; // Your server name
$port = "11211"; // Default MySQL port
$user = "root"; // Your MySQL username
$pass = ""; // Your MySQL password (default is empty in XAMPP)
$dbname = "stuff4sale"; // Replace with your actual database name

try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

