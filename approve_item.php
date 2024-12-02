<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if ($itemId <= 0 || empty($action) || empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please provide valid details.']);
        exit();
    }

    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit();
    }

    try {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $db->prepare("UPDATE items SET approval_status = :status, reason = :reason WHERE item_id = :itemId");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "Item successfully $status."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update item status.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}
?>
