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

document.addEventListener('DOMContentLoaded', loadFavorites);
</script>