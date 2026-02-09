<?php
/**
 * Homepage Backend - Handles all database operations and data processing for the homepage
 * 
 * This file contains all the backend logic for the homepage including:
 * - Database queries for statistics
 * - Product data retrieval
 * - Slideshow content management
 * - Testimonials and events data
 * - Error handling and fallbacks
 */

require_once __DIR__ . '/db_connect.php';

class HomepageBackend {
    private $pdo;
    private $error_log = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all homepage data in one call
     */
    public function getAllHomepageData() {
        return [
            'stats' => $this->getStatistics(),
            'slides' => $this->getSlideshowData(),
            'featured_products' => $this->getFeaturedProducts(),
            'popular_products' => $this->getPopularProducts(),
            'testimonials' => $this->getTestimonials(),
            'events' => $this->getEvents(),
            'offers' => $this->getCurrentOffers(),
            'errors' => $this->error_log
        ];
    }
    
    /**
     * Get live statistics from database
     */
    public function getStatistics() {
        $stats = [
            'total_products' => 0,
            'recent_orders' => 0,
            'total_customers' => 0,
            'average_rating' => 4.8,
            'total_revenue' => 0
        ];
        
        try {
            // Total active products
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
            $stats['total_products'] = $stmt->fetchColumn();
            
            // Orders in last 30 days
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM orders
                WHERE created_at >= NOW() - INTERVAL '30 days'
                AND status != 'cancelled'
            ");
            $stmt->execute();
            $stats['recent_orders'] = $stmt->fetchColumn();
            
            // Total customers
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'");
            $stats['total_customers'] = $stmt->fetchColumn();
            
            // Total revenue (last 30 days)
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(total_amount), 0) 
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                AND status = 'completed'
            ");
            $stmt->execute();
            $stats['total_revenue'] = $stmt->fetchColumn();
            
            // Average rating from reviews
            $stmt = $this->pdo->query("
                SELECT COALESCE(AVG(rating), 4.8) 
                FROM product_reviews 
                WHERE status = 'approved'
            ");
            $stats['average_rating'] = round($stmt->fetchColumn(), 1);
            
        } catch (PDOException $e) {
            $this->logError('Statistics query failed: ' . $e->getMessage());
            // Use fallback values
            $stats = [
                'total_products' => 150,
                'recent_orders' => 89,
                'total_customers' => 1200,
                'average_rating' => 4.8,
                'total_revenue' => 125000
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get slideshow data
     */
    public function getSlideshowData() {
        // Try to get slides from database first
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM homepage_slides 
                WHERE status = 'active' 
                ORDER BY sort_order ASC 
                LIMIT 5
            ");
            $slides_db = $stmt->fetchAll();
            
            if (!empty($slides_db)) {
                return $this->formatSlidesFromDB($slides_db);
            }
        } catch (PDOException $e) {
            $this->logError('Slides query failed: ' . $e->getMessage());
        }
        
        // Fallback to static slides
        return $this->getStaticSlides();
    }
    
    /**
     * Get featured products from database
     */
    public function getFeaturedProducts($limit = 8) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id, 
                    p.name, 
                    p.price, 
                    p.category, 
                    p.image, 
                    p.description,
                    p.stock_quantity,
                    p.discount_percentage,
                    COALESCE(AVG(pr.rating), 0) as average_rating,
                    COUNT(pr.id) as review_count
                FROM products p
                LEFT JOIN product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
                WHERE p.featured = 1 
                AND p.status = 'active'
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $products = $stmt->fetchAll();
            
            if (!empty($products)) {
                return $this->formatProducts($products);
            }
        } catch (PDOException $e) {
            $this->logError('Featured products query failed: ' . $e->getMessage());
        }
        
        // Fallback to static data
        return $this->getStaticFeaturedProducts();
    }
    
    /**
     * Get most popular products based on sales
     */
    public function getPopularProducts($limit = 8) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id, 
                    p.name, 
                    p.price, 
                    p.category, 
                    p.image, 
                    p.description,
                    p.stock_quantity,
                    p.discount_percentage,
                    COUNT(oi.id) as sales_count,
                    COALESCE(AVG(pr.rating), 0) as average_rating,
                    COUNT(pr.id) as review_count
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
                LEFT JOIN product_reviews pr ON p.id = pr.product_id AND pr.status = 'approved'
                WHERE p.status = 'active'
                GROUP BY p.id
                ORDER BY sales_count DESC, p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $products = $stmt->fetchAll();
            
            if (!empty($products)) {
                return $this->formatProducts($products);
            }
        } catch (PDOException $e) {
            $this->logError('Popular products query failed: ' . $e->getMessage());
        }
        
        // Fallback to static data
        return $this->getStaticPopularProducts();
    }
    
    /**
     * Get testimonials from database
     */
    public function getTestimonials($limit = 3) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.text,
                    t.author,
                    t.info,
                    t.rating,
                    t.created_at
                FROM testimonials t
                WHERE t.status = 'active'
                ORDER BY t.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $testimonials = $stmt->fetchAll();
            
            if (!empty($testimonials)) {
                return $testimonials;
            }
        } catch (PDOException $e) {
            $this->logError('Testimonials query failed: ' . $e->getMessage());
        }
        
        // Fallback to static testimonials
        return $this->getStaticTestimonials();
    }
    
    /**
     * Get upcoming events
     */
    public function getEvents($limit = 3) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    e.title,
                    e.description,
                    e.event_date,
                    e.event_type,
                    e.status
                FROM events e
                WHERE e.status = 'active'
                AND e.event_date >= CURRENT_DATE
                ORDER BY e.event_date ASC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $events = $stmt->fetchAll();
            
            if (!empty($events)) {
                return $this->formatEvents($events);
            }
        } catch (PDOException $e) {
            $this->logError('Events query failed: ' . $e->getMessage());
        }
        
        // Fallback to static events
        return $this->getStaticEvents();
    }
    
    /**
     * Get current offers and promotions
     */
    public function getCurrentOffers() {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    o.title,
                    o.description,
                    o.discount_code,
                    o.discount_percentage,
                    o.valid_until,
                    o.status
                FROM offers o
                WHERE o.status = 'active'
                AND o.valid_until >= CURRENT_DATE
                ORDER BY o.created_at DESC
                LIMIT 1
            ");
            $offer = $stmt->fetch();
            
            if ($offer) {
                return $offer;
            }
        } catch (PDOException $e) {
            $this->logError('Offers query failed: ' . $e->getMessage());
        }
        
        // Fallback to static offer
        return [
            'title' => '🎓 Student Discount Alert!',
            'description' => 'Get 15% off on all academic supplies with your student ID',
            'discount_code' => 'STUDENT15',
            'discount_percentage' => 15,
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'link' => '/pages/student-discount.php'
        ];
    }
    
    /**
     * Format products data for frontend
     */
    private function formatProducts($products) {
        $formatted = [];
        foreach ($products as $product) {
            $price = $product['price'];
            if ($product['discount_percentage'] > 0) {
                $discounted_price = $price - ($price * $product['discount_percentage'] / 100);
                $price = '₱' . number_format($discounted_price, 2);
            } else {
                $price = '₱' . number_format($price, 2);
            }
            
            $formatted[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'image' => $product['image'] ?: 'default-product.jpg',
                'price' => $price,
                'category' => $product['category'],
                'description' => $product['description'],
                'stock_quantity' => $product['stock_quantity'],
                'average_rating' => round($product['average_rating'], 1),
                'review_count' => $product['review_count'],
                'in_stock' => $product['stock_quantity'] > 0
            ];
        }
        return $formatted;
    }
    
    /**
     * Format events data for frontend
     */
    private function formatEvents($events) {
        $formatted = [];
        foreach ($events as $event) {
            $date = new DateTime($event['event_date']);
            $formatted[] = [
                'title' => $event['title'],
                'description' => $event['description'],
                'day' => $date->format('d'),
                'month' => strtoupper($date->format('M')),
                'event_type' => $event['event_type']
            ];
        }
        return $formatted;
    }
    
    /**
     * Format slides from database
     */
    private function formatSlidesFromDB($slides) {
        $formatted = [];
        foreach ($slides as $slide) {
            $formatted[] = [
                'category' => $slide['category'],
                'title' => $slide['title'],
                'subtitle' => $slide['subtitle'],
                'button_text' => $slide['button_text'],
                'button_link' => $slide['button_link'],
                'product_image' => $slide['image'],
                'gradient' => $slide['gradient'] ?: 'linear-gradient(135deg, #ffffff 0%, #e6f2ff 100%)',
                'text_color' => $slide['text_color'] ?: '#003366'
            ];
        }
        return $formatted;
    }
    
    /**
     * Static slides data (fallback)
     */
    private function getStaticSlides() {
        return [
            [
                'category' => 'About',
                'title' => 'About BU',
                'subtitle' => 'Learn more about Bicol University and our official store.',
                'button_text' => 'About BU',
                'button_link' => '/pages/about.php',
                'product_image' => 'banners/bicol-university-torch.jpg',
                'gradient' => 'linear-gradient(135deg, #ffffff 0%, #e6f2ff 100%)',
                'text_color' => '#003366'
            ],
            [
                'category' => 'Best Sellers',
                'title' => 'Campus Essentials',
                'subtitle' => 'Top-rated products loved by BU students',
                'button_text' => 'Explore Bestsellers',
                'button_link' => '/pages/bestsellers.php',
                'product_image' => 'hoodie-model.jpg',
                'gradient' => 'linear-gradient(135deg, #ffffff 0%, #e6f2ff 100%)',
                'text_color' => '#003366'
            ],
            [
                'category' => 'New Arrivals',
                'title' => 'Just Released',
                'subtitle' => 'Discover our latest university merchandise',
                'button_text' => 'View New Items',
                'button_link' => '/pages/new-arrivals.php',
                'product_image' => 'tech-bundle.jpg',
                'gradient' => 'linear-gradient(135deg, #ffffff 0%, #fff5e6 100%)',
                'text_color' => '#003366'
            ],
            [
                'category' => 'Seasonal',
                'title' => 'Back to School',
                'subtitle' => 'Prepare for the new semester with essentials',
                'button_text' => 'Shop Seasonal',
                'button_link' => '/pages/seasonal.php',
                'product_image' => 'study-kit.jpg',
                'gradient' => 'linear-gradient(135deg, #ffffff 0%, #ffebd6 100%)',
                'text_color' => '#003366'
            ],
            [
                'category' => 'Clearance',
                'title' => 'Special Offers',
                'subtitle' => 'Limited-time discounts on quality items',
                'button_text' => 'View Deals',
                'button_link' => '/pages/clearance.php',
                'product_image' => 'discounted-items.jpg',
                'gradient' => 'linear-gradient(135deg, #ffffff 0%, #ffe6e6 100%)',
                'text_color' => '#003366'
            ],
            [
                'category' => 'Faculty Picks',
                'title' => 'Recommended Tools',
                'subtitle' => 'Curated by BU professors for academic success',
                'button_text' => 'See Recommendations',
                'button_link' => '/pages/faculty-picks.php',
                'product_image' => 'engineering-kit.jpg',
                'gradient' => 'linear-gradient(135deg, #ffffff 0%, #e6ffe6 100%)',
                'text_color' => '#003366'
            ]
        ];
    }
    
    /**
     * Static featured products (fallback)
     */
    private function getStaticFeaturedProducts() {
        return [
            [
                'id' => 1,
                'name' => 'BU Varsity Jacket',
                'image' => 'varsity-jacket.jpg',
                'price' => '₱1,899',
                'category' => 'Apparel',
                'description' => 'Classic varsity jacket with BU embroidery. Warm, stylish, and perfect for campus life.',
                'in_stock' => true,
                'average_rating' => 4.8,
                'review_count' => 45
            ],
            [
                'id' => 2,
                'name' => 'BU Stainless Tumbler',
                'image' => 'stainless-tumbler.jpg',
                'price' => '₱499',
                'category' => 'Drinkware',
                'description' => 'Keep your drinks hot or cold with this durable, BU-branded stainless tumbler.',
                'in_stock' => true,
                'average_rating' => 4.6,
                'review_count' => 32
            ],
            [
                'id' => 3,
                'name' => 'BU Lanyard',
                'image' => 'lanyard.jpg',
                'price' => '₱99',
                'category' => 'Accessories',
                'description' => 'Handy lanyard for your ID, keys, or USB. Show your BU pride everywhere.',
                'in_stock' => true,
                'average_rating' => 4.5,
                'review_count' => 28
            ],
            [
                'id' => 4,
                'name' => 'BU Notebook/Planner',
                'image' => 'notebook-planner.jpg',
                'price' => '₱199',
                'category' => 'Stationery',
                'description' => 'Organize your notes and schedule in this stylish BU notebook/planner.',
                'in_stock' => true,
                'average_rating' => 4.7,
                'review_count' => 56
            ],
            [
                'id' => 5,
                'name' => 'BU Backpack',
                'image' => 'backpack.jpg',
                'price' => '₱1,299',
                'category' => 'Bags',
                'description' => 'Spacious and sturdy backpack for books, gadgets, and daily essentials.',
                'in_stock' => true,
                'average_rating' => 4.9,
                'review_count' => 67
            ],
            [
                'id' => 6,
                'name' => 'BU Face Mask (3pcs set)',
                'image' => 'face-mask.jpg',
                'price' => '₱150',
                'category' => 'Health',
                'description' => 'Comfortable, washable face masks with BU logo. 3 pieces per set.',
                'in_stock' => true,
                'average_rating' => 4.4,
                'review_count' => 23
            ],
            [
                'id' => 7,
                'name' => 'BU Mouse Pad',
                'image' => 'mouse-pad.jpg',
                'price' => '₱120',
                'category' => 'Tech',
                'description' => 'Smooth, non-slip mouse pad for study or gaming, featuring BU colors.',
                'in_stock' => true,
                'average_rating' => 4.3,
                'review_count' => 19
            ],
            [
                'id' => 8,
                'name' => 'BU Graduation Bear',
                'image' => 'graduation-bear.jpg',
                'price' => '₱399',
                'category' => 'Gifts',
                'description' => 'Cute plush bear in BU graduation attire. Perfect for grads and alumni.',
                'in_stock' => true,
                'average_rating' => 4.8,
                'review_count' => 41
            ]
        ];
    }
    
    /**
     * Static popular products (fallback)
     */
    private function getStaticPopularProducts() {
        return [
            [
                'id' => 101,
                'name' => 'BU Hoodie Classic',
                'image' => 'hoodie-classic.jpg',
                'price' => '₱1,299',
                'category' => 'Apparel',
                'description' => 'Soft, comfy hoodie with classic BU print. A campus favorite for all seasons.',
                'in_stock' => true,
                'average_rating' => 4.9,
                'review_count' => 89
            ],
            [
                'id' => 102,
                'name' => 'BU T-Shirt (Unisex)',
                'image' => 'tshirt-unisex.jpg',
                'price' => '₱399',
                'category' => 'Apparel',
                'description' => 'Everyday t-shirt with BU logo. Unisex fit for students and alumni.',
                'in_stock' => true,
                'average_rating' => 4.7,
                'review_count' => 76
            ],
            [
                'id' => 103,
                'name' => 'BU Tote Bag',
                'image' => 'tote-bag.jpg',
                'price' => '₱250',
                'category' => 'Bags',
                'description' => 'Eco-friendly tote for books, groceries, or gym. Show your BU spirit.',
                'in_stock' => true,
                'average_rating' => 4.6,
                'review_count' => 54
            ],
            [
                'id' => 104,
                'name' => 'BU Pen Set (5pcs)',
                'image' => 'pen-set.jpg',
                'price' => '₱80',
                'category' => 'Stationery',
                'description' => 'Set of 5 smooth-writing pens with BU branding. Perfect for class or office.',
                'in_stock' => true,
                'average_rating' => 4.5,
                'review_count' => 43
            ],
            [
                'id' => 105,
                'name' => 'BU Water Bottle',
                'image' => 'water-bottle.jpg',
                'price' => '₱350',
                'category' => 'Drinkware',
                'description' => 'Reusable water bottle with leak-proof lid. Stay hydrated, BU style.',
                'in_stock' => true,
                'average_rating' => 4.8,
                'review_count' => 61
            ],
            [
                'id' => 106,
                'name' => 'BU Keychain',
                'image' => 'keychain.jpg',
                'price' => '₱60',
                'category' => 'Accessories',
                'description' => 'Metal keychain with BU logo. A small but meaningful keepsake.',
                'in_stock' => true,
                'average_rating' => 4.4,
                'review_count' => 37
            ],
            [
                'id' => 107,
                'name' => 'BU Button Pins (Set of 4)',
                'image' => 'button-pins.jpg',
                'price' => '₱70',
                'category' => 'Accessories',
                'description' => 'Set of 4 colorful pins for your bag, jacket, or lanyard.',
                'in_stock' => true,
                'average_rating' => 4.3,
                'review_count' => 29
            ],
            [
                'id' => 108,
                'name' => 'BU Alumni Mug',
                'image' => 'alumni-mug.jpg',
                'price' => '₱220',
                'category' => 'Drinkware',
                'description' => 'Ceramic mug for proud BU alumni. Great for coffee or display.',
                'in_stock' => true,
                'average_rating' => 4.7,
                'review_count' => 48
            ]
        ];
    }
    
    /**
     * Static testimonials (fallback)
     */
    private function getStaticTestimonials() {
        return [
            [
                'text' => "The BUragon Hoodie is so comfy and the delivery was super fast!",
                'author' => "Maria",
                'info' => "2nd Year Student",
                'rating' => 5,
                'created_at' => '2024-01-15'
            ],
            [
                'text' => "I love the exclusive student discounts. Shopping here is always a breeze!",
                'author' => "John",
                'info' => "4th Year Engineering",
                'rating' => 5,
                'created_at' => '2024-01-10'
            ],
            [
                'text' => "Great quality and friendly support. I always find what I need for my classes!",
                'author' => "Angela",
                'info' => "3rd Year Business Admin",
                'rating' => 4,
                'created_at' => '2024-01-08'
            ]
        ];
    }
    
    /**
     * Static events (fallback)
     */
    private function getStaticEvents() {
        return [
            [
                'title' => 'Christmas Sale',
                'description' => 'Holiday discounts on all BU merchandise. Perfect gifts for family and friends!',
                'day' => '15',
                'month' => 'DEC',
                'event_type' => 'Sale'
            ],
            [
                'title' => 'Graduation Collection',
                'description' => 'New graduation merchandise available. Celebrate your achievement in style!',
                'day' => '20',
                'month' => 'DEC',
                'event_type' => 'New'
            ],
            [
                'title' => 'Back to School',
                'description' => 'Prepare for the new semester with our academic essentials collection.',
                'day' => '05',
                'month' => 'JAN',
                'event_type' => 'Academic'
            ]
        ];
    }
    
    /**
     * Log errors for debugging
     */
    private function logError($message) {
        $this->error_log[] = [
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        error_log("Homepage Backend Error: " . $message);
    }
    
    /**
     * Get error log
     */
    public function getErrorLog() {
        return $this->error_log;
    }
    
    /**
     * Check if database is connected
     */
    public function isDatabaseConnected() {
        try {
            $this->pdo->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Initialize the backend
$pdo = getDbConnection(); // Ensure db_connect.php is included
$homepageBackend = new HomepageBackend($pdo);

// Export functions for easy access
function getHomepageData() {
    global $homepageBackend;
    return $homepageBackend->getAllHomepageData();
}

function getHomepageStats() {
    global $homepageBackend;
    return $homepageBackend->getStatistics();
}

function getHomepageSlides() {
    global $homepageBackend;
    return $homepageBackend->getSlideshowData();
}

function getHomepageFeaturedProducts($limit = 8) {
    global $homepageBackend;
    return $homepageBackend->getFeaturedProducts($limit);
}

function getHomepagePopularProducts($limit = 8) {
    global $homepageBackend;
    return $homepageBackend->getPopularProducts($limit);
}

function getHomepageTestimonials($limit = 3) {
    global $homepageBackend;
    return $homepageBackend->getTestimonials($limit);
}

function getHomepageEvents($limit = 3) {
    global $homepageBackend;
    return $homepageBackend->getEvents($limit);
}

function getHomepageOffers() {
    global $homepageBackend;
    return $homepageBackend->getCurrentOffers();
}
?> 
