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
        function confirmAction(action, username) {
            return confirm(`Are you sure you want to ${action} ${username}?`);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Welcome to Stuff4Sale</h2>
       
        <!-- Search Bar -->
        <input type="text" id="searchInput" placeholder="Search for items...">
        <button id="searchButton">Search</button>

        <div id="itemsList">
            <!-- Items will be dynamically inserted here by PHP -->
            <?php foreach ($items as $item): ?>
                <div class="item">
                    <img src="<?php echo $item['image']; ?>" alt="Item Image">
                    <h3><?php echo $item['name']; ?></h3>
                    <p>Category: <?php echo $item['category']; ?></p>
                    <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                    <p>Description: <?php echo $item['description']; ?></p>
                    
                    <!-- Show pending status for staff/admin -->
                    <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
                        <p>Status: <?php echo $item['approval_status']; ?></p>
                        <?php if ($item['approval_status'] === 'pending'): ?>
                            <form action="approve_item.php" method="POST">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="approve">Approve</button>
                                <button type="submit" name="reject">Reject</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
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

        <!-- Role-Based Panels -->
        <?php if ($userRole === 'staff' || $userRole === 'admin'): ?>
            <div class="staff-panel">
                <h3>Staff Panel</h3>
                <button id="reviewItemsButton">Review Items</button>
                <div id="staffContent" style="display:none;">
                    <h4>Review Items</h4>
                    <?php foreach ($items as $item): ?>
                        <div class="item">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Category: <?php echo htmlspecialchars($item['category']); ?></p>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                            <p>Description: <?php echo htmlspecialchars($item['description']); ?></p>
                            <p>Status: <?php echo htmlspecialchars($item['approval_status']); ?></p>
                            <?php if ($item['approval_status'] === 'pending'): ?>
                                <form action="approve_item.php" method="POST">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="approve">Approve</button>
                                    <button type="submit" name="reject">Reject</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Customer Panel -->
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
            <form id="addItemFormElement" method="POST" action ="process_add_item.php" enctype="multipart/form-data">
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

        <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </div>

    <script>
        // Toggle Admin Content
        const adminButton = document.getElementById("manageUsersButton");
        if (adminButton) {
            adminButton.addEventListener("click", function() {
                const adminContent = document.getElementById("manageUsersContent");
                adminContent.style.display = adminContent.style.display === "none" ? "block" : "none";
            });
        }

        // Toggle Add Item Form
        const addItemButton = document.getElementById("addItemButton");
        if (addItemButton) {
            addItemButton.addEventListener("click", function() {
                const addItemForm = document.getElementById("addItemForm");
                addItemForm.style.display = addItemForm.style.display === "none" ? "block" : "none";
            });
        }
    </script>
</body>
</html>
