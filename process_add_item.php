<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    die("<script>alert('Access denied.'); window.location.href = 'welcome.html';</script>");
}

require 'db.php';

if (isset($_POST['submitItem'])) {
    $itemName = trim($_POST['itemName']);
    $itemCategory = trim($_POST['itemCategory']);
    $itemPrice = trim($_POST['itemPrice']);
    $itemDescription = trim($_POST['itemDescription']);
    $userId = $_SESSION['user_id'];

    if (!empty($_FILES['itemImage']['name'])) {
        $uploadDir = 'uploads/';
        $uploadedFile = $uploadDir . basename($_FILES['itemImage']['name']);

        if (move_uploaded_file($_FILES['itemImage']['tmp_name'], $uploadedFile)) {
            try {
                // Step 1: Check if the category exists
                $stmt = $db->prepare("SELECT category_id FROM categories WHERE name = :categoryName");
                $stmt->bindParam(':categoryName', $itemCategory, PDO::PARAM_STR);
                $stmt->execute();

                // Step 2: Get the category_id (fetch the first column)
                $categoryId = $stmt->fetchColumn();  // Ensure this fetches the correct category_id

                // Step 3: If the category doesn't exist, insert a new one
                if (!$categoryId) {
                    // Insert the new category into the categories table
                    $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:categoryName)");
                    $stmt->bindParam(':categoryName', $itemCategory, PDO::PARAM_STR);
                    if ($stmt->execute()) {
                        // Get the last inserted category_id
                        $categoryId = $db->lastInsertId();  // Fetch the newly inserted category_id

                        if (!$categoryId) {
                            die("<script>alert('Error: Failed to insert new category.'); window.location.href = 'index.php';</script>");
                        }
                    } else {
                        die("<script>alert('Error: Failed to insert new category.'); window.location.href = 'index.php';</script>");
                    }
                }

                $stmt = $db->prepare("INSERT INTO items (name, category_id, price, description, image_path, approval_status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$itemName, $categoryId, $itemPrice, $itemDescription, $uploadedFile, 'pending', $userId]);

                if ($stmt->rowCount() > 0) {
                    die("<script>alert('Item added successfully!'); window.location.href = 'index.php';</script>");
                } else {
                    die("<script>alert('Error: Could not add item. Please try again.'); window.location.href = 'index.php';</script>");
                }
            } catch (Exception $e) {
                die("<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'index.php';</script>");
            }
        } else {
            die("<script>alert('Error: Failed to upload image.'); window.location.href = 'index.php';</script>");
        }
    } else {
        die("<script>alert('Error: No image provided.'); window.location.href = 'index.php';</script>");
    }
} else {
    die("<script>alert('Invalid request.'); window.location.href = 'index.php';</script>");
}
?>
