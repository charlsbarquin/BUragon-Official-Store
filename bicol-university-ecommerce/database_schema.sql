-- Bicol University E-Commerce Database Schema
-- This file contains all the necessary tables for the homepage backend

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS bicol_university_ecommerce;
USE bicol_university_ecommerce;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('customer', 'admin', 'faculty') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    student_id VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image VARCHAR(255) NULL,
    stock_quantity INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product reviews table
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Testimonials table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    info VARCHAR(100) NULL,
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Offers table
CREATE TABLE offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_code VARCHAR(50) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Homepage slides table
CREATE TABLE homepage_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    button_text VARCHAR(100) NOT NULL,
    button_link VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    gradient VARCHAR(255) NULL,
    text_color VARCHAR(7) DEFAULT '#003366',
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Newsletter subscribers table
CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wishlists table
CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupons table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data for testing

-- Sample users
INSERT INTO users (username, email, password_hash, first_name, last_name, role, student_id) VALUES
('admin', 'admin@bicol-u.edu.ph', '$2y$12$example_hash', 'Admin', 'User', 'admin', NULL),
('student1', 'student1@bicol-u.edu.ph', '$2y$12$example_hash', 'Maria', 'Santos', 'customer', '2021-001234'),
('student2', 'student2@bicol-u.edu.ph', '$2y$12$example_hash', 'John', 'Doe', 'customer', '2021-005678'),
('faculty1', 'faculty1@bicol-u.edu.ph', '$2y$12$example_hash', 'Dr. Angela', 'Cruz', 'faculty', NULL);

-- Sample products for Academic, Merchandise, and more
INSERT INTO products (name, category, price, image, description, stock_quantity, discount_percentage, featured, status, created_at)
VALUES
('Engineering Kit', 'Academic', 899, 'engineering-kit.jpg', 'Complete kit for engineering students: ruler, compass, protractor, and more.', 50, 10, 1, 'active', NOW()),
('Scientific Calculator', 'Academic', 599, 'scientific-calculator.jpg', 'BU-branded scientific calculator, perfect for exams and labs.', 100, 0, 1, 'active', NOW()),
('Math Set', 'Academic', 120, 'math-set.jpg', 'Compass, protractor, ruler, and set square in a handy case.', 80, 0, 0, 'active', NOW()),
('Laboratory Coat', 'Academic', 450, 'lab-coat.jpg', 'White lab coat for science and engineering students.', 40, 5, 0, 'active', NOW()),
('Drawing/Sketch Pad', 'Academic', 150, 'sketch-pad.jpg', 'A3 size, 50 sheets, acid-free paper for technical and creative work.', 60, 0, 0, 'active', NOW()),
('Technical Pen Set', 'Academic', 350, 'technical-pen-set.jpg', 'Set of 3 technical pens for precise drawing and drafting.', 30, 0, 0, 'active', NOW()),
('USB Flash Drive (BU Edition)', 'Academic', 299, 'usb-flash-drive.jpg', '16GB USB drive with BU logo.', 120, 0, 0, 'active', NOW()),
('Graphing Paper Pad', 'Academic', 80, 'graphing-paper.jpg', 'Pad of 100 graphing sheets for math and engineering.', 70, 0, 0, 'active', NOW()),
('Highlighter Set', 'Academic', 99, 'highlighter-set.jpg', 'Set of 5 assorted color highlighters.', 90, 0, 0, 'active', NOW()),
('Index Cards', 'Academic', 60, 'index-cards.jpg', 'Pack of 100 ruled index cards for study notes.', 100, 0, 0, 'active', NOW()),
('Academic Planner', 'Academic', 199, 'academic-planner.jpg', 'Organize your semester with this BU academic planner.', 80, 0, 1, 'active', NOW()),
('Exam Blue Book', 'Academic', 25, 'exam-blue-book.jpg', 'Official exam blue book for written tests.', 200, 0, 0, 'active', NOW()),
('Sticky Notes Pack', 'Academic', 45, 'sticky-notes.jpg', 'Pack of 5 sticky note pads, assorted colors.', 100, 0, 0, 'active', NOW()),
('Correction Tape', 'Academic', 35, 'correction-tape.jpg', 'Smooth, no-mess correction tape.', 100, 0, 0, 'active', NOW()),
('Ballpen Set (BU Logo)', 'Academic', 70, 'ballpen-set.jpg', 'Set of 3 ballpens with BU logo.', 100, 0, 0, 'active', NOW()),

