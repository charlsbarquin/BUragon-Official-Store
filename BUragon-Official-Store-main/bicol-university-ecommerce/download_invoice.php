<?php
// download_invoice.php
require_once 'vendor/autoload.php';
require_once 'includes/db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Security check - only allow downloads of valid invoice files
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid request');
}

$filename = $_GET['file'];
$filepath = sys_get_temp_dir() . '/' . $filename;

// Validate filename format and check if file exists
if (!preg_match('/^invoice_[A-Z0-9]+\.pdf$/', $filename) || !file_exists($filepath)) {
    header('HTTP/1.1 404 Not Found');
    exit('Invoice not found');
}

// Get order details from the filename
$order_id = str_replace(['invoice_', '.pdf'], '', $filename);

// Fetch order details from database (if available)
$pdo = getDbConnection();
$order_details = null;

try {
    // Try to get order from database if it exists
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_details = $stmt->fetch();
} catch (Exception $e) {
    // If no database record, we'll use the file directly
}

// If file exists, serve it
if (file_exists($filepath)) {
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Output the file
    readfile($filepath);
    exit;
} else {
    // If file doesn't exist, generate a new invoice
    generateInvoicePDF($order_id);
}

function generateInvoicePDF($order_id) {
    // Create new DOMPDF instance
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('defaultMediaType', 'screen');
    $options->set('isFontSubsettingEnabled', true);
    
    $dompdf = new Dompdf($options);
    
    // Generate HTML content for the invoice
    $html = generateInvoiceHTML($order_id);
    
    // Load HTML into DOMPDF
    $dompdf->loadHtml($html);
    
    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');
    
    // Render the PDF
    $dompdf->render();
    
    // Output the PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoice_' . $order_id . '.pdf"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    echo $dompdf->output();
    exit;
}

