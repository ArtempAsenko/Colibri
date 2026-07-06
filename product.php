<?php
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$product_id = $_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    redirect('index.php');
}

$images = getProductImages($product_id);
$colors = getProductColors($product_id);
$sizes = getProductSizes($product_id);
?>
<?php include 'includes/header.php'; ?>

<style>
    .product-page { display: flex; gap: 50px; padding: 40px 0; flex-wrap: wrap; }
    .product-images { flex: 1; min-width: 300px; }
    .product-images img { width: 100%; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 15px; }
    .product-thumbs { display: flex; gap: 10px; flex-wrap: wrap; }
    .product-thumbs img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; cursor: pointer; border: 2px solid transparent; transition: 0.3s; }
    .product-thumbs img:hover, .product-thumbs img.active { border-color: #81D8D0; }
    .product-info { flex: 1; min-width: 300px; }
    .product-info h1 { font-size: 32px; margin-bottom: 10px; }
    .product-info .price { font-size: 28px; color: #0ABAB5; font-weight: bold; margin-bottom: 20px; }
    .product-info .desc { font-size: 16px; color: #666; line-height: 1.6; margin-bottom: 20px; }
    .product-info .meta { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .btn-add-cart-big { padding: 15px 40px; background: #81D8D0; color: white; border: none; border-radius: 50px; font-size: 18px; cursor: pointer; transition: 0.3s; margin-top: 20px; }
    .btn-add-cart-big:hover { background: #0ABAB5; transform: scale(1.02); }
    @media (max-width: 768px) { .product-page { flex-direction: column; } }
</style>

<section class="container">
    <div class="product-page">
        <div class="product-images">
            <?php 
            $main_image = getMainImage($product_id);
            echo '<img src="' . $main_image . '" id="mainImage" alt="' . htmlspecialchars($product['name']) . '">';
            ?>
            <div class="product-thumbs">
                <?php while($img = $images->fetch_assoc()): ?>
                    <img src="<?php echo $img['image_path']; ?>" onclick="changeImage(this.src)">
                <?php endwhile; ?>
            </div>
        </div>
        
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="price"><?php echo number_format($product['price'], 0, ',', ' '); ?> грн</p>
            <p class="desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <div class="meta">
                <?php if($product['occasion']): ?><span class="badge badge-occasion"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($product['occasion']); ?></span><?php endif; ?>
                <?php if($product['category']): ?><span class="badge" style="background:#f0f0f0;"><?php echo htmlspecialchars($product['category']); ?></span><?php endif; ?>
                <?php while($s = $sizes->fetch_assoc()): ?><span class="badge badge-size"><?php echo htmlspecialchars($s['size']); ?></span><?php endwhile; ?>
                <?php while($c = $colors->fetch_assoc()): ?><span class="badge badge-color"><?php echo htmlspecialchars($c['color']); ?></span><?php endwhile; ?>
            </div>
            
            <button class="btn-add-cart-big" onclick="addToCart(<?php echo $product['id']; ?>)">
                <i class="fas fa-shopping-cart"></i> Додати в кошик
            </button>
        </div>
    </div>
</section>

<script>
function changeImage(src) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.product-thumbs img').forEach(img => img.classList.remove('active'));
    event.target.classList.add('active');
}
function addToCart(id) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + id + '&quantity=1'
    })
    .then(r => r.json())
    .then(d => {
        if(d.status === 'success') {
            alert('Товар додано в кошик! 🛒');
            if(typeof updateCartBadge === 'function') updateCartBadge();
        } else {
            alert(d.message || 'Помилка');
        }
    });
}
</script>
<?php include 'includes/footer.php'; ?>