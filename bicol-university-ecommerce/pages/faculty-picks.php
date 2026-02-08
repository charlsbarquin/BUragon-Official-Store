<?php
$page_title = 'Faculty Picks';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'active' AND category = 'Faculty Picks'");
$stmt->execute();
$products = $stmt->fetchAll();
?>
<div style='max-width:1100px;margin:60px auto 40px auto;'>
    <h1 style='text-align:center;margin-bottom:32px;'>Faculty Picks</h1>
    <?php if ($products): ?>
    <div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:28px;'>
        <?php foreach ($products as $product): ?>
            <div style='background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);border:1px solid #e1e8ed;padding:24px;text-align:center;'>
                <img src='<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>' alt='<?php echo htmlspecialchars($product['name']); ?>' style='max-width:100%;max-height:140px;margin-bottom:12px;'>
                <div style='font-size:1.1rem;font-weight:600;color:#003366;margin-bottom:6px;'><?php echo htmlspecialchars($product['name']); ?></div>
                <div style='color:#ff6b00;font-weight:700;margin-bottom:10px;'>₱<?php echo number_format($product['price'],2); ?></div>
                <a href='<?php echo SITE_URL; ?>/pages/products/view.php?id=<?php echo $product['id']; ?>' style='color:#fff;background:#003366;padding:8px 18px;border-radius:8px;text-decoration:none;font-weight:600;'>View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style='text-align:center;color:#888;font-size:1.1rem;'>No faculty picks found.</div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?> 