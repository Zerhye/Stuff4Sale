<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.html");
    exit();
}

require 'db.php';  // Include your database connection
require 'header.php'; // Assuming this includes session handling and common HTML headers

// Fetch user role and other necessary data
$userRole = $_SESSION['role']; // Assume 'role' is stored in session during login
$currentUserId = $_SESSION['user_id']; // Unique ID for the logged-in user

// Fetch items from the database based on user role
if ($userRole === 'customer') {
    $stmt = $db->query("SELECT * FROM items WHERE approval_status = 'approved'");
} else {
    // Show all items to admin/staff, including those pending approval
    $stmt = $db->query("SELECT * FROM items");
}
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users from the database (for admins only)
if ($userRole === 'admin') {
    $userStmt = $db->query("SELECT * FROM users");
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include user management logic for admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userRole === 'admin') {
        include 'manage_user.php'; // Logic to handle user actions like promoting, demoting, deleting
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stuff4Sale - Home</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Confirmation for admin actions
        function confirmAction(action, targetName) {
            return confirm(`Are you sure you want to ${action} ${targetName}?`);
        }
        
        function toggleVisibility(buttonId, contentId) {
            const button = document.getElementById(buttonId);
            const content = document.getElementById(contentId);
            if (button) {
                button.addEventListener('click', function () {
                    content.style.display = content.style.display === 'none' ? 'block' : 'none';
                });
            }
        }
        // Toggle Visibility for Admin or Staff Panels
        document.addEventListener('DOMContentLoaded', function () {
            const adminButton = document.getElementById("manageUsersButton");
            if (adminButton) {
                adminButton.addEventListener("click", function() {
                    const adminContent = document.getElementById("manageUsersContent");
                    adminContent.style.display = adminContent.style.display === "none" ? "block" : "none";
                });
            }

            // Handle AJAX submission for user management forms
            const userManagementForms = document.querySelectorAll('.user-management-form');
            userManagementForms.forEach(form => {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault(); // Prevent the default form submission
                    
                    const formData = new FormData(this);
                    try {
                        const response = await fetch('manage_user.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.text(); // Expect plain text message
                        
                        alert(result); // Display response message

                        // Optionally reload the page to reflect changes
                        location.reload(); // Remove if you don't want the page to reload
                    } catch (error) {
                        alert('An error occurred: ' + error.message);
                    }
                });
            });

            // Add Event Listener to Form for AJAX Submission
            const addItemForm = document.getElementById('addItemFormElement');
            if (addItemForm) {
                addItemForm.addEventListener('submit', async function (e) {
                    e.preventDefault(); // Prevent the default form submission
                    const formData = new FormData(this);

                    try {
                        const response = await fetch('process_add_item.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();

                        if (result.status === 'success') {
                            alert(result.message);
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert(result.message);
                        }
                    } catch (error) {
                        alert('An error occurred: ' + error.message);
                    }
                });
            }
        });
                // Search Items
                document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const itemsList = document.getElementById('itemsList');

            if (searchButton) {
                searchButton.addEventListener('click', function () {
                    const query = searchInput.value.toLowerCase();
                    const items = itemsList.getElementsByClassName('item');

                    Array.from(items).forEach(item => {
                        const itemName = item.querySelector('h3').textContent.toLowerCase();
                        if (itemName.includes(query)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });

        
    </script>
</head>
<body>
    <div class="container">
        <h2>Welcome to Stuff4Sale</h2>

        <!-- Search Bar -->
        <input type="text" id="searchInput" placeholder="Search for items...">
        <button id="searchButton">Search</button>

        <!-- Items Listing -->
        <div id="itemsList">
            <!-- Items will be dynamically inserted here by PHP -->
            <?php foreach ($items as $item): ?>
                <?php
                // Fetch the category name for the current item based on category_id
                $categoryStmt = $db->prepare("SELECT name FROM categories WHERE category_id = :categoryId");
                $categoryStmt->bindParam(':categoryId', $item['category_id'], PDO::PARAM_INT);
                $categoryStmt->execute();
                $categoryName = $categoryStmt->fetchColumn();
                ?>
                <div class="item">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Item Image">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p>Category: <?php echo htmlspecialchars($categoryName); ?></p>
                    <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                    <p>Description: <?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
                        <p>Status: <?php echo htmlspecialchars($item['approval_status']); ?></p>
                        <?php if ($item['approval_status'] === 'pending'): ?>
                            <form action="approve_item.php" method="POST" onsubmit="return confirmAction('approve', '<?php echo htmlspecialchars($item['name']); ?>');">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <label for="reason">Reason for Approval:</label>
                                <textarea id="reason" name="reason" required></textarea>
                                <button type="submit">Approve</button>
                            </form>
                            <form action="approve_item.php" method="POST" onsubmit="return confirmAction('reject', '<?php echo htmlspecialchars($item['name']); ?>');">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <label for="reason">Reason for Rejection:</label>
                                <textarea id="reason" name="reason" required></textarea>
                                <button type="submit">Reject</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Role-Based Panels -->
        <?php if ($userRole === 'customer'): ?>
            <div class="customer-panel">
                <h3>Your Actions</h3>
                <button id="addItemButton">Add New Item</button>
                <button id="viewMyItemsButton">My Items</button>
            </div>
        <?php endif; ?>

        <!-- Add Item Form -->
        <div id="addItemForm" style="display:none;">
            <h4>Enter Item Details</h4>
            <form id="addItemFormElement" method="POST" enctype="multipart/form-data">
                <label for="itemName">Item Name:</label>
                <input type="text" id="itemName" name="itemName" required><br><br>
                
                <label for="itemCategory">Category:</label>
                <select id="itemCategory" name="itemCategory" required>
                    <option value="electronics">Electronics</option>
                    <option value="clothing">Clothing</option>
                    <option value="home">Home Goods</option>
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

        <!-- Admin Panel -->
        <?php if ($userRole === 'admin'): ?>
            <div class="admin-panel">
                <h3>Admin Panel</h3>
                <button id="manageUsersButton">Manage Users</button>
                <div id="manageUsersContent" style="display:none;">
                    <h4>Manage Users</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <?php if ($user['user_id'] !== $currentUserId): // Prevent admins from modifying themselves ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td>
                                            <!-- Allow actions only if the target user is not an admin -->
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <form action="manage_user.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <?php if ($user['role'] === 'staff'): ?>
                                                        <button type="submit" name="demote" onclick="return confirmAction('demote', '<?php echo htmlspecialchars($user['username']); ?>')">Demote to Customer</button>
                                                    <?php else: ?>
                                                        <button type="submit" name="promote" onclick="return confirmAction('promote', '<?php echo htmlspecialchars($user['username']); ?>')">Promote to Staff</button>
                                                    <?php endif; ?>
                                                    <button type="submit" name="delete" onclick="return confirmAction('delete', '<?php echo htmlspecialchars($user['username']); ?>')">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span>No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </div>

    <script>
        toggleVisibility('addItemButton', 'addItemForm');
    </script>
</body>
</html>
