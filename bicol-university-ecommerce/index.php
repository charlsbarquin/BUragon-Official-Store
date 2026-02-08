<?php
// index.php
$page_title = "Home";
require_once 'includes/header.php';
require_once 'includes/homepage_backend.php';

// Get all homepage data from backend
$homepageData = getHomepageData();

// Extract data for use in template
$stats = $homepageData['stats'];
$slides = $homepageData['slides'];
$featured_products = $homepageData['featured_products'];
$most_popular_products = $homepageData['popular_products'];
$testimonials = $homepageData['testimonials'];
$events = $homepageData['events'];
$offers = $homepageData['offers'];

// All data is now loaded from the backend
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - BUragon | Bicol University Official Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/homepage.css">
</head>

<body>
<main>
    <!-- Hero Slideshow -->
    <section class="minimalist-slideshow">
        <div class="slides-container">
            <?php foreach ($slides as $index => $slide): ?>
                <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                     style="background: <?php echo $slide['gradient']; ?>; color: <?php echo $slide['text_color']; ?>">
                    
                    <div class="image-content">
                        <div class="image-frame">
                            <img src="<?php echo SITE_URL; ?>/assets/images/<?php echo $slide['product_image']; ?>" 
                                 alt="<?php echo $slide['title']; ?>" 
                                 loading="lazy"
                                 class="product-image">
                        </div>
                    </div>
                    
                    <div class="text-content">
                        <span class="category-tag"><?php echo $slide['category']; ?></span>
                        <h1><?php echo $slide['title']; ?></h1>
                        <p><?php echo $slide['subtitle']; ?></p>
                        <a href="<?php echo SITE_URL . $slide['button_link']; ?>" class="cta-button">
                            <?php echo $slide['button_text']; ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="slide-nav" role="navigation" aria-label="Slideshow Navigation">
            <button class="nav-button prev" aria-label="Previous slide" tabindex="0">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="pagination" aria-label="Slide pagination">
                <?php foreach ($slides as $index => $slide): ?>
                    <button class="pagination-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-index="<?php echo $index; ?>" aria-label="Go to slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <button class="nav-button next" aria-label="Next slide" tabindex="0">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="slideshow-progress">
            <div class="progress-bar"></div>
        </div>
    </section>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="container">
            <div class="welcome-content">
                <h2>Welcome to BUragon</h2>
                <p>Your official Bicol University merchandise store. Discover quality products, exclusive student discounts, and everything you need for campus life.</p>
                <a href="pages/about.php" class="cta-button">Learn More About Us</a>
            </div>
        </div>
    </section>

    <!-- Live Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_products']); ?>+</div>
                    <div class="stat-label">Products Available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['recent_orders']); ?>+</div>
                    <div class="stat-label">Orders This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total_customers']); ?>+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number">4.8</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Offers Banner -->
    <section class="offers-banner">
        <div class="container">
            <div class="offers-content">
                <div class="offer-text">
                    <h3><?php echo htmlspecialchars($offers['title']); ?></h3>
                    <p><?php echo htmlspecialchars($offers['description']); ?></p>
                    <span class="offer-code">Use code: <strong><?php echo htmlspecialchars($offers['discount_code']); ?></strong></span>
                </div>
                <div class="offer-cta">
                    <a href="<?php echo isset($offers['link']) ? SITE_URL . $offers['link'] : SITE_URL . '/pages/products/index.php'; ?>" class="cta-button">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="quick-links">
        <div class="container">
            <h2 class="section-header">Why Shop With Us?</h2>
            <div class="quick-links-grid">
                <div class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="quick-link-title">Official Merchandise</h3>
                    <p>Authentic Bicol University products</p>
                </div>
                <div class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h3 class="quick-link-title">Student Discounts</h3>
                    <p>Exclusive deals for BU students</p>
                </div>
                <div class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="quick-link-title">Fast Delivery</h3>
                    <p>Campus pickup available</p>
                </div>
                <div class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="quick-link-title">Support</h3>
                    <p>Dedicated customer service</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Discover our most popular items</p>
            </div>
            <div class="products-grid">
                <?php if (empty($featured_products)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #888;">No featured products available at the moment.</div>
                <?php endif; ?>
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $product['image']; ?>"
                                alt="<?php echo $product['name']; ?>"
                                loading="lazy">
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo $product['category']; ?></span>
                            <h3 class="product-title"><?php echo $product['name']; ?></h3>
                            <div class="product-rating">
                                <div class="stars">
                                    <?php
                                    $rating = $product['average_rating'] ?? 0;
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    for ($i = 1; $i <= 5; $i++):
                                        if ($i <= $fullStars): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                    <?php endif;
                                    endfor; ?>
                                </div>
                                <span class="rating-count">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                            </div>
                            <div class="product-price"><?php echo $product['price']; ?></div>
                            <div class="product-actions">
                                <a href="pages/products/view.php?id=<?php echo $product['id']; ?>" class="cta-button">View</a>
                                <button class="buy-now" title="Add to Cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button class="wishlist-btn" title="Add to Wishlist" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Most Popular Section -->
    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2>Most Popular</h2>
                <p>Our best-selling and most loved items</p>
            </div>
            <div class="products-grid">
                <?php if (empty($most_popular_products)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #888;">No popular products available at the moment.</div>
                <?php endif; ?>
                <?php foreach ($most_popular_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $product['image']; ?>"
                                alt="<?php echo $product['name']; ?>"
                                loading="lazy">
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo $product['category']; ?></span>
                            <h3 class="product-title"><?php echo $product['name']; ?></h3>
                            <div class="product-rating">
                                <div class="stars">
                                    <?php
                                    $rating = $product['average_rating'] ?? 0;
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    for ($i = 1; $i <= 5; $i++):
                                        if ($i <= $fullStars): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                    <?php endif;
                                    endfor; ?>
                                </div>
                                <span class="rating-count">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                            </div>
                            <div class="product-price"><?php echo $product['price']; ?></div>
                            <div class="product-actions">
                                <a href="pages/products/view.php?id=<?php echo $product['id']; ?>" class="cta-button">View</a>
                                <button class="buy-now" title="Add to Cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button class="wishlist-btn" title="Add to Wishlist" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="pages/products/index.php" class="cta-button">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>What Our Customers Say</h2>
                <p>Feedback from our BU community</p>
            </div>

            <div class="testimonials-grid">
                <?php if (empty($testimonials)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #888;">No testimonials yet. Be the first to leave feedback!</div>
                <?php endif; ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <p class="testimonial-text"><?php echo $testimonial['text']; ?></p>
                        <div class="testimonial-author"><?php echo $testimonial['author']; ?></div>
                        <div class="testimonial-info"><?php echo $testimonial['info']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Campus Events & Announcements -->
    <section class="events-section">
        <div class="container">
            <div class="section-header">
                <h2>Campus Events & Updates</h2>
                <p>Stay connected with BU community</p>
            </div>

            <div class="events-grid">
                <?php if (empty($events)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #888;">No upcoming events or announcements.</div>
                <?php endif; ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <span class="day"><?php echo htmlspecialchars($event['day']); ?></span>
                            <span class="month"><?php echo htmlspecialchars($event['month']); ?></span>
                        </div>
                        <div class="event-content">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            <span class="event-tag"><?php echo htmlspecialchars($event['event_type']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h2>Stay Updated</h2>
                <p>Subscribe to our newsletter for exclusive deals, new arrivals, and campus updates.</p>

                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Your email address" required>
                    <button type="submit" class="newsletter-button">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Cart Modal -->
    <div id="cart-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:10000;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.18);max-width:90vw;width:350px;text-align:center;position:relative;">
            <div style="font-size:2.2rem;color:#28a745;margin-bottom:10px;"><i class="fas fa-check-circle"></i></div>
            <div id="cart-modal-message" style="font-size:1.1rem;font-weight:600;margin-bottom:18px;">Product added to cart.</div>
            <div style="display:flex;gap:16px;justify-content:center;">
                <button id="cart-modal-continue" style="padding:10px 18px;border-radius:8px;border:none;background:#003366;color:#fff;font-weight:600;cursor:pointer;">Continue Shopping</button>
                <button id="cart-modal-goto" style="padding:10px 18px;border-radius:8px;border:none;background:#ff6b00;color:#fff;font-weight:600;cursor:pointer;">Go to Cart</button>
            </div>
            <button id="cart-modal-close" aria-label="Close" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:1.3rem;color:#888;cursor:pointer;">&times;</button>
        </div>
    </div>
</main>

<script src="assets/js/homepage.js"></script>
<?php require_once 'includes/footer.php'; ?>
</body>

</html>