<?php
session_start();
require 'db.php';

// Initialize error message variable
$error = "";

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the login query
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id']; // Assuming 'id' is the primary key
        $_SESSION['role'] = $user['role'];
        header(header: "Location: index.php"); 
    } else {
        $error = "Invalid email or password. Please try again.";
    }

    
}
?>