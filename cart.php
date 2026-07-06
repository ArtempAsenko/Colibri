<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('admin/login.php');
}

global $conn;

// Отримуємо товари з кошика з картинками та кольорами
$stmt = $conn->prepare("
    SELECT c.id as cart_id, c.quantity, p.*, 
           (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image,
           (SELECT GROUP_CONCAT(DISTINCT pc.color SEPARATOR ', ') FROM product_colors pc WHERE pc.product_id = p.id) as colors
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->get_result();

// Підраховуємо загальну суму
$stmt_total = $conn->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt_total->bind_param("i", $_SESSION['user_id']);
$stmt_total->execute();
$result = $stmt_total->get_result();
$cart_total = $result->fetch_assoc()['total'] ?? 0;
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
                            <div class="cart-item-image">
                                <img src="<?php echo $item['main_image'] ?? 'images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.src='images/placeholder.jpg'">
                            </div>
                            
                            <div class="cart-item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="item-price"><?php echo number_format($item['price'], 0, ',', ' '); ?> грн</p>
                                <?php if(!empty($item['colors'])): ?>
                                    <p class="item-colors">
                                        <i class="fas fa-palette" style="color: #81D8D0;"></i> 
                                        <?php echo htmlspecialchars($item['colors']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cart-item-quantity">
                                <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)" class="qty-btn">-</button>
                                <input type="number" value="<?php echo $item['quantity']; ?>" 
                                       min="1" class="qty-input" 
                                       onchange="updateQuantity(<?php echo $item['cart_id']; ?>, 0, this.value)">
                                <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)" class="qty-btn">+</button>
                            </div>
                            
                            <div class="cart-item-total">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> грн
                            </div>
                            
                            <button onclick="removeItem(<?php echo $item['cart_id']; ?>)" class="remove-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Разом</h3>
                    <div class="summary-row">
                        <span>Сума товарів:</span>
                        <span><?php echo number_format($cart_total, 0, ',', ' '); ?> грн</span>
                    </div>
                    <div class="summary-row">
                        <span>Доставка:</span>
                        <span style="color: #0ABAB5; font-weight: bold;">Безкоштовно</span>
                    </div>
                    <div class="summary-total">
                        <span>До сплати:</span>
                        <span class="final-price"><?php echo number_format($cart_total, 0, ',', ' '); ?> грн</span>
                    </div>
                    
                    <button onclick="openCheckout()" class="btn-checkout">
                        <i class="fas fa-truck"></i> Оформити замовлення
                    </button>
                    
                    <p class="checkout-info">
                        Після оформлення ми зв'яжемося з вами для підтвердження
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Ваш кошик порожній</h3>
                <p>Додайте товари, які вам сподобалися</p>
                <a href="index.php" class="btn-primary">Перейти до каталогу</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- МОДАЛЬНЕ ВІКНО ОФОРМЛЕННЯ ЗАМОВЛЕННЯ (НОВИЙ ДИЗАЙН) -->
<div id="checkoutModal">
    <div class="modal-content-checkout">
        <button class="modal-close-x" onclick="closeCheckout()">&times;</button>
        
        <h3>Дані клієнта</h3>
        
        <form id="checkoutForm">
            <!-- Email -->
            <div class="form-group">
                <label>Ел. адреса *</label>
                <input type="email" name="email" id="field_email" placeholder="example@mail.com" required>
            </div>

            <!-- Ім'я та Прізвище (в один рядок) -->
            <div class="form-row">
                <div class="form-group half">
                    <label>Ім'я *</label>
                    <input type="text" name="first_name" id="field_first_name" placeholder="Олена" required>
                </div>
                <div class="form-group half">
                    <label>Прізвище *</label>
                    <input type="text" name="last_name" id="field_last_name" placeholder="Іваненко" required>
                </div>
            </div>

            <!-- Телефон -->
            <div class="form-group">
                <label>Телефон *</label>
                <input type="tel" name="phone" id="field_phone" placeholder="+380 (00) 000-00-00" required>
            </div>

            <!-- Попередження -->
            <div class="warning-message">
                <strong>Обов'язково перевірте правильність введення!</strong>
            </div>

            <!-- Місто -->
            <div class="form-group">
                <label>Місто</label>
                <input type="text" name="city" id="field_city" placeholder="Київ">
            </div>

            <!-- Відділення Нової пошти -->
            <div class="form-group">
                <label>Відділення або поштомат Нової пошти</label>
                <input type="text" name="nova_poshta" id="field_nova_poshta" placeholder="Відділення №1, вул. Хрещатик, 10">
            </div>

            <!-- Спосіб зв'язку (Радіо-кнопки) -->
            <div class="form-group">
                <label>Написати Вам у:</label>
                <div class="radio-group">
                    <label class="radio-item">
                        <input type="radio" name="contact_method" value="telegram" id="method_telegram" checked>
                        <span>Telegram</span>
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="contact_method" value="viber" id="method_viber">
                        <span>Viber</span>
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="contact_method" value="phone" id="method_phone">
                        <span>Краще зателефонуйте</span>
                    </label>
                </div>
            </div>

            <!-- Telegram нік (з'являється тільки якщо вибрано Telegram) -->
            <div class="form-group" id="telegram_nick_container" style="display: block;">
                <label>Вкажіть нік у Telegram</label>
                <input type="text" name="telegram_nick" id="field_telegram_nick" placeholder="@username">
            </div>

            <!-- Коментар (додав для зручності) -->
            <div class="form-group">
                <label>Коментар до замовлення</label>
                <textarea name="comment" id="field_comment" placeholder="Наприклад: побажання щодо розміру або кольору..."></textarea>
            </div>

            <button type="submit" class="btn-submit">Продовжити</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// ==========================================
// 1. ФУНКЦІЇ ДЛЯ КОШИКА (ЗМІНА КІЛЬКОСТІ ТА ВИДАЛЕННЯ)
// ==========================================
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
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cart_id=' + cartId + '&quantity=' + quantity
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') location.reload();
    });
}

