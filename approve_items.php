<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: welcome.html");
    exit();
}

if (isset($_POST['item_id'])) {
    $itemId = $_POST['item_id'];
    $approvalStatus = 'approved';

    if (isset($_POST['reject'])) {
        $approvalStatus = 'rejected';
    }

    $query = "UPDATE items SET approval_status = ? WHERE id = ?";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$approvalStatus, $itemId])) {
        header("Location: index.php"); // Redirect back to the main page after action
        exit();
    } else {
        echo "Error updating item status.";
    }
} else {
    echo "Invalid request.";
}
?>
