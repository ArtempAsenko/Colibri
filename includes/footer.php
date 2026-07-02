<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-hummingbird"></i> COLIBRI UA</h3>
            <p>Ексклюзивний одяг для особливих моментів. Знайдіть свій ідеальний образ разом з нами.</p>
            <div class="social-links">
                <a href="https://www.instagram.com/colibriua" target="_blank" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
        
        <div class="footer-section">
            <h3>Час роботи</h3>
            <p><i class="fas fa-clock"></i> Пн-Нд: 10:00 - 20:00</p>
        </div>
        
        <div class="footer-section">
            <h3>Інформація</h3>
            <p><a href="index.php" style="color: #ccc; text-decoration: none;">Головна</a></p>
            <p><a href="index.php#catalog" style="color: #ccc; text-decoration: none;">Каталог</a></p>
            <p><a href="favorites.php" style="color: #ccc; text-decoration: none;">Вподобайки</a></p>
            <p><a href="https://www.instagram.com/colibriua" target="_blank" style="color: #ccc; text-decoration: none;">Instagram</a></p>
        </div>
    </div>
    
    <div class="footer-maps">
        <div class="maps-container">
            <div class="map-block">
                <div class="map-header">
                    <i class="fas fa-store"></i>
                    <div>
                        <h4>Магазин №1</h4>
                        <p>Житомир, вул. Київська, 81</p>
                    </div>
                </div>
                <div class="map-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4675.544182202213!2d28.6891501!3d50.2677161!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x472c64ae57b6f6ff%3A0xf0116b3f19e9a486!2z0LLRg9C70LjRhtGPINCa0LjRl9Cy0YHRjNC60LAsIDgxLCDQltC40YLQvtC80LjRgCwg0JbQuNGC0L7QvNC40YDRgdGM0LrQsCDQvtCx0LvQsNGB0YLRjCwgMTAwMDE!5e1!3m2!1suk!2sua!4v1782210717539!5m2!1suk!2sua" 
                    width="600" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                </div>
                <div class="map-info">
                    <p><i class="fas fa-phone"></i> +380 (44) 111-11-11</p>
                    <p><i class="fas fa-clock"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="map-block">
                <div class="map-header">
                    <i class="fas fa-store"></i>
                    <div>
                        <h4>Магазин №2</h4>
                        <p>Житомир, вул. Небесної Сотні, 23</p>
                    </div>
                </div>
                <div class="map-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2338.1866419359862!2d28.666335553129223!3d50.259270824554235!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x472c64bdf32276e9%3A0x94644303ecc13240!2z0LLRg9C70LjRhtGPINCd0LXQsdC10YHQvdC-0Zcg0KHQvtGC0L3RliwgMjMsINCW0LjRgtC-0LzQuNGALCDQltC40YLQvtC80LjRgNGB0YzQutCwINC-0LHQu9Cw0YHRgtGMLCAxMDAwMQ!5e1!3m2!1suk!2sua!4v1782210857894!5m2!1suk!2sua" 
                    width="600" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                </div>
                <div class="map-info">
                    <p><i class="fas fa-phone"></i> +380 (44) 222-22-22</p>
                    <p><i class="fas fa-clock"></i> 10:00 - 20:00</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> COLIBRI UA. Всі права захищено.</p>
        <p>Створено з ❤️ для вас</p>
    </div>
</footer>

<!-- Кнопка "Нагору" -->
<button class="scroll-top" id="scrollTopBtn" title="Нагору">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
// Кнопка "Нагору"
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scrollTopBtn');
    if (window.scrollY > 300) {
        scrollBtn.classList.add('visible');
    } else {
        scrollBtn.classList.remove('visible');
    }
});

document.getElementById('scrollTopBtn').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

</body>
</html>