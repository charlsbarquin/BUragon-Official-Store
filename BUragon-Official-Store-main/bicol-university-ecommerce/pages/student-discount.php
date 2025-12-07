<?php
$page_title = 'Student Discount';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
$pdo = getDbConnection();

// Sorting logic
$sort = $_GET['sort'] ?? 'newest';
$orderBy = 'id DESC';
if ($sort === 'price_asc') $orderBy = 'price ASC';
if ($sort === 'price_desc') $orderBy = 'price DESC';
if ($sort === 'name_asc') $orderBy = 'name ASC';
if ($sort === 'name_desc') $orderBy = 'name DESC';

$stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' AND category = 'Academic' ORDER BY $orderBy");
$stmt->execute();
$products = $stmt->fetchAll();
?>
<main class="student-discount-products">
    <div class="background-decor decor-1"></div>
    <div class="background-decor decor-2"></div>
    <div class="container">
        <div class="page-header animated-header">
            <h1 class="page-title">🎓 Student Discount Alert!</h1>
            <p class="page-subtitle">Get <strong>15% off</strong> on all academic supplies with your student ID!<br>
            Use code <strong>STUDENT15</strong> at checkout.<br>
            Valid for a limited time only.</p>
        </div>
        <div class="product-toolbar">
            <form method="get" class="sort-form" aria-label="Sort products">
                <label for="sort" class="sort-label">Sort by:</label>
                <select name="sort" id="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="newest" <?php if($sort==='newest') echo 'selected'; ?>>Newest</option>
                    <option value="price_asc" <?php if($sort==='price_asc') echo 'selected'; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php if($sort==='price_desc') echo 'selected'; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php if($sort==='name_asc') echo 'selected'; ?>>Name: A-Z</option>
                    <option value="name_desc" <?php if($sort==='name_desc') echo 'selected'; ?>>Name: Z-A</option>
                </select>
            </form>
        </div>
        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" tabindex="0" aria-label="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-image-container">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="product-image lazy-blur"
                                 loading="lazy">
                            <div class="product-overlay">
                                <button class="view-btn" data-product-id="<?php echo $product['id']; ?>" onclick="quickView(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-eye"></i> Quick View
                                </button>
                            </div>
                        </div>
                        <div class="product-details">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-actions">
                                <a href="<?php echo SITE_URL; ?>/pages/products/view.php?id=<?php echo $product['id']; ?>" class="details-btn">
                                    View Details
                                </a>
                                <button class="add-cart-btn" data-product-id="<?php echo $product['id']; ?>" aria-label="Add <?php echo htmlspecialchars($product['name']); ?> to cart">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <button class="wishlist-btn" data-product-id="<?php echo $product['id']; ?>" title="Add to Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <img src="../assets/images/empty-books.svg" alt="No products" class="empty-illustration" style="max-width:180px;margin-bottom:18px;">
                <h3>No Academic Products Available</h3>
                <p>We currently don't have any academic products listed. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Quick View Modal -->
