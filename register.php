<?php
session_start();
require 'db.php'; // Ensure this line is included

// Process registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashed_password
        ]);
        // Registration successful, redirect or show a message
        header("Location: login.html");
        exit();
    } catch (PDOException $e) {
        // Handle errors
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.href = 'register.html'; // Change to your desired page
          </script>";
        //exit();
    }
}
?>
