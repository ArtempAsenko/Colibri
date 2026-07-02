<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function getProducts($filters = []) {
    global $conn;
    $sql = "SELECT DISTINCT p.* FROM products p 
            LEFT JOIN product_colors pc ON p.id = pc.product_id 
            LEFT JOIN product_sizes ps ON p.id = ps.product_id 
            WHERE p.is_active = 1";
    
    if (!empty($filters['color'])) {
        $color = sanitize($filters['color']);
        $sql .= " AND pc.color = '$color'";
    }
    if (!empty($filters['size'])) {
        $size = sanitize($filters['size']);
        $sql .= " AND ps.size = '$size'";
    }
    if (!empty($filters['occasion'])) {
        $occasion = sanitize($filters['occasion']);
        $sql .= " AND p.occasion LIKE '%$occasion%'";
    }
    if (!empty($filters['category'])) {
        $category = sanitize($filters['category']);
        $sql .= " AND p.category = '$category'";
    }
    if (!empty($filters['search'])) {
        $search = sanitize($filters['search']);
        $sql .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    return $conn->query($sql);
}

function getProductImages($product_id) {
    global $conn;
    $sql = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_main DESC, sort_order ASC";
    return $conn->query($sql);
}

function getMainImage($product_id) {
    global $conn;
    $sql = "SELECT image_path FROM product_images WHERE product_id = $product_id AND is_main = 1 LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['image_path'];
    }
    $sql = "SELECT image_path FROM product_images WHERE product_id = $product_id ORDER BY sort_order ASC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['image_path'];
    }
    return 'images/placeholder.jpg';
}

function uploadProductImage($product_id, $file, $is_main = false) {
    global $conn;
    
    $target_dir = "../uploads/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_name = time() . '_' . basename($file['name']);
    $target_file = $target_dir . $image_name;
    
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Дозволені тільки JPG, JPEG, PNG, GIF та WEBP'];
    }
    
    if ($file['size'] > 5000000) { 
        return ['success' => false, 'message' => 'Файл занадто великий (макс. 5MB)'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $image_path = 'uploads/products/' . $image_name;
        
        if ($is_main) {
            $conn->query("UPDATE product_images SET is_main = 0 WHERE product_id = $product_id");
        }
        
        $count = $conn->query("SELECT COUNT(*) as count FROM product_images WHERE product_id = $product_id")->fetch_assoc()['count'];
        if ($count == 0) {
            $is_main = true;
        }
        
        $sql = "INSERT INTO product_images (product_id, image_path, is_main) VALUES ($product_id, '$image_path', " . ($is_main ? 1 : 0) . ")";
        $conn->query($sql);
        
        return ['success' => true, 'image_path' => $image_path];
    }
    
    return ['success' => false, 'message' => 'Помилка завантаження файлу'];
}

