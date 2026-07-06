<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit();
}

global $conn;
$stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['count'] ?? 0]);
?>