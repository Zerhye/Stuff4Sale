<?php
session_start();
require 'database.php'; // Include your database connection file

// Check if the user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: welcome.html");
    exit();
}

// Staff dashboard content goes here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to CSS -->
</head>
<body>
    <div class="container">
        <h2>Staff Dashboard</h2>
        <p>Welcome, Staff Member!</p>
        
        <h3>Item Management</h3>
        <p><a href="approve_items.php"><button>Approve/Decline Pending Items</button></a></p>

        <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </div>
</body>
</html>