('BU Varsity Jacket', 'Merchandise', 1899, 'varsity-jacket.jpg', 'Classic varsity jacket with BU embroidery. Warm, stylish, and perfect for campus life.', 30, 15, 1, 'active', NOW()),
('BU Hoodie (Classic)', 'Merchandise', 999, 'hoodie-classic.jpg', 'Classic BU hoodie, soft and comfortable.', 40, 10, 1, 'active', NOW()),
('BU T-shirt (Unisex)', 'Merchandise', 399, 'tshirt-unisex.jpg', 'Unisex BU t-shirt, available in all sizes.', 100, 0, 1, 'active', NOW()),
('BU Cap', 'Merchandise', 250, 'bu-cap.jpg', 'Adjustable BU cap, perfect for sunny days.', 60, 0, 0, 'active', NOW()),
('BU Tote Bag', 'Merchandise', 180, 'tote-bag.jpg', 'Eco-friendly tote bag with BU print.', 80, 0, 0, 'active', NOW()),
('BU Stainless Tumbler', 'Merchandise', 499, 'stainless-tumbler.jpg', 'Keep your drinks hot or cold with this durable, BU-branded stainless tumbler.', 70, 0, 1, 'active', NOW()),
('BU Lanyard', 'Merchandise', 99, 'lanyard.jpg', 'Handy lanyard for your ID, keys, or USB. Show your BU pride everywhere.', 120, 0, 0, 'active', NOW()),
('BU Keychain', 'Merchandise', 60, 'keychain.jpg', 'BU logo keychain, perfect for bags and keys.', 100, 0, 0, 'active', NOW()),
('BU Button Pins', 'Merchandise', 50, 'button-pins.jpg', 'Set of 3 BU button pins.', 100, 0, 0, 'active', NOW()),
('BU Mouse Pad', 'Merchandise', 120, 'mouse-pad.jpg', 'Smooth BU mouse pad for your desk.', 80, 0, 0, 'active', NOW()),
('BU Water Bottle', 'Merchandise', 220, 'water-bottle.jpg', 'Reusable water bottle with BU logo.', 90, 0, 0, 'active', NOW()),
('BU Face Mask', 'Merchandise', 80, 'face-mask.jpg', 'Washable face mask with BU print.', 100, 0, 0, 'active', NOW()),
('BU Notebook/Planner', 'Merchandise', 199, 'notebook-planner.jpg', 'Organize your notes and schedule in this stylish BU notebook/planner.', 100, 0, 0, 'active', NOW()),
('BU Backpack', 'Merchandise', 799, 'backpack.jpg', 'Spacious and durable backpack with BU logo.', 50, 0, 1, 'active', NOW()),
('BU Graduation Bear', 'Merchandise', 350, 'graduation-bear.jpg', 'Cute plush bear in BU graduation attire.', 40, 0, 0, 'active', NOW()),

('BU Alumni Mug', 'Accessories', 180, 'alumni-mug.jpg', 'Ceramic mug for BU alumni.', 60, 0, 0, 'active', NOW()),
('BU Pen Set', 'Accessories', 120, 'pen-set.jpg', 'Set of 5 pens in a BU gift box.', 80, 0, 0, 'active', NOW()),
('BU Study Kit', 'Accessories', 299, 'study-kit.jpg', 'Complete study kit: pens, highlighters, sticky notes, and more.', 70, 0, 0, 'active', NOW()),
('BU Tech Bundle', 'Accessories', 999, 'tech-bundle.jpg', 'Bundle: USB drive, mouse pad, and headphones.', 30, 0, 0, 'active', NOW()),
('BU Discounted Items', 'Clearance', 99, 'discounted-items.jpg', 'Special discounted BU merchandise. Limited stocks only!', 20, 20, 0, 'active', NOW());

