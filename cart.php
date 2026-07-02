<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('admin/login.php');
}

$cart_items = getCartItems($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);
?>

<?php include 'includes/header.php'; ?>

<section class="cart-section">
    <div class="container">
        <h2><i class="fas fa-shopping-cart" style="color: #81D8D0;"></i> Мій кошик</h2>
        
        <?php if ($cart_items && $cart_items->num_rows > 0): ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php while($item = $cart_items->fetch_assoc()): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <?php if($item['color']): ?>
                                    <span class="badge"><?php echo htmlspecialchars($item['color']); ?></span>
                                <?php endif; ?>
                                <p class="item-price"><?php echo number_format($item['price'], 0, ',', ' '); ?> грн</p>
                            </div>
                            
                            <div class="cart-item-quantity">
                                <button onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" class="qty-btn">-</button>
                                <input type="number" value="<?php echo $item['quantity']; ?>" 
                                       min="1" class="qty-input" 
                                       onchange="updateQuantity(<?php echo $item['id']; ?>, 0, this.value)">
                                <button onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" class="qty-btn">+</button>
                            </div>
                            
                            <div class="cart-item-total">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> грн
                            </div>
                            
                            <button onclick="removeItem(<?php echo $item['id']; ?>)" class="remove-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Разом:</h3>
                    <div class="summary-row">
                        <span>Сума:</span>
                        <span class="total-price"><?php echo number_format($cart_total, 0, ',', ' '); ?> грн</span>
                    </div>
                    <div class="summary-row">
                        <span>Доставка:</span>
                        <span>Безкоштовно</span>
                    </div>
                    <div class="summary-total">
                        <span>До сплати:</span>
                        <span class="final-price"><?php echo number_format($cart_total, 0, ',', ' '); ?> грн</span>
                    </div>
                    
                    <a href="https://www.instagram.com/colibriua" target="_blank" class="btn-checkout">
                        <i class="fab fa-instagram"></i> Оформити в Instagram
                    </a>
                    
                    <p class="checkout-info">
                        Для оформлення замовлення перейдіть в наш Instagram
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart" style="font-size: 64px; color: #81D8D0;"></i>
                <h3>Ваш кошик порожній</h3>
                <p>Додайте товари в кошик, щоб оформити замовлення</p>
                <a href="index.php" class="btn-primary">Перейти до каталогу</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function updateQuantity(cartId, change, newValue = null) {
    let quantity;
    if (newValue !== null) {
        quantity = parseInt(newValue);
    } else {
        const input = event.target.parentElement.querySelector('.qty-input');
        quantity = parseInt(input.value) + change;
    }
    
    if (quantity < 1) return;
    
    fetch('ajax/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_id=' + cartId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        }
    });
}

function removeItem(cartId) {
    if (confirm('Видалити товар з кошика?')) {
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cart_id=' + cartId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>