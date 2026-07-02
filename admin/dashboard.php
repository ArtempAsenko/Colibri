<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isAdmin()) {
    redirect('../index.php');
}

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_favorites = $conn->query("SELECT COUNT(*) as count FROM favorites")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель - COLIBRI UA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-hummingbird"></i> COLIBRI UA</h2>
            <ul class="admin-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Головна</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Товари</a></li>
                <li><a href="add_product.php"><i class="fas fa-plus"></i> Додати товар</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Вийти</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="welcome-message">
                <h1>Ласкаво просимо, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                <p>Ви увійшли як <strong>адміністратор</strong></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <h3>Всього товарів</h3>
                    <p class="stat-number"><?php echo $total_products; ?></p>
                </div>

                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Користувачів</h3>
                    <p class="stat-number"><?php echo $total_users; ?></p>
                </div>

                <div class="stat-card">
                    <i class="fas fa-heart"></i>
                    <h3>Вподобайок</h3>
                    <p class="stat-number"><?php echo $total_favorites; ?></p>
                </div>
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="products.php" class="btn-add">
                    <i class="fas fa-list"></i> Переглянути товари
                </a>
                <a href="add_product.php" class="btn-add">
                    <i class="fas fa-plus"></i> Додати товар
                </a>
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> На сайт
                </a>
            </div>
        </main>
    </div>
</body>
</html>