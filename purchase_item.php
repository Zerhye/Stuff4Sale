<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($_SESSION['user_id']) || !isset($data['item_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit();
    }

    $currentUserId = $_SESSION['user_id'];
    $itemId = $data['item_id'];

    // Fetch the current owner of the item
    $stmt = $db->prepare("SELECT user_id FROM items WHERE item_id = :itemId");
    $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
    $stmt->execute();
    $currentOwnerId = $stmt->fetchColumn();

    // Prevent the user from buying their own item
    if ($currentOwnerId == $currentUserId) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot buy your own item.']);
        exit();
    }

    // Update the item to mark it as purchased by the current user
    $updateStmt = $db->prepare("UPDATE items SET user_id = :newOwnerId WHERE item_id = :itemId");
    $updateStmt->bindParam(':newOwnerId', $currentUserId, PDO::PARAM_INT);
    $updateStmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

    if ($updateStmt->execute() && $updateStmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Purchase successful!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to process the purchase.']);
    }
}
?>
