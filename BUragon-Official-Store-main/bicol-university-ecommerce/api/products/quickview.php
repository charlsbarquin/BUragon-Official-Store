<?php
require_once dirname(__DIR__, 2) . '/includes/db_connect.php';
require_once dirname(__DIR__, 2) . '/functions/user_functions.php';
if (!function_exists('getCurrentUserId')) {
    die('getCurrentUserId not loaded from user_functions.php');
}
$pdo = getDbConnection();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    echo '<div style="padding:30px;text-align:center;">Invalid product.</div>';
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND status = "active"');
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    echo '<div style="padding:30px;text-align:center;">Product not found.</div>';
    exit;
}
// Format price and discount
$price = $product['price'];
$original_price = $price;
if ($product['discount_percentage'] > 0) {
    $price = $price - ($price * $product['discount_percentage'] / 100);
}
// Get reviews summary
$review_stmt = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM product_reviews WHERE product_id = ? AND status = "approved"');
$review_stmt->execute([$product_id]);
$review = $review_stmt->fetch();
$avg_rating = $review && $review['avg_rating'] ? round($review['avg_rating'], 1) : 0;
$review_count = $review ? intval($review['review_count']) : 0;
// Wishlist state
$user_id = getCurrentUserId();
$in_wishlist = false;
if ($user_id) {
    $w_stmt = $pdo->prepare('SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ?');
    $w_stmt->execute([$user_id, $product_id]);
    $in_wishlist = (bool)$w_stmt->fetchColumn();
} else {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $in_wishlist = isset($_SESSION['wishlist']) && in_array($product_id, $_SESSION['wishlist']);
}
// Multiple images support (comma-separated in 'image' field)
$images = array_map('trim', explode(',', $product['image'] ?: 'default-product.jpg'));
?>
<div style="display:flex;flex-wrap:wrap;gap:32px;align-items:flex-start;">
  <div style="flex:1;min-width:220px;text-align:center;">
    <?php if (count($images) > 1): ?>
      <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width:100%;max-height:320px;object-fit:contain;">
        <div style="display:flex;gap:6px;justify-content:center;">
          <?php foreach ($images as $img): ?>
            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($img); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;cursor:pointer;border:1.5px solid #eee;" onclick="this.closest('div').querySelector('img').src='<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($img); ?>';">
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width:100%;max-height:320px;object-fit:contain;">
    <?php endif; ?>
  </div>
  <div style="flex:2;min-width:260px;">
    <h2 style="margin-top:0;font-size:2rem;color:#003366;"><?php echo htmlspecialchars($product['name']); ?></h2>
    <div style="font-size:1.2rem;margin-bottom:10px;">
      <span style="color:#ff6b00;font-weight:700;">₱<?php echo number_format($price,2); ?></span>
      <?php if ($original_price != $price): ?>
        <span style="text-decoration:line-through;color:#888;margin-left:10px;">₱<?php echo number_format($original_price,2); ?></span>
        <span style="background:#dc3545;color:#fff;padding:2px 8px;border-radius:6px;font-size:0.9rem;margin-left:8px;">Save <?php echo $product['discount_percentage']; ?>%</span>
      <?php endif; ?>
    </div>
    <div style="margin-bottom:10px;">
      <span style="color:#ffc107;font-size:1.1rem;">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="fas fa-star<?php echo $i <= round($avg_rating) ? '' : '-o'; ?>"></i>
        <?php endfor; ?>
      </span>
      <span style="color:#666;font-size:0.98rem;">(<?php echo $avg_rating; ?> / 5 from <?php echo $review_count; ?> review<?php echo $review_count != 1 ? 's' : ''; ?>)</span>
    </div>
    <div style="margin-bottom:18px;color:#666;">
      <?php echo nl2br(htmlspecialchars($product['description'])); ?>
    </div>
    <div style="margin-bottom:18px;">
      <span style="background:#e6f2ff;color:#003366;padding:3px 10px;border-radius:8px;font-size:0.95rem;">Category: <?php echo htmlspecialchars($product['category']); ?></span>
      <span style="margin-left:18px;color:#888;font-size:0.95rem;">Stock: <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?></span>
      <?php if (!empty($product['sku'])): ?>
        <span style="margin-left:18px;color:#888;font-size:0.95rem;">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
      <?php endif; ?>
    </div>
    <button onclick="addToCart(<?php echo $product['id']; ?>, 1)" style="background:#ff6b00;color:#fff;padding:12px 28px;border:none;border-radius:8px;font-weight:600;font-size:1.1rem;cursor:pointer;<?php echo $product['stock_quantity'] > 0 ? '' : 'opacity:0.5;pointer-events:none;'; ?>">
      <i class="fas fa-shopping-cart"></i> Add to Cart
    </button>
    <button onclick="toggleWishlist(<?php echo $product['id']; ?>)" style="margin-left:12px;padding:12px 18px;border-radius:8px;border:2px solid #ff6b00;background:<?php echo $in_wishlist ? '#ff6b00' : '#fff'; ?>;color:<?php echo $in_wishlist ? '#fff' : '#ff6b00'; ?>;font-weight:600;cursor:pointer;">
      <i class="fas fa-heart"></i> <?php echo $in_wishlist ? 'Wishlisted' : 'Add to Wishlist'; ?>
    </button>
    <a href="<?php echo SITE_URL; ?>/pages/products/view.php?id=<?php echo $product['id']; ?>" style="margin-left:18px;font-weight:600;color:#003366;text-decoration:underline;">View Details</a>
  </div>
</div>
