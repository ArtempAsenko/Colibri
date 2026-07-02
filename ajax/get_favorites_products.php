<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_POST['ids']) || empty($_POST['ids'])) {
    echo json_encode(['status' => 'error', 'message' => 'Немає ID']);
    exit();
}

$ids = explode(',', $_POST['ids']);
$ids = array_map('intval', $ids);
$ids = array_filter($ids);
$ids_string = implode(',', $ids);

$sql = "SELECT p.*, 
        (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as image,
        (SELECT GROUP_CONCAT(color SEPARATOR ',') FROM product_colors WHERE product_id = p.id) as colors,
        (SELECT GROUP_CONCAT(size SEPARATOR ',') FROM product_sizes WHERE product_id = p.id) as sizes
        FROM products p 
        WHERE p.id IN ($ids_string) AND p.is_active = 1";

$result = $conn->query($sql);
$products = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode(['status' => 'success', 'products' => $products]);
?>