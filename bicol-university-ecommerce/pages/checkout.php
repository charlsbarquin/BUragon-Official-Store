<?php
// pages/checkout.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';
require_once '../includes/utilities.php';

// Initialize cart and products
$cart = [];
$products = [];
$total = 0;
$out_of_stock = false;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Use database cart for logged-in users
    $pdo = getDbConnection();
    
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
        if ($qty > $row['stock_quantity']) {
            $out_of_stock = true;
        }
    }
    
} else {
    // Use session cart for guests
    $cart = $_SESSION['cart'] ?? [];
    
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
            if ($qty > $row['stock_quantity']) {
                $out_of_stock = true;
            }
        }
    }
}

// Get coupon
$applied_coupon = $_SESSION['applied_coupon'] ?? null;

// Calculate coupon discount
$coupon_discount = 0;
if ($applied_coupon && $total > 0) {
    if ($applied_coupon['type'] === 'percent') {
        $coupon_discount = round($total * ($applied_coupon['value'] / 100), 2);
    } else {
        $coupon_discount = min($applied_coupon['value'], $total);
    }
}
$grand_total = max(0, $total - $coupon_discount);

// Step logic
$step = $_POST['step'] ?? 'form';
$order_placed = false;
$invoice_file = '';
$form_data = [
    'name' => '',
    'email' => '',
    'address' => '',
    'phone' => ''
];
$payment_method = $_POST['payment_method'] ?? '';

if ($step === 'form' && isset($_POST['name'])) {
    // Go to review step
    $form_data = [
        'name' => trim($_POST['name']),
        'email' => trim($_POST['email']),
        'address' => trim($_POST['address']),
        'phone' => trim($_POST['phone'])
    ];
    $_SESSION['checkout_form'] = $form_data;
    $step = 'review';
} elseif ($step === 'review' && isset($_SESSION['checkout_form'])) {
    $form_data = $_SESSION['checkout_form'];
    if (isset($_POST['edit'])) {
        $step = 'form';
    } elseif (isset($_POST['confirm'])) {
        $step = 'payment';
    }
} elseif ($step === 'payment' && isset($_SESSION['checkout_form'])) {
    $form_data = $_SESSION['checkout_form'];
    if (isset($_POST['pay']) && in_array($payment_method, ['paypal','stripe','gcash','paymaya'])) {
        // Generate order ID and details
        $order_id = 'ORD' . date('YmdHis') . rand(100,999);
        $order_items = array_map(function($product) {
            return [
                'name' => $product['name'],
                'quantity' => $product['quantity'],
                'subtotal' => $product['subtotal']
            ];
        }, $products);
        $order = [
            'id' => $order_id,
            'items' => $order_items,
            'total' => $grand_total,
            'address' => $form_data['address'],
            'name' => $form_data['name'],
            'email' => $form_data['email'],
        ];
        // Generate PDF invoice and save to temp file
        $pdf_data = generateOrderInvoicePDF($order);
        $invoice_file = sys_get_temp_dir() . "/invoice_{$order_id}.pdf";
        file_put_contents($invoice_file, $pdf_data);
        // Send confirmation email to customer (with PDF)
        sendOrderConfirmationEmail($form_data['email'], $form_data['name'], $order, false, $invoice_file);
        // Send notification to admin (with PDF)
        sendOrderConfirmationEmail('admin@bicol-university-ecommerce.local', 'Admin', $order, true, $invoice_file);
        // --- BEGIN: Save order and order items to database ---
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $order_number = $order_id; // Use generated order_id as order_number
        $status = 'pending';
        $payment_status = 'pending';
        $shipping_address = $form_data['address'];
        $payment_method_db = $payment_method;
        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $order_number,
            $grand_total,
            $status,
            $shipping_address,
            $payment_method_db,
            $payment_status
        ]);
        $db_order_id = $pdo->lastInsertId();
        // Insert order items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt_item->execute([
                $db_order_id,
                $product['id'],
                $product['quantity'],
                $product['price'],
                $product['subtotal']
            ]);
        }
        // --- END: Save order and order items to database ---
        // Clear cart/session
        if (isset($_SESSION['user_id'])) {
            // Clear database cart for logged-in users
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } else {
            // Clear session cart for guests
            unset($_SESSION['cart']);
        }
        unset($_SESSION['applied_coupon']);
        unset($_SESSION['checkout_form']);
        $order_placed = true;
        $step = 'done';
    }
} elseif (isset($_SESSION['checkout_form'])) {
    $form_data = $_SESSION['checkout_form'];
}

