<?php
// pages/products/index.php
$page_title = "Products";
require_once '../../includes/header.php';
require_once '../../includes/homepage_backend.php';

// Security: Prevent XSS and SQL Injection
function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Get and sanitize filter parameters
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'name';
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : '';
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : '';
$availability = isset($_GET['availability']) ? sanitize_input($_GET['availability']) : '';
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : '';
$view_mode = isset($_GET['view']) ? sanitize_input($_GET['view']) : 'grid';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = $view_mode === 'list' ? 8 : 12;
$offset = ($page - 1) * $limit;

// Get products with filters
try {
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    $param_types = '';

    if ($category) {
        $where_conditions[] = "p.category = ?";
        $params[] = $category;
        $param_types .= 's';
    }

    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.category LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $param_types .= 'sss';
    }

    if ($price_min !== '') {
        $where_conditions[] = "p.price >= ?";
        $params[] = $price_min;
        $param_types .= 'd';
    }

    if ($price_max !== '') {
        $where_conditions[] = "p.price <= ?";
        $params[] = $price_max;
        $param_types .= 'd';
    }

    if ($availability === 'in_stock') {
        $where_conditions[] = "p.stock_quantity > 0";
    } elseif ($availability === 'out_of_stock') {
        $where_conditions[] = "p.stock_quantity = 0";
    }

    if ($rating !== '') {
        $where_conditions[] = "COALESCE(AVG(pr.rating), 0) >= ?";
        $params[] = $rating;
        $param_types .= 'i';
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM products p WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);

    // Bind parameters dynamically
    if (!empty($params)) {
        $count_stmt->execute($params);
    } else {
        $count_stmt->execute();
    }

    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $limit);

    // Get products with enhanced sorting
    $order_clause = match ($sort) {
        'price_low' => 'p.price ASC',
        'price_high' => 'p.price DESC',
        'newest' => 'p.created_at DESC',
        'popular' => 'sales_count DESC',
        'rating' => 'average_rating DESC',
        'name_az' => 'p.name ASC',
        'name_za' => 'p.name DESC',
        default => 'p.name ASC'
    };

    $sql = "
        SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.category, 
            p.image, 
            p.description,
            p.stock_quantity,
            p.discount_percentage,
            p.featured,
            COALESCE(AVG(pr.rating), 0) as average_rating,
            COUNT(DISTINCT pr.id) as review_count,
            COUNT(DISTINCT oi.id) as sales_count
        FROM products p
        LEFT JOIN product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
        WHERE $where_clause
        GROUP BY p.id
        ORDER BY $order_clause
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get categories with product counts
    $cat_stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM products 
        WHERE status = 'active' 
        GROUP BY category 
        ORDER BY category
    ");
    $categories = $cat_stmt->fetchAll();

    // Get price range for filter
    $price_stmt = $pdo->query("
        SELECT MIN(price) as min_price, MAX(price) as max_price 
        FROM products 
        WHERE status = 'active'
    ");
    $price_range = $price_stmt->fetch();
} catch (PDOException $e) {
    error_log("Products query failed: " . $e->getMessage());
    $products = [];
    $categories = [];
    $total_pages = 1;
    $price_range = ['min_price' => 0, 'max_price' => 10000];
    $error_message = "We're experiencing technical difficulties. Please try again later.";
}

// Format products for display
$formatted_products = [];
foreach ($products as $product) {
    $price = $product['price'];
    $original_price = $price;

    if ($product['discount_percentage'] > 0) {
        $discounted_price = $price - ($price * $product['discount_percentage'] / 100);
        $price = $discounted_price;
    }

    $formatted_products[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'image' => $product['image'] ?: 'default-product.jpg',
        'price' => '₱' . number_format($price, 2),
        'original_price' => $original_price != $price ? '₱' . number_format($original_price, 2) : null,
        'category' => $product['category'],
        'description' => $product['description'],
        'stock_quantity' => $product['stock_quantity'],
        'average_rating' => round($product['average_rating'], 1),
        'review_count' => $product['review_count'],
        'in_stock' => $product['stock_quantity'] > 0,
        'featured' => $product['featured'],
        'discount_percentage' => $product['discount_percentage'],
        'sales_count' => $product['sales_count']
    ];
}

