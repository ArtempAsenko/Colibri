<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Видалення товару
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    deleteProduct($_GET['delete']);
    $success = "Товар успішно видалено!";
}

$products = getAllProducts();
$total_products = $products->num_rows;
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товари - Адмін-панель COLIBRI UA</title>
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
                <h1><i class="fas fa-box"></i> Товари</h1>
                <a href="add_product.php" class="btn-add">
                    <i class="fas fa-plus"></i> Додати товар
                </a>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <span>Всього товарів:</span>
                    <strong><?php echo $total_products; ?></strong>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Назва</th>
                            <th>Ціна</th>
                            <th>Кольори</th>
                            <th>Розміри</th>
                            <th>Категорія</th>
                            <th>Захід</th>
                            <th>Статус</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products && $products->num_rows > 0): ?>
                            <?php while($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $product['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <?php if($product['description']): ?>
                                            <br><small style="color: #666;"><?php echo mb_substr(htmlspecialchars($product['description']), 0, 50); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($product['price'], 0, ',', ' '); ?> грн</td>
                                    <td>
                                        <?php 
                                        $colors = getProductColors($product['id']);
                                        if ($colors && $colors->num_rows > 0):
                                            while($color = $colors->fetch_assoc()):
                                        ?>
                                            <span class="badge badge-color"><?php echo htmlspecialchars($color['color']); ?></span>
                                        <?php endwhile; else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $sizes = getProductSizes($product['id']);
                                        if ($sizes && $sizes->num_rows > 0):
                                            while($size = $sizes->fetch_assoc()):
                                        ?>
                                            <span class="badge badge-size"><?php echo htmlspecialchars($size['size']); ?></span>
                                        <?php endwhile; else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['category'] ? htmlspecialchars($product['category']) : '-'; ?></td>
                                    <td><?php echo $product['occasion'] ? htmlspecialchars($product['occasion']) : '-'; ?></td>
                                    <td>
                                        <span class="badge-status <?php echo $product['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo $product['is_active'] ? 'Активний' : 'Неактивний'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-edit">
                                                <i class="fas fa-edit"></i> Ред.
                                            </a>
                                            <a href="?delete=<?php echo $product['id']; ?>" 
                                               class="btn-delete"
                                               onclick="return confirm('Ви впевнені, що хочете видалити товар #<?php echo $product['id']; ?>?')">
                                                <i class="fas fa-trash"></i> Вид.
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-box-open" style="font-size: 48px; color: #81D8D0; display: block; margin-bottom: 15px;"></i>
                                    <p style="color: #666; font-size: 16px;">Немає жодного товару</p>
                                    <a href="add_product.php" class="btn-add" style="margin-top: 15px; display: inline-block;">
                                        <i class="fas fa-plus"></i> Додати перший товар
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>