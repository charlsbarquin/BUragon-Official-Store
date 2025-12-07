// Products Page JavaScript
let minPrice = window.minPrice;
let maxPrice = window.maxPrice;
let currentMinPrice = window.currentMinPrice;
let currentMaxPrice = window.currentMaxPrice;

// Document ready
// (The PHP variables should be set as window variables in the HTML)
document.addEventListener('DOMContentLoaded', function() {
    // Initialize price range slider
    // initPriceSlider(); // Commented out as per edit hint

    // Set up event listeners
    setupEventListeners();

    // Add direct event listener for back button in modal
    var backBtn = document.getElementById('backModalBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('quickViewModal').classList.remove('active');
        });
    }

    // Show filters on mobile if filters are applied
    if (window.innerWidth <= 992 && (window.location.search.includes('search=') || 
        window.location.search.includes('category=') || 
        window.location.search.includes('price_min=') || 
        window.location.search.includes('price_max=') || 
        window.location.search.includes('availability=') || 
        window.location.search.includes('rating='))) {
        document.getElementById('filtersGrid').classList.add('visible');
    }

    // Event delegation for product card buttons
    document.body.addEventListener('click', function(e) {
        // Quick View
        if (e.target.closest('.btn-view')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-view');
            const productId = btn.dataset.productId;
            if (productId) quickView(productId);
        }
        // Add to Cart
        if (e.target.closest('.btn-cart')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-cart');
            const productId = btn.dataset.productId;
            if (productId) addToCart(productId, 1);
        }
        // Wishlist
        if (e.target.closest('.wishlist-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.wishlist-btn');
            const productId = btn.dataset.productId;
            if (productId) toggleWishlist(btn, productId);
        }
        // Modal close
        if (e.target.closest('#closeModal')) {
            document.getElementById('quickViewModal').classList.remove('active');
        }
    });
    // Modal close on overlay click
    document.getElementById('quickViewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
    // Modal close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('quickViewModal').classList.remove('active');
        }
    });
});

// ... (rest of the JS functions: initPriceSlider, setupEventListeners, changeView, updateSort, quickView, addToCart, toggleWishlist, updateCartCount, showLoading, hideLoading, showNotification) ... 