function removeItem(cartId) {
    if (confirm('Видалити товар з кошика?')) {
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'cart_id=' + cartId
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') location.reload();
        });
    }
}

// ==========================================
// 2. МОДАЛЬНЕ ВІКНО (ВІДКРИТТЯ/ЗАКРИТТЯ)
// ==========================================
function openCheckout() {
    document.getElementById('checkoutModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    loadFormData(); // Завантажити збережені дані
}

function closeCheckout() {
    document.getElementById('checkoutModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Закриття по кліку на фон
document.getElementById('checkoutModal').addEventListener('click', function(e) {
    if (e.target === this) closeCheckout();
});

// Закриття по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('checkoutModal').style.display === 'flex') {
        closeCheckout();
    }
});

// ==========================================
// 3. АВТОМАТИЧНЕ ЗБЕРЕЖЕННЯ ДАНИХ (localStorage)
// ==========================================
function saveFormData() {
    const fields = ['email', 'first_name', 'last_name', 'phone', 'city', 'nova_poshta', 'telegram_nick', 'comment'];
    const data = {};
    fields.forEach(field => {
        const el = document.getElementById('field_' + field);
        if (el) data[field] = el.value;
    });
    // Зберігаємо радіо-кнопку
    const contact = document.querySelector('input[name="contact_method"]:checked');
    if (contact) data.contact_method = contact.value;
    
    localStorage.setItem('colibriua_checkout_data', JSON.stringify(data));
}

function loadFormData() {
    const saved = localStorage.getItem('colibriua_checkout_data');
    if (!saved) return;
    
    const data = JSON.parse(saved);
    // Заповнюємо текстові поля
    for (const [key, value] of Object.entries(data)) {
        const el = document.getElementById('field_' + key);
        if (el) el.value = value || '';
    }
    // Заповнюємо радіо-кнопку
    if (data.contact_method) {
        const radio = document.querySelector('input[name="contact_method"][value="' + data.contact_method + '"]');
        if (radio) radio.checked = true;
        toggleTelegramField();
    }
}

// Очищення даних після успішного замовлення
function clearFormData() {
    localStorage.removeItem('colibriua_checkout_data');
}

// Слухаємо події вводу на всіх полях
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    if (form) {
        form.querySelectorAll('input, textarea').forEach(el => {
            el.addEventListener('input', saveFormData);
        });
        form.querySelectorAll('input[type="radio"]').forEach(el => {
            el.addEventListener('change', function() {
                saveFormData();
                toggleTelegramField();
            });
        });
    }
});

// ==========================================
// 4. ПОКАЗАТИ/СХОВАТИ ПОЛЕ TELEGRAM
// ==========================================
function toggleTelegramField() {
    const method = document.querySelector('input[name="contact_method"]:checked');
    const container = document.getElementById('telegram_nick_container');
    if (method && method.value === 'telegram') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// ==========================================
// 5. ВІДПРАВКА ФОРМИ
// ==========================================
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('ajax/place_order.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            clearFormData(); // Очистити збережені дані
            alert('✅ Замовлення оформлено! Ми зв\'яжемося з вами найближчим часом.');
            location.reload();
        } else {
            alert('❌ Помилка: ' + data.message);
        }
    })
    .catch(() => alert('❌ Помилка з\'єднання. Спробуйте ще раз.'));
});
</script>