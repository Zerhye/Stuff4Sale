<?php
session_start();
require 'db.php'; // Include your database connection file

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: welcome.html");
    exit();
}

// Admin dashboard content goes here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to CSS -->
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <p>Welcome, Admin!</p>
        
        <h3>Staff Management</h3>
        <p><a href="create_staff.php"><button>Create New Staff Account</button></a></p>
        <p><a href="view_staff_activity.php"><button>View Staff Activity</button></a></p>

        <h3>Approval Management</h3>
        <p><a href="view_pending_items.php"><button>View Pending Items</button></a></p>

        <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </div>
</body>
</html>
