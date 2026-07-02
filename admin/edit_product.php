<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('products.php');
}

$product_id = $_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    redirect('products.php');
}

$current_sizes = [];
$sizes_result = getProductSizes($product_id);
if ($sizes_result && $sizes_result->num_rows > 0) {
    while($row = $sizes_result->fetch_assoc()) {
        $current_sizes[] = $row['size'];
    }
}

$current_colors = [];
$colors_result = getProductColors($product_id);
if ($colors_result && $colors_result->num_rows > 0) {
    while($row = $colors_result->fetch_assoc()) {
        $current_colors[] = $row['color'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $occasion = sanitize($_POST['occasion']);
    $category = sanitize($_POST['category']);
    $instagram_url = sanitize($_POST['instagram_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || $price <= 0) {
        $error = "❌ Назва та ціна обов'язкові!";
    } else {
        $sql = "UPDATE products SET 
                name = '$name',
                description = '$description',
                price = $price,
                occasion = '$occasion',
                category = '$category',
                instagram_url = '$instagram_url',
                is_active = $is_active
                WHERE id = $product_id";
        
        if ($conn->query($sql)) {
            saveProductSizes($product_id, $_POST['sizes'] ?? []);
            
            // Збираємо всі кольори
            $colors = $_POST['colors'] ?? [];
            if (!empty($_POST['new_colors'])) {
                $new_colors = explode(',', $_POST['new_colors']);
                foreach ($new_colors as $color) {
                    $color = trim($color);
                    if (!empty($color)) {
                        $colors[] = $color;
                    }
                }
            }
            saveProductColors($product_id, $colors);
            
            // Завантаження нових зображень
            if (isset($_FILES['images']) && $_FILES['images']['error'][0] != 4) {
                $files = $_FILES['images'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] == 0) {
                        $file = [
                            'name' => $files['name'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'size' => $files['size'][$i]
                        ];
                        uploadProductImage($product_id, $file, false);
                    }
                }
            }
            
            // Видалення зображень
            if (isset($_POST['delete_images']) && !empty($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    if (!empty($image_id)) {
                        deleteProductImage(intval($image_id));
                    }
                }
            }
            
            if (isset($_POST['main_image'])) {
                setMainImage($_POST['main_image'], $product_id);
            }
            
            $success = "✅ Товар успішно оновлено!";
            
            // Оновлюємо дані
            $product = getProductById($product_id);
            $current_sizes = [];
            $sizes_result = getProductSizes($product_id);
            if ($sizes_result && $sizes_result->num_rows > 0) {
                while($row = $sizes_result->fetch_assoc()) {
                    $current_sizes[] = $row['size'];
                }
            }
            $current_colors = [];
            $colors_result = getProductColors($product_id);
            if ($colors_result && $colors_result->num_rows > 0) {
                while($row = $colors_result->fetch_assoc()) {
                    $current_colors[] = $row['color'];
                }
            }
        } else {
            $error = "❌ Помилка: " . $conn->error;
        }
    }
}

// Отримуємо всі кольори з бази для чекбоксів
$all_colors_from_db = [];
$all_colors_result = $conn->query("SELECT DISTINCT color FROM product_colors ORDER BY color");
if ($all_colors_result && $all_colors_result->num_rows > 0) {
    while($row = $all_colors_result->fetch_assoc()) {
        $all_colors_from_db[] = $row['color'];
    }
}

// Додаємо стандартні кольори, якщо їх немає
$standard_colors = ['Чорний', 'Білий', 'Червоний', 'Синій', 'Зелений', 'Тіфані', 'Рожевий', 'Бежевий', 'Бірюзовий', 'Жовтий', 'Фіолетовий', 'Сірий', 'Коричневий'];
$all_colors = array_unique(array_merge($all_colors_from_db, $standard_colors));
sort($all_colors);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати товар - COLIBRI UA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-hummingbird"></i> COLIBRI UA</h2>
            <ul class="admin-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Головна</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> Товари</a></li>
                <li><a href="add_product.php"><i class="fas fa-plus"></i> Додати товар</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Вийти</a></li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Редагувати товар #<?php echo $product['id']; ?></h1>
                <a href="products.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> До списку
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="edit-form">
                    <div class="form-group">
                        <label>Назва товару *</label>
                        <input type="text" name="name" required 
                               value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Опис</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ціна (грн) *</label>
                        <input type="number" name="price" required 
                               value="<?php echo $product['price'] ?? 0; ?>" 
                               step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Розміри</label>
                        <div class="checkbox-grid">
                            <?php
                            $all_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                            foreach ($all_sizes as $size) {
                                $checked = in_array($size, $current_sizes) ? 'checked' : '';
                                echo "<label class='checkbox-item'>";
                                echo "<input type='checkbox' name='sizes[]' value='$size' $checked> $size";
                                echo "</label>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Кольори</label>
                        <div class="checkbox-grid">
                            <?php
                            foreach ($all_colors as $color) {
                                $checked = in_array($color, $current_colors) ? 'checked' : '';
                                echo "<label class='checkbox-item'>";
                                echo "<input type='checkbox' name='colors[]' value='" . htmlspecialchars($color) . "' $checked> " . htmlspecialchars($color);
                                echo "</label>";
                            }
                            ?>
                        </div>
                        
                        <!-- Поле для додавання нових кольорів -->
                        <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 8px;">
                            <label style="font-size: 13px; color: #666;">Додати нові кольори (через кому):</label>
                            <input type="text" name="new_colors" 
                                   placeholder="Наприклад: М'ятний, Лавандовий, Кораловий"
                                   style="margin-top: 5px;">
                            <small style="color: #999;">Введіть назви кольорів через кому. Вони додадуться до вибраних вище.</small>
                        </div>
                        
                        <!-- Поточні нестандартні кольори -->
                        <?php 
                        $custom_colors = array_diff($current_colors, $all_colors);
                        if (!empty($custom_colors)): 
                        ?>
                        <div style="margin-top: 10px;">
                            <label style="font-size: 13px; color: #666;">Поточні кольори товару:</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                <?php foreach ($custom_colors as $color): ?>
                                    <span class="badge badge-color"><?php echo htmlspecialchars($color); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Для якого заходу</label>
                        <input type="text" name="occasion" 
                               value="<?php echo htmlspecialchars($product['occasion'] ?? ''); ?>"
                               placeholder="Вечірка, Робота, Прогулянка...">
                    </div>
                    
                    <div class="form-group">
                        <label>Категорія</label>
                        <select name="category">
                            <option value="">Виберіть категорію</option>
                            <?php
                            $categories = ['Сукні', 'Костюми', 'Сарафани', 'Блузи', 'Спортивний одяг', 'Верхній одяг', 'Пляжний одяг', 'Аксесуари'];
                            foreach ($categories as $cat) {
                                $selected = ($product['category'] ?? '') == $cat ? 'selected' : '';
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Посилання на Instagram</label>
                        <input type="text" name="instagram_url" 
                               value="<?php echo htmlspecialchars($product['instagram_url'] ?? 'https://www.instagram.com/colibriua'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" <?php echo ($product['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            Товар активний
                        </label>
                    </div>
                    
                    <!-- Поточні зображення -->
                    <?php 
                    $images = getProductImages($product_id);
                    if ($images && $images->num_rows > 0): 
                    ?>
                    <div class="form-group">
                        <label>Поточні зображення</label>
                        <div class="images-grid">
                            <?php while($image = $images->fetch_assoc()): ?>
                                <div class="image-item" id="image-<?php echo $image['id']; ?>">
                                    <img src="../<?php echo $image['image_path']; ?>" class="image-thumb">
                                    <button type="button" class="btn-delete-img" 
                                            onclick="deleteImage(<?php echo $image['id']; ?>)" 
                                            title="Видалити">×</button>
                                    <label class="radio-label">
                                        <input type="radio" name="main_image" value="<?php echo $image['id']; ?>"
                                            <?php echo $image['is_main'] ? 'checked' : ''; ?>>
                                        Головне
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div id="delete-images-container"></div>
                    
                    <div class="form-group">
                        <label>Додати нові зображення</label>
                        <input type="file" name="images[]" multiple accept="image/*">
                        <small>Можна вибрати декілька файлів</small>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Зберегти зміни
                        </button>
                        <a href="products.php" class="btn-back" style="text-align: center;">
                            <i class="fas fa-times"></i> Скасувати
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
    function deleteImage(imageId) {
        if (confirm('Видалити це зображення?')) {
            const imageItem = document.getElementById('image-' + imageId);
            if (imageItem) {
                imageItem.remove();
            }
            
            const container = document.getElementById('delete-images-container');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_images[]';
            input.value = imageId;
            container.appendChild(input);
        }
    }
    </script>
</body>
</html>