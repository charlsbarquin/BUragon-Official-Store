<?php
// api/stripe_create_intent.php
require_once __DIR__ . '/../vendor/autoload.php';
use Stripe\Stripe;
use Stripe\PaymentIntent;

header('Content-Type: application/json');
require_once '../includes/config.php';

// Stripe test secret key (replace with your own in production)
$stripe_secret = 'sk_test_51N...'; // TODO: Replace with your Stripe test secret key

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

Stripe::setApiKey($stripe_secret);

try {
    $intent = PaymentIntent::create([
        'amount' => round($amount * 100), // Stripe expects cents
        'currency' => 'php',
        'payment_method_types' => ['card'],
    ]);
    echo json_encode(['client_secret' => $intent->client_secret]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 