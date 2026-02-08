<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['remove'])) {
    $user_id = $_SESSION['user_id'];
    $remove_id = intval($_GET['remove']);
    $pdo->prepare('DELETE FROM wishlists WHERE user_id=? AND product_id=?')->execute([$user_id, $remove_id]);
    header('Location: wishlist.php');
    exit;
}

require_once '../includes/header.php';
$user_id = $_SESSION['user_id'];
$pdo = getDbConnection();
// Fetch wishlist products
$stmt = $pdo->prepare('SELECT p.* FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?');
$stmt->execute([$user_id]);
$products = $stmt->fetchAll();
?>
<link rel="stylesheet" href="../assets/css/cart.css">
<style>
:root {
    --primary: #003366;
    --secondary: #ff6b00;
    --light-bg: #f8fafc;
    --border-color: #e1e8ed;
    --shadow-xl: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
}
.wishlist-hero {
    position: relative;
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary) 0%, #002244 100%);
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 0;
}
.wishlist-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
    padding: 40px 20px 30px 20px;
}
.wishlist-hero-title {
    font-size: clamp(1.7rem, 3vw, 2.2rem);
    font-weight: 700;
    color: white;
    margin-bottom: 10px;
    letter-spacing: -0.025em;
}
.wishlist-hero-subtitle {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.92);
    margin-bottom: 0;
    font-weight: 400;
    line-height: 1.5;
}
.wishlist-main {
    max-width: 1100px;
    margin: 30px auto 40px auto;
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--shadow-xl);
    padding: 40px 32px 32px 32px;
    position: relative;
    z-index: 2;
}
.wishlist-main h2 {
    color: var(--primary);
    margin-bottom: 18px;
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: -0.02em;
}
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 28px;
    margin-top: 18px;
}
.wishlist-card {
    background: var(--light-bg);
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    padding: 24px 16px 18px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    border: 1px solid var(--border-color);
    transition: box-shadow 0.2s, border 0.2s;
}
.wishlist-card:hover {
    box-shadow: 0 6px 18px rgba(0,51,102,0.08);
    border-color: var(--secondary);
}
.wishlist-card img {
    width: 110px; height: 110px; object-fit: contain; border-radius: 10px; margin-bottom: 14px; background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.wishlist-card h3 {
    font-size: 1.08em; color: var(--primary); margin: 0 0 6px 0; text-align: center; font-weight: 600;
}
.wishlist-card .category {
    color: #4a5568;
    font-size: 0.92em;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 500;
}
.wishlist-card .price {
    color: var(--secondary); font-weight: 700; margin-bottom: 12px; font-size: 1.08em;
}
.wishlist-actions {
    display: flex; gap: 10px; margin-top: 10px;
}
.wishlist-actions button.cta-button {
    font-size: 0.98em;
    padding: 8px 18px;
    border-radius: 8px;
}
.wishlist-actions button.cta-button.secondary {
    background: #fff;
    color: var(--primary);
    border: 2px solid var(--primary);
    box-shadow: none;
}
.wishlist-actions button.cta-button.secondary:hover {
    background: var(--primary);
    color: #fff;
}
.wishlist-actions button[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}
.wishlist-empty {
    text-align: center;
    padding: 90px 20px 70px 20px;
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--shadow-xl);
    max-width: 600px;
    margin: 0 auto;
    border: 1px solid var(--border-color);
}
.wishlist-empty-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #ebf8ff 0%, #fef5e7 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
    font-size: 2.3rem;
    color: var(--primary);
    box-shadow: 0 4px 18px rgba(0,0,0,0.07);
    position: relative;
}
.wishlist-empty-icon::after {
    content: '';
    position: absolute;
    top: -4px; left: -4px; right: -4px; bottom: -4px;
    background: linear-gradient(135deg, var(--secondary) 0%, #ff9e4d 100%);
    border-radius: 50%;
    z-index: -1;
    opacity: 0.25;
}
.wishlist-empty h2 {
    font-size: 1.5rem;
    color: var(--primary);
    margin-bottom: 14px;
    font-weight: 700;
}
.wishlist-empty p {
    color: #4a5568;
    font-size: 1rem;
    margin-bottom: 32px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.5;
    font-weight: 400;
}
.wishlist-empty-actions {
    display: flex;
    gap: 18px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 18px;
}
/* --- Academic/Products Button Styles for Wishlist --- */
.product-actions {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    justify-content: center;
    align-items: center;
    flex-wrap: nowrap;
}
.add-cart-btn, .wishlist-btn {
    min-width: 140px;
}
.add-cart-btn {
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
    height: 48px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--secondary) 0%, #e05d00 100%);
    color: white;
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
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-decoration: none;
    letter-spacing: 0.02em;
    height: 48px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #6c757d;
    border: 2px solid #e9ecef;
    flex: 1;
    min-width: 120px;
    white-space: nowrap;
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
.add-cart-btn i {
    font-size: 1rem;
    transition: all 0.3s ease;
}
.add-cart-btn:hover i {
    transform: scale(1.1);
}
@media (max-width: 900px) {
    .wishlist-main {
        padding: 24px 8px 18px 8px;
    }
    .wishlist-grid {
        gap: 16px;
    }
}
@media (max-width: 600px) {
    .wishlist-hero-content {
        padding: 24px 8px 18px 8px;
    }
    .wishlist-main {
        padding: 12px 2px 8px 2px;
    }
    .wishlist-empty {
        padding: 50px 8px 40px 8px;
    }
    .product-actions {
        flex-direction: row;
        gap: 8px;
    }
    .add-cart-btn, .wishlist-btn {
        width: 100%;
        min-width: 0;
    }
}
</style>
<div class="wishlist-hero">
    <div class="wishlist-hero-content">
        <h1 class="wishlist-hero-title">Your Wishlist</h1>
        <p class="wishlist-hero-subtitle">Save your favorite Bicol University merchandise and add them to your cart anytime!</p>
    </div>
</div>
<main class="wishlist-main">
    <h2>My Wishlist</h2>
    <?php if (empty($products)): ?>
        <div class="wishlist-empty">
            <div class="wishlist-empty-icon">
                <i class="fas fa-heart-broken"></i>
            </div>
            <h2>Your wishlist is empty</h2>
            <p>Looks like you haven't added any items to your wishlist yet. Start browsing and save your favorites for later!</p>
            <div class="wishlist-empty-actions">
                <a href="products/index.php" class="cta-button">
                    <i class="fas fa-shopping-bag"></i>
                    Browse Products
                </a>
                <a href="merchandise.php" class="cta-button secondary">
                    <i class="fas fa-fire"></i>
                    View Merchandise
                </a>
            </div>
        </div>
    <?php else: ?>
    <div class="wishlist-grid">
        <?php foreach ($products as $product): ?>
        <div class="wishlist-card">
            <img src="<?php echo '../assets/images/products/' . htmlspecialchars($product['image'] ?: 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <div class="category"><?php echo htmlspecialchars($product['category']); ?></div>
            <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
            <div class="product-actions wishlist-actions">
                <form method="post" action="../cart_add.php" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="add-cart-btn" <?php if ($product['stock_quantity'] < 1) echo 'disabled'; ?>><i class="fas fa-cart-plus"></i> Add to Cart</button>
                </form>
                <form method="post" action="?remove=<?php echo $product['id']; ?>" style="display:inline;">
                    <button type="submit" class="wishlist-btn"><i class="fas fa-trash"></i> Remove</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
<?php
require_once '../includes/footer.php'; 
?>
<script src="../assets/js/homepage.js"></script>
<script>
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
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart AJAX
    document.querySelectorAll('.wishlist-actions form[action$="cart_add.php"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button');
            const card = form.closest('.wishlist-card');
            btn.disabled = true;
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    showNotification('Added to cart!', 'success');
                    // Remove from wishlist visually and in DOM
                    card.style.opacity = 0.5;
                    setTimeout(() => {
                        card.remove();
                        if (document.querySelectorAll('.wishlist-card').length === 0) {
                            document.querySelector('.wishlist-main').innerHTML = document.querySelector('.wishlist-empty').outerHTML;
                        }
                    }, 400);
                } else {
                    showNotification(data.message || 'Failed to add to cart.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    });
    // Remove from Wishlist AJAX
    document.querySelectorAll('.wishlist-actions form[action^="?remove="]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const card = form.closest('.wishlist-card');
            const btn = form.querySelector('button');
            btn.disabled = true;
            fetch(form.action, { method: 'POST' })
            .then(() => {
                showNotification('Removed from wishlist!', 'success');
                card.style.opacity = 0.5;
                setTimeout(() => {
                    card.remove();
                    if (document.querySelectorAll('.wishlist-card').length === 0) {
                        document.querySelector('.wishlist-main').innerHTML = document.querySelector('.wishlist-empty').outerHTML;
                    }
                }, 400);
            })
            .catch(() => {
                btn.disabled = false;
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    });
});
</script> 