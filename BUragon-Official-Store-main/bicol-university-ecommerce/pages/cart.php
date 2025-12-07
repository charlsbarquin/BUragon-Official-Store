<?php
// pages/cart.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';
require_once '../classes/Product.php';

// Initialize cart
$cart = [];
$products = [];
$total = 0;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Use database cart for logged-in users
    $pdo = getDbConnection();
    
    // Handle POST requests for logged-in users
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update']) && isset($_POST['product_id'], $_POST['quantity'])) {
            $pid = (int)$_POST['product_id'];
            $qty = max(1, (int)$_POST['quantity']);
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$qty, $_SESSION['user_id'], $pid]);
            
        } elseif (isset($_POST['remove']) && isset($_POST['product_id'])) {
            $pid = (int)$_POST['product_id'];
            
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $pid]);
        }
        
        // Redirect to refresh the page
        header('Location: cart.php');
        exit;
    }
    
    // Fetch cart items from database
    $stmt = $pdo->prepare("SELECT c.product_id, c.quantity, p.* FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    while ($row = $stmt->fetch()) {
        $pid = $row['product_id'];
        $qty = $row['quantity'];
        $row['quantity'] = $qty;
        $row['subtotal'] = $row['price'] * $qty;
        $products[] = $row;
        $total += $row['subtotal'];
    }
    
} else {
    // Use session cart for guests
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $cart = $_SESSION['cart'];

    // Handle POST requests for guests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update']) && isset($_POST['product_id'], $_POST['quantity'])) {
            $pid = (int)$_POST['product_id'];
            $qty = max(1, (int)$_POST['quantity']);
            if (isset($cart[$pid])) {
                $cart[$pid]['quantity'] = $qty;
            }
        } elseif (isset($_POST['remove']) && isset($_POST['product_id'])) {
            $pid = (int)$_POST['product_id'];
            unset($cart[$pid]);
        }
        $_SESSION['cart'] = $cart;
        header('Location: cart.php');
        exit;
    }

    // Fetch product details for session cart
    if (!empty($cart)) {
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        while ($row = $stmt->fetch()) {
            $pid = $row['id'];
            $qty = $cart[$pid]['quantity'];
            $row['quantity'] = $qty;
            $row['subtotal'] = $row['price'] * $qty;
            $products[] = $row;
            $total += $row['subtotal'];
        }
    }
}

// Coupon logic
$applied_coupon = $_SESSION['applied_coupon'] ?? null;
$coupon_error = '';
$coupon_discount = 0;
$coupon_code = '';

// Handle coupon operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply_coupon']) && isset($_POST['coupon_code'])) {
        $coupon_code = strtoupper(trim($_POST['coupon_code']));
        if ($coupon_code !== '') {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND active = 1 AND (expires_at IS NULL OR expires_at > NOW())");
            $stmt->execute([$coupon_code]);
            $coupon = $stmt->fetch();
            if ($coupon) {
                $_SESSION['applied_coupon'] = $coupon;
                $applied_coupon = $coupon;
            } else {
                $coupon_error = 'Invalid or expired coupon code.';
                unset($_SESSION['applied_coupon']);
                $applied_coupon = null;
            }
        }
    } elseif (isset($_POST['remove_coupon'])) {
        unset($_SESSION['applied_coupon']);
        $applied_coupon = null;
    }
}

// Calculate coupon discount
if ($applied_coupon && $total > 0) {
    if ($applied_coupon['type'] === 'percent') {
        $coupon_discount = round($total * ($applied_coupon['value'] / 100), 2);
    } else {
        $coupon_discount = min($applied_coupon['value'], $total);
    }
}
$grand_total = max(0, $total - $coupon_discount);

