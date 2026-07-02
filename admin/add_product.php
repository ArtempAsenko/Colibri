<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $occasion = sanitize($_POST['occasion']);
    $category = sanitize($_POST['category']);
    $instagram_url = sanitize($_POST['instagram_url']);
    
    if (empty($name) || $price <= 0) {
        $error = "❌ Назва та ціна обов'язкові!";
    } else {
        $sql = "INSERT INTO products (name, description, price, occasion, category, instagram_url) 
                VALUES ('$name', '$description', $price, '$occasion', '$category', '$instagram_url')";
        
        if ($conn->query($sql)) {
            $product_id = $conn->insert_id;
            
            // Зберігаємо розміри
            if (isset($_POST['sizes'])) {
                saveProductSizes($product_id, $_POST['sizes']);
            }
            
            // Збираємо всі кольори (вибрані + нові)
            $colors = [];
            if (isset($_POST['colors'])) {
                $colors = $_POST['colors'];
            }
            // Додаємо нові кольори з текстового поля
            if (!empty($_POST['new_colors'])) {
                $new_colors = explode(',', $_POST['new_colors']);
                foreach ($new_colors as $color) {
                    $color = trim($color);
                    if (!empty($color)) {
                        $colors[] = $color;
                    }
                }
            }
            if (!empty($colors)) {
                saveProductColors($product_id, $colors);
            }
            
            // Завантажуємо зображення
            if (isset($_FILES['images'])) {
                $files = $_FILES['images'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] == 0) {
                        $file = [
                            'name' => $files['name'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'size' => $files['size'][$i]
                        ];
                        $is_main = ($i == 0);
                        uploadProductImage($product_id, $file, $is_main);
                    }
                }
            }
            
            $success = "✅ Товар успішно додано! <a href='products.php'>Переглянути</a>";
        } else {
            $error = "❌ Помилка: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати товар - COLIBRI UA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-hummingbird"></i> COLIBRI UA</h2>
            <ul class="admin-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Головна</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Товари</a></li>
                <li><a href="add_product.php" class="active"><i class="fas fa-plus"></i> Додати товар</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Вийти</a></li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-plus"></i> Додати новий товар</h1>
                <a href="products.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> До списку
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Назва товару *</label>
                        <input type="text" name="name" required placeholder="Введіть назву товару">
                    </div>
                    
                    <div class="form-group">
                        <label>Опис</label>
                        <textarea name="description" rows="4" placeholder="Опишіть товар..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ціна (грн) *</label>
                        <input type="number" name="price" required step="0.01" min="0" placeholder="2500">
                    </div>
                    
                    <div class="form-group">
                        <label>Розміри</label>
                        <div class="checkbox-grid">
                            <?php
                            $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                            foreach ($sizes as $size) {
                                echo "<label class='checkbox-item'>";
                                echo "<input type='checkbox' name='sizes[]' value='$size'> $size";
                                echo "</label>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Кольори</label>
                        <div class="checkbox-grid">
                            <?php
                            $colors = ['Чорний', 'Білий', 'Червоний', 'Синій', 'Зелений', 'Тіфані', 'Рожевий', 'Бежевий', 'Бірюзовий', 'Жовтий', 'Фіолетовий', 'Сірий', 'Коричневий'];
                            foreach ($colors as $color) {
                                echo "<label class='checkbox-item'>";
                                echo "<input type='checkbox' name='colors[]' value='$color'> $color";
                                echo "</label>";
                            }
                            ?>
                        </div>
                        
                        <!-- Поле для додавання нових кольорів -->
                        <div style="margin-top: 10px;">
                            <label style="font-size: 13px; color: #666;">Або додайте свої кольори через кому:</label>
                            <input type="text" name="new_colors" 
                                   placeholder="Наприклад: М'ятний, Лавандовий, Кораловий"
                                   style="margin-top: 5px;">
                            <small style="color: #999;">Введіть назви кольорів через кому</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Для якого заходу</label>
                        <input type="text" name="occasion" placeholder="Вечірка, Робота, Прогулянка...">
                    </div>
                    
                    <div class="form-group">
                        <label>Категорія</label>
                        <select name="category">
                            <option value="">Виберіть категорію</option>
                            <option>Сукні</option>
                            <option>Костюми</option>
                            <option>Сарафани</option>
                            <option>Блузи</option>
                            <option>Спортивний одяг</option>
                            <option>Верхній одяг</option>
                            <option>Пляжний одяг</option>
                            <option>Аксесуари</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Посилання на Instagram</label>
                        <input type="text" name="instagram_url" value="https://www.instagram.com/colibriua">
                    </div>
                    
                    <div class="form-group">
                        <label>Зображення товару</label>
                        <input type="file" name="images[]" multiple accept="image/*">
                        <small>Можна вибрати декілька файлів. Перше зображення буде головним.</small>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i> Додати товар
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>