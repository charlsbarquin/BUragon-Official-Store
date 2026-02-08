<?php
require_once __DIR__ . '/../includes/db_connect.php';

function addProductToCartAjax($productId) {
    $productId = intval($productId);
    if ($productId <= 0) return false;
    
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        // Use database cart for logged-in users
        $pdo = getDbConnection();
        
        // Check if product already exists in cart
        $stmt = $pdo->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$_SESSION['user_id'], $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $stmt = $pdo->prepare('UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$_SESSION['user_id'], $productId]);
        } else {
            // Add new item
            $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)');
            $stmt->execute([$_SESSION['user_id'], $productId]);
        }
        
        return true;
    } else {
        // Use session cart for guests
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
        
        // Store cart as associative array with product ID as key and quantity as value
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity']++;
        } else {
            $_SESSION['cart'][$productId] = ['quantity' => 1];
    }
        
    return true;
    }
}

function getCartCount() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    if (isset($_SESSION['user_id'])) {
        // Get count from database for logged-in users
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT SUM(quantity) FROM cart WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn() ?? 0;
    } else {
        // Get count from session for guests
        if (!isset($_SESSION['cart'])) {
            return 0;
        }
        
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}
