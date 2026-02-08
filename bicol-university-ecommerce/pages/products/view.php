<?php
// pages/products/view.php
$page_title = "Product Details";
require_once '../../includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/db_connect.php';
$pdo = getDbConnection();

// Get product ID
$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Get product details
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            COALESCE(AVG(pr.rating), 0) as average_rating,
            COUNT(pr.id) as review_count,
            COUNT(oi.id) as sales_count
        FROM products p
        LEFT JOIN product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
        WHERE p.id = ? AND p.status = 'active'
        GROUP BY p.id
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: index.php');
        exit;
    }
    
    // Get related products
    $related_stmt = $pdo->prepare("
        SELECT id, name, price, image, discount_percentage
        FROM products 
        WHERE category = ? AND id != ? AND status = 'active'
        LIMIT 4
    ");
    $related_stmt->execute([$product['category'], $product_id]);
    $related_products = $related_stmt->fetchAll();
    
    // Get product reviews
    $reviews_stmt = $pdo->prepare("
        SELECT pr.*, u.first_name, u.last_name
        FROM product_reviews pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.product_id = ? AND pr.status = 'approved'
        ORDER BY pr.created_at DESC
        LIMIT 10
    ");
    $reviews_stmt->execute([$product_id]);
    $reviews = $reviews_stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Product view query failed: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

// Calculate pricing
$original_price = $product['price'];
$current_price = $product['price'];

if ($product['discount_percentage'] > 0) {
    $current_price = $original_price - ($original_price * $product['discount_percentage'] / 100);
}

$page_title = $product['name'];

$user_review = null;
$user_has_purchased = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    // Check if user has purchased
    $purch_stmt = $pdo->prepare('SELECT COUNT(*) FROM orders o JOIN order_items oi ON o.id=oi.order_id WHERE o.user_id=? AND oi.product_id=? AND o.status IN ("completed", "delivered", "shipped")');
    $purch_stmt->execute([$uid, $product_id]);
    $user_has_purchased = $purch_stmt->fetchColumn() > 0;
    // Check if user already reviewed
    $ur_stmt = $pdo->prepare('SELECT * FROM product_reviews WHERE product_id=? AND user_id=?');
    $ur_stmt->execute([$product_id, $uid]);
    $user_review = $ur_stmt->fetch();
}
$review_success = $review_error = '';
// Handle review form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);
    if ($rating < 1 || $rating > 5) {
        $review_error = 'Please select a rating.';
    } elseif (!$user_has_purchased) {
        $review_error = 'You can only review products you have purchased.';
    } elseif ($user_review) {
        // Update review
        $upd = $pdo->prepare('UPDATE product_reviews SET rating=?, review_text=?, status="approved", created_at=NOW() WHERE id=? AND user_id=?');
        $upd->execute([$rating, $review_text, $user_review['id'], $_SESSION['user_id']]);
        $review_success = 'Your review has been updated!';
        $user_review['rating'] = $rating;
        $user_review['review_text'] = $review_text;
    } else {
        // Insert new review
        $ins = $pdo->prepare('INSERT INTO product_reviews (product_id, user_id, rating, review_text, status, created_at) VALUES (?, ?, ?, ?, "approved", NOW())');
        $ins->execute([$product_id, $_SESSION['user_id'], $rating, $review_text]);
        $review_success = 'Thank you for your review!';
        $user_review = [
            'rating' => $rating,
            'review_text' => $review_text,
            'first_name' => $_SESSION['first_name'] ?? '',
            'last_name' => $_SESSION['last_name'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}
// Handle review delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review']) && $user_review && isset($_SESSION['user_id'])) {
    $del = $pdo->prepare('DELETE FROM product_reviews WHERE id=? AND user_id=?');
    $del->execute([$user_review['id'], $_SESSION['user_id']]);
    $review_success = 'Your review has been deleted.';
    $user_review = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - BUragon | Bicol University Official Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            min-height: 100vh;
        }
        .product-detail-page {
            background: transparent;
            min-height: 100vh;
            padding: 40px 0 0 0;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 16px;
        }
        .breadcrumb {
            background: #fff;
            padding: 15px 0 15px 0;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.05rem;
        }
        .breadcrumb a {
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .breadcrumb i {
            font-size: 1em;
            color: var(--primary);
        }
        .breadcrumb-sep {
            color: #bbb;
            margin: 0 4px;
        }
        
        .product-container {
            background: #fff;
            border-radius: 24px;
            overflow: visible;
            box-shadow: 0 10px 32px rgba(0,0,0,0.10);
            margin-bottom: 40px;
            position: relative;
        }
        
        .product-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            padding: 48px 48px 40px 48px;
        }
        @media (max-width: 900px) {
            .product-content {
                gap: 24px;
                padding: 24px 8px 24px 8px;
            }
        }
        
        .product-images {
            position: relative;
            z-index: 2;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            background: linear-gradient(120deg, #e6f2ff 0%, #fff5e6 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            transition: box-shadow 0.3s;
        }
        
        .main-image img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            transition: transform 0.3s;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.10));
        }
        
        .main-image:hover img {
            transform: scale(1.08);
            filter: drop-shadow(0 6px 18px rgba(0,0,0,0.13));
        }
        
        .product-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 3;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .badge-featured {
            background: var(--secondary);
            color: white;
            box-shadow: 0 2px 8px rgba(255,107,0,0.10);
        }
        .badge-featured i {
            color: #fff;
        }
        
        .badge-discount {
            background: #dc3545;
            color: white;
            box-shadow: 0 2px 8px rgba(220,53,69,0.10);
        }
        .badge-discount i {
            color: #fff;
        }
        
        .badge-out-of-stock {
            background: #6c757d;
            color: white;
            box-shadow: 0 2px 8px rgba(108,117,125,0.10);
        }
        .badge-out-of-stock i {
            color: #fff;
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 2;
        }
        
        .product-category {
            display: inline-block;
            background: #e6f2ff;
            color: var(--primary);
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 4px 12px;
            align-self: flex-start;
            box-shadow: 0 1px 4px rgba(0,51,102,0.04);
        }
        
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.2;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }
        
        .rating-text {
            font-size: 1rem;
            color: var(--light-text);
            font-weight: 500;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        .current-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary);
            letter-spacing: 1px;
        }
        
        .original-price {
            font-size: 1.5rem;
            color: #6c757d;
            text-decoration: line-through;
        }
        
        .discount-badge {
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 4px;
        }
        
        .product-description {
            color: var(--light-text);
            line-height: 1.6;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .product-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
            font-size: 1.05rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .meta-icon {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        
        .meta-text {
            color: var(--light-text);
            font-weight: 500;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
            font-size: 1.08rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            overflow: hidden;
            background: #f8fafc;
        }
        
        .quantity-btn {
            background: #f8f9fa;
            border: none;
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .quantity-btn:hover {
            background: #e9ecef;
        }
        
        .quantity-input {
            border: none;
            padding: 12px 16px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
            width: 60px;
            background: transparent;
        }
        
        .quantity-input:focus {
            outline: none;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            flex: 1;
            background: var(--secondary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            background: #e05d00;
        }
        
        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-secondary:hover {
            background: #002244;
        }
        
        .btn-wishlist {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-wishlist:hover {
            background: var(--primary);
            color: white;
        }
        
        .btn-wishlist i {
            transition: color 0.2s;
        }
        
        .btn-wishlist:hover i {
            color: #e74c3c;
        }
        
        .reviews-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            margin-top: 40px;
        }
        
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .reviews-title {
            font-size: 1.8rem;
            color: var(--primary);
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .reviews-summary {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .average-rating {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary);
            letter-spacing: 1px;
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .review-item {
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            background: #fafdff;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: var(--primary);
        }
        
        .review-date {
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        .review-rating {
            color: #ffc107;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .review-text {
            color: var(--light-text);
            line-height: 1.6;
        }
        
        .related-products {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 40px;
        }
        
        .related-title {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .related-card {
            border: 1px solid #e1e8ed;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #fff;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .related-image {
            height: 150px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .related-image img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }
        
        .related-info {
            padding: 15px;
        }
        
        .related-name {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .related-price {
            color: var(--secondary);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .product-content {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            
            .product-title {
                font-size: 1.5rem;
            }
            
            .current-price {
                font-size: 2rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .reviews-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
        @media (max-width: 600px) {
            .container {
                padding: 0 2vw;
            }
            .product-container {
                border-radius: 0;
                box-shadow: none;
            }
            .reviews-section, .related-products {
                border-radius: 0;
                box-shadow: none;
                padding: 18px 4vw;
            }
        }
    </style>
</head>
<body>
    <div class="product-detail-page">
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb" aria-label="Breadcrumb">
                <a href="/" title="Home"><i class="fas fa-home"></i> Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-angle-right"></i></span>
                <a href="/pages/products/index.php" title="Products"><i class="fas fa-box"></i> Products</a>
                <span class="breadcrumb-sep"><i class="fas fa-angle-right"></i></span>
                <a href="/pages/products/index.php?category=<?php echo urlencode($product['category']); ?>" title="<?php echo htmlspecialchars($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a>
                <span class="breadcrumb-sep"><i class="fas fa-angle-right"></i></span>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>
            
            <!-- Product Details -->
            <div class="product-container">
                <div class="product-content">
                    <!-- Product Images -->
                    <div class="product-images">
                        <div class="main-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <div class="product-badges">
                            <?php if ($product['featured']): ?>
                                <span class="badge badge-featured"><i class="fas fa-star"></i> Featured</span>
                            <?php endif; ?>
                            <?php if ($product['discount_percentage'] > 0): ?>
                                <span class="badge badge-discount"><i class="fas fa-tags"></i> -<?php echo $product['discount_percentage']; ?>% OFF</span>
                            <?php endif; ?>
                            <?php if ($product['stock_quantity'] <= 0): ?>
                                <span class="badge badge-out-of-stock"><i class="fas fa-box-open"></i> Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-rating">
                            <div class="stars" aria-label="Average rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= round($product['average_rating']) ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text">
                                <?php echo number_format($product['average_rating'], 1); ?> 
                                (<?php echo $product['review_count']; ?> reviews)
                            </span>
                        </div>
                        
                        <div class="product-price">
                            <span class="current-price">₱<?php echo number_format($current_price, 2); ?></span>
                            <?php if ($original_price != $current_price): ?>
                                <span class="original-price">₱<?php echo number_format($original_price, 2); ?></span>
                                <span class="discount-badge">-<?php echo $product['discount_percentage']; ?>%</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                        
                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fas fa-box meta-icon"></i>
                                <span class="meta-text">
                                    <?php echo $product['stock_quantity']; ?> in stock
                                </span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-shopping-cart meta-icon"></i>
                                <span class="meta-text">
                                    <?php echo $product['sales_count']; ?> sold
                                </span>
                            </div>
                        </div>
                        
                        <div class="quantity-selector">
                            <label>Quantity:</label>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="quantity-input">
                                <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn-primary" id="addToCartBtn" onclick="addToCart()" 
                                    <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i> <span>Add to Cart</span>
                            </button>
                            <button class="btn-secondary" onclick="buyNow()" 
                                    <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-bolt"></i> Buy Now
                            </button>
                            <button class="btn-wishlist" id="wishlistBtn" onclick="addToWishlist()" aria-label="Add to wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section">
                <div class="reviews-header">
                    <h2 class="reviews-title">Customer Reviews</h2>
                    <div class="reviews-summary">
                        <span class="average-rating"><?php echo number_format($product['average_rating'], 1); ?></span>
                        <div class="stars" aria-label="Average rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= round($product['average_rating']) ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text"><?php echo $product['review_count']; ?> reviews</span>
                    </div>
                </div>
                
                <?php if (!empty($reviews)): ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="reviewer-name">
                                        <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                    </span>
                                    <span class="review-date">
                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= round($review['rating']) ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-text">
                                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--light-text);">No reviews yet. Be the first to review this product!</p>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div style="margin-top:40px;">
                    <h3 style="color:#003366;">Leave a Review</h3>
                    <?php if ($review_success): ?><div class="account-success" style="margin-bottom:10px; color:#28a745;"><?php echo $review_success; ?></div><?php endif; ?>
                    <?php if ($review_error): ?><div class="account-error" style="margin-bottom:10px; color:#dc3545;"><?php echo $review_error; ?></div><?php endif; ?>
                    <?php if ($user_review): ?>
                        <form method="post" style="margin-bottom:10px;">
                            <label for="rating">Your Rating:</label>
                            <span id="starRatingInput">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <i class="fas fa-star star-input <?php if ($user_review['rating']>=$i) echo 'selected'; ?>" data-value="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <input type="hidden" name="rating" id="rating" value="<?php echo $user_review['rating']; ?>">
                            <br>
                            <label for="review_text">Your Review:</label><br>
                            <textarea name="review_text" id="review_text" rows="3" required style="width:100%;max-width:500px;"><?php echo htmlspecialchars($user_review['review_text']); ?></textarea><br>
                            <button type="submit" name="submit_review" class="cta-button">Update Review</button>
                            <button type="submit" name="delete_review" class="cta-button secondary" onclick="return confirm('Delete your review?');">Delete</button>
                        </form>
                    <?php elseif ($user_has_purchased): ?>
                        <form method="post" style="margin-bottom:10px;">
                            <label for="rating">Your Rating:</label>
                            <select name="rating" id="rating" required>
                                <option value="">Select</option>
                                <?php for ($i=5; $i>=1; $i--): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i>1?'s':''; ?></option>
                                <?php endfor; ?>
                            </select>
                            <br>
                            <span id="starRatingInputNew">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <i class="fas fa-star star-input" data-value="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <br>
                            <label for="review_text">Your Review:</label><br>
                            <textarea name="review_text" id="review_text" rows="3" required style="width:100%;max-width:500px;"></textarea><br>
                            <button type="submit" name="submit_review" class="cta-button">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <div style="color:#888; margin-top:10px;">You can only review products you have purchased.</div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="margin-top:40px; color:#888;">Please <a href="/pages/login.php">log in</a> to leave a review.</div>
            <?php endif; ?>
            
            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
                <div class="related-products">
                    <h2 class="related-title">Related Products</h2>
                    <div class="related-grid">
                        <?php foreach ($related_products as $related): ?>
                            <?php
                            $rel_price = $related['price'];
                            if ($related['discount_percentage'] > 0) {
                                $rel_price = $rel_price - ($rel_price * $related['discount_percentage'] / 100);
                            }
                            ?>
                            <a href="view.php?id=<?php echo $related['id']; ?>" class="related-card">
                                <div class="related-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($related['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                                </div>
                                <div class="related-info">
                                    <div class="related-name"><?php echo htmlspecialchars($related['name']); ?></div>
                                    <div class="related-price">₱<?php echo number_format($rel_price, 2); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Star rating input for review form
        document.addEventListener('DOMContentLoaded', function() {
            function handleStarInput(starContainerId, inputId) {
                const stars = document.querySelectorAll(`#${starContainerId} .star-input`);
                const input = document.getElementById(inputId);
                if (!stars.length || !input) return;
                stars.forEach(star => {
                    star.addEventListener('mouseenter', function() {
                        const val = parseInt(this.getAttribute('data-value'));
                        stars.forEach((s, idx) => {
                            s.classList.toggle('selected', idx < val);
                        });
                    });
                    star.addEventListener('mouseleave', function() {
                        const val = parseInt(input.value) || 0;
                        stars.forEach((s, idx) => {
                            s.classList.toggle('selected', idx < val);
                        });
                    });
                    star.addEventListener('click', function() {
                        const val = parseInt(this.getAttribute('data-value'));
                        input.value = val;
                        stars.forEach((s, idx) => {
                            s.classList.toggle('selected', idx < val);
                        });
                    });
                });
            }
            handleStarInput('starRatingInput', 'rating');
            handleStarInput('starRatingInputNew', 'rating');
        });

        // Toast/snackbar notification
        function showToast(message, success = true) {
            let toast = document.getElementById('toastNotification');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toastNotification';
                toast.style.position = 'fixed';
                toast.style.bottom = '32px';
                toast.style.left = '50%';
                toast.style.transform = 'translateX(-50%)';
                toast.style.background = success ? 'var(--success-green)' : 'var(--danger-red)';
                toast.style.color = '#fff';
                toast.style.padding = '16px 32px';
                toast.style.borderRadius = '8px';
                toast.style.fontSize = '1.1rem';
                toast.style.fontWeight = '600';
                toast.style.boxShadow = '0 4px 18px rgba(0,0,0,0.13)';
                toast.style.zIndex = '9999';
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.style.background = success ? 'var(--success-green)' : 'var(--danger-red)';
            toast.style.opacity = '1';
            setTimeout(() => { toast.style.opacity = '0'; }, 2200);
        }

        function changeQuantity(delta) {
            const input = document.getElementById('quantity');
            const newValue = Math.max(1, Math.min(<?php echo $product['stock_quantity']; ?>, parseInt(input.value) + delta));
            input.value = newValue;
        }
        
        function addToCart() {
            const btn = document.getElementById('addToCartBtn');
            const quantity = document.getElementById('quantity').value;
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Adding...';
            
            fetch('/bicol-university-ecommerce/api/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + encodeURIComponent(<?php echo $product_id; ?>) + '&quantity=' + encodeURIComponent(quantity)
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Add to Cart';
                if (data.success) {
                    showToast('Product added to cart!', true);
                } else {
                    // Handle authentication redirect
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
                btn.querySelector('span').textContent = 'Add to Cart';
                showToast('Error adding product to cart', false);
            });
        }
        
        function buyNow() {
            const quantity = document.getElementById('quantity').value;
            // Redirect to checkout with this product
            window.location.href = `/checkout?product_id=<?php echo $product_id; ?>&quantity=${quantity}`;
        }
        
        function addToWishlist() {
            const btn = document.getElementById('wishlistBtn');
            btn.disabled = true;
            
            fetch('/bicol-university-ecommerce/api/wishlist/toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: <?php echo $product_id; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    // Toggle heart icon
                    const icon = btn.querySelector('i');
                    if (data.action === 'added') {
                        icon.className = 'fas fa-heart';
                        icon.style.color = '#e74c3c';
                    } else {
                        icon.className = 'far fa-heart';
                        icon.style.color = '';
                    }
                    showToast(data.action === 'added' ? 'Product added to wishlist!' : 'Product removed from wishlist!', true);
                } else {
                    // Handle authentication redirect
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
                showToast('Failed to update wishlist. Please try again.', false);
            });
        }
    </script>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
</body>
</html>
