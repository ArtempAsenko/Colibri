<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Необхідно увійти']);
    exit();
}

$cart_id = intval($_POST['cart_id']);
$quantity = intval($_POST['quantity']);

if (updateCartQuantity($cart_id, $quantity)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Помилка оновлення']);
}
?>