<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: welcome.html");
    exit();
}

require 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];

    // Fetch the target user's role
    $stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    // Prevent actions on admins
    if ($user['role'] === 'admin') {
        die("You cannot modify other admins.");
    }

    if (isset($_POST['delete'])) {
        // Delete user
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        header("Location: index.php");
        exit();
    }

    if (isset($_POST['promote'])) {
        // Promote to staff
        $stmt = $db->prepare("UPDATE users SET role = 'staff' WHERE user_id = ?");
        $stmt->execute([$userId]);
        header("Location: index.php");
        exit();
    }

    if (isset($_POST['demote'])) {
        // Demote to customer
        $stmt = $db->prepare("UPDATE users SET role = 'customer' WHERE user_id = ?");
        $stmt->execute([$userId]);
        header("Location: index.php");
        exit();
    }
}