function generateInvoiceHTML($order_id) {
    // Get order details (this would typically come from database)
    // For demo purposes, we'll create sample data
    $order = [
        'id' => $order_id,
        'date' => date('F j, Y'),
        'time' => date('g:i A'),
        'customer' => [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+63 912 345 6789',
            'address' => '123 Main Street, Legazpi City, Albay, Philippines'
        ],
        'items' => [
            [
                'name' => 'BU Classic Hoodie',
                'category' => 'Apparel',
                'quantity' => 2,
                'price' => 850.00,
                'subtotal' => 1700.00
            ],
            [
                'name' => 'BU Cap',
                'category' => 'Accessories',
                'quantity' => 1,
                'price' => 250.00,
                'subtotal' => 250.00
            ],
            [
                'name' => 'BU Mug',
                'category' => 'Merchandise',
                'quantity' => 3,
                'price' => 150.00,
                'subtotal' => 450.00
            ]
        ],
        'subtotal' => 2400.00,
        'discount' => 120.00,
        'total' => 2280.00,
        'payment_method' => 'PayPal',
        'status' => 'Paid'
    ];
    
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice - ' . $order['id'] . '</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "DejaVu Sans", Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #333;
                background: #fff;
            }
            
            .invoice-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
            
            .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #003366;
            }
            
            .logo-section {
                flex: 1;
            }
            
            .logo-title {
                font-size: 24px;
                font-weight: bold;
                color: #003366;
                margin-bottom: 5px;
            }
            
            .logo-subtitle {
                font-size: 14px;
                color: #666;
                margin-bottom: 10px;
            }
            
            .logo-address {
                font-size: 11px;
                color: #666;
                line-height: 1.3;
            }
            
            .invoice-info {
                text-align: right;
                flex: 1;
            }
            
            .invoice-title {
                font-size: 28px;
                font-weight: bold;
                color: #003366;
                margin-bottom: 10px;
            }
            
            .invoice-number {
                font-size: 14px;
                color: #666;
                margin-bottom: 5px;
            }
            
            .invoice-date {
                font-size: 12px;
                color: #666;
            }
            
            .customer-section {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
            }
            
            .customer-info {
                flex: 1;
            }
            
            .section-title {
                font-size: 14px;
                font-weight: bold;
                color: #003366;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .customer-details {
                font-size: 12px;
                line-height: 1.4;
            }
            
            .customer-details p {
                margin-bottom: 3px;
            }
            
            .order-summary {
                flex: 1;
                text-align: right;
            }
            
            .summary-item {
                font-size: 12px;
                margin-bottom: 5px;
            }
            
            .summary-label {
                font-weight: bold;
                color: #666;
            }
            
            .summary-value {
                color: #333;
            }
            
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }
            
            .items-table th {
                background: #003366;
                color: white;
                padding: 12px 8px;
                text-align: left;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .items-table td {
                padding: 12px 8px;
                border-bottom: 1px solid #eee;
                font-size: 11px;
                vertical-align: top;
            }
            
            .items-table tr:nth-child(even) {
                background: #f9f9f9;
            }
            
            .item-name {
                font-weight: bold;
                color: #003366;
            }
            
            .item-category {
                font-size: 10px;
                color: #666;
                text-transform: uppercase;
                margin-top: 2px;
            }
            
            .item-quantity {
                text-align: center;
            }
            
            .item-price {
                text-align: right;
            }
            
            .item-subtotal {
                text-align: right;
                font-weight: bold;
                color: #003366;
            }
            
            .totals-section {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 30px;
            }
            
            .totals-table {
                width: 300px;
                border-collapse: collapse;
            }
            
            .totals-table td {
                padding: 8px 12px;
                font-size: 12px;
                border-bottom: 1px solid #eee;
            }
            
            .totals-table .label {
                text-align: left;
                color: #666;
            }
            
            .totals-table .value {
                text-align: right;
                font-weight: bold;
            }
            
            .totals-table .discount {
                color: #38a169;
            }
            
            .totals-table .total {
                background: #003366;
                color: white;
                font-size: 14px;
            }
            
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 10px;
                color: #666;
                text-align: center;
            }
            
            .footer-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .payment-info {
                text-align: left;
            }
            
            .thank-you {
                text-align: center;
                font-size: 12px;
                color: #003366;
                font-weight: bold;
            }
            
            .contact-info {
                text-align: right;
            }
            
            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                background: #38a169;
                color: white;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                border-radius: 3px;
            }
            
            .page-break {
                page-break-before: always;
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <!-- Header -->
            <div class="header">
                <div class="logo-section">
                    <div class="logo-title">Bicol University</div>
                    <div class="logo-subtitle">Official E-Commerce Store</div>
                    <div class="logo-address">
                        Rizal Street, Legazpi City<br>
                        Albay, Philippines 4500<br>
                        Tel: +63 52 820 6809<br>
                        Email: info@bu.edu.ph
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">Invoice #: ' . $order['id'] . '</div>
                    <div class="invoice-date">Date: ' . $order['date'] . '</div>
                    <div class="invoice-date">Time: ' . $order['time'] . '</div>
                </div>
            </div>
            
            <!-- Customer and Order Info -->
            <div class="customer-section">
                <div class="customer-info">
                    <div class="section-title">Bill To:</div>
                    <div class="customer-details">
                        <p><strong>' . $order['customer']['name'] . '</strong></p>
                        <p>' . $order['customer']['email'] . '</p>
                        <p>' . $order['customer']['phone'] . '</p>
                        <p>' . $order['customer']['address'] . '</p>
                    </div>
                </div>
                <div class="order-summary">
                    <div class="section-title">Order Summary</div>
                    <div class="summary-item">
                        <span class="summary-label">Payment Method:</span>
                        <span class="summary-value">' . $order['payment_method'] . '</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Status:</span>
                        <span class="summary-value">
                            <span class="status-badge">' . $order['status'] . '</span>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Items:</span>
                        <span class="summary-value">' . count($order['items']) . ' products</span>
                    </div>
                </div>
            </div>
            
            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($order['items'] as $item) {
        $html .= '
                    <tr>
                        <td>
                            <div class="item-name">' . $item['name'] . '</div>
                        </td>
                        <td>
                            <div class="item-category">' . $item['category'] . '</div>
                        </td>
                        <td class="item-quantity">' . $item['quantity'] . '</td>
                        <td class="item-price">&#8369;' . number_format($item['price'], 2) . '</td>
                        <td class="item-subtotal">&#8369;' . number_format($item['subtotal'], 2) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
            </table>
            
            <!-- Totals -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                                             <td class="value">&#8369;' . number_format($order['subtotal'], 2) . '</td>
                     </tr>
                     <tr>
                         <td class="label">Discount:</td>
                         <td class="value discount">-&#8369;' . number_format($order['discount'], 2) . '</td>
                     </tr>
                     <tr>
                         <td class="label">Total:</td>
                         <td class="value total">&#8369;' . number_format($order['total'], 2) . '</td>
                    </tr>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="footer-content">
                    <div class="payment-info">
                        <strong>Payment Information:</strong><br>
                        Method: ' . $order['payment_method'] . '<br>
                        Status: ' . $order['status'] . '<br>
                        Date: ' . $order['date'] . '
                    </div>
                    <div class="thank-you">
                        Thank you for your purchase!<br>
                        We appreciate your support of Bicol University.
                    </div>
                    <div class="contact-info">
                        <strong>Contact Us:</strong><br>
                        Email: info@bu.edu.ph<br>
                        Phone: +63 52 820 6809<br>
                        Website: www.bu.edu.ph
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?> 