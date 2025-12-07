<?php
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$page_title = 'My Orders';
$user_id = $_SESSION['user_id'];
$pdo = getDbConnection();
// Fetch orders for this user
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<link rel="stylesheet" href="../assets/css/cart.css">
<style>
.account-orders {
    max-width: 800px;
    margin: 0 auto 40px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    padding: 32px 24px 24px 24px;
}
.account-orders h2 {
    color: #003366;
    margin-bottom: 18px;
}
.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}
.orders-table th, .orders-table td {
    border: 1px solid #e1e8ed;
    padding: 10px 8px;
    text-align: left;
}
.orders-table th {
    background: #fafdff;
    color: #003366;
}
.order-status {
    font-weight: 600;
    color: #28a745;
}
.order-status.processing { color: #ff9800; }
.order-status.pending { color: #888; }
.order-status.cancelled { color: #dc3545; }
@media (max-width: 600px) {
    .account-orders { padding: 12px 2px; }
    .orders-table th, .orders-table td { font-size: 0.97em; padding: 7px 4px; }
}
</style>
<main class="account-orders">
    <h2>My Orders</h2>
    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
        <a href="products/index.php" class="cta-button">Shop Now</a>
    <?php else: ?>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                <td class="order-status <?php echo htmlspecialchars(strtolower($order['status'])); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></td>
                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <?php $invoice_file = 'invoice_' . $order['order_number'] . '.pdf'; ?>
                    <a href="<?php echo '../download_invoice.php?file=' . urlencode($invoice_file); ?>" target="_blank" class="cta-button" style="padding:4px 10px; font-size:0.97em;">Download</a>
                    <a href="account_order_view.php?order=<?php echo urlencode($order['order_number']); ?>" class="cta-button secondary" style="padding:4px 10px; font-size:0.97em; margin-left:6px;">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</main>
<?php require_once '../includes/footer.php'; ?> 