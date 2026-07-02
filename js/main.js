document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Product card hover effect
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
        });
    });

    // Filter form animation
    const filterForm = document.querySelector('.filters-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-filter');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Фільтруємо...';
            button.disabled = true;
        });
    }

    // Instagram button click tracking
    document.querySelectorAll('.btn-instagram').forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('Redirecting to Instagram shop...');
        });
    });

    // Confirm delete in admin panel
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Ви впевнені, що хочете видалити цей товар?')) {
                e.preventDefault();
            }
        });
    });

    // Image preview for product upload
    const imageInput = document.getElementById('product-image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
});