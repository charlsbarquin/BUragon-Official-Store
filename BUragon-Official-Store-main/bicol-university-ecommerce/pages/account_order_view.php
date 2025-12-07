<?php
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$order_number = $_GET['order'] ?? '';
if (!$order_number) {
    header('Location: account_orders.php');
    exit;
}
$pdo = getDbConnection();
// Fetch order
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = ? AND user_id = ?');
$stmt->execute([$order_number, $user_id]);
$order = $stmt->fetch();
if (!$order) {
    header('Location: account_orders.php');
    exit;
}
// Fetch order items
$item_stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$item_stmt->execute([$order['id']]);
$items = $item_stmt->fetchAll();
?>
<link rel="stylesheet" href="../assets/css/cart.css">
<style>
.order-details {
    max-width: 700px;
    margin: 0 auto 40px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    padding: 32px 24px 24px 24px;
}
.order-details h2 {
    color: #003366;
    margin-bottom: 18px;
}
.order-info {
    margin-bottom: 18px;
    color: #003366;
}
.order-info strong { color: #003366; }
.order-items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}
.order-items-table th, .order-items-table td {
    border: 1px solid #e1e8ed;
    padding: 10px 8px;
    text-align: left;
}
.order-items-table th {
    background: #fafdff;
    color: #003366;
}
.order-total {
    text-align: right;
    font-size: 1.1em;
    font-weight: bold;
    color: #003366;
    margin-bottom: 12px;
}
@media (max-width: 600px) {
    .order-details { padding: 12px 2px; }
    .order-items-table th, .order-items-table td { font-size: 0.97em; padding: 7px 4px; }
}
</style>
<main class="order-details">
    <h2>Order Details</h2>
    <div class="order-info">
        <strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?><br>
        <strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?><br>
        <strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'])); ?><br>
        <strong>Payment:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'])); ?> (<?php echo htmlspecialchars(ucfirst($order['payment_status'])); ?>)<br>
        <strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?>
    </div>
    <table class="order-items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                <td>₱<?php echo number_format($item['total_price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="order-total">Total: ₱<?php echo number_format($order['total_amount'], 2); ?></div>
    <?php $invoice_file = 'invoice_' . $order['order_number'] . '.pdf'; ?>
    <a href="<?php echo '../download_invoice.php?file=' . urlencode($invoice_file); ?>" target="_blank" class="cta-button" style="margin-bottom:10px;">Download Invoice (PDF)</a>
    <a href="account_orders.php" class="cta-button secondary" style="margin-left:10px;">Back to Orders</a>
</main>
<?php require_once '../includes/footer.php'; ?> 