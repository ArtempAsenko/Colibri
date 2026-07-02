<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COLIBRI UA - Instagram Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-main">
            <a href="index.php" class="logo">
                <img src="images/logo.jpg" alt="COLIBRI UA" class="logo-img">
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Головна</a></li>
                    <li><a href="index.php#catalog">Каталог</a></li>
                    <li><a href="https://www.instagram.com/colibriua" target="_blank">
                        <i class="fab fa-instagram"></i> Instagram
                    </a></li>
                    <li>
                        <a href="favorites.php" class="nav-favorites">
                            <span class="heart-icon"><i class="fas fa-heart"></i></span>
                            <span>Вподобайки</span>
                        </a>
                    </li>
                    
                    <?php if ($is_admin): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">
                            <i class="fas fa-user-shield"></i> Адмін
                        </a></li>
                        <li><a href="admin/logout.php" style="color: #666;">
                            <i class="fas fa-sign-out-alt"></i> Вийти
                        </a></li>
                    <?php else: ?>
                        <li><a href="admin/login.php" class="admin-link">
                            <i class="fas fa-user-shield"></i> Вхід
                        </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>