<div id="quickViewModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div class="modal-content" style="background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.18);max-width:90vw;width:900px;max-height:90vh;overflow-y:auto;position:relative;">
        <button id="closeModal" aria-label="Close" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:1.5rem;color:#888;cursor:pointer;">&times;</button>
        <div id="quickViewContent"></div>
    </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #003366;
        --secondary: #ff6b00;
        --light-bg: #f8fafc;
        --dark-text: #222;
        --light-text: #666;
        --border-color: #d1d5db;
        --success-green: #28a745;
        --danger-red: #dc3545;
    }
    html, body {
        font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--light-bg);
    }
    .student-discount-products {
        padding: 60px 20px;
        background-color: transparent;
        min-height: calc(100vh - 160px);
        position: relative;
        z-index: 1;
    }
    .background-decor {
        position: absolute;
        border-radius: 50%;
        z-index: 0;
        opacity: 0.13;
        pointer-events: none;
    }
    .decor-1 {
        width: 320px; height: 320px;
        background: var(--primary);
        top: -80px; left: -120px;
    }
    .decor-2 {
        width: 220px; height: 220px;
        background: var(--secondary);
        bottom: -60px; right: -80px;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    .page-header {
        text-align: center;
        margin-bottom: 50px;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInHeader 1s 0.2s forwards;
    }
    @keyframes fadeInHeader {
        to {
            opacity: 1;
            transform: none;
        }
    }
    .page-title {
        font-size: 2.5rem;
        color: var(--primary);
        margin-bottom: 10px;
        font-weight: 700;
        position: relative;
        display: inline-block;
        letter-spacing: 1px;
    }
    .page-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: var(--secondary);
        border-radius: 2px;
    }
    .page-subtitle {
        font-size: 1.1rem;
        color: var(--light-text);
        max-width: 600px;
        margin: 0 auto;
    }
    .product-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 30px;
    }
    .sort-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sort-label {
        font-size: 1rem;
        color: var(--dark-text);
        font-weight: 500;
    }
    .sort-select {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        font-size: 1rem;
        font-family: 'Poppins', sans-serif;
        background: #fff;
        color: var(--dark-text);
        transition: border 0.2s;
    }
    .sort-select:focus {
        border-color: var(--primary);
        outline: none;
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }
    .product-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.07);
        transition: all 0.3s cubic-bezier(.39,.575,.56,1.000);
        border: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        outline: none;
    }
    .product-card:focus {
        box-shadow: 0 0 0 3px var(--secondary);
    }
    .product-card:hover {
        transform: translateY(-8px) scale(1.025);
        box-shadow: 0 16px 32px rgba(0, 51, 102, 0.13);
    }
    .product-image-container {
        position: relative;
        height: 210px;
        overflow: hidden;
        background: #f5f7fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-image {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
        transition: transform 0.3s cubic-bezier(.39,.575,.56,1.000), filter 0.3s;
        filter: blur(8px);
    }
    .product-image.lazy-blur[loading="lazy"] {
        filter: blur(0);
        transition: filter 0.7s cubic-bezier(.39,.575,.56,1.000);
    }
    .product-card:hover .product-image {
        transform: scale(1.07);
    }
    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 51, 102, 0.82);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s cubic-bezier(.39,.575,.56,1.000);
    }
    .product-card:hover .product-overlay {
        opacity: 1;
    }
    .view-btn {
        color: white;
        background: var(--secondary);
        padding: 10px 20px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(.39,.575,.56,1.000);
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        border: none;
        outline: none;
        box-shadow: 0 2px 8px rgba(255,107,0,0.08);
        cursor: pointer;
    }
    .view-btn:hover {
        background: #e05d00;
        transform: scale(1.08);
        box-shadow: 0 4px 16px rgba(255,107,0,0.18);
    }

    /* Modal Styles */
    .modal-overlay {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .modal-overlay[style*="display: flex"] {
        opacity: 1;
        pointer-events: all;
    }

    .modal-content {
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }

    .modal-overlay[style*="display: flex"] .modal-content {
        transform: translateY(0);
    }
    .product-details {
        padding: 22px 18px 18px 18px;
        text-align: center;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .product-name {
        font-size: 1.13rem;
        color: var(--primary);
        margin-bottom: 8px;
        font-weight: 600;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
    }
    .product-price {
        color: var(--secondary);
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
        font-family: 'Poppins', sans-serif;
    }
    .product-actions {
        margin-top: 20px;
        display: flex;
        gap: 12px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }
    .details-btn, .add-cart-btn, .wishlist-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        font-family: 'Poppins', sans-serif;
        border: none;
        outline: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(.39,.575,.56,1.000);
        box-shadow: 0 4px 12px rgba(0,51,102,0.1);
        text-decoration: none;
        letter-spacing: 0.02em;
        min-width: 48px;
        height: 48px;
        position: relative;
        overflow: hidden;
    }
    .details-btn {
        background: linear-gradient(135deg, var(--primary) 0%, #002244 100%);
        color: white;
        flex: 1;
        min-width: 120px;
        white-space: nowrap;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .details-btn:hover {
        background: linear-gradient(135deg, #002244 0%, #001122 100%);
        box-shadow: 0 8px 25px rgba(0, 51, 102, 0.3);
        transform: translateY(-3px) scale(1.02);
        border-color: rgba(255,255,255,0.2);
    }
    .details-btn:active {
        transform: translateY(-1px) scale(1.01);
    }
    .details-btn:focus {
        outline: 2px solid rgba(255,255,255,0.5);
        outline-offset: 2px;
    }
    .add-cart-btn {
        background: linear-gradient(135deg, var(--secondary) 0%, #e05d00 100%);
        color: white;
        position: relative;
        flex: 1;
        min-width: 120px;
        white-space: nowrap;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .add-cart-btn:disabled {
        background: linear-gradient(135deg, #ccc 0%, #bbb 100%);
        color: #fff;
        cursor: not-allowed;
        transform: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .add-cart-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, #e05d00 0%, #d04d00 100%);
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 25px rgba(255,107,0,0.3);
        border-color: rgba(255,255,255,0.2);
    }
    .add-cart-btn:active:not(:disabled) {
        transform: translateY(-1px) scale(1.01);
    }
    .add-cart-btn:focus:not(:disabled) {
        outline: 2px solid rgba(255,255,255,0.5);
        outline-offset: 2px;
    }
    
    .wishlist-btn {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #6c757d;
        border: 2px solid #e9ecef;
        min-width: 48px;
        width: 48px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .wishlist-btn:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        border-color: #dee2e6;
        color: #495057;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .wishlist-btn:active {
        transform: translateY(-1px) scale(1.02);
    }
    .wishlist-btn:focus {
        outline: 2px solid rgba(0,51,102,0.3);
        outline-offset: 2px;
    }
    
    .wishlist-btn i {
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    
    .wishlist-btn:hover i {
        color: #e74c3c;
        transform: scale(1.1);
    }
    
    /* Button icon improvements */
    .details-btn i,
    .add-cart-btn i {
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .details-btn:hover i,
    .add-cart-btn:hover i {
        transform: scale(1.1);
    }
    .add-cart-btn .cart-spinner {
        display: none;
        margin-left: 8px;
        font-size: 1em;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        max-width: 500px;
        margin: 0 auto;
    }
    .empty-illustration {
        display: block;
        margin: 0 auto 18px auto;
        opacity: 0.8;
    }
    .empty-icon {
        font-size: 3rem;
        color: var(--secondary);
        margin-bottom: 20px;
    }
    .empty-state h3 {
        color: var(--primary);
        margin-bottom: 10px;
    }
    .empty-state p {
        color: var(--light-text);
    }
    @media (max-width: 900px) {
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .page-title {
            font-size: 2rem;
        }
    }
    @media (max-width: 600px) {
        .student-discount-products {
            padding: 40px 15px;
        }
        .product-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .page-title {
            font-size: 1.8rem;
        }
        .product-toolbar {
            justify-content: center;
        }
        .sort-form {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>

<script>
// Quick View functionality
function quickView(productId) {
    const modal = document.getElementById('quickViewModal');
    const content = document.getElementById('quickViewContent');
    
    // Show loading state
    content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:var(--secondary);"></i><p style="margin-top:10px;">Loading product details...</p></div>';
    modal.style.display = 'flex';
    
    // Fetch product details
    fetch(`/bicol-university-ecommerce/api/products/quickview.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;color:var(--danger-red);"></i><p style="margin-top:10px;">Failed to load product details.</p></div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;color:var(--danger-red);"></i><p style="margin-top:10px;">Error loading product details.</p></div>';
        });
}

// Close modal
document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('quickViewModal').style.display = 'none';
});

// Close modal when clicking outside
document.getElementById('quickViewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

// Add to Cart functionality
function addToCart(productId, quantity = 1) {
    const btn = document.querySelector(`[data-product-id="${productId}"].add-cart-btn`);
    if (!btn) return;
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    fetch('/bicol-university-ecommerce/api/cart/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            showToast('Product added to cart!', true);
        } else {
            if (data.redirect) {
                showToast(data.message, false);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showToast('Error: ' + data.message, false);
            }
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showToast('Error adding product to cart', false);
    });
}

// Wishlist functionality
function toggleWishlist(productId, button) {
    button.disabled = true;
    
    fetch('/bicol-university-ecommerce/api/wishlist/toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        if (data.success) {
            const icon = button.querySelector('i');
            if (data.action === 'added') {
                icon.className = 'fas fa-heart';
                icon.style.color = '#e74c3c';
            } else {
                icon.className = 'far fa-heart';
                icon.style.color = '';
            }
            showToast(data.action === 'added' ? 'Product added to wishlist!' : 'Product removed from wishlist!', true);
        } else {
            if (data.redirect) {
                showToast(data.message, false);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showToast('Error: ' + data.message, false);
            }
        }
    })
    .catch(error => {
        button.disabled = false;
        showToast('Failed to update wishlist. Please try again.', false);
    });
}

// Toast notification
function showToast(message, isSuccess) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        background: ${isSuccess ? 'var(--success-green)' : 'var(--danger-red)'};
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Add event listeners to buttons
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart buttons
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            addToCart(productId, 1);
        });
    });
    
    // Wishlist buttons
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            toggleWishlist(productId, this);
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 