// Include header after all potential redirects
require_once '../includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="checkout-hero">
        <div class="hero-background">
            <div class="hero-gradient"></div>
            <div class="hero-pattern"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">Checkout</h1>
            <p class="hero-subtitle">Complete your purchase and secure your order</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($products); ?></span>
                    <span class="stat-label">Items</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">₱<?php echo number_format($grand_total, 2); ?></span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $applied_coupon ? 'Yes' : 'No'; ?></span>
                    <span class="stat-label">Discount</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Progress -->
    <section class="checkout-progress-section">
        <div class="progress-container">
            <div class="progress-steps">
                <div class="progress-step <?php if ($step === 'form') echo 'active'; elseif ($step !== 'form') echo 'completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="step-content">
                        <span class="step-title">Information</span>
                        <span class="step-desc">Personal Details</span>
                    </div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step <?php if ($step === 'review') echo 'active'; elseif ($step === 'payment' || $step === 'done') echo 'completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="step-content">
                        <span class="step-title">Review</span>
                        <span class="step-desc">Order Summary</span>
                    </div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step <?php if ($step === 'payment') echo 'active'; elseif ($step === 'done') echo 'completed'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="step-content">
                        <span class="step-title">Payment</span>
                        <span class="step-desc">Secure Payment</span>
                    </div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step <?php if ($step === 'done') echo 'active'; ?>">
                    <div class="step-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="step-content">
                        <span class="step-title">Complete</span>
                        <span class="step-desc">Order Confirmed</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Content -->
    <section class="checkout-content">
        <?php if ($order_placed): ?>
            <!-- Order Success -->
            <div class="order-success">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Thank you for your order!</h2>
                <p>Your order has been successfully placed and confirmed. We'll send you an email confirmation with all the details.</p>
                <div class="success-actions">
                    <?php if ($invoice_file && file_exists($invoice_file)): ?>
                        <a href="<?php echo '../download_invoice.php?file=' . urlencode(basename($invoice_file)); ?>" class="cta-btn primary" target="_blank">
                            <i class="fas fa-download"></i>
                            Download Invoice
                        </a>
                    <?php endif; ?>
                    <a href="products/index.php" class="cta-btn secondary">
                        <i class="fas fa-shopping-bag"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php elseif (empty($products)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
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
        <?php elseif ($step === 'form'): ?>
            <!-- Checkout Form -->
            <div class="checkout-layout">
                <div class="checkout-form-section">
                    <div class="form-header">
                        <h2>Shipping & Billing Information</h2>
                        <p>Please provide your details to complete your order</p>
                    </div>
                    
                    <form method="post" class="checkout-form" autocomplete="off" id="checkoutInfoForm">
                        <input type="hidden" name="step" value="form">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($form_data['name']); ?>" placeholder="Enter your full name">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($form_data['email']); ?>" placeholder="Enter your email address">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="address">Shipping Address *</label>
                                <textarea id="address" name="address" required placeholder="Enter your complete shipping address"><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($form_data['phone']); ?>" placeholder="Enter your phone number">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="cta-btn primary" <?php if ($out_of_stock) echo 'disabled'; ?>>
                                <i class="fas fa-arrow-right"></i>
                                Continue to Review
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                        <i class="fas fa-receipt"></i>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($products as $product): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="item-category"><?php echo htmlspecialchars($product['category']); ?></p>
                                    <div class="item-meta">
                                        <span class="quantity">Qty: <?php echo $product['quantity']; ?></span>
                                        <span class="price">₱<?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                    <?php if ($product['quantity'] > $product['stock_quantity']): ?>
                                        <div class="stock-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Only <?php echo $product['stock_quantity']; ?> left in stock!
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-subtotal">
                                    ₱<?php echo number_format($product['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
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
                    
                    <?php if ($out_of_stock): ?>
                        <div class="stock-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Stock Issue</strong>
                                <p>Some items exceed available stock. Please update your cart before proceeding.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($step === 'review'): ?>
            <!-- Review Step -->
            <div class="checkout-layout">
                <div class="checkout-review-section">
                    <div class="review-header">
                        <h2>Review Your Order</h2>
                        <p>Please review your information before proceeding to payment</p>
                    </div>
                    
                    <form method="post" class="review-form">
                        <input type="hidden" name="step" value="review">
                        
                        <div class="review-info">
                            <h3>Shipping Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="label">Name:</span>
                                    <span class="value"><?php echo htmlspecialchars($form_data['name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Email:</span>
                                    <span class="value"><?php echo htmlspecialchars($form_data['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Phone:</span>
                                    <span class="value"><?php echo htmlspecialchars($form_data['phone']); ?></span>
                                </div>
                                <div class="info-item full-width">
                                    <span class="label">Address:</span>
                                    <span class="value"><?php echo htmlspecialchars($form_data['address']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-actions">
                            <button type="submit" name="edit" class="cta-btn secondary">
                                <i class="fas fa-edit"></i>
                                Edit Information
                            </button>
                            <button type="submit" name="confirm" class="cta-btn primary" <?php if ($out_of_stock) echo 'disabled'; ?>>
                                <i class="fas fa-credit-card"></i>
                                Proceed to Payment
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary (same as form step) -->
                <div class="order-summary">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                        <i class="fas fa-receipt"></i>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($products as $product): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="item-category"><?php echo htmlspecialchars($product['category']); ?></p>
                                    <div class="item-meta">
                                        <span class="quantity">Qty: <?php echo $product['quantity']; ?></span>
                                        <span class="price">₱<?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="item-subtotal">
                                    ₱<?php echo number_format($product['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
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
                </div>
            </div>
        <?php elseif ($step === 'payment'): ?>
            <!-- Payment Step -->
            <div class="checkout-layout">
                <div class="checkout-payment-section">
                    <div class="payment-header">
                        <h2>Select Payment Method</h2>
                        <p>Choose your preferred payment method to complete your order</p>
                    </div>
                    
                    <form method="post" class="payment-form" autocomplete="off" id="paymentForm">
                        <input type="hidden" name="step" value="payment">
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" required <?php if($payment_method==='paypal') echo 'checked'; ?> onchange="this.form.submit()">
                                <label for="paypal" class="payment-option">
                                    <div class="payment-icon">
                                        <i class="fab fa-paypal"></i>
                                    </div>
                                    <div class="payment-info">
                                        <span class="payment-name">PayPal</span>
                                        <span class="payment-desc">Pay with your PayPal account</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="stripe" name="payment_method" value="stripe" required <?php if($payment_method==='stripe') echo 'checked'; ?> onchange="this.form.submit()">
                                <label for="stripe" class="payment-option">
                                    <div class="payment-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="payment-info">
                                        <span class="payment-name">Credit Card</span>
                                        <span class="payment-desc">Pay with Visa, Mastercard, or other cards</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="gcash" name="payment_method" value="gcash" required <?php if($payment_method==='gcash') echo 'checked'; ?> onchange="this.form.submit()">
                                <label for="gcash" class="payment-option">
                                    <div class="payment-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="payment-info">
                                        <span class="payment-name">GCash</span>
                                        <span class="payment-desc">Pay with your GCash wallet</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="paymaya" name="payment_method" value="paymaya" required <?php if($payment_method==='paymaya') echo 'checked'; ?> onchange="this.form.submit()">
                                <label for="paymaya" class="payment-option">
                                    <div class="payment-icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="payment-info">
                                        <span class="payment-name">PayMaya</span>
                                        <span class="payment-desc">Pay with your PayMaya account</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <?php if ($payment_method === 'paypal'): ?>
                            <div class="payment-integration">
                                <div id="paypal-button-container"></div>
                                <script src="https://www.paypal.com/sdk/js?client-id=sb&currency=PHP"></script>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    if (window.paypal) {
                                        paypal.Buttons({
                                            createOrder: function(data, actions) {
                                                return actions.order.create({
                                                    purchase_units: [{
                                                        amount: { value: '<?php echo number_format($grand_total, 2, '.', ''); ?>' }
                                                    }]
                                                });
                                            },
                                            onApprove: function(data, actions) {
                                                return actions.order.capture().then(function(details) {
                                                    var f = document.createElement('form');
                                                    f.method = 'post';
                                                    f.action = '';
                                                    f.innerHTML = '<input type="hidden" name="step" value="payment">' +
                                                        '<input type="hidden" name="payment_method" value="paypal">' +
                                                        '<input type="hidden" name="pay" value="1">';
                                                    document.body.appendChild(f);
                                                    f.submit();
                                                });
                                            }
                                        }).render('#paypal-button-container');
                                    }
                                });
                                </script>
                            </div>
                        <?php elseif ($payment_method === 'stripe'): ?>
                            <div class="payment-integration">
                                <div id="stripe-card-form"></div>
                                <div id="stripe-error" class="payment-error"></div>
                                <button type="button" id="stripePayBtn" class="cta-btn primary">
                                    <i class="fas fa-lock"></i>
                                    Pay Securely with Stripe
                                </button>
                                <script src="https://js.stripe.com/v3/"></script>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var stripe = Stripe('YOUR_REAL_PUBLISHABLE_KEY');
                                    var elements = stripe.elements();
                                    var card = elements.create('card');
                                    card.mount('#stripe-card-form');
                                    var payBtn = document.getElementById('stripePayBtn');
                                    var errorDiv = document.getElementById('stripe-error');
                                    payBtn.addEventListener('click', function(e) {
                                        payBtn.disabled = true;
                                        errorDiv.textContent = '';
                                        fetch('../api/stripe_create_intent.php', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                            body: 'amount=<?php echo $grand_total; ?>'
                                        })
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.error) throw new Error(data.error);
                                            return stripe.confirmCardPayment(data.client_secret, {
                                                payment_method: { card: card }
                                            });
                                        })
                                        .then(function(result) {
                                            if (result.error) {
                                                errorDiv.textContent = result.error.message;
                                                payBtn.disabled = false;
                                            } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                                                var f = document.createElement('form');
                                                f.method = 'post';
                                                f.action = '';
                                                f.innerHTML = '<input type="hidden" name="step" value="payment">' +
                                                    '<input type="hidden" name="payment_method" value="stripe">' +
                                                    '<input type="hidden" name="pay" value="1">';
                                                document.body.appendChild(f);
                                                f.submit();
                                            }
                                        })
                                        .catch(function(err) {
                                            errorDiv.textContent = err.message;
                                            payBtn.disabled = false;
                                        });
                                    });
                                });
                                </script>
                            </div>
                        <?php elseif ($payment_method === 'gcash' || $payment_method === 'paymaya'): ?>
                            <div class="payment-integration">
                                <div class="demo-payment">
                                    <div class="demo-header">
                                        <i class="fas fa-info-circle"></i>
                                        <strong><?php echo strtoupper($payment_method); ?> Demo Payment</strong>
                                    </div>
                                    <p>This is a demonstration. No real payment will be processed. Click the button below to simulate a successful payment and complete your order.</p>
                                </div>
                                <button type="submit" name="pay" class="cta-btn primary">
                                    <i class="fas fa-play"></i>
                                    Simulate Payment with <?php echo ucfirst($payment_method); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Order Summary (same as other steps) -->
                <div class="order-summary">
                    <div class="summary-header">
                        <h3>Order Summary</h3>
                        <i class="fas fa-receipt"></i>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($products as $product): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="item-category"><?php echo htmlspecialchars($product['category']); ?></p>
                                    <div class="item-meta">
                                        <span class="quantity">Qty: <?php echo $product['quantity']; ?></span>
                                        <span class="price">₱<?php echo number_format($product['price'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="item-subtotal">
                                    ₱<?php echo number_format($product['subtotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
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
    
    /* Hero Section */
    .checkout-hero {
        position: relative;
        min-height: 320px;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary) 0%, #002244 100%);
        border-bottom: 1px solid var(--border-color);
    }
    
    .checkout-hero::before {
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
    
    .hero-title {
        font-size: clamp(1.75rem, 3.5vw, 2.5rem);
        font-weight: 700;
        color: white;
        margin-bottom: 12px;
        line-height: 1.2;
        letter-spacing: -0.025em;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
    
    /* Progress Section */
    .checkout-progress-section {
        padding: 40px 20px;
        background: white;
        border-bottom: 1px solid var(--border-color);
    }
    
    .progress-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .progress-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .progress-step {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px;
        background: var(--light-bg);
        border: 2px solid var(--border-color);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .progress-step.active {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        border-color: var(--secondary);
        color: white;
        box-shadow: var(--shadow-lg);
    }
    
    .progress-step.completed {
        background: var(--success-green);
        border-color: var(--success-green);
        color: white;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        font-size: 1.1rem;
    }
    
    .step-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .step-title {
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .step-desc {
        font-size: 0.8rem;
        opacity: 0.8;
    }
    
    .progress-line {
        width: 40px;
        height: 2px;
        background: var(--border-color);
        flex-shrink: 0;
    }
    
    /* Checkout Content */
    .checkout-content {
        padding: 80px 40px;
        background: var(--light-bg);
    }
    
    /* Order Success */
    .order-success {
        text-align: center;
        padding: 100px 40px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-xl);
        max-width: 700px;
        margin: 0 auto;
        border: 1px solid var(--border-color);
    }
    
    .success-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--success-green) 0%, #48bb78 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        font-size: 2.5rem;
        color: white;
        box-shadow: var(--shadow-lg);
    }
    
    .order-success h2 {
        font-size: 1.75rem;
        color: var(--primary);
        margin-bottom: 16px;
        font-weight: 700;
    }
    
    .order-success p {
        color: var(--light-text);
        font-size: 1rem;
        margin-bottom: 40px;
        max-width: 450px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .success-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
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
    }
    
    .empty-cart h2 {
        font-size: 1.75rem;
        color: var(--primary);
        margin-bottom: 16px;
        font-weight: 700;
    }
    
    .empty-cart p {
        color: var(--light-text);
        font-size: 1rem;
        margin-bottom: 40px;
        max-width: 450px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .empty-cart-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    /* Checkout Layout */
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 50px;
        align-items: start;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    /* Form Sections */
    .checkout-form-section,
    .checkout-review-section,
    .checkout-payment-section {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    
    .form-header,
    .review-header,
    .payment-header {
        padding: 30px 30px 24px 30px;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
    }
    
    .form-header h2,
    .review-header h2,
    .payment-header h2 {
        font-size: 1.4rem;
        color: var(--primary);
        margin-bottom: 8px;
        font-weight: 700;
    }
    
    .form-header p,
    .review-header p,
    .payment-header p {
        color: var(--light-text);
        margin: 0;
        font-size: 0.95rem;
    }
    
    /* Form Styles */
    .checkout-form,
    .review-form,
    .payment-form {
        padding: 30px;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-group label {
        font-weight: 600;
        color: var(--dark-text);
        font-size: 0.95rem;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 12px 16px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: white;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 16px;
    }
    
    /* Review Info */
    .review-info {
        margin-bottom: 30px;
    }
    
    .review-info h3 {
        font-size: 1.2rem;
        color: var(--primary);
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .info-item.full-width {
        grid-column: 1 / -1;
    }
    
    .info-item .label {
        font-weight: 600;
        color: var(--light-text);
        font-size: 0.9rem;
    }
    
    .info-item .value {
        color: var(--dark-text);
        font-size: 1rem;
    }
    
    .review-actions {
        display: flex;
        gap: 16px;
        justify-content: space-between;
    }
    
    /* Payment Methods */
    .payment-methods {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 30px;
    }
    
    .payment-method {
        position: relative;
    }
    
    .payment-method input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    .payment-option {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .payment-method input[type="radio"]:checked + .payment-option {
        border-color: var(--secondary);
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        box-shadow: var(--shadow-md);
    }
    
    .payment-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--light-bg);
        font-size: 1.5rem;
        color: var(--primary);
    }
    
    .payment-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .payment-name {
        font-weight: 600;
        color: var(--dark-text);
        font-size: 1rem;
    }
    
    .payment-desc {
        color: var(--light-text);
        font-size: 0.9rem;
    }
    
    /* Payment Integration */
    .payment-integration {
        margin-top: 20px;
        padding: 20px;
        background: var(--light-bg);
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }
    
    .demo-payment {
        margin-bottom: 20px;
    }
    
    .demo-header {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--primary);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .demo-payment p {
        color: var(--light-text);
        font-size: 0.9rem;
        margin: 0;
    }
    
    .payment-error {
        color: var(--danger-red);
        font-size: 0.9rem;
        margin-bottom: 16px;
        font-weight: 500;
    }
    
    /* Order Summary */
    .order-summary {
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
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--border-color);
    }
    
    .summary-header h3 {
        font-size: 1.25rem;
        color: var(--primary);
        margin: 0;
        font-weight: 700;
    }
    
    .summary-header i {
        font-size: 1.5rem;
        color: var(--secondary);
    }
    
    .order-items {
        margin-bottom: 24px;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        background: var(--light-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
    }
    
    .item-image img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
    }
    
    .item-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .item-details h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--dark-text);
        margin: 0;
    }
    
    .item-category {
        font-size: 0.8rem;
        color: var(--light-text);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
    }
    
    .item-meta {
        display: flex;
        gap: 12px;
        font-size: 0.85rem;
        color: var(--light-text);
    }
    
    .item-subtotal {
        font-weight: 600;
        color: var(--primary);
        font-size: 0.95rem;
    }
    
    .stock-warning {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--danger-red);
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .stock-error {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        margin-top: 16px;
    }
    
    .stock-error i {
        color: var(--danger-red);
        font-size: 1.1rem;
        margin-top: 2px;
    }
    
    .stock-error strong {
        color: var(--danger-red);
        font-size: 0.9rem;
    }
    
    .stock-error p {
        color: var(--light-text);
        font-size: 0.85rem;
        margin: 4px 0 0 0;
    }
    
    /* Order Totals */
    .order-totals {
        background: var(--light-bg);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid var(--border-color);
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 0.95rem;
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
        margin-top: 8px;
        padding-top: 12px;
    }
    
    /* Buttons */
    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
        letter-spacing: 0.025em;
    }
    
    .cta-btn.primary {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
        box-shadow: var(--shadow-lg);
    }
    
    .cta-btn.primary:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: var(--shadow-xl);
    }
    
    .cta-btn.primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
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
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .checkout-content {
            padding: 70px 35px;
        }
        
        .checkout-layout {
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
    }
    
    @media (max-width: 900px) {
        .checkout-content {
            padding: 60px 30px;
        }
        
        .checkout-layout {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .order-summary {
            position: static;
        }
        
        .progress-steps {
            flex-direction: column;
            gap: 16px;
        }
        
        .progress-line {
            width: 2px;
            height: 20px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions,
        .review-actions {
            flex-direction: column;
        }
    }
    
    @media (max-width: 600px) {
        .checkout-hero {
            min-height: 250px;
        }
        
        .hero-content {
            padding: 30px 20px;
        }
        
        .hero-stats {
            gap: 20px;
        }
        
        .checkout-content {
            padding: 40px 20px;
        }
        
        .checkout-form,
        .review-form,
        .payment-form {
            padding: 20px;
        }
        
        .form-header,
        .review-header,
        .payment-header {
            padding: 20px;
        }
        
        .order-summary {
            padding: 20px;
        }
        
        .success-actions,
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
    // Form validation
    const checkoutForm = document.getElementById('checkoutInfoForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e53e3e';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
    
    // Payment method selection animation
    document.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.style.transform = 'scale(1)';
            });
            
            // Add active class to clicked option
            this.style.transform = 'scale(1.02)';
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
