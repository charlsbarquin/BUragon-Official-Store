<?php
function saveNewsletterEmail($email) {
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    require_once __DIR__ . '/../includes/db_connect.php';
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (:email)');
        $stmt->execute(['email' => $email]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
} 