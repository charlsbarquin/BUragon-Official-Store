<?php
$page_title = 'Clearance';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';

$pdo = getDbConnection();

// Get sort parameter
$sort = $_GET['sort'] ?? 'newest';

// Build query with sorting
$orderBy = match($sort) {
    'price_asc' => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'name_asc' => 'ORDER BY name ASC',
    'name_desc' => 'ORDER BY name DESC',
    default => 'ORDER BY created_at DESC'
};

$stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' AND category = 'Clearance' $orderBy");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - BUragon | Bicol University Official Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<main>
    <!-- Enhanced Hero Section -->
    <section class="clearance-hero">
        <div class="hero-background">
            <div class="hero-gradient"></div>
            <div class="hero-pattern"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-fire"></i>
                    <span>Limited Time</span>
                </div>
                <h1 class="hero-title">Clearance Sale</h1>
                <p class="hero-subtitle">Amazing deals on selected items - up to 70% off! Don't miss out on these incredible savings.</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">70%</span>
                        <span class="stat-label">Off</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24</span>
                        <span class="stat-label">Hours Left</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Items</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Content Section -->
    <section class="clearance-content">
        <div class="container">
            <div class="content-header">
                <div class="header-left">
                    <h2 class="section-title">Clearance Products</h2>
                    <p class="section-subtitle">Quality products at unbeatable prices</p>
                </div>
                <div class="header-right">
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
                </div>
            </div>
        </div>
        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" tabindex="0" aria-label="<?php echo htmlspecialchars($product['name']); ?>">
                        <!-- Clearance Badge -->
                        <div class="clearance-badge">
                            <i class="fas fa-fire"></i>
                            <span>Clearance</span>
                        </div>
                        
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
                            <div class="price-container">
                                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                                <div class="original-price">₱<?php echo number_format($product['price'] * 1.5, 2); ?></div>
                            </div>
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
                <h3>No Clearance Products Available</h3>
                <p>We currently don't have any clearance products listed. Please check back later.</p>
            </div>
        <?php endif; ?>
        
        <!-- Call to Action Section -->
        <section class="clearance-cta">
            <div class="cta-content">
                <div class="cta-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Don't Miss Out!</h3>
                <p>These clearance deals won't last forever. Shop now and save big on quality Bicol University merchandise.</p>
                <div class="cta-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/merchandise.php" class="cta-btn primary">
                        <i class="fas fa-shopping-bag"></i>
                        Shop All Products
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/student-discount.php" class="cta-btn secondary">
                        <i class="fas fa-graduation-cap"></i>
                        Student Discount
                    </a>
                </div>
            </div>
        </section>
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
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
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
        --accent-orange: #ff9e4d;
        --accent-blue: #e6f2ff;
        --accent-yellow: #fff5e6;
    }
    
    html, body {
        font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--light-bg);
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        box-sizing: border-box;
    }
    
    /* Enhanced Hero Section */
    .clearance-hero {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: linear-gradient(120deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        animation: heroGradient 8s ease-in-out infinite alternate;
        border: 3px solid #ff0000; /* Temporary test border */
    }
    
    @keyframes heroGradient {
        0% { background: linear-gradient(120deg, var(--accent-blue) 0%, var(--accent-yellow) 100%); }
        100% { background: linear-gradient(120deg, var(--accent-yellow) 0%, var(--accent-blue) 100%); }
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
        max-width: 800px;
        margin: 0 auto;
        padding: 60px 20px;
    }
    
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 16px;
        line-height: 1.1;
    }
    
    .hero-subtitle {
        font-size: clamp(1.1rem, 2vw, 1.3rem);
        color: var(--light-text);
        margin-bottom: 40px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
    }
    
    .stat-item {
        text-align: center;
        padding: 20px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-width: 120px;
    }
    
    .stat-number {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 4px;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: var(--light-text);
        font-weight: 500;
    }
    
    /* Enhanced Content Section */
    .clearance-content {
        padding: 80px 0;
        background: white;
    }
    
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 50px;
        gap: 30px;
    }
    
    .header-left {
        flex: 1;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 12px;
        position: relative;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, var(--secondary) 0%, var(--accent-orange) 100%);
        border-radius: 2px;
    }
    
    .section-subtitle {
        font-size: 1.1rem;
        color: var(--light-text);
        margin: 0;
    }
    
    .header-right {
        flex-shrink: 0;
    }
    .product-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0;
    }
    .sort-form {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sort-label {
        font-size: 0.9rem;
        color: var(--light-text);
        font-weight: 500;
    }
    .sort-select {
        padding: 10px 16px;
        border-radius: 50px;
        border: 1px solid var(--border-color);
        font-size: 0.9rem;
        font-family: 'Poppins', sans-serif;
        background: white;
        color: var(--dark-text);
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .sort-select:focus {
        border-color: var(--secondary);
        outline: none;
        box-shadow: 0 4px 12px rgba(255, 107, 0, 0.15);
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 30px;
        padding: 20px 0;
    }
    .product-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(.39,.575,.56,1.000);
        border: 1px solid rgba(0, 51, 102, 0.08);
        display: flex;
        flex-direction: column;
        outline: none;
        position: relative;
        min-height: 450px;
    }
    .product-card:focus {
        box-shadow: 0 0 0 3px var(--secondary);
    }
    .product-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 51, 102, 0.15);
        border-color: rgba(0, 51, 102, 0.15);
    }
    
    /* Clearance Badge */
    .clearance-badge {
        position: absolute;
        top: 16px;
        left: 16px;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(255, 107, 0, 0.3);
        animation: pulse 2s infinite;
        border: 2px solid #00ff00; /* Temporary test border */
    }
    
    .clearance-badge i {
        font-size: 0.9rem;
    }
    .product-image-container {
        position: relative;
        height: 240px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid rgba(0, 51, 102, 0.05);
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
        padding: 24px 20px 20px 20px;
        text-align: center;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
    }
    .product-name {
        font-size: 1.15rem;
        color: var(--primary);
        margin-bottom: 12px;
        font-weight: 700;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
        line-height: 1.4;
        letter-spacing: 0.02em;
    }
    
    .price-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        margin-bottom: 16px;
    }
    
    .product-price {
        color: var(--secondary);
        font-size: 1.4rem;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
        margin: 0;
    }
    
    .original-price {
        color: var(--light-text);
        font-size: 1rem;
        text-decoration: line-through;
        opacity: 0.7;
        font-weight: 500;
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
    
    /* Enhanced Call to Action Section */
    .clearance-cta {
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-yellow) 100%);
        padding: 80px 0;
        margin-top: 80px;
        text-align: center;
    }
    
    .cta-content {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .cta-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 8px 25px rgba(255, 107, 0, 0.3);
    }
    
    .cta-icon i {
        font-size: 2rem;
        color: white;
    }
    
    .clearance-cta h3 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 16px;
    }
    
    .clearance-cta p {
        font-size: 1.1rem;
        color: var(--light-text);
        margin-bottom: 40px;
        line-height: 1.6;
    }
    
    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 16px 32px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .cta-btn.primary {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--accent-orange) 100%);
        color: white;
    }
    
    .cta-btn.primary:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(255, 107, 0, 0.4);
    }
    
    .cta-btn.secondary {
        background: white;
        color: var(--primary);
        border: 2px solid var(--primary);
    }
    
    .cta-btn.secondary:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 51, 102, 0.3);
    }
    
    .modal-overlay {
        backdrop-filter: blur(4px);
    }
    .modal-content {
        animation: modalSlideIn 0.3s ease;
    }
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--success-green);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .toast.show {
        transform: translateX(0);
    }
    .toast.error {
        background: var(--danger-red);
    }
    @media (max-width: 1200px) {
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 28px;
        }
        .hero-stats {
            gap: 30px;
        }
    }
    @media (max-width: 900px) {
        .container {
            max-width: 98vw;
        }
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        .product-card {
            min-height: 420px;
        }
        .product-image-container {
            height: 220px;
        }
        .content-header {
            flex-direction: column;
            gap: 20px;
        }
        .hero-stats {
            gap: 20px;
        }
        .stat-item {
            min-width: 100px;
            padding: 16px;
        }
        .clearance-cta {
            padding: 60px 0;
        }
    }
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        .hero-subtitle {
            font-size: 1.1rem;
        }
        .hero-stats {
            flex-direction: column;
            gap: 16px;
        }
        .stat-item {
            min-width: 200px;
        }
        .section-title {
            font-size: 2rem;
        }
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 15px 0;
        }
        .product-card {
            min-height: 400px;
            border-radius: 20px;
        }
        .product-image-container {
            height: 200px;
        }
        .product-details {
            padding: 20px 16px 16px 16px;
        }
        .product-name {
            font-size: 1.1rem;
            min-height: 48px;
        }
        .product-price {
            font-size: 1.3rem;
        }
        .product-actions {
            flex-direction: column;
            gap: 10px;
        }
        .details-btn, .add-cart-btn, .wishlist-btn {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
            height: auto;
            min-height: 48px;
        }
        .wishlist-btn {
            width: 100%;
            min-width: auto;
        }
        .clearance-cta h3 {
            font-size: 2rem;
        }
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        .cta-btn {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }
    }
    @media (max-width: 480px) {
        .hero-title {
            font-size: 2rem;
        }
        .hero-subtitle {
            font-size: 1rem;
        }
        .hero-badge {
            padding: 10px 20px;
            font-size: 0.8rem;
        }
        .stat-item {
            min-width: 150px;
            padding: 12px;
        }
        .stat-number {
            font-size: 1.5rem;
        }
        .product-grid {
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 10px 0;
        }
        .product-card {
            min-height: 380px;
        }
        .product-image-container {
            height: 180px;
        }
        .product-details {
            padding: 16px 12px 12px 12px;
        }
        .product-name {
            font-size: 1rem;
            min-height: 40px;
        }
        .product-price {
            font-size: 1.2rem;
        }
        .product-actions {
            gap: 8px;
        }
        .details-btn, .add-cart-btn, .wishlist-btn {
            padding: 10px 16px;
            font-size: 0.9rem;
            height: auto;
            min-height: 44px;
        }
        .wishlist-btn {
            width: 100%;
            min-width: auto;
        }
        .clearance-cta {
            padding: 40px 0;
        }
        .clearance-cta h3 {
            font-size: 1.8rem;
        }
        .cta-icon {
            width: 60px;
            height: 60px;
        }
        .cta-icon i {
            font-size: 1.5rem;
        }
    }
