// Homepage JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.pagination-dot');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const productImages = document.querySelectorAll('.product-image');
    const progressBar = document.querySelector('.progress-bar');
    let currentSlide = 0;
    let autoSlideInterval;
    let progressInterval;
    let isAnimating = false;
    const SLIDE_DURATION = 6000;

    // Initialize slideshow
    function initSlideshow() {
        // Set first slide as active
        showSlide(0);

        // Start auto slide
        startAutoSlide();

        // Set up event listeners
        setupEventListeners();

        // Optimize images
        optimizeImages();
    }

    // Show specific slide
    function showSlide(index) {
        if (isAnimating || currentSlide === index) return;

        isAnimating = true;

        // Hide current slide
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');

        // Update current slide index
        currentSlide = index;

        // Show new slide
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');

        // Reset progress bar
        resetProgressBar();

        // Optimize image for new slide
        optimizeImages();

        // Allow animations to complete
        setTimeout(() => {
            isAnimating = false;
        }, 800);
    }

    // Next slide
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    // Previous slide
    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }

    // Start auto slide
    function startAutoSlide() {
        clearInterval(autoSlideInterval);
        clearInterval(progressInterval);

        autoSlideInterval = setInterval(() => {
            if (!document.hidden) {
                nextSlide();
            }
        }, SLIDE_DURATION);

        startProgressBar();
    }

    // Progress bar functions
    function startProgressBar() {
        let startTime = Date.now();

        progressBar.style.width = '0%';

        progressInterval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / SLIDE_DURATION * 100, 100);
            progressBar.style.width = progress + '%';
        }, 50);
    }

    function resetProgressBar() {
        clearInterval(progressInterval);
        progressBar.style.width = '0%';
        startProgressBar();
    }

    // Optimize images
    function optimizeImages() {
        productImages.forEach(img => {
            const container = img.closest('.image-frame');
            if (container) {
                const containerRatio = container.clientWidth / container.clientHeight;
                const imgRatio = img.naturalWidth / img.naturalHeight;

                if (imgRatio > containerRatio) {
                    img.style.width = '100%';
                    img.style.height = 'auto';
                } else {
                    img.style.width = 'auto';
                    img.style.height = '100%';
                }
            }
        });
    }

    // Set up event listeners
    function setupEventListeners() {
        // Navigation buttons
        prevBtn.addEventListener('click', () => {
            clearInterval(autoSlideInterval);
            clearInterval(progressInterval);
            prevSlide();
            startAutoSlide();
        });

        nextBtn.addEventListener('click', () => {
            clearInterval(autoSlideInterval);
            clearInterval(progressInterval);
            nextSlide();
            startAutoSlide();
        });

        // Pagination dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
                showSlide(index);
                startAutoSlide();
            });
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
                prevSlide();
                startAutoSlide();
            } else if (e.key === 'ArrowRight') {
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
                nextSlide();
                startAutoSlide();
            }
        });

        // Touch/swipe support
        let touchStartX = 0;
        let touchEndX = 0;

        document.querySelector('.minimalist-slideshow').addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, {
            passive: true
        });

        document.querySelector('.minimalist-slideshow').addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, {
            passive: true
        });

        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                // Swipe left - next slide
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
                nextSlide();
                startAutoSlide();
            } else if (touchEndX > touchStartX + 50) {
                // Swipe right - previous slide
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
                prevSlide();
                startAutoSlide();
            }
        }

        // Pause on hover
        const slideshow = document.querySelector('.minimalist-slideshow');

        slideshow.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
            clearInterval(progressInterval);
        });

        slideshow.addEventListener('mouseleave', () => {
            startAutoSlide();
        });

        // Pause when tab is inactive
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(autoSlideInterval);
                clearInterval(progressInterval);
            } else {
                startAutoSlide();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            optimizeImages();
        });
    }

    // Add View Details, Wishlist, and Buy Now button functionality for product cards
    function setupProductCardButtons() {
        // View Details: navigate to product page
        document.querySelectorAll('.product-card .cta-button, .product-card .details-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Only handle if it's a View Details button
                if (btn.textContent.trim().toLowerCase().includes('view details')) {
                    e.preventDefault();
                    const href = btn.getAttribute('href');
                    if (href) {
                        window.location.href = href;
                    }
                }
            });
        });

        // Wishlist button functionality
        document.querySelectorAll('.product-card .wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = btn.getAttribute('data-product-id');
                if (!productId) return;

                btn.disabled = true;
                const icon = btn.querySelector('i');
                const originalIcon = icon.className;

                fetch('api/wishlist/toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Toggle heart icon
                        if (data.action === 'added') {
                            icon.className = 'fas fa-heart';
                            icon.style.color = '#e74c3c';
                        } else {
                            icon.className = 'far fa-heart';
                            icon.style.color = '';
                        }
                        
                        // Show success message
                        showNotification(data.message, 'success');
                    } else {
                        // Handle authentication redirect
                        if (data.redirect) {
                            showNotification(data.message, 'error');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    }
                })
                .catch(() => {
                    showNotification('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                });
            });
        });

        // Buy Now (Add to Cart) button functionality
        document.querySelectorAll('.product-card .buy-now, .product-card .add-cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = btn.getAttribute('data-product-id');
                if (!productId) return;

                btn.disabled = true;
                const originalText = btn.innerHTML;

                fetch('cart_add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Show cart modal
                        document.getElementById('cart-modal-message').textContent = data.message;
                        document.getElementById('cart-modal').style.display = 'flex';
                        
                        // Update cart count in header if available
                        const cartIcon = document.querySelector('.nav-icon[href*="cart.php"]');
                        if (cartIcon && data.cart_count !== undefined) {
                            // You might want to update a cart count badge here
                        }
                    } else {
                        // Handle authentication redirect
                        if (data.redirect) {
                            showNotification(data.message, 'error');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    }
                })
                .catch(() => {
                    showNotification('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                });
            });
        });
    }

    // Show notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.position = 'fixed';
        notification.style.top = '30px';
        notification.style.right = '30px';
        notification.style.padding = '16px 28px';
        notification.style.borderRadius = '8px';
        notification.style.fontWeight = '600';
        notification.style.zIndex = '9999';
        notification.style.color = '#fff';
        notification.style.maxWidth = '400px';
        notification.style.wordWrap = 'break-word';
        
        // Set background color based on type
        switch(type) {
            case 'success':
                notification.style.background = '#28a745';
                break;
            case 'error':
                notification.style.background = '#dc3545';
                break;
            case 'warning':
                notification.style.background = '#ffc107';
                notification.style.color = '#212529';
                break;
            default:
                notification.style.background = '#17a2b8';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Initialize the slideshow
    initSlideshow();
    setupProductCardButtons();
});

