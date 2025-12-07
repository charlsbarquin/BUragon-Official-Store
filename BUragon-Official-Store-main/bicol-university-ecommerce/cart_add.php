<?php
header('Content-Type: application/json');
require_once __DIR__ . '/functions/cart_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$productId = $_POST['product_id'] ?? 0;
if (addProductToCartAjax($productId)) {
    $cartCount = getCartCount();
    echo json_encode([
        'success' => true, 
        'message' => 'Product added to cart.',
        'cart_count' => $cartCount
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
}