function deleteProductImage($image_id) {
    global $conn;
    
    $result = $conn->query("SELECT image_path FROM product_images WHERE id = $image_id");
    if ($result->num_rows > 0) {
        $image = $result->fetch_assoc();
        $file_path = '../' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $conn->query("DELETE FROM product_images WHERE id = $image_id");
    return true;
}

function setMainImage($image_id, $product_id) {
    global $conn;
    $conn->query("UPDATE product_images SET is_main = 0 WHERE product_id = $product_id");
    $conn->query("UPDATE product_images SET is_main = 1 WHERE id = $image_id");
    return true;
}

function getProductSizes($product_id) {
    global $conn;
    $sql = "SELECT * FROM product_sizes WHERE product_id = $product_id ORDER BY size";
    return $conn->query($sql);
}

function saveProductSizes($product_id, $sizes) {
    global $conn;
    $conn->query("DELETE FROM product_sizes WHERE product_id = $product_id");
    
    if (!empty($sizes)) {
        foreach ($sizes as $size) {
            $size = sanitize($size);
            if (!empty($size)) {
                $conn->query("INSERT INTO product_sizes (product_id, size) VALUES ($product_id, '$size')");
            }
        }
    }
}

// Кольори товару
function getProductColors($product_id) {
    global $conn;
    $sql = "SELECT * FROM product_colors WHERE product_id = $product_id ORDER BY color";
    return $conn->query($sql);
}

function saveProductColors($product_id, $colors) {
    global $conn;
    $conn->query("DELETE FROM product_colors WHERE product_id = $product_id");
    
    if (!empty($colors)) {
        foreach ($colors as $color) {
            $color = sanitize($color);
            if (!empty($color)) {
                $conn->query("INSERT INTO product_colors (product_id, color) VALUES ($product_id, '$color')");
            }
        }
    }
}

// Отримання всіх доступних кольорів для фільтрів
function getFilterOptions($type) {
    global $conn;
    switch ($type) {
        case 'color':
            $sql = "SELECT DISTINCT color FROM product_colors ORDER BY color";
            break;
        case 'size':
            $sql = "SELECT DISTINCT size FROM product_sizes ORDER BY size";
            break;
        case 'occasion':
            $sql = "SELECT DISTINCT occasion FROM products WHERE is_active = 1 AND occasion IS NOT NULL AND occasion != '' ORDER BY occasion";
            break;
        case 'category':
            $sql = "SELECT DISTINCT category FROM products WHERE is_active = 1 AND category IS NOT NULL AND category != '' ORDER BY category";
            break;
        default:
            return false;
    }
    return $conn->query($sql);
}

// Вподобайки
function isFavorite($user_id, $product_id) {
    global $conn;
    $sql = "SELECT id FROM favorites WHERE user_id = $user_id AND product_id = $product_id";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

function toggleFavorite($user_id, $product_id) {
    global $conn;
    
    if (isFavorite($user_id, $product_id)) {
        $sql = "DELETE FROM favorites WHERE user_id = $user_id AND product_id = $product_id";
        $conn->query($sql);
        return 'removed';
    } else {
        $sql = "INSERT INTO favorites (user_id, product_id) VALUES ($user_id, $product_id)";
        $conn->query($sql);
        return 'added';
    }
}

function getFavorites($user_id) {
    global $conn;
    $sql = "SELECT p.*, f.created_at as favorite_date 
            FROM favorites f 
            JOIN products p ON f.product_id = p.id 
            WHERE f.user_id = $user_id 
            ORDER BY f.created_at DESC";
    return $conn->query($sql);
}

function getFavoritesCount($user_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = $user_id";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['count'];
}

function getFavoritesTotal($user_id) {
    global $conn;
    $sql = "SELECT SUM(p.price) as total 
            FROM favorites f 
            JOIN products p ON f.product_id = p.id 
            WHERE f.user_id = $user_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Функції для адмінки
function getAllProducts() {
    global $conn;
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    return $conn->query($sql);
}

function getProductById($id) {
    global $conn;
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

function deleteProduct($id) {
    global $conn;
    
    // Видаляємо зображення з сервера
    $images = getProductImages($id);
    while ($image = $images->fetch_assoc()) {
        $file_path = '../' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Видаляємо з бази даних
    $conn->query("DELETE FROM favorites WHERE product_id = $id");
    $conn->query("DELETE FROM product_images WHERE product_id = $id");
    $conn->query("DELETE FROM product_sizes WHERE product_id = $id");
    $conn->query("DELETE FROM product_colors WHERE product_id = $id");
    $conn->query("DELETE FROM products WHERE id = $id");
    
    return true;
}
function getColorHex($color_name) {
    $colors = [
        'Чорний' => '#333333',
        'Білий' => '#FFFFFF',
        'Червоний' => '#FF0000',
        'Синій' => '#0000FF',
        'Зелений' => '#00FF00',
        'Тіфані' => '#81D8D0',
        'Рожевий' => '#FFC0CB',
        'Бежевий' => '#F5F5DC',
        'Бірюзовий' => '#40E0D0',
        'Жовтий' => '#FFFF00',
        'Оранжевий' => '#FFA500',
        'Фіолетовий' => '#800080',
        'Сірий' => '#808080',
        'Коричневий' => '#8B4513',
    ];
    
    return $colors[$color_name] ?? '#81D8D0';
}
?>