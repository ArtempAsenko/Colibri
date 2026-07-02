<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Невірний ID товару']);
    exit();
}

$product_id = $_GET['product_id'];
$product = getProductById($product_id);

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Товар не знайдено']);
    exit();
}

// Отримуємо зображення
$images = [];
$images_result = getProductImages($product_id);
if ($images_result && $images_result->num_rows > 0) {
    while($image = $images_result->fetch_assoc()) {
        $images[] = $image['image_path'];
    }
}

// Якщо немає зображень, додаємо плейсхолдер
if (empty($images)) {
    $images[] = 'images/placeholder.jpg';
}

echo json_encode([
    'status' => 'success',
    'product' => [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'instagram_url' => $product['instagram_url']
    ],
    'images' => $images
]);
?>