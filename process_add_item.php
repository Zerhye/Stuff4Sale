<?php
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    die(json_encode(['status' => 'error', 'message' => 'Access denied.']));
}

// Database connection
require 'db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id']; // Get user ID from session
    $itemName = trim($_POST['itemName']);
    $itemCategory = trim($_POST['itemCategory']);
    $itemPrice = trim($_POST['itemPrice']);
    $itemDescription = trim($_POST['itemDescription']);
    $uploadedFile = null;

    // Validate fields
    if (empty($itemName) || empty($itemCategory) || empty($itemPrice) || empty($itemDescription) || !is_numeric($itemPrice)) {
        die(json_encode(['status' => 'error', 'message' => 'All fields are required, and price must be a number.']));
    }

    // Handle file upload
    if (!empty($_FILES['itemImage']['name'])) {
        $uploadDir = 'uploads/';
        $uploadedFile = $uploadDir . basename($_FILES['itemImage']['name']);

        if (!move_uploaded_file($_FILES['itemImage']['tmp_name'], $uploadedFile)) {
            die(json_encode(['status' => 'error', 'message' => 'Failed to upload image.']));
        }
    } else {
        die(json_encode(['status' => 'error', 'message' => 'Image is required.']));
    }

    try {
        // Step 1: Check if category exists
        $stmt = $db->prepare("SELECT category_id FROM categories WHERE name = :categoryName");
        $stmt->bindParam(':categoryName', $itemCategory, PDO::PARAM_STR);
        $stmt->execute();
        $categoryId = $stmt->fetchColumn();

        // Step 2: If category doesn't exist, insert it
        if (!$categoryId) {
            $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:categoryName)");
            $stmt->bindParam(':categoryName', $itemCategory, PDO::PARAM_STR);
            $stmt->execute();
            $categoryId = $db->lastInsertId();
        }

        // Step 3: Insert item into the database (including description)
        $stmt = $db->prepare("INSERT INTO items (user_id, name, category_id, price, image_path, description, approval_status, reason, created_at) 
                              VALUES (:userId, :itemName, :categoryId, :itemPrice, :imagePath, :itemDescription, 'pending', NULL, NOW())");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':itemName', $itemName, PDO::PARAM_STR);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':itemPrice', $itemPrice, PDO::PARAM_STR);
        $stmt->bindParam(':imagePath', $uploadedFile, PDO::PARAM_STR);
        $stmt->bindParam(':itemDescription', $itemDescription, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Item added successfully and is pending approval.']);
        } else {
            throw new Exception('Failed to add item to the database.');
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
