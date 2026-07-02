<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Необхідно увійти']);
    exit();
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity'] ?? 1);

if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Помилка додавання в кошик']);
}
?>