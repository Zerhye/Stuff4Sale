<?php
require 'db.php';

// Test the connection
try {
    $db->query("SELECT 1");
    echo "Database connection is successful.";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>