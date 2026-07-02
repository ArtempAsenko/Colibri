<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Необхідно увійти']);
    exit();
}

$product_id = intval($_POST['product_id']);
$action = toggleFavorite($_SESSION['user_id'], $product_id);

echo json_encode([
    'status' => 'success',
    'action' => $action
]);
?>