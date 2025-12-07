<?php
// api/cart/add.php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login or signup to add items to cart.',
        'redirect' => '/bicol-university-ecommerce/pages/login.php'
    ]);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity.']);
    exit;
}

// Fetch product from DB
$pdo = getDbConnection();
$stmt = $pdo->prepare('SELECT id, name, price, image, stock_quantity FROM products WHERE id = ? AND status = "active"');
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}
if ($product['stock_quantity'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'This product is out of stock.']);
    exit;
}

if ($quantity > $product['stock_quantity']) {
    echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock.']);
    exit;
}

// User is logged in, add to persistent cart
$user_id = $_SESSION['user_id'];
// Check if product already in cart
$stmt = $pdo->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
$stmt->execute([$user_id, $product_id]);
$row = $stmt->fetch();
if ($row) {
    $new_qty = $row['quantity'] + $quantity;
    $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$new_qty, $user_id, $product_id]);
} else {
    $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $product_id, $quantity]);
}
// Get total cart count
$stmt = $pdo->prepare('SELECT SUM(quantity) FROM cart WHERE user_id = ?');
$stmt->execute([$user_id]);
$cart_count = $stmt->fetchColumn() ?? 0;

echo json_encode([
    'success' => true,
    'message' => $quantity . 'x ' . $product['name'] . ' added to cart!',
    'cart_count' => $cart_count
]); 