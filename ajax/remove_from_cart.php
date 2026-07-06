<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Необхідно увійти']);
    exit();
}

$cart_id = intval($_POST['cart_id']);

global $conn;
$stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => 'success']);
?>