// Newsletter AJAX
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = newsletterForm.querySelector('.newsletter-input');
        const email = input.value.trim();
        const button = newsletterForm.querySelector('.newsletter-button');
        button.disabled = true;
        fetch('newsletter_subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(res => res.json())
            .then(data => {
                let msg = document.createElement('div');
                msg.style.marginTop = '15px';
                msg.style.fontWeight = '600';
                msg.style.color = data.success ? '#28a745' : '#d32f2f';
                msg.textContent = data.message;
                newsletterForm.appendChild(msg);
                setTimeout(() => {
                    msg.remove();
                }, 4000);
                if (data.success) input.value = '';
            })
            .catch(() => {
                let msg = document.createElement('div');
                msg.style.marginTop = '15px';
                msg.style.fontWeight = '600';
                msg.style.color = '#d32f2f';
                msg.textContent = 'An error occurred. Please try again.';
                newsletterForm.appendChild(msg);
                setTimeout(() => {
                    msg.remove();
                }, 4000);
            })
            .finally(() => {
                button.disabled = false;
            });
    });
}

// Cart Modal logic
const cartModal = document.getElementById('cart-modal');
if (cartModal) {
    document.getElementById('cart-modal-continue').onclick = () => {
        cartModal.style.display = 'none';
    };
    document.getElementById('cart-modal-goto').onclick = () => {
        window.location.href = '/bicol-university-ecommerce/pages/cart.php';
    };
    document.getElementById('cart-modal-close').onclick = () => {
        cartModal.style.display = 'none';
    };
    cartModal.addEventListener('click', (e) => {
        if (e.target === cartModal) cartModal.style.display = 'none';
    });
} 