</style>

<script>
// Lazy blur effect for images
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.lazy-blur').forEach(img => {
        img.addEventListener('load', function() {
            img.style.filter = 'blur(0)';
        });
    });
});
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

// Quick View Modal Function
function quickView(productId) {
    console.log('Quick view function called for product:', productId);
    
    // Show loading state
    const modal = document.getElementById('quickViewModal');
    const content = document.getElementById('quickViewContent');
    content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#003366;"></i><p>Loading product details...</p></div>';
    modal.style.display = 'flex';

    fetch(`/bicol-university-ecommerce/api/products/quickview.php?id=${productId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><p>Failed to load product details. Please try again.</p></div>';
        });
}

// Close modal function
function closeModal() {
    document.getElementById('quickViewModal').style.display = 'none';
}

// AJAX Add to Cart
function addToCart(productId, btn) {
    btn.disabled = true;
    const spinner = document.createElement('span');
    spinner.className = 'cart-spinner';
    spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.appendChild(spinner);
    
    fetch('/bicol-university-ecommerce/api/cart/add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + encodeURIComponent(productId) + '&quantity=1'
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        spinner.remove();
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            showNotification(data.message, 'success');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
            }, 1200);
        } else {
            // Handle authentication redirect
            if (data.redirect) {
                showNotification(data.message, 'error');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                btn.innerHTML = '<i class="fas fa-times"></i> Error';
                showNotification(data.message, 'error');
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                }, 1200);
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        spinner.remove();
        btn.innerHTML = '<i class="fas fa-times"></i> Error';
        showNotification('An error occurred. Please try again.', 'error');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
        }, 1200);
    });
}

// AJAX Add to Wishlist
function addToWishlist(productId, btn) {
    btn.disabled = true;
    const icon = btn.querySelector('i');
    const originalIcon = icon.className;
    
    fetch('/bicol-university-ecommerce/api/wishlist/toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            // Toggle heart icon
            if (data.action === 'added') {
                icon.className = 'fas fa-heart';
                icon.style.color = '#e74c3c';
            } else {
                icon.className = 'far fa-heart';
                icon.style.color = '';
            }
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
        btn.disabled = false;
        showNotification('An error occurred. Please try again.', 'error');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart buttons
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            addToCart(this.getAttribute('data-product-id'), this);
        });
    });
    
    // Wishlist buttons
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            addToWishlist(this.getAttribute('data-product-id'), this);
        });
    });

    // Modal close functionality
    document.getElementById('closeModal').addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    document.getElementById('quickViewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
</body>
</html> 