// Include header after all potential redirects
require_once '../includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="cart-hero">
        <div class="hero-background">
            <div class="hero-gradient"></div>
            <div class="hero-pattern"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">Your Cart</h1>
            <p class="hero-subtitle">Review your items and proceed to checkout</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($products); ?></span>
                    <span class="stat-label">Items</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">₱<?php echo number_format($total, 2); ?></span>
                    <span class="stat-label">Subtotal</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $applied_coupon ? 'Yes' : 'No'; ?></span>
                    <span class="stat-label">Discount</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="cart-content">
        <?php if (empty($products)): ?>
                <!-- Empty Cart State -->
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet. Start shopping to discover amazing Bicol University merchandise!</p>
                    <div class="empty-cart-actions">
                        <a href="products/index.php" class="cta-btn primary">
                            <i class="fas fa-shopping-bag"></i>
                            Start Shopping
                        </a>
                        <a href="merchandise.php" class="cta-btn secondary">
                            <i class="fas fa-fire"></i>
                            View Merchandise
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="cart-layout">
                    <div class="cart-items">
                        <div class="cart-header">
                            <h2>Shopping Cart (<?php echo count($products); ?> items)</h2>
                            <p>Review and modify your items before checkout</p>
                        </div>
                        
                        <div class="cart-items-list">
                            <?php foreach ($products as $product): ?>
                                <div class="cart-item-card">
                                    <div class="item-image">
                                        <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             loading="lazy">
                                    </div>
                                    
                                    <div class="item-details">
                                        <h3 class="item-name">
                                            <a href="products/view.php?id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
                                        <p class="item-category"><?php echo htmlspecialchars($product['category']); ?></p>
                                        <div class="item-price">₱<?php echo number_format($product['price'], 2); ?></div>
                                    </div>
                                    
                                    <div class="item-quantity">
                                        <form method="post" class="quantity-form">
                                            <label for="qty-<?php echo $product['id']; ?>">Quantity:</label>
                                            <div class="quantity-controls">
                                                <button type="button" class="qty-btn minus" data-product-id="<?php echo $product['id']; ?>">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       id="qty-<?php echo $product['id']; ?>"
                                                       name="quantity" 
                                                       value="<?php echo $product['quantity']; ?>" 
                                                       min="1" 
                                                       class="quantity-input">
                                                <button type="button" class="qty-btn plus" data-product-id="<?php echo $product['id']; ?>">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="update" class="update-btn">
                                                <i class="fas fa-sync-alt"></i>
                                                Update
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <div class="item-subtotal">
                                        <span class="subtotal-label">Subtotal</span>
                                        <span class="subtotal-amount">₱<?php echo number_format($product['subtotal'], 2); ?></span>
                                    </div>
                                    
                                    <div class="item-actions">
                                        <form method="post" class="remove-form">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="remove" value="1" class="remove-btn" 
                                                    onclick="return confirm('Are you sure you want to remove this item?')">
                                                <i class="fas fa-trash"></i>
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="summary-header">
                            <h3>Order Summary</h3>
                            <i class="fas fa-receipt"></i>
                        </div>
                        
                        <!-- Coupon Section -->
                        <div class="coupon-section">
                            <h4>Apply Coupon</h4>
                            <form method="post" class="coupon-form">
                                <?php if ($applied_coupon): ?>
                                    <div class="coupon-applied">
                                        <div class="coupon-info">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?php echo htmlspecialchars($applied_coupon['code']); ?></span>
                                        </div>
                                        <button type="submit" name="remove_coupon" class="remove-coupon-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="coupon-input-group">
                                        <input type="text" 
                                               name="coupon_code" 
                                               placeholder="Enter coupon code" 
                                               value="<?php echo htmlspecialchars($coupon_code); ?>"
                                               class="coupon-input">
                                        <button type="submit" name="apply_coupon" class="apply-coupon-btn">
                                            <i class="fas fa-tag"></i>
                                            Apply
                                        </button>
                                    </div>
                                    <?php if ($coupon_error): ?>
                                        <div class="coupon-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <?php echo $coupon_error; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <!-- Order Totals -->
                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal (<?php echo count($products); ?> items)</span>
                                <span>₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <?php if ($coupon_discount > 0): ?>
                                <div class="total-row discount">
                                    <span>Discount</span>
                                    <span>-₱<?php echo number_format($coupon_discount, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="total-row grand-total">
                                <span>Total</span>
                                <span>₱<?php echo number_format($grand_total, 2); ?></span>
                            </div>
                        </div>
                        
                        <!-- Checkout Actions -->
                        <div class="checkout-actions">
                            <a href="checkout.php" class="checkout-btn">
                                <i class="fas fa-credit-card"></i>
                                Proceed to Checkout
                            </a>
                            <a href="products/index.php" class="continue-shopping-btn">
                                <i class="fas fa-arrow-left"></i>
                                Continue Shopping
                            </a>
                        </div>
                        
                        <!-- Security Notice -->
                        <div class="security-notice">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Secure Checkout</strong>
                                <p>Your payment information is protected with bank-level security</p>
                            </div>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
    </section>
</main>

<style>
    :root {
        --primary: #003366;
        --secondary: #ff6b00;
        --light-bg: #f8fafc;
        --dark-text: #1a202c;
        --light-text: #4a5568;
        --border-color: #e2e8f0;
        --success-green: #38a169;
        --danger-red: #e53e3e;
        --accent-orange: #ff9e4d;
        --accent-blue: #ebf8ff;
        --accent-yellow: #fef5e7;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    html, body {
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: var(--light-bg);
        margin: 0;
        padding: 0;
        line-height: 1.7;
        color: var(--dark-text);
        font-size: 16px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        box-sizing: border-box;
    }
    
    /* Hero Section */
    .cart-hero {
        position: relative;
        min-height: 320px;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary) 0%, #002244 100%);
        border-bottom: 1px solid var(--border-color);
    }
    
    .cart-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('../assets/images/bu-pattern.png') repeat;
        opacity: 0.1;
        pointer-events: none;
    }
    
    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }
    
    .hero-gradient {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 158, 77, 0.1) 0%, rgba(230, 242, 255, 0.1) 100%);
    }
    
    .hero-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: radial-gradient(circle at 25% 25%, rgba(255, 107, 0, 0.05) 0%, transparent 50%),
                          radial-gradient(circle at 75% 75%, rgba(0, 51, 102, 0.05) 0%, transparent 50%);
        background-size: 100px 100px, 150px 150px;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 900px;
        margin: 0 auto;
        padding: 60px 40px;
    }
    
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 20px;
        box-shadow: var(--shadow-lg);
        letter-spacing: 0.025em;
    }
    
    .hero-title {
        font-size: clamp(1.75rem, 3.5vw, 2.5rem);
        font-weight: 700;
        color: white;
        margin-bottom: 12px;
        line-height: 1.2;
        letter-spacing: -0.025em;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1;
    }
    
    .hero-subtitle {
        font-size: clamp(0.95rem, 1.8vw, 1.1rem);
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 30px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
        font-weight: 400;
        line-height: 1.5;
        position: relative;
        z-index: 1;
    }
    
    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 12px 18px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 80px;
        position: relative;
        z-index: 1;
    }
    
    .stat-number {
        font-size: 1.4rem;
        font-weight: 700;
        color: white;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Cart Content */
    .cart-content {
        padding: 80px 40px;
        background: var(--light-bg);
    }
    
    /* Empty Cart */
    .empty-cart {
        text-align: center;
        padding: 100px 40px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-xl);
        max-width: 700px;
        margin: 0 auto;
        border: 1px solid var(--border-color);
    }
    
    .empty-cart-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        font-size: 2.5rem;
        color: var(--primary);
        box-shadow: var(--shadow-lg);
        position: relative;
    }
    
    .empty-cart-icon::after {
        content: '';
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        border-radius: 50%;
        z-index: -1;
        opacity: 0.3;
    }
    
    .empty-cart h2 {
        font-size: 1.75rem;
        color: var(--primary);
        margin-bottom: 16px;
        font-weight: 700;
        letter-spacing: -0.025em;
    }
    
    .empty-cart p {
        color: var(--light-text);
        font-size: 1rem;
        margin-bottom: 40px;
        max-width: 450px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.5;
        font-weight: 400;
    }
    
    .empty-cart-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
        letter-spacing: 0.025em;
        position: relative;
        overflow: hidden;
    }
    
    .cta-btn.primary {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
        box-shadow: var(--shadow-lg);
    }
    
    .cta-btn.primary:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-xl);
    }
    
    .cta-btn.primary:active {
        transform: translateY(-1px);
    }
    
    .cta-btn.secondary {
        background: white;
        color: var(--primary);
        border: 2px solid var(--primary);
        box-shadow: var(--shadow-sm);
    }
    
    .cta-btn.secondary:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    
    .cta-btn.secondary:active {
        transform: translateY(-1px);
    }
    
    /* Cart Layout */
    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 50px;
        align-items: start;
        margin-top: 20px;
    }
    
    /* Cart Items */
    .cart-items {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    
    .cart-header {
        padding: 30px 30px 24px 30px;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        position: relative;
    }
    
    .cart-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 107, 0, 0.05) 0%, rgba(0, 51, 102, 0.05) 100%);
        pointer-events: none;
    }
    
    .cart-header h2 {
        font-size: 1.4rem;
        color: var(--primary);
        margin-bottom: 8px;
        font-weight: 700;
        letter-spacing: -0.025em;
        position: relative;
        z-index: 1;
    }
    
    .cart-header p {
        color: var(--light-text);
        margin: 0;
        font-size: 0.95rem;
        position: relative;
        z-index: 1;
    }
    
    .cart-items-list {
        padding: 24px;
    }
    
    /* Cart Item Card */
    .cart-item-card {
        display: grid;
        grid-template-columns: 120px 1fr auto auto auto;
        gap: 20px;
        align-items: center;
        padding: 24px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        margin-bottom: 16px;
        background: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .cart-item-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-3px);
        border-color: var(--secondary);
    }
    
    .cart-item-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 107, 0, 0.02) 0%, rgba(0, 51, 102, 0.02) 100%);
        border-radius: 12px;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .cart-item-card:hover::before {
        opacity: 1;
    }
    
    .item-image {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        overflow: hidden;
        background: var(--light-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
        position: relative;
    }
    
    .item-image img {
        max-width: 85%;
        max-height: 85%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    
    .cart-item-card:hover .item-image img {
        transform: scale(1.05);
    }
    
    .item-details {
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: relative;
        z-index: 1;
    }
    
    .item-name a {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--primary);
        text-decoration: none;
        line-height: 1.4;
        transition: color 0.2s ease;
    }
    
    .item-name a:hover {
        color: var(--secondary);
    }
    
    .item-category {
        color: var(--light-text);
        font-size: 0.85rem;
        margin: 0;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .item-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--secondary);
        letter-spacing: -0.025em;
    }
    
    /* Quantity Controls */
    .item-quantity {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
        position: relative;
        z-index: 1;
    }
    
    .quantity-form {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: center;
    }
    
    .quantity-form label {
        font-size: 0.9rem;
        color: var(--light-text);
        font-weight: 500;
    }
    
    .quantity-controls {
        display: flex;
        align-items: center;
        border: 2px solid var(--border-color);
        border-radius: 10px;
        overflow: hidden;
        background: white;
        box-shadow: var(--shadow-sm);
    }
    
    .qty-btn {
        background: var(--light-bg);
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--primary);
        font-weight: 600;
    }
    
    .qty-btn:hover {
        background: var(--secondary);
        color: white;
    }
    
    .qty-btn:active {
        transform: scale(0.95);
    }
    
    .quantity-input {
        width: 60px;
        text-align: center;
        border: none;
        padding: 8px;
        font-size: 1rem;
        font-weight: 600;
        color: var(--primary);
        background: white;
    }
    
    .quantity-input:focus {
        outline: none;
        background: var(--accent-blue);
    }
    
    .update-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: var(--shadow-sm);
        letter-spacing: 0.025em;
    }
    
    .update-btn:hover {
        background: var(--secondary);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .update-btn:active {
        transform: translateY(0);
    }
    
    /* Item Subtotal */
    .item-subtotal {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        position: relative;
        z-index: 1;
    }
    
    .subtotal-label {
        font-size: 0.9rem;
        color: var(--light-text);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .subtotal-amount {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary);
        letter-spacing: -0.025em;
    }
    
    /* Remove Button */
    .remove-btn {
        background: var(--danger-red);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: var(--shadow-sm);
        letter-spacing: 0.025em;
        position: relative;
        z-index: 1;
    }
    
    .remove-btn:hover {
        background: #c53030;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .remove-btn:active {
        transform: translateY(0);
    }
    
    /* Cart Summary */
    .cart-summary {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow-xl);
        padding: 30px;
        position: sticky;
        top: 20px;
        border: 1px solid var(--border-color);
    }
    
    .summary-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--border-color);
    }
    
    .summary-header h3 {
        font-size: 1.25rem;
        color: var(--primary);
        margin: 0;
        font-weight: 700;
        letter-spacing: -0.025em;
    }
    
    .summary-header i {
        font-size: 1.5rem;
        color: var(--secondary);
    }
    
    /* Coupon Section */
    .coupon-section {
        margin-bottom: 32px;
        padding: 24px;
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        position: relative;
    }
    
    .coupon-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 107, 0, 0.05) 0%, rgba(0, 51, 102, 0.05) 100%);
        border-radius: 12px;
        pointer-events: none;
    }
    
    .coupon-section h4 {
        font-size: 1rem;
        color: var(--primary);
        margin-bottom: 12px;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    
    .coupon-input-group {
        display: flex;
        gap: 10px;
        position: relative;
        z-index: 1;
    }
    
    .coupon-input {
        flex: 1;
        padding: 10px 14px;
        border: 2px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.2s ease;
        background: white;
    }
    
    .coupon-input:focus {
        outline: none;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
    }
    
    .apply-coupon-btn {
        background: var(--secondary);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: var(--shadow-sm);
        letter-spacing: 0.025em;
    }
    
    .apply-coupon-btn:hover {
        background: var(--accent-orange);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .apply-coupon-btn:active {
        transform: translateY(0);
    }
    
    .coupon-applied {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: var(--success-green);
        color: white;
        border-radius: 10px;
        box-shadow: var(--shadow-sm);
        position: relative;
        z-index: 1;
    }
    
    .coupon-info {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }
    
    .remove-coupon-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.2s;
    }
    
    .remove-coupon-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .coupon-error {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--danger-red);
        font-size: 0.95rem;
        margin-top: 12px;
        font-weight: 500;
        position: relative;
        z-index: 1;
    }
    
    /* Order Totals */
    .order-totals {
        margin-bottom: 32px;
        background: var(--light-bg);
        border-radius: 12px;
        padding: 24px;
        border: 1px solid var(--border-color);
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
    }
    
    .total-row:last-child {
        border-bottom: none;
    }
    
    .total-row.discount {
        color: var(--success-green);
        font-weight: 600;
    }
    
    .total-row.grand-total {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        border-top: 2px solid var(--border-color);
        margin-top: 10px;
        padding-top: 16px;
        letter-spacing: -0.025em;
    }
    
    /* Checkout Actions */
    .checkout-actions {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 32px;
    }
    
    .checkout-btn {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
        text-decoration: none;
        padding: 14px 24px;
        border-radius: 10px;
        font-weight: 700;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: var(--shadow-lg);
        font-size: 1rem;
        letter-spacing: 0.025em;
    }
    
    .checkout-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-xl);
    }
    
    .checkout-btn:active {
        transform: translateY(-1px);
    }
    
    .continue-shopping-btn {
        background: white;
        color: var(--primary);
        text-decoration: none;
        padding: 12px 24px;
        border: 2px solid var(--primary);
        border-radius: 10px;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.95rem;
        letter-spacing: 0.025em;
        box-shadow: var(--shadow-sm);
    }
    
    .continue-shopping-btn:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    
    .continue-shopping-btn:active {
        transform: translateY(-1px);
    }
    
    /* Security Notice */
    .security-notice {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        border-radius: 12px;
        border-left: 4px solid var(--primary);
        box-shadow: var(--shadow-sm);
        position: relative;
    }
    
    .security-notice::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 107, 0, 0.05) 0%, rgba(0, 51, 102, 0.05) 100%);
        border-radius: 12px;
        pointer-events: none;
    }
    
    .security-notice i {
        font-size: 1.75rem;
        color: var(--primary);
        position: relative;
        z-index: 1;
    }
    
    .security-notice strong {
        color: var(--primary);
        font-size: 0.9rem;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    
    .security-notice p {
        color: var(--light-text);
        font-size: 0.8rem;
        margin: 4px 0 0 0;
        position: relative;
        z-index: 1;
        line-height: 1.4;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .cart-content {
            padding: 70px 35px;
        }
        
        .cart-layout {
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
    }
    
    @media (max-width: 900px) {
        .cart-content {
            padding: 60px 30px;
        }
        
        .cart-layout {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .cart-summary {
            position: static;
        }
        
        .cart-item-card {
            grid-template-columns: 100px 1fr;
            gap: 16px;
        }
        
        .item-quantity,
        .item-subtotal,
        .item-actions {
            grid-column: 1 / -1;
            justify-self: start;
        }
        
        .item-quantity {
            flex-direction: row;
            align-items: center;
            gap: 16px;
        }
        
        .quantity-form {
            flex-direction: row;
            align-items: center;
        }
    }
    
    @media (max-width: 600px) {
        .cart-hero {
            min-height: 250px;
        }
        
        .hero-content {
            padding: 30px 20px;
        }
        
        .hero-stats {
            gap: 20px;
        }
        
        .cart-content {
            padding: 40px 20px;
        }
        
        .cart-item-card {
            grid-template-columns: 1fr;
            text-align: center;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }
        
        .empty-cart-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .cta-btn {
            width: 100%;
            max-width: 250px;
            justify-content: center;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity button functionality
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.getElementById(`qty-${productId}`);
            const currentValue = parseInt(input.value);
            
            if (this.classList.contains('minus')) {
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                }
            } else if (this.classList.contains('plus')) {
                input.value = currentValue + 1;
            }
        });
    });
    
    // Auto-submit quantity form when quantity changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const form = this.closest('.quantity-form');
            if (form) {
                form.submit();
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
