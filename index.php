<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.html");
    exit();
}
require 'db.php';  // Include the database connection
require 'header.php'; // Assuming this includes session handling and any common HTML headers
?>

<script>
window.addEventListener('beforeunload', function () {
    // Send an AJAX request to end the session (logout)
    navigator.sendBeacon('logout.php');
});
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stuff4Sale - Home</title>
    <link rel="stylesheet" href="style.css">
    <script src="search.js" defer></script>
</head>
<body>
    <div class="container">
        <h2>Welcome to Stuff4Sale</h2>
        
        <input type="text" id="searchInput" placeholder="Search for items...">
        <button id="searchButton">Search</button>

        <div id="itemsList">
            <!-- Items will be dynamically inserted here by JavaScript -->
        </div>

        <!-- Admin and Staff Options: Toggle Content (without page reload) -->
        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <h3>Admin and Staff Options</h3>
            
            <!-- Admin or Staff Content -->
            <button id="toggleAdminContent">Toggle Admin/Staff Content</button>
            <div id="adminContent" style="display:none;">
                <h4>Admin/Staff Panel</h4>
                <p>Here are options for managing the site...</p>
                <!-- Example content for Admin/Staff -->
                <!-- You could display things like item approvals, user management, etc. -->
            </div>
        <?php endif; ?>

        <!-- All Users: Add Item Button -->
        <h3>Add Item</h3>
        <button id="addItemButton">Add New Item</button>
        
        <!-- Add Item Form (Initially Hidden) -->
        <div id="addItemForm" style="display:none;">
            <h4>Enter Item Details</h4>
            <form action="add_item.php" method="POST" enctype="multipart/form-data">
                <label for="itemName">Item Name:</label>
                <input type="text" id="itemName" name="itemName" required><br><br>
                
                <label for="itemCategory">Category:</label>
                <select id="itemCategory" name="itemCategory" required>
                    <option value="electronics">Electronics</option>
                    <option value="clothing">Clothing</option>
                    <option value="home">Home Goods</option>
                    <!-- Add more categories as needed -->
                </select><br><br>
                
                <label for="itemImage">Image:</label>
                <input type="file" id="itemImage" name="itemImage" accept="image/*" required><br><br>
                
                <label for="itemPrice">Price:</label>
                <input type="number" id="itemPrice" name="itemPrice" step="0.01" required><br><br>
                
                <label for="itemDescription">Description:</label>
                <textarea id="itemDescription" name="itemDescription" required></textarea><br><br>
                
                <button type="submit" name="submitItem">Submit Item</button>
            </form>
        </div>

        <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </div>

    <script>
        // Toggle Admin/Staff Content Display
        document.getElementById("toggleAdminContent").addEventListener("click", function() {
            var adminContent = document.getElementById("adminContent");
            if (adminContent.style.display === "none") {
                adminContent.style.display = "block";
            } else {
                adminContent.style.display = "none";
            }
        });

        // Show Add Item Form
        document.getElementById("addItemButton").addEventListener("click", function() {
            var addItemForm = document.getElementById("addItemForm");
            if (addItemForm.style.display === "none") {
                addItemForm.style.display = "block";
            } else {
                addItemForm.style.display = "none";
            }
        });
    </script>
</body>
</html>
