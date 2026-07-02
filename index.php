<?php
require_once 'includes/functions.php';

$filters = [
    'color' => $_GET['color'] ?? '',
    'size' => $_GET['size'] ?? '',
    'occasion' => $_GET['occasion'] ?? '',
    'category' => $_GET['category'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$products = getProducts($filters);
$colors = getFilterOptions('color');
$sizes = getFilterOptions('size');
$occasions = getFilterOptions('occasion');
$categories = getFilterOptions('category');
?>

<?php include 'includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>COLIBRI UA</h1>
        <p>Ексклюзивний одяг для особливих моментів</p>
        <a href="#catalog" class="btn-primary">Переглянути каталог</a>
    </div>
</section>

<section class="filters-section" id="catalog">
    <div class="container">
        <h2>Каталог товарів</h2>
        
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="🔍 Пошук товарів..." 
                       value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
            
            <div class="filter-group">
                <select name="category">
                    <option value="">Всі категорії</option>
                    <?php if ($categories && $categories->num_rows > 0): ?>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                <?php echo $filters['category'] == $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="color">
                    <option value="">Всі кольори</option>
                    <?php if ($colors && $colors->num_rows > 0): ?>
                        <?php while($color = $colors->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($color['color']); ?>" 
                                <?php echo $filters['color'] == $color['color'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($color['color']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="size">
                    <option value="">Всі розміри</option>
                    <?php if ($sizes && $sizes->num_rows > 0): ?>
                        <?php while($size = $sizes->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($size['size']); ?>"
                                <?php echo $filters['size'] == $size['size'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($size['size']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="occasion">
                    <option value="">Будь-який захід</option>
                    <?php if ($occasions && $occasions->num_rows > 0): ?>
                        <?php while($occasion = $occasions->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($occasion['occasion']); ?>"
                                <?php echo $filters['occasion'] == $occasion['occasion'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($occasion['occasion']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Фільтрувати
            </button>
            <a href="index.php" class="btn-reset">
                <i class="fas fa-times"></i> Скинути
            </a>
        </form>
    </div>
</section>

<section class="products-section">
    <div class="container">
        <?php if ($products && $products->num_rows > 0): ?>
            <p class="products-count">Знайдено товарів: <?php echo $products->num_rows; ?></p>
            
            <div class="products-grid">
                <?php while($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image" onclick="openGallery(<?php echo $product['id']; ?>)">
                            <?php 
                            $mainImage = getMainImage($product['id']);
                            ?>
                            <img src="<?php echo $mainImage; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='images/placeholder.jpg'">
                            
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                                <span>Переглянути фото</span>
                            </div>
                            
                            <!-- Вподобайки доступні всім -->
                            <button class="favorite-btn"
                                    onclick="event.stopPropagation(); toggleFavorite(<?php echo $product['id']; ?>, this)"
                                    title="Додати в вподобайки">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            
                            <?php if($product['description']): ?>
                                <p class="product-desc">
                                    <?php echo mb_substr(htmlspecialchars($product['description']), 0, 80); ?>
                                    <?php echo mb_strlen($product['description']) > 80 ? '...' : ''; ?>
                                </p>
                            <?php endif; ?>
                            
                            <p class="product-price">
                                <?php echo number_format($product['price'], 0, ',', ' '); ?> грн
                            </p>
                            
                            <?php
                            $productColors = getProductColors($product['id']);
                            $productSizes = getProductSizes($product['id']);
                            ?>
                            
                            <div class="product-details">
                                <?php if($product['occasion']): ?>
                                    <span class="badge badge-occasion">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo htmlspecialchars($product['occasion']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($productSizes && $productSizes->num_rows > 0): ?>
                                    <?php while($size = $productSizes->fetch_assoc()): ?>
                                        <span class="badge badge-size">
                                            <?php echo htmlspecialchars($size['size']); ?>
                                        </span>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                                
                                <?php if ($productColors && $productColors->num_rows > 0): ?>
                                    <?php while($color = $productColors->fetch_assoc()): ?>
                                        <span class="badge badge-color">
                                            <i class="fas fa-circle" style="color: <?php echo getColorHex($color['color']); ?>; font-size: 10px;"></i>
                                            <?php echo htmlspecialchars($color['color']); ?>
                                        </span>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                            
                            <a href="<?php echo $product['instagram_url'] ?: 'https://www.instagram.com/colibriua'; ?>" 
                               target="_blank" 
                               class="btn-instagram"
                               onclick="event.stopPropagation();">
                                <i class="fab fa-instagram"></i> Замовити в Instagram
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-search" style="font-size: 64px; color: #81D8D0; margin-bottom: 20px;"></i>
                <h3>Товари не знайдено</h3>
                <p>Спробуйте змінити параметри пошуку або фільтри</p>
                <a href="index.php" class="btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-sync"></i> Показати всі товари
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Модальне вікно для фото -->
<div class="modal" id="gallery-modal">
    <div class="modal-overlay" onclick="closeGallery()"></div>
    <div class="modal-content">
        <button class="modal-close" onclick="closeGallery()">
            <i class="fas fa-times"></i>
        </button>
        <div class="gallery-container">
            <div class="gallery-main">
                <button class="gallery-nav gallery-prev" onclick="changeImage(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <img id="gallery-main-image" src="" alt="">
                <button class="gallery-nav gallery-next" onclick="changeImage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="gallery-counter">
                    <span id="gallery-current">1</span> / <span id="gallery-total">1</span>
                </div>
            </div>
            <div class="gallery-thumbnails" id="gallery-thumbnails"></div>
            <div class="gallery-product-info" id="gallery-product-info"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Зберігання вподобайок в localStorage
function getFavorites() {
    const favs = localStorage.getItem('colibriua_favorites');
    return favs ? JSON.parse(favs) : [];
}

function saveFavorites(favs) {
    localStorage.setItem('colibriua_favorites', JSON.stringify(favs));
}

function isFavorite(productId) {
    const favs = getFavorites();
    return favs.includes(productId);
}

function toggleFavorite(productId, button) {
    button.style.transform = 'scale(1.3)';
    setTimeout(() => { button.style.transform = 'scale(1)'; }, 200);
    
    let favs = getFavorites();
    const icon = button.querySelector('i');
    
    if (favs.includes(productId)) {
        favs = favs.filter(id => id !== productId);
        button.classList.remove('active');
        icon.className = 'far fa-heart';
        button.title = 'Додати в вподобайки';
        showNotification('Видалено з вподобайок', 'info');
    } else {
        favs.push(productId);
        button.classList.add('active');
        icon.className = 'fas fa-heart';
        button.title = 'Видалити з вподобайок';
        showNotification('Додано в вподобайки ❤️', 'success');
    }
    
    saveFavorites(favs);
}

// Позначаємо вподобані товари при завантаженні
document.addEventListener('DOMContentLoaded', function() {
    const favs = getFavorites();
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const productId = parseInt(btn.getAttribute('onclick').match(/\d+/)[0]);
        if (favs.includes(productId)) {
            btn.classList.add('active');
            btn.querySelector('i').className = 'fas fa-heart';
        }
    });
});

// Галерея
const productsData = {};

function openGallery(productId) {
    if (productsData[productId]) {
        showGallery(productsData[productId]);
        return;
    }
    
    fetch('ajax/get_product_images.php?product_id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                productsData[productId] = data;
                showGallery(data);
            }
        });
}

let currentGalleryImages = [];
let currentImageIndex = 0;

function showGallery(data) {
    currentGalleryImages = data.images;
    currentImageIndex = 0;
    updateMainImage();
    
    const thumbnailsContainer = document.getElementById('gallery-thumbnails');
    thumbnailsContainer.innerHTML = '';
    
    currentGalleryImages.forEach((image, index) => {
        const thumb = document.createElement('img');
        thumb.src = image;
        thumb.className = 'gallery-thumb' + (index === 0 ? ' active' : '');
        thumb.onclick = () => { currentImageIndex = index; updateMainImage(); };
        thumbnailsContainer.appendChild(thumb);
    });
    
    document.getElementById('gallery-product-info').innerHTML = `
        <h3>${data.product.name}</h3>
        <p class="gallery-price">${new Intl.NumberFormat('uk-UA').format(data.product.price)} грн</p>
        <a href="${data.product.instagram_url || 'https://www.instagram.com/colibriua'}" 
           target="_blank" class="btn-instagram" style="display: inline-block; width: auto; padding: 10px 30px;">
            <i class="fab fa-instagram"></i> Замовити в Instagram
        </a>
    `;
    
    document.getElementById('gallery-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function updateMainImage() {
    document.getElementById('gallery-main-image').src = currentGalleryImages[currentImageIndex];
    document.getElementById('gallery-current').textContent = currentImageIndex + 1;
    document.getElementById('gallery-total').textContent = currentGalleryImages.length;
    document.querySelectorAll('.gallery-thumb').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentImageIndex);
    });
}

function changeImage(dir) {
    currentImageIndex += dir;
    if (currentImageIndex < 0) currentImageIndex = currentGalleryImages.length - 1;
    if (currentImageIndex >= currentGalleryImages.length) currentImageIndex = 0;
    updateMainImage();
}

function closeGallery() {
    document.getElementById('gallery-modal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeGallery();
    if (document.getElementById('gallery-modal').classList.contains('active')) {
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
    }
});

function showNotification(message, type) {
    const old = document.querySelector('.notification');
    if (old) old.remove();
    
    const n = document.createElement('div');
    n.className = 'notification';
    n.textContent = message;
    n.style.cssText = `
        position: fixed; bottom: 20px; right: 20px;
        background: ${type === 'success' ? '#81D8D0' : type === 'error' ? '#ff4757' : '#333'};
        color: white; padding: 15px 25px; border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2); z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    document.body.appendChild(n);
    
    setTimeout(() => {
        n.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => n.remove(), 300);
    }, 3000);
}
</script>

</body>
</html>