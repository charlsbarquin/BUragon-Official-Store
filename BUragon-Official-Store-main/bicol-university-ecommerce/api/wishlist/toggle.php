<?php
header('Content-Type: application/json');
require_once '../../functions/user_functions.php';
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

$user_id = getCurrentUserId();
if (!$user_id) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login or signup to manage your wishlist.',
        'redirect' => '/bicol-university-ecommerce/pages/login.php'
    ]);
    exit;
}

// User is logged in, manage persistent DB wishlist
$pdo = getDbConnection();
$stmt = $pdo->prepare('SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?');
$stmt->execute([$user_id, $product_id]);
if ($stmt->fetch()) {
    // Remove from wishlist
    $del = $pdo->prepare('DELETE FROM wishlists WHERE user_id = ? AND product_id = ?');
    $del->execute([$user_id, $product_id]);
    $action = 'removed';
    $message = 'Removed from wishlist!';
} else {
    // Add to wishlist
    $add = $pdo->prepare('INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)');
    $add->execute([$user_id, $product_id]);
    $action = 'added';
    $message = 'Added to wishlist!';
}
echo json_encode(['success' => true, 'action' => $action, 'message' => $message]);
