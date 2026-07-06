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
                    
                    <!-- Вподобайки -->
                    <li>
                        <a href="favorites.php" class="nav-favorites">
                            <span class="heart-icon"><i class="fas fa-heart"></i></span>
                            <span>Вподобайки</span>
                            <span class="fav-badge" id="fav-count" style="display: none;">0</span>
                        </a>
                    </li>
                    
                    <!-- Кошик (додано) -->
                    <li>
                        <a href="cart.php" class="nav-cart">
                            <span class="cart-icon"><i class="fas fa-shopping-cart"></i></span>
                            <span>Кошик</span>
                            <span class="cart-badge" id="cart-count" style="display: none;">0</span>
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

    <!-- СКРИПТ ДЛЯ ЛІЧИЛЬНИКІВ (додано в header) -->
    <script>
    // ========== ЛІЧИЛЬНИК ВПОДОБАЙОК (localStorage) ==========
    function getFavorites() {
        const favs = localStorage.getItem('colibriua_favorites');
        return favs ? JSON.parse(favs) : [];
    }

    function updateFavoritesBadge() {
        const favs = getFavorites();
        const badge = document.getElementById('fav-count');
        if (badge) {
            if (favs.length > 0) {
                badge.textContent = favs.length;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Оновлюємо лічильник вподобайок при завантаженні
    document.addEventListener('DOMContentLoaded', function() {
        updateFavoritesBadge();
    });

    // ========== ЛІЧИЛЬНИК КОШИКА (серверний) ==========
    function updateCartBadge() {
        <?php if (isLoggedIn()): ?>
        fetch('ajax/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('cart-count');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(err => console.error('Помилка лічильника кошика:', err));
        <?php else: ?>
        const badge = document.getElementById('cart-count');
        if (badge) badge.style.display = 'none';
        <?php endif; ?>
    }

    // Оновлюємо лічильник кошика при завантаженні
    document.addEventListener('DOMContentLoaded', function() {
        updateCartBadge();
    });
    </script>