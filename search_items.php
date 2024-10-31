<?php
require 'db.php';

$query = $_GET['query'];
$stmt = $db->prepare("SELECT * FROM Items WHERE name LIKE ? AND status = 'approved'");
$stmt->execute(["%$query%"]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
?>
