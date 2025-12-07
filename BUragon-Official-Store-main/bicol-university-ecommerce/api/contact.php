<?php
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Get and sanitize input
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

// Validate
if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Prevent header injection
if (preg_match('/[\r\n]/', $name) || preg_match('/[\r\n]/', $email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Store in database
require_once __DIR__ . '/../includes/db_connect.php';
$pdo = getDbConnection();
try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);
} catch (PDOException $e) {
    error_log('Contact form DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'DB ERROR: ' . $e->getMessage()]);
    exit;
}

// (Optional) Email notification (can be commented out if not needed)
$to = 'support@buragon.com';
$subject = 'BUragon Contact Form Message';
$body = "Name: $name\nEmail: $email\nMessage:\n$message";
$headers = "From: $name <$email>\r\nReply-To: $email\r\n";
// @mail($to, $subject, $body, $headers); // Uncomment if you want to try sending email

// Success
echo json_encode(['success' => true, 'message' => 'Thank you for reaching out! We have received your message.']); 