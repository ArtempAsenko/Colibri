<?php 
require_once 'includes/functions.php'; 
include 'includes/header.php';
?>

<section class="favorites-section">
    <div class="container">
        <h2><i class="fas fa-heart" style="color: #81D8D0;"></i> Мої вподобайки</h2>
        
        <div id="favorites-container">
            <p style="text-align: center; color: #666;">Завантаження...</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function loadFavorites() {
    const favs = JSON.parse(localStorage.getItem('colibriua_favorites') || '[]');
    const container = document.getElementById('favorites-container');
    
    if (favs.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-heart-broken" style="font-size: 64px; color: #81D8D0;"></i>
                <h3>У вас немає вподобаних товарів</h3>
                <p>Додавайте товари в улюблені, натискаючи на сердечко ❤️</p>
                <a href="index.php" class="btn-primary">Перейти до каталогу</a>
            </div>
        `;
        return;
    }
    
    // Завантажуємо товари через AJAX
    fetch('ajax/get_favorites_products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ids=' + favs.join(',')
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.products.length > 0) {
            let totalPrice = 0;
            let html = `
                <div class="favorites-summary">
                    <div class="summary-card">
                        <i class="fas fa-heart"></i>
                        <span>Вподобано: <strong>${data.products.length}</strong></span>
                    </div>
                    <div class="summary-card total">
                        <i class="fas fa-calculator"></i>
                        <span>Сума: <strong id="total-price">0 грн</strong></span>
                    </div>
                </div>
                <div class="products-grid">
            `;
            
            data.products.forEach(product => {
                totalPrice += parseFloat(product.price);
                const colors = product.colors ? product.colors.split(',') : [];
                const sizes = product.sizes ? product.sizes.split(',') : [];
                
                html += `
                    <div class="product-card">
                        <div class="product-image">
                            <img src="${product.image || 'images/placeholder.jpg'}" alt="${product.name}">
                            <button class="favorite-btn active" onclick="removeFavorite(${product.id})">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <div class="product-info">
                            <h3>${product.name}</h3>
                            <p class="product-price">${new Intl.NumberFormat('uk-UA').format(product.price)} грн</p>
                            <div class="product-details">
                                ${product.occasion ? `<span class="badge badge-occasion"><i class="fas fa-calendar"></i> ${product.occasion}</span>` : ''}
                                ${sizes.map(s => `<span class="badge badge-size">${s}</span>`).join('')}
                                ${colors.map(c => `<span class="badge badge-color">${c}</span>`).join('')}
                            </div>
                            <a href="${product.instagram_url || 'https://www.instagram.com/colibriua'}" target="_blank" class="btn-instagram">
                                <i class="fab fa-instagram"></i> Замовити в Instagram
                            </a>
                            
                            <!-- ===== ДОДАНО КНОПКУ "ДОДАТИ В КОШИК" ===== -->
                            <button class="btn-add-cart" onclick="addToCartFromFav(${product.id})">
                                <i class="fas fa-shopping-cart"></i> Додати в кошик
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
            document.getElementById('total-price').textContent = new Intl.NumberFormat('uk-UA').format(totalPrice) + ' грн';
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <p>Помилка завантаження</p>
                </div>
            `;
        }
    });
}

function removeFavorite(productId) {
    let favs = JSON.parse(localStorage.getItem('colibriua_favorites') || '[]');
    favs = favs.filter(id => id !== productId);
    localStorage.setItem('colibriua_favorites', JSON.stringify(favs));
    loadFavorites();
}

// ===== ДОДАНО ФУНКЦІЮ ДЛЯ ДОДАВАННЯ В КОШИК ЗІ СТОРІНКИ ВПОДОБАЙОК =====
function addToCartFromFav(productId) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Товар додано в кошик! 🛒', 'success');
            // Оновлюємо лічильник кошика (якщо функція є в header)
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
        } else {
            showNotification(data.message || 'Помилка', 'error');
        }
    });
}

// Функція сповіщення (якщо ще не визначена)
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

document.addEventListener('DOMContentLoaded', loadFavorites);
</script>