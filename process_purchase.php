<?php
session_start();
require 'db.php'; // Include database connection

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
$itemId = isset($data['item_id']) ? (int)$data['item_id'] : 0;
$buyerId = isset($data['buyer_id']) ? (int)$data['buyer_id'] : 0;

// Check if item ID and buyer ID are valid
if ($itemId > 0 && $buyerId > 0) {
    try {
        // Start transaction
        $db->beginTransaction();

        // Check if the item is already owned
        $stmt = $db->prepare("SELECT user_id FROM items WHERE item_id = :item_id");
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $ownerId = $stmt->fetchColumn();

        if ($ownerId == $buyerId) {
            echo json_encode(['status' => 'error', 'message' => 'You already own this item.']);
            exit();
        }

        // Update the item's owner to the buyer
        $updateStmt = $db->prepare("UPDATE items SET user_id = :buyer_id WHERE item_id = :item_id");
        $updateStmt->bindParam(':buyer_id', $buyerId, PDO::PARAM_INT);
        $updateStmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $updateStmt->execute();

        // Insert the transaction into the transactions table
        $transactionStmt = $db->prepare("INSERT INTO transactions (buyer_id, item_id) VALUES (:buyer_id, :item_id)");
        $transactionStmt->bindParam(':buyer_id', $buyerId, PDO::PARAM_INT);
        $transactionStmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $transactionStmt->execute();

        // Commit the transaction
        $db->commit();

        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'Item purchased successfully!']);
    } catch (PDOException $e) {
        // Rollback on error
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid item or user ID.']);
}
?>