// Generate canonical URL for SEO
$canonical_url = SITE_URL . '/products/?' . http_build_query($_GET);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - BUragon | Bicol University Official Store</title>
    <meta name="description" content="Browse our collection of official Bicol University merchandise, apparel, and accessories. Quality products with student discounts available.">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/products.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #ff6b00;
            --accent: #ff9e4d;
            --light-bg: #f8fafc;
            --dark-text: #222;
            --light-text: #666;
            --border-color: #e1e8ed;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --white: #ffffff;
            --gray-light: #f5f5f5;
            --gray-medium: #6c757d;
            --gray-dark: #343a40;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 15px;
        }

        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark-text);
            line-height: 1.6;
            background-color: var(--light-bg);
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #002244 100%);
            color: var(--white);
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('<?php echo SITE_URL; ?>/assets/images/bu-pattern.png') repeat;
            opacity: 0.1;
            pointer-events: none;
        }

        .page-title {
            font-size: 2.8rem;
            margin-bottom: 15px;
            position: relative;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
        }

        /* Breadcrumb Navigation */
        .breadcrumb {
            padding: 15px 0;
            font-size: 0.9rem;
            color: var(--gray-medium);
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 8px;
            color: var(--gray-medium);
        }

        /* Filters Section */
        .filters-section {
            background: var(--white);
            padding: 30px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }

        .filters-toggle {
            display: none;
            background: var(--secondary);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            width: 100%;
            transition: var(--transition);
        }

        .filters-toggle:hover {
            background: #e05d00;
            transform: translateY(-2px);
        }

        .filters-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: stretch;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group--wide {
            grid-column: span 2;
        }

        .filter-group--actions {
            grid-column: span 2;
        }

        .filter-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 0.95rem;
        }

        .filter-input,
        .filter-select {
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--white);
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.2);
        }

        .filter-button {
            background: var(--secondary);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .filter-button:hover {
            background: #e05d00;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .clear-button {
            background: var(--gray-medium);
            margin-top: 10px;
        }

        .clear-button:hover {
            background: #5a6268;
        }

        /* Price Range Slider */
        .price-range-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .price-range-inputs {
            display: flex;
            gap: 10px;
        }

        .price-range-inputs input {
            flex: 1;
        }

        .price-range-slider {
            width: 100%;
            height: 6px;
            background: var(--border-color);
            border-radius: 3px;
            margin-top: 10px;
            position: relative;
        }

        .price-range-slider .track {
            height: 100%;
            background: var(--secondary);
            border-radius: 3px;
            position: absolute;
        }

        .price-range-slider .thumb {
            width: 18px;
            height: 18px;
            background: var(--white);
            border: 2px solid var(--secondary);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
        }

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-count {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .view-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .view-buttons {
            display: flex;
            gap: 5px;
        }

        .view-btn {
            background: var(--gray-light);
            border: 2px solid var(--border-color);
            padding: 8px 12px;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            color: var(--dark-text);
        }

        .view-btn.active {
            background: var(--secondary);
            border-color: var(--secondary);
            color: var(--white);
        }

        .view-btn:hover {
            border-color: var(--secondary);
        }

        .sort-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .sort-select:focus {
            outline: none;
            border-color: var(--secondary);
        }

        /* Products Grid View */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .product-card.featured {
            border: 2px solid var(--secondary);
        }

        .product-card.featured::before {
            content: '⭐ Featured';
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 10;
        }

        .product-image-container {
            height: 220px;
            background: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .product-image {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .product-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            z-index: 2;
        }

        .badge {
            padding: 4px 10px;
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--white);
        }

        .badge-featured {
            background: var(--secondary);
        }

        .badge-discount {
            background: var(--danger);
        }

        .badge-out-of-stock {
            background: var(--gray-medium);
        }

        .badge-new {
            background: var(--success);
        }

        .badge-bestseller {
            background: var(--warning);
            color: var(--dark-text);
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            display: inline-block;
            background: rgba(0, 51, 102, 0.1);
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: var(--border-radius-sm);
            padding: 3px 10px;
            margin-bottom: 10px;
            align-self: flex-start;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary);
            line-height: 1.3;
            flex: 1;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .current-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary);
        }

        .original-price {
            font-size: 0.95rem;
            color: var(--gray-medium);
            text-decoration: line-through;
        }

        .discount-percentage {
            font-size: 0.85rem;
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            padding: 2px 8px;
            border-radius: var(--border-radius-sm);
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }

        .stars {
            color: var(--warning);
            font-size: 0.9rem;
        }

        .rating-text {
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .product-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .product-stat {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .btn-view {
            flex: 1;
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-view:hover {
            background: #002244;
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-cart {
            background: var(--secondary);
            color: var(--white);
            border: none;
            padding: 10px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-cart:hover {
            background: #e05d00;
            transform: translateY(-2px);
        }

        .btn-cart:disabled {
            background: var(--gray-medium);
            cursor: not-allowed;
            transform: none;
        }

        .wishlist-btn {
            background: var(--white);
            color: var(--gray-medium);
            border: 2px solid var(--border-color);
            padding: 8px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wishlist-btn:hover {
            background: var(--gray-light);
            border-color: var(--gray-medium);
            color: var(--dark-text);
        }

        .wishlist-btn i {
            font-size: 1rem;
            transition: color 0.2s;
        }

        .wishlist-btn:hover i {
            color: var(--danger);
        }

        .wishlist-btn.active {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
        }

        .wishlist-btn.active:hover {
            background: var(--success);
            border-color: var(--success);
        }

        /* Products List View */
        .products-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }

        .product-list-item {
            background: var(--white);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            position: relative;
        }

        .product-list-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .list-image-container {
            width: 180px;
            height: 180px;
            background: var(--gray-light);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .list-image {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .product-list-item:hover .list-image {
            transform: scale(1.1);
        }

        .list-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .list-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
            min-width: 200px;
        }

        .list-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .list-description {
            color: var(--light-text);
            margin-bottom: 10px;
            line-height: 1.5;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
            transition: var(--transition);
            min-width: 40px;
            text-align: center;
        }

        .page-link:hover {
            border-color: var(--secondary);
            background: var(--secondary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .page-link.active {
            background: var(--secondary);
            border-color: var(--secondary);
            color: var(--white);
        }

        .page-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* No Products Found */
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 40px;
        }

        .no-products i {
            font-size: 4rem;
            color: var(--gray-medium);
            margin-bottom: 20px;
        }

        .no-products h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .no-products p {
            color: var(--light-text);
            margin-bottom: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--border-color);
            border-top-color: var(--secondary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Quick View Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--border-radius-md);
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-medium);
        }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .product-list-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .list-image-container {
                width: 100%;
                height: 200px;
            }

            .list-actions {
                width: 100%;
                flex-direction: row;
                margin-top: 15px;
            }
        }

        @media (max-width: 992px) {
            .filters-section {
                position: static;
            }

            .filters-toggle {
                display: block;
            }

            .filters-grid {
                display: none;
            }

            .filters-grid.visible {
                display: grid;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 40px 0;
            }

            .page-title {
                font-size: 2.2rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .results-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .view-controls {
                width: 100%;
                justify-content: space-between;
            }

            .product-actions {
                flex-direction: column;
            }

            .btn-view,
            .btn-cart {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .filter-group {
                grid-column: span 2;
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }

        /* Print Styles */
        @media print {

            .page-header,
            .filters-section,
            .results-info,
            .product-actions {
                display: none !important;
            }

            .products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
            }

            .product-card {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal-overlay" id="quickViewModal">
        <div class="modal-content">
            <button class="close-modal" id="closeModal">&times;</button>
            <div id="quickViewContent"></div>
        </div>
    </div>

    <div class="products-page">
        <!-- Breadcrumb Navigation -->
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Products</span>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1 class="page-title">Our Products</h1>
                <p class="page-subtitle">Discover quality Bicol University merchandise, apparel, and accessories</p>
            </div>
        </div>

        <div class="container">
            <!-- Classic Professional Filter Section -->
            <form method="GET" action="" class="classic-filters" autocomplete="off">
                <div class="classic-filters-row">
                    <!-- Search -->
                    <div class="classic-filter-group">
                        <label for="classic-search" class="classic-filter-label">Search</label>
                        <input type="text" id="classic-search" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" class="classic-filter-input" placeholder="Search products...">
                    </div>
                    <!-- Category -->
                    <div class="classic-filter-group">
                        <label for="classic-category" class="classic-filter-label">Category</label>
                        <select id="classic-category" name="category" class="classic-filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo ($category ?? '') === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?> (<?php echo $cat['count']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Price Range -->
                    <div class="classic-filter-group classic-filter-price-group">
                        <label class="classic-filter-label">Price Range</label>
                        <div class="classic-filter-price-fields">
                            <input type="number" name="price_min" id="classic-price-min" value="<?php echo htmlspecialchars($price_min ?? ''); ?>" class="classic-filter-input classic-filter-input-short" placeholder="Min">
                            <span class="classic-filter-price-sep">-</span>
                            <input type="number" name="price_max" id="classic-price-max" value="<?php echo htmlspecialchars($price_max ?? ''); ?>" class="classic-filter-input classic-filter-input-short" placeholder="Max">
                        </div>
                    </div>
                    <!-- Availability -->
                    <div class="classic-filter-group">
                        <label for="classic-availability" class="classic-filter-label">Availability</label>
                        <select id="classic-availability" name="availability" class="classic-filter-select">
                            <option value="">All Items</option>
                            <option value="in_stock" <?php echo ($availability ?? '') === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                            <option value="out_of_stock" <?php echo ($availability ?? '') === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <!-- Sort -->
                    <div class="classic-filter-group">
                        <label for="classic-sort" class="classic-filter-label">Sort By</label>
                        <select id="classic-sort" name="sort" class="classic-filter-select">
                            <option value="name" <?php echo ($sort ?? '') === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="name_za" <?php echo ($sort ?? '') === 'name_za' ? 'selected' : ''; ?>>Name Z-A</option>
                            <option value="price_low" <?php echo ($sort ?? '') === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo ($sort ?? '') === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo ($sort ?? '') === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="popular" <?php echo ($sort ?? '') === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="rating" <?php echo ($sort ?? '') === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                    <!-- Actions -->
                    <div class="classic-filter-group classic-filter-actions">
                        <button type="submit" class="classic-filter-btn">Apply</button>
                        <a href="?" class="classic-filter-btn classic-filter-clear">Clear</a>
                    </div>
                </div>
            </form>
            <!-- Results Info -->
            <div class="results-info">
                <div class="results-count">
                    <?php echo number_format($total_products); ?> product<?php echo $total_products != 1 ? 's' : ''; ?> found
                    <?php if ($search || $category || $price_min !== '' || $price_max !== '' || $availability || $rating): ?>
                        <span style="font-size: 0.9rem; color: var(--light-text); margin-left: 8px;">
                            (filtered results)
                        </span>
                    <?php endif; ?>
                </div>
                <div class="view-controls">
                    <div class="view-buttons">
                        <button class="view-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>"
                            onclick="changeView('grid')" title="Grid View" aria-label="Grid view">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>"
                            onclick="changeView('list')" title="List View" aria-label="List view">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    <div>
                        <label for="sort-select">Sort: </label>
                        <select id="sort-select" class="sort-select" onchange="updateSort(this.value)">
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Name Z-A</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Products Display -->
            <?php if (!empty($formatted_products)): ?>
                <?php if ($view_mode === 'grid'): ?>
                    <div class="products-grid">
                        <?php foreach ($formatted_products as $product): ?>
                            <div class="product-card <?php echo $product['featured'] ? 'featured' : ''; ?>">
                                <div class="product-image-container">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="product-image"
                                        loading="lazy">

                                    <div class="product-badges">
                                        <?php if ($product['featured']): ?>
                                            <span class="badge badge-featured">Featured</span>
                                        <?php endif; ?>

                                        <?php if ($product['discount_percentage'] > 0): ?>
                                            <span class="badge badge-discount">-<?php echo $product['discount_percentage']; ?>%</span>
                                        <?php endif; ?>

                                        <?php if (!$product['in_stock']): ?>
                                            <span class="badge badge-out-of-stock">Out of Stock</span>
                                        <?php endif; ?>

                                        <?php if ($product['sales_count'] > 50): ?>
                                            <span class="badge badge-bestseller">Bestseller</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="product-info">
                                    <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>

                                    <div class="product-price">
                                        <span class="current-price"><?php echo $product['price']; ?></span>
                                        <?php if ($product['original_price']): ?>
                                            <span class="original-price"><?php echo $product['original_price']; ?></span>
                                            <span class="discount-percentage">Save <?php echo $product['discount_percentage']; ?>%</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $product['average_rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">
                                            <?php echo $product['average_rating']; ?>
                                            (<?php echo $product['review_count']; ?> review<?php echo $product['review_count'] != 1 ? 's' : ''; ?>)
                                        </span>
                                    </div>

                                    <div class="product-stats">
                                        <span class="product-stat">
                                            <i class="fas fa-chart-line"></i> <?php echo $product['sales_count']; ?> sold
                                        </span>
                                        <span class="product-stat">
                                            <i class="fas fa-box"></i> <?php echo $product['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </div>

                                    <div class="product-actions">
                                        <button class="btn-view" data-product-id="<?php echo $product['id']; ?>" type="button">
                                            <i class="fas fa-eye"></i> Quick View
                                        </button>
                                        <button class="btn-cart"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            <?php echo !$product['in_stock'] ? 'disabled' : ''; ?>
                                            aria-label="Add to cart" type="button">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <button class="wishlist-btn"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            aria-label="Add to wishlist" type="button">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="products-list">
                        <?php foreach ($formatted_products as $product): ?>
                            <div class="product-list-item">
                                <div class="list-image-container">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="list-image"
                                        loading="lazy">

                                    <div class="product-badges">
                                        <?php if ($product['featured']): ?>
                                            <span class="badge badge-featured">Featured</span>
                                        <?php endif; ?>

                                        <?php if ($product['discount_percentage'] > 0): ?>
                                            <span class="badge badge-discount">-<?php echo $product['discount_percentage']; ?>%</span>
                                        <?php endif; ?>

                                        <?php if (!$product['in_stock']): ?>
                                            <span class="badge badge-out-of-stock">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="list-info">
                                    <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <h3 class="list-title"><?php echo htmlspecialchars($product['name']); ?></h3>

                                    <div class="product-price">
                                        <span class="current-price"><?php echo $product['price']; ?></span>
                                        <?php if ($product['original_price']): ?>
                                            <span class="original-price"><?php echo $product['original_price']; ?></span>
                                            <span class="discount-percentage">Save <?php echo $product['discount_percentage']; ?>%</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $product['average_rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">
                                            <?php echo $product['average_rating']; ?>
                                            (<?php echo $product['review_count']; ?> review<?php echo $product['review_count'] != 1 ? 's' : ''; ?>)
                                        </span>
                                    </div>

                                    <p class="list-description">
                                        <?php echo substr(htmlspecialchars($product['description']), 0, 200); ?>...
                                    </p>

                                    <div class="product-stats">
                                        <span class="product-stat">
                                            <i class="fas fa-chart-line"></i> <?php echo $product['sales_count']; ?> sold
                                        </span>
                                        <span class="product-stat">
                                            <i class="fas fa-box"></i> <?php echo $product['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="list-actions">
                                    <button class="btn-view" data-product-id="<?php echo $product['id']; ?>" type="button">
                                        <i class="fas fa-eye"></i> Quick View
                                    </button>
                                    <button class="btn-cart"
                                        data-product-id="<?php echo $product['id']; ?>"
                                        <?php echo !$product['in_stock'] ? 'disabled' : ''; ?> type="button">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <button class="wishlist-btn" data-product-id="<?php echo $product['id']; ?>" type="button">
                                        <i class="far fa-heart"></i> Wishlist
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
                                class="page-link" title="First Page">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                                class="page-link" title="Previous Page">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link disabled"><i class="fas fa-angle-double-left"></i></span>
                            <span class="page-link disabled"><i class="fas fa-angle-left"></i></span>
                        <?php endif; ?>

                        <?php
                        // Show first page
                        if ($page > 3): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
                                class="page-link">1</a>
                            <?php if ($page > 4): ?>
                                <span class="page-link disabled">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php
                        // Show pages around current page
                        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php
                        // Show last page
                        if ($page < $total_pages - 2): ?>
                            <?php if ($page < $total_pages - 3): ?>
                                <span class="page-link disabled">...</span>
                            <?php endif; ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"
                                class="page-link"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                                class="page-link" title="Next Page">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"
                                class="page-link" title="Last Page">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link disabled"><i class="fas fa-angle-right"></i></span>
                            <span class="page-link disabled"><i class="fas fa-angle-double-right"></i></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div style="margin-bottom: 40px;"></div>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>We couldn't find any products matching your criteria. Try adjusting your filters or browse our full catalog.</p>
                    <a href="?" class="btn-view">View All Products</a>
                </div>
            <?php endif; ?>
        </div>


        <script>
            // Global variables
            let minPrice = <?php echo $price_range['min_price'] ?? 0; ?>;
            let maxPrice = <?php echo $price_range['max_price'] ?? 10000; ?>;
            let currentMinPrice = <?php echo $price_min !== '' ? $price_min : $price_range['min_price'] ?? 0; ?>;
            let currentMaxPrice = <?php echo $price_max !== '' ? $price_max : $price_range['max_price'] ?? 10000; ?>;

            // Document ready
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize price range slider
                // initPriceSlider(); // Commented out as per edit hint

                // Set up event listeners
                setupEventListeners();
                
                // Attach product button listeners
                attachProductButtonListeners();

                // Show filters on mobile if filters are applied
                if (window.innerWidth <= 992 && (window.location.search.includes('search=') ||
                        window.location.search.includes('category=') ||
                        window.location.search.includes('price_min=') ||
                        window.location.search.includes('price_max=') ||
                        window.location.search.includes('availability=') ||
                        window.location.search.includes('rating='))) {
                    document.getElementById('filtersGrid').classList.add('visible');
                }
            });

            // Initialize price range slider
            function initPriceSlider() {
                const slider = document.getElementById('priceRangeSlider');
                const track = document.getElementById('priceRangeTrack');
                const minThumb = document.getElementById('priceMinThumb');
                const maxThumb = document.getElementById('priceMaxThumb');
                const minInput = document.querySelector('input[name="price_min"]');
                const maxInput = document.querySelector('input[name="price_max"]');

                // Set initial positions
                updateSlider();

                // Mouse events for min thumb
                minThumb.addEventListener('mousedown', function(e) {
                    startDrag(e, minThumb, 'min');
                });

                // Mouse events for max thumb
                maxThumb.addEventListener('mousedown', function(e) {
                    startDrag(e, maxThumb, 'max');
                });

                // Input events
                minInput.addEventListener('input', function() {
                    currentMinPrice = Math.min(parseFloat(this.value) || minPrice, currentMaxPrice);
                    updateSlider();
                });

                maxInput.addEventListener('input', function() {
                    currentMaxPrice = Math.max(parseFloat(this.value) || maxPrice, currentMinPrice);
                    updateSlider();
                });

                function startDrag(e, thumb, type) {
                    e.preventDefault();
                    const startX = e.clientX;
                    const startLeft = parseInt(thumb.style.left || '0');
                    const sliderRect = slider.getBoundingClientRect();
                    const sliderWidth = sliderRect.width;

                    function moveThumb(e) {
                        const deltaX = e.clientX - startX;
                        let newLeft = startLeft + deltaX;

                        // Constrain within slider bounds
                        newLeft = Math.max(0, Math.min(sliderWidth, newLeft));

                        // Convert to price value
                        const percentage = newLeft / sliderWidth;
                        const priceValue = minPrice + percentage * (maxPrice - minPrice);

                        if (type === 'min') {
                            currentMinPrice = Math.min(priceValue, currentMaxPrice);
                            minInput.value = Math.round(currentMinPrice * 100) / 100;
                        } else {
                            currentMaxPrice = Math.max(priceValue, currentMinPrice);
                            maxInput.value = Math.round(currentMaxPrice * 100) / 100;
                        }

                        updateSlider();
                    }

                    function stopDrag() {
                        document.removeEventListener('mousemove', moveThumb);
                        document.removeEventListener('mouseup', stopDrag);
                    }

                    document.addEventListener('mousemove', moveThumb);
                    document.addEventListener('mouseup', stopDrag);
                }

                function updateSlider() {
                    const minPercentage = (currentMinPrice - minPrice) / (maxPrice - minPrice) * 100;
                    const maxPercentage = (currentMaxPrice - minPrice) / (maxPrice - minPrice) * 100;

                    track.style.left = minPercentage + '%';
                    track.style.width = (maxPercentage - minPercentage) + '%';

                    minThumb.style.left = minPercentage + '%';
                    maxThumb.style.left = maxPercentage + '%';
                }
            }

            // Set up event listeners
            function setupEventListeners() {
                // Close modal
                document.getElementById('closeModal').addEventListener('click', function() {
                    document.getElementById('quickViewModal').classList.remove('active');
                });

                // Close modal when clicking outside
                document.getElementById('quickViewModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('active');
                    }
                });

                // Close modal with ESC key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        document.getElementById('quickViewModal').classList.remove('active');
                    }
                });
            }

            // Change view mode (grid/list)
            function changeView(mode) {
                const url = new URL(window.location.href);
                url.searchParams.set('view', mode);
                url.searchParams.set('page', 1);
                window.location.href = url.toString();
            }

            // Update sort parameter
            function updateSort(sortValue) {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortValue);
                window.location.href = url.toString();
            }

            // Quick view modal
            function quickView(productId) {
                console.log('Quick view function called for product:', productId);
                showLoading();

                fetch(`/bicol-university-ecommerce/api/products/quickview.php?id=${productId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('quickViewContent').innerHTML = html;
                        document.getElementById('quickViewModal').classList.add('active');
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        hideLoading();
                        alert('Failed to load product details. Please try again.');
                    });
            }

            // Add to cart function
            function addToCart(productId, quantity = 1) {
                console.log('Add to cart function called for product:', productId, 'quantity:', quantity);
                showLoading();

                fetch(`/bicol-university-ecommerce/api/cart/add.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Cart API response:', data);
                        if (data.success) {
                            // Update cart count in header
                            updateCartCount(data.cart_count);

                            // Show success notification
                            showNotification(data.message || 'Product added to cart!', 'success');
                        } else {
                            // Handle authentication redirect
                            if (data.redirect) {
                                showNotification(data.message, 'error');
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 2000);
                            } else {
                                showNotification(data.message || 'Error adding product to cart', 'error');
                            }
                        }
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Failed to add product to cart. Please try again.', 'error');
                        hideLoading();
                    });
            }

            // Toggle wishlist function
            function toggleWishlist(productId, button) {
                console.log('Toggle wishlist function called for product:', productId);
                button.disabled = true;
                const icon = button.querySelector('i');

                fetch(`/bicol-university-ecommerce/api/wishlist/toggle.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Wishlist API response:', data);
                        if (data.success) {
                            // Toggle heart icon
                            if (data.action === 'added') {
                                icon.className = 'fas fa-heart';
                                icon.style.color = '#e74c3c';
                            } else {
                                icon.className = 'far fa-heart';
                                icon.style.color = '';
                            }
                            button.classList.toggle('active');
                            const message = data.action === 'added' ?
                                'Added to wishlist!' : 'Removed from wishlist!';
                            showNotification(message, 'success');
                        } else {
                            // Handle authentication redirect
                            if (data.redirect) {
                                showNotification(data.message, 'error');
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 2000);
                            } else {
                                showNotification(data.message || 'Error updating wishlist', 'error');
                            }
                        }
                        button.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Failed to update wishlist. Please try again.', 'error');
                        button.disabled = false;
                    });
            }

            // Update cart count in header
            function updateCartCount(count) {
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(el => {
                    el.textContent = count;
                    el.style.display = count > 0 ? 'inline-block' : 'none';
                });
            }

            // Show loading overlay
            function showLoading() {
                document.getElementById('loadingOverlay').classList.add('active');
            }

            // Hide loading overlay
            function hideLoading() {
                document.getElementById('loadingOverlay').classList.remove('active');
            }

            // Show notification
            function showNotification(message, type = 'success') {
                // Remove existing notifications
                const existingNotifications = document.querySelectorAll('.notification');
                existingNotifications.forEach(notification => {
                    notification.remove();
                });

                // Create new notification
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="close-notification">&times;</button>
                `;

                document.body.appendChild(notification);

                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);

                // Auto-hide after 3 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);

                // Close button
                notification.querySelector('.close-notification').addEventListener('click', () => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });
            }

            // Note: Instant search functionality has been removed to prevent conflicts with button functionality

            // API base path is already correctly set in fetch calls

            // Attach event listeners for product action buttons
            function attachProductButtonListeners() {
                console.log('Attaching product button listeners...');
                
                // Remove existing listeners first to avoid duplicates
                document.querySelectorAll('.btn-view, .btn-cart, .wishlist-btn').forEach(button => {
                    button.replaceWith(button.cloneNode(true));
                });

                // Quick View
                const viewButtons = document.querySelectorAll('.btn-view');
                console.log('Found', viewButtons.length, 'view buttons');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.getAttribute('data-product-id');
                        console.log('Quick view clicked for product:', productId);
                        quickView(productId);
                    });
                });
                
                // Add to Cart
                const cartButtons = document.querySelectorAll('.btn-cart');
                console.log('Found', cartButtons.length, 'cart buttons');
                cartButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.getAttribute('data-product-id');
                        console.log('Add to cart clicked for product:', productId);
                        addToCart(productId, 1);
                    });
                });
                
                // Wishlist
                const wishlistButtons = document.querySelectorAll('.wishlist-btn');
                console.log('Found', wishlistButtons.length, 'wishlist buttons');
                wishlistButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const productId = this.getAttribute('data-product-id');
                        console.log('Wishlist clicked for product:', productId);
                        toggleWishlist(productId, this);
                    });
                });
            }

            // Call attachProductButtonListeners after a short delay to ensure DOM is ready
            setTimeout(() => {
                attachProductButtonListeners();
            }, 100);
        </script>

        <style>
            /* Notification Styles */
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: var(--border-radius-sm);
                box-shadow: var(--shadow-lg);
                z-index: 1100;
                display: flex;
                align-items: center;
                justify-content: space-between;
                max-width: 350px;
                transform: translateX(120%);
                transition: transform 0.3s ease;
            }

            .notification.show {
                transform: translateX(0);
            }

            .notification-success {
                background: #28a745;
                color: white;
            }

            .notification-error {
                background: #dc3545;
                color: white;
            }

            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .close-notification {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                margin-left: 15px;
                padding: 0;
            }
            .loading-overlay,
            .modal-overlay {
                pointer-events: none;
            }
            .loading-overlay.active,
            .modal-overlay.active {
                pointer-events: all;
            }
        </style>
        <?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
</body>

</html>