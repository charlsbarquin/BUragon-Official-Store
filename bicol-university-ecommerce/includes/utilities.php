<?php
// ... existing code ...

/**
 * Send order confirmation email using PHPMailer
 * @param string $toEmail
 * @param string $toName
 * @param array $order (keys: id, items, total, address, etc.)
 * @param bool $isAdmin
 * @return bool
 */
function sendOrderConfirmationEmail($toEmail, $toName, $order, $isAdmin = false) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // SMTP config (optional, set your SMTP server here)
        // $mail->isSMTP();
        // $mail->Host = 'smtp.example.com';
        // $mail->SMTPAuth = true;
        // $mail->Username = 'your@email.com';
        // $mail->Password = 'yourpassword';
        // $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port = 587;

        $mail->setFrom('no-reply@bicol-university-ecommerce.local', 'BUragon Store');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $isAdmin ? 'New Order Received' : 'Your BUragon Order Confirmation';
        $mail->Body = '<h2>Thank you for your order!</h2>' .
            '<p>Order ID: <strong>' . htmlspecialchars($order['id']) . '</strong></p>' .
            '<p>Name: ' . htmlspecialchars($toName) . '</p>' .
            '<p>Address: ' . htmlspecialchars($order['address']) . '</p>' .
            '<h3>Order Details:</h3>' .
            '<ul>' .
            implode('', array_map(function($item) {
                return '<li>' . htmlspecialchars($item['name']) . ' x ' . $item['quantity'] . ' - ₱' . number_format($item['subtotal'], 2) . '</li>';
            }, $order['items'])) .
            '</ul>' .
            '<p><strong>Total: ₱' . number_format($order['total'], 2) . '</strong></p>' .
            '<p>If you have questions, reply to this email.</p>';
        $mail->AltBody = 'Thank you for your order! Order ID: ' . $order['id'];
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Order email error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generate a PDF invoice for an order using Dompdf
 * @param array $order (keys: id, items, total, address, name, email)
 * @return string PDF binary data
 */
function generateOrderInvoicePDF($order) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $options = new Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf\Dompdf($options);
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; color: #222; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { color: #003366; margin: 0; }
        .header p { color: #666; margin: 0; }
        .info { margin-bottom: 18px; }
        .info strong { color: #003366; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #e1e8ed; padding: 8px; text-align: left; }
        th { background: #fafdff; color: #003366; }
        .total { text-align: right; font-size: 1.1em; font-weight: bold; }
        .footer { color: #888; font-size: 0.95em; text-align: center; margin-top: 24px; }
    </style></head><body>';
    $html .= '<div class="header"><h1>BUragon Store</h1><p>Official Bicol University Merchandise</p></div>';
    $html .= '<div class="info">
        <strong>Invoice #: </strong>' . htmlspecialchars($order['id']) . '<br>
        <strong>Date: </strong>' . date('F j, Y') . '<br>
        <strong>Customer: </strong>' . htmlspecialchars($order['name'] ?? '') . '<br>
        <strong>Email: </strong>' . htmlspecialchars($order['email'] ?? '') . '<br>
        <strong>Address: </strong>' . htmlspecialchars($order['address']) . '
    </div>';
    $html .= '<table><thead><tr><th>Item</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>';
    foreach ($order['items'] as $item) {
        $html .= '<tr><td>' . htmlspecialchars($item['name']) . '</td><td>' . $item['quantity'] . '</td><td>₱' . number_format($item['subtotal'], 2) . '</td></tr>';
    }
    $html .= '</tbody></table>';
    $html .= '<div class="total">Total: ₱' . number_format($order['total'], 2) . '</div>';
    $html .= '<div class="footer">Thank you for shopping with BUragon!<br>Bicol University Official Store</div>';
    $html .= '</body></html>';
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}
// ... existing code ...
