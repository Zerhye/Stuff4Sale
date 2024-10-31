<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.html");
    exit();
}
?>

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
        <h2>Welcome to the Stuff4Sale</h2>
        <input type="text" id="searchInput" placeholder="Search for items...">
        <button id="searchButton">Search</button>

        <div id="itemsList">
            <!-- Items will be dynamically inserted here by JavaScript -->
        </div>

        <!-- Admin and Staff Dashboard Links -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <h3>Admin Options</h3>
            <a href="admin_dashboard.php"><button>Admin Dashboard</button></a>
        <?php elseif ($_SESSION['role'] === 'staff'): ?>
            <h3>Staff Options</h3>
            <a href="staff_dashboard.php"><button>Staff Dashboard</button></a>
        <?php endif; ?>

        <footer>
            <p><a href="logout.php">Logout</a></p>
        </footer>
    </div>
</body>
</html>
