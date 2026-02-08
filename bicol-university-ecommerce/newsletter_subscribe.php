<?php
header('Content-Type: application/json');
require_once __DIR__ . '/functions/newsletter_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = $_POST['email'] ?? '';
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

if (saveNewsletterEmail($email)) {
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or duplicate email.']);
} 