-- More new products for Academic and Merchandise
INSERT INTO products (name, category, price, image, description, stock_quantity, discount_percentage, featured, status, created_at) VALUES
('BU Chemistry Set', 'Academic', 950, 'chemistry-set.jpg', 'Complete chemistry set for lab experiments and study.', 40, 0, 1, 'active', NOW()),
('BU Physics Kit', 'Academic', 1050, 'physics-kit.jpg', 'Physics experiment kit for students and science fairs.', 35, 0, 1, 'active', NOW()),
('BU Art Portfolio', 'Academic', 320, 'art-portfolio.jpg', 'A3 size art portfolio for sketches and projects.', 60, 0, 0, 'active', NOW()),
('BU Exam Board', 'Academic', 80, 'exam-board.jpg', 'Sturdy exam board for written tests and drawing.', 100, 0, 0, 'active', NOW()),
('BU Scientific Ruler', 'Academic', 60, 'scientific-ruler.jpg', '30cm ruler with metric and imperial units.', 100, 0, 0, 'active', NOW()),
('BU Flashcards', 'Academic', 70, 'flashcards.jpg', 'Pack of 100 blank flashcards for study and review.', 120, 0, 0, 'active', NOW()),
('BU Correction Fluid', 'Academic', 35, 'correction-fluid.jpg', 'Quick-dry correction fluid for neat notes.', 90, 0, 0, 'active', NOW()),
('BU Binder Clips', 'Academic', 45, 'binder-clips.jpg', 'Set of 12 binder clips for organizing papers.', 100, 0, 0, 'active', NOW()),
('BU Desk Organizer', 'Academic', 220, 'desk-organizer.jpg', 'Multi-compartment desk organizer for students.', 50, 0, 0, 'active', NOW()),
('BU Sticky Note Cube', 'Academic', 85, 'sticky-note-cube.jpg', 'Cube of sticky notes in assorted colors.', 100, 0, 0, 'active', NOW()),
('BU Exam Eraser', 'Academic', 25, 'exam-eraser.jpg', 'High-quality eraser for exams and sketches.', 100, 0, 0, 'active', NOW()),
('BU Student Lapel Pin', 'Academic', 60, 'student-lapel-pin.jpg', 'BU logo lapel pin for students and graduates.', 100, 0, 0, 'active', NOW()),

('BU Travel Organizer', 'Merchandise', 180, 'travel-organizer.jpg', 'Compact travel organizer for documents and gadgets.', 70, 0, 0, 'active', NOW()),
('BU Power Bank', 'Merchandise', 499, 'power-bank.jpg', '5000mAh power bank with BU logo.', 60, 0, 1, 'active', NOW()),
('BU Sports Towel', 'Merchandise', 150, 'sports-towel.jpg', 'Quick-dry sports towel with BU print.', 80, 0, 0, 'active', NOW()),
('BU Card Holder', 'Merchandise', 90, 'card-holder.jpg', 'Slim card holder for IDs and credit cards.', 100, 0, 0, 'active', NOW()),
('BU Desk Mat', 'Merchandise', 220, 'desk-mat.jpg', 'Large desk mat with BU campus design.', 50, 0, 0, 'active', NOW()),
('BU Reusable Straw Set', 'Merchandise', 80, 'reusable-straw-set.jpg', 'Set of 3 reusable straws with cleaning brush.', 100, 0, 0, 'active', NOW()),
('BU Mini Fan', 'Merchandise', 180, 'mini-fan.jpg', 'USB mini fan for desk or travel.', 70, 0, 0, 'active', NOW()),
('BU Badge Reel', 'Merchandise', 60, 'badge-reel.jpg', 'Retractable badge reel with BU logo.', 100, 0, 0, 'active', NOW()),
('BU Canvas Wallet', 'Merchandise', 160, 'canvas-wallet.jpg', 'Durable canvas wallet with BU print.', 80, 0, 0, 'active', NOW()),
('BU Key Organizer', 'Merchandise', 90, 'key-organizer.jpg', 'Compact key organizer for up to 8 keys.', 100, 0, 0, 'active', NOW()),
('BU Phone Stand', 'Merchandise', 70, 'phone-stand.jpg', 'Foldable phone stand for desk or travel.', 100, 0, 0, 'active', NOW()),
('BU Patch Set', 'Merchandise', 120, 'patch-set.jpg', 'Set of 4 iron-on BU patches.', 100, 0, 0, 'active', NOW()),
('BU Magnet Set', 'Merchandise', 80, 'magnet-set.jpg', 'Set of 5 BU fridge magnets.', 100, 0, 0, 'active', NOW()),
('BU Shoelaces', 'Merchandise', 50, 'shoelaces.jpg', 'Pair of BU-themed shoelaces.', 100, 0, 0, 'active', NOW()),
('BU Wristband', 'Merchandise', 40, 'wristband.jpg', 'Silicone wristband with BU logo.', 100, 0, 0, 'active', NOW());

-- Sample orders
INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method, payment_status) VALUES
(2, 'ORD-2024-001', 1899.00, 'delivered', '123 Main St, Legazpi City', 'cash_on_delivery', 'paid'),
(2, 'ORD-2024-002', 598.00, 'delivered', '123 Main St, Legazpi City', 'cash_on_delivery', 'paid'),
(3, 'ORD-2024-003', 1299.00, 'processing', '456 College Ave, Legazpi City', 'cash_on_delivery', 'paid'),
(2, 'ORD-2024-004', 399.00, 'pending', '123 Main St, Legazpi City', 'cash_on_delivery', 'pending');

-- Sample order items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 1, 1899.00, 1899.00),
(2, 2, 1, 499.00, 499.00),
(2, 3, 1, 99.00, 99.00),
(3, 5, 1, 1299.00, 1299.00),
(4, 10, 1, 399.00, 399.00);

-- Sample testimonials
INSERT INTO testimonials (text, author, info, rating) VALUES
('The BUragon Hoodie is so comfy and the delivery was super fast!', 'Maria', '2nd Year Student', 5),
('I love the exclusive student discounts. Shopping here is always a breeze!', 'John', '4th Year Engineering', 5),
('Great quality and friendly support. I always find what I need for my classes!', 'Angela', '3rd Year Business Admin', 4);

-- Sample events
INSERT INTO events (title, description, event_date, event_type) VALUES
('Christmas Sale', 'Holiday discounts on all BU merchandise. Perfect gifts for family and friends!', '2024-12-15', 'Sale'),
('Graduation Collection', 'New graduation merchandise available. Celebrate your achievement in style!', '2024-12-20', 'New'),
('Back to School', 'Prepare for the new semester with our academic essentials collection.', '2025-01-05', 'Academic');

-- Sample offers
INSERT INTO offers (title, description, discount_code, discount_percentage, valid_from, valid_until) VALUES
('🎓 Student Discount Alert!', 'Get 15% off on all academic supplies with your student ID', 'STUDENT15', 15.00, '2024-01-01', '2024-12-31');

-- Sample homepage slides
INSERT INTO homepage_slides (category, title, subtitle, button_text, button_link, image, gradient, text_color, sort_order) VALUES
('Best Sellers', 'Campus Essentials', 'Top-rated products loved by BU students', 'Explore Bestsellers', '/bestsellers', 'hoodie-model.jpg', 'linear-gradient(135deg, #ffffff 0%, #e6f2ff 100%)', '#003366', 1),
('New Arrivals', 'Just Released', 'Discover our latest university merchandise', 'View New Items', '/new-arrivals', 'tech-bundle.jpg', 'linear-gradient(135deg, #ffffff 0%, #fff5e6 100%)', '#003366', 2),
('Seasonal', 'Back to School', 'Prepare for the new semester with essentials', 'Shop Seasonal', '/seasonal', 'study-kit.jpg', 'linear-gradient(135deg, #ffffff 0%, #ffebd6 100%)', '#003366', 3),
('Clearance', 'Special Offers', 'Limited-time discounts on quality items', 'View Deals', '/clearance', 'discounted-items.jpg', 'linear-gradient(135deg, #ffffff 0%, #ffe6e6 100%)', '#003366', 4),
('Faculty Picks', 'Recommended Tools', 'Curated by BU professors for academic success', 'See Recommendations', '/faculty-picks', 'engineering-kit.jpg', 'linear-gradient(135deg, #ffffff 0%, #e6ffe6 100%)', '#003366', 5);

-- Sample product reviews
INSERT INTO product_reviews (product_id, user_id, rating, review_text, status) VALUES
(1, 2, 5, 'Perfect fit and great quality!', 'approved'),
(1, 3, 4, 'Love the design, very comfortable', 'approved'),
(2, 2, 5, 'Keeps drinks hot for hours!', 'approved'),
(5, 3, 5, 'Spacious and durable backpack', 'approved'),
(9, 2, 5, 'My favorite hoodie ever!', 'approved'),
(10, 3, 4, 'Great fit and comfortable material', 'approved');

-- Create indexes for better performance
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
CREATE INDEX idx_product_reviews_product_id ON product_reviews(product_id);
CREATE INDEX idx_product_reviews_status ON product_reviews(status);
CREATE INDEX idx_events_event_date ON events(event_date);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_offers_valid_until ON offers(valid_until);
CREATE INDEX idx_offers_status ON offers(status);
CREATE INDEX idx_homepage_slides_sort_order ON homepage_slides(sort_order);
CREATE INDEX idx_homepage_slides_status ON homepage_slides(status); 