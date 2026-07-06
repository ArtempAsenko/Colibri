<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Необхідно увійти']);
    exit();
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity'] ?? 1);

global $conn;

// Перевіряємо, чи вже є товар у кошику
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_quantity, $row['id']);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $_SESSION['user_id'], $product_id, $quantity);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['status' => 'success']);
?>