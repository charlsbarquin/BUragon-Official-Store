-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Jul 28, 2025 at 08:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bicol_university_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(12, 5, 1015, 1, '2025-07-28 05:04:27', '2025-07-28 05:04:27');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'hi', 'hi@gmail.com', 'hi', '2025-07-23 07:31:42'),
(2, 'charls', 'hi@gmail.com', 'wala', '2025-07-24 11:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_type`, `status`, `created_at`) VALUES
(1, 'Christmas Sale', 'Holiday discounts on all BU merchandise. Perfect gifts for family and friends!', '2024-12-15', 'Sale', 'active', '2025-07-20 08:14:01'),
(2, 'Graduation Collection', 'New graduation merchandise available. Celebrate your achievement in style!', '2024-12-20', 'New', 'active', '2025-07-20 08:14:01'),
(3, 'Back to School', 'Prepare for the new semester with our academic essentials collection.', '2025-01-05', 'Academic', 'active', '2025-07-20 08:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_slides`
--

CREATE TABLE `homepage_slides` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `button_text` varchar(100) NOT NULL,
  `button_link` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `gradient` varchar(255) DEFAULT NULL,
  `text_color` varchar(7) DEFAULT '#003366',
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homepage_slides`
--

INSERT INTO `homepage_slides` (`id`, `category`, `title`, `subtitle`, `button_text`, `button_link`, `image`, `gradient`, `text_color`, `sort_order`, `status`, `created_at`) VALUES
(20, 'About', 'About BU', 'Learn more about Bicol University and our official store.', 'About BU', '/pages/about.php', 'banners/bicol-university-torch.jpg', 'linear-gradient(135deg, #ffffff 0%, #e6f2ff 100%)', '#003366', 1, 'active', '2025-07-26 10:08:29'),
(21, 'Best Sellers', 'Campus Essentials', 'Top-rated products loved by BU students', 'Explore Bestsellers', '/pages/bestsellers.php', 'banners/hoodie-model.jpg', 'linear-gradient(135deg, #ffffff 0%, #e6f2ff 100%)', '#003366', 2, 'active', '2025-07-26 10:08:29'),
(22, 'New Arrivals', 'Just Released', 'Discover our latest university merchandise', 'View New Items', '/pages/new-arrivals.php', 'banners/tech-bundle.jpg', 'linear-gradient(135deg, #ffffff 0%, #fff5e6 100%)', '#003366', 3, 'active', '2025-07-26 10:08:29'),
(23, 'Seasonal', 'Back to School', 'Prepare for the new semester with essentials', 'Shop Seasonal', '/pages/seasonal.php', 'banners/study-kit.jpg', 'linear-gradient(135deg, #ffffff 0%, #ffebd6 100%)', '#003366', 4, 'active', '2025-07-26 10:08:29'),
(24, 'Clearance', 'Special Offers', 'Limited-time discounts on quality items', 'View Deals', '/pages/clearance.php', 'banners/discounted-items.jpg', 'linear-gradient(135deg, #ffffff 0%, #ffe6e6 100%)', '#003366', 5, 'active', '2025-07-26 10:08:29'),
(25, 'Faculty Picks', 'Recommended Tools', 'Curated by BU professors for academic success', 'See Recommendations', '/pages/faculty-picks.php', 'banners/engineering-kit.jpg', 'linear-gradient(135deg, #ffffff 0%, #e6ffe6 100%)', '#003366', 6, 'active', '2025-07-26 10:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES
(1, 'charlsbarquin@gmail.com', '2025-07-22 07:37:45');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_code` varchar(50) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `title`, `description`, `discount_code`, `discount_percentage`, `valid_from`, `valid_until`, `status`, `created_at`) VALUES
(1, '🎓 Student Discount Alert!', 'Get 15% off on all academic supplies with your student ID', 'STUDENT15', 15.00, '2024-01-01', '2024-12-31', 'active', '2025-07-20 08:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `shipping_address`, `payment_method`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-2024-001', 1899.00, 'delivered', '123 Main St, Legazpi City', 'cash_on_delivery', 'paid', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(2, 2, 'ORD-2024-002', 598.00, 'delivered', '123 Main St, Legazpi City', 'cash_on_delivery', 'paid', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(3, 3, 'ORD-2024-003', 1299.00, 'processing', '456 College Ave, Legazpi City', 'cash_on_delivery', 'paid', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(4, 2, 'ORD-2024-004', 399.00, 'pending', '123 Main St, Legazpi City', 'cash_on_delivery', 'pending', '2025-07-20 08:14:01', '2025-07-20 08:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 1, 1, 1899.00, 1899.00, '2025-07-20 08:14:01'),
(2, 2, 2, 1, 499.00, 499.00, '2025-07-20 08:14:01'),
(3, 2, 3, 1, 99.00, 99.00, '2025-07-20 08:14:01'),
(4, 3, 5, 1, 1299.00, 1299.00, '2025-07-20 08:14:01'),
(5, 4, 10, 1, 399.00, 399.00, '2025-07-20 08:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `image`, `stock_quantity`, `featured`, `discount_percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'BU Varsity Jacket', 'Classic varsity jacket with BU embroidery. Warm, stylish, and perfect for campus life.', 1899.00, 'Apparel', 'varsity-jacket.jpg', 50, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(2, 'BU Stainless Tumbler', 'Keep your drinks hot or cold with this durable, BU-branded stainless tumbler.', 499.00, 'Drinkware', 'stainless-tumbler.jpg', 100, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(3, 'BU Lanyard', 'Handy lanyard for your ID, keys, or USB. Show your BU pride everywhere.', 99.00, 'Accessories', 'lanyard.jpg', 200, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(4, 'BU Notebook/Planner', 'Organize your notes and schedule in this stylish BU notebook/planner.', 199.00, 'Stationery', 'notebook-planner.jpg', 150, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(5, 'BU Backpack', 'Spacious and sturdy backpack for books, gadgets, and daily essentials.', 1299.00, 'Bags', 'backpack.jpg', 75, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(6, 'BU Face Mask (3pcs set)', 'Comfortable, washable face masks with BU logo. 3 pieces per set.', 150.00, 'Health', 'face-mask.jpg', 300, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(7, 'BU Mouse Pad', 'Smooth, non-slip mouse pad for study or gaming, featuring BU colors.', 120.00, 'Tech', 'mouse-pad.jpg', 100, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(8, 'BU Graduation Bear', 'Cute plush bear in BU graduation attire. Perfect for grads and alumni.', 399.00, 'Gifts', 'graduation-bear.jpg', 80, 1, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(9, 'BU Hoodie Classic', 'Soft, comfy hoodie with classic BU print. A campus favorite for all seasons.', 1299.00, 'Apparel', 'hoodie-classic.jpg', 60, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(10, 'BU T-Shirt (Unisex)', 'Everyday t-shirt with BU logo. Unisex fit for students and alumni.', 399.00, 'Apparel', 'tshirt-unisex.jpg', 120, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(11, 'BU Tote Bag', 'Eco-friendly tote for books, groceries, or gym. Show your BU spirit.', 250.00, 'Bags', 'tote-bag.jpg', 90, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(12, 'BU Pen Set (5pcs)', 'Set of 5 smooth-writing pens with BU branding. Perfect for class or office.', 80.00, 'Stationery', 'pen-set.jpg', 200, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(13, 'BU Water Bottle', 'Reusable water bottle with leak-proof lid. Stay hydrated, BU style.', 350.00, 'Drinkware', 'water-bottle.jpg', 85, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(14, 'BU Keychain', 'Metal keychain with BU logo. A small but meaningful keepsake.', 60.00, 'Accessories', 'keychain.jpg', 150, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(15, 'BU Button Pins (Set of 4)', 'Set of 4 colorful pins for your bag, jacket, or lanyard.', 70.00, 'Accessories', 'button-pins.jpg', 180, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(16, 'BU Alumni Mug', 'Ceramic mug for proud BU alumni. Great for coffee or display.', 220.00, 'Drinkware', 'alumni-mug.jpg', 95, 0, 0.00, 'active', '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(1001, 'Engineering Kit', 'Complete kit for engineering students: ruler, compass, protractor, and more.', 899.00, 'Academic', 'engineering-kit.jpg', 50, 1, 10.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1002, 'Scientific Calculator', 'BU-branded scientific calculator, perfect for exams and labs.', 599.00, 'Academic', 'scientific-calculator.jpg', 100, 1, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1003, 'Math Set', 'Compass, protractor, ruler, and set square in a handy case.', 120.00, 'Academic', 'math-set.jpg', 80, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1004, 'Laboratory Coat', 'White lab coat for science and engineering students.', 450.00, 'Academic', 'lab-coat.jpg', 40, 0, 5.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1005, 'Drawing/Sketch Pad', 'A3 size, 50 sheets, acid-free paper for technical and creative work.', 150.00, 'Academic', 'sketch-pad.jpg', 60, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1006, 'Technical Pen Set', 'Set of 3 technical pens for precise drawing and drafting.', 350.00, 'Academic', 'technical-pen-set.jpg', 30, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1007, 'USB Flash Drive (BU Edition)', '16GB USB drive with BU logo.', 299.00, 'Academic', 'usb-flash-drive.jpg', 120, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1008, 'Graphing Paper Pad', 'Pad of 100 graphing sheets for math and engineering.', 80.00, 'Academic', 'graphing-paper.jpg', 70, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1009, 'Highlighter Set', 'Set of 5 assorted color highlighters.', 99.00, 'Academic', 'highlighter-set.jpg', 90, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1010, 'Index Cards', 'Pack of 100 ruled index cards for study notes.', 60.00, 'Academic', 'index-cards.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1011, 'Academic Planner', 'Organize your semester with this BU academic planner.', 199.00, 'Academic', 'academic-planner.jpg', 80, 1, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1012, 'Exam Blue Book', 'Official exam blue book for written tests.', 25.00, 'Academic', 'exam-blue-book.jpg', 200, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1013, 'Sticky Notes Pack', 'Pack of 5 sticky note pads, assorted colors.', 45.00, 'Academic', 'sticky-notes.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1014, 'Correction Tape', 'Smooth, no-mess correction tape.', 35.00, 'Academic', 'correction-tape.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1015, 'Ballpen Set \r\n', 'Set of 3 ballpens with BU logo.', 70.00, 'Academic', 'ballpen-set.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:43:56'),
(1019, 'BU Cap', 'Adjustable BU cap, perfect for sunny days.', 250.00, 'Merchandise', 'bu-cap.jpg', 60, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1033, 'BU Study Kit', 'Complete study kit: pens, highlighters, sticky notes, and more.', 299.00, 'Accessories', 'study-kit.jpg', 70, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1034, 'BU Tech Bundle', 'Bundle: USB drive, mouse pad, and headphones.', 999.00, 'Accessories', 'tech-bundle.jpg', 30, 0, 0.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1035, 'BU Discounted Items', 'Special discounted BU merchandise. Limited stocks only!', 99.00, 'Clearance', 'discounted-items.jpg', 20, 0, 20.00, 'active', '2025-07-22 12:05:48', '2025-07-22 12:05:48'),
(1036, 'BU Geometry Set', 'Complete geometry set for math and engineering students.', 110.00, 'Academic', 'geometry-set.jpg', 80, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1037, 'BU Scientific Notebook', '200-page notebook for lab and science notes.', 95.00, 'Academic', 'scientific-notebook.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1038, 'BU Exam Pencil Pack', 'Pack of 5 #2 pencils for exams.', 60.00, 'Academic', 'exam-pencil-pack.jpg', 120, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1039, 'BU Whiteboard Marker Set', 'Set of 4 colored whiteboard markers.', 120.00, 'Academic', 'whiteboard-marker-set.jpg', 60, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1040, 'BU Correction Pen', 'Fine-tip correction pen for neat notes.', 40.00, 'Academic', 'correction-pen.jpg', 90, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1041, 'BU Sticky Flags', 'Colorful sticky flags for marking pages.', 55.00, 'Academic', 'sticky-flags.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1042, 'BU Document Envelope', 'Plastic envelope for organizing papers.', 35.00, 'Academic', 'document-envelope.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1043, 'BU Index Tab Set', 'Set of 10 index tabs for binders and notebooks.', 50.00, 'Academic', 'index-tab-set.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1044, 'BU Math Formula Card', 'Handy card with essential math formulas.', 30.00, 'Academic', 'math-formula-card.jpg', 150, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1045, 'BU Ruler (30cm)', 'Durable 30cm plastic ruler with BU logo.', 25.00, 'Academic', 'ruler-30cm.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1046, 'BU Eraser (Large)', 'Large, clean eraser for exams and sketches.', 20.00, 'Academic', 'eraser-large.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1047, 'BU Student ID Holder', 'Clear ID holder with BU lanyard.', 45.00, 'Academic', 'student-id-holder.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1048, 'BU Academic Medal Holder', 'Display your academic medals with pride.', 150.00, 'Academic', 'medal-holder.jpg', 30, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1049, 'BU Sports Water Jug', 'Large-capacity water jug for athletes and students.', 350.00, 'Merchandise', 'sports-water-jug.jpg', 50, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1050, 'BU Canvas Pouch', 'Multipurpose canvas pouch with BU print.', 120.00, 'Merchandise', 'canvas-pouch.jpg', 80, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1051, 'BU Umbrella', 'Compact umbrella with BU logo, perfect for rainy days.', 299.00, 'Merchandise', 'bu-umbrella.jpg', 60, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1052, 'BU Travel Mug', 'Insulated travel mug for hot and cold drinks.', 250.00, 'Merchandise', 'travel-mug.jpg', 70, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1053, 'BU Drawstring Bag', 'Lightweight drawstring bag for gym or travel.', 180.00, 'Merchandise', 'drawstring-bag.jpg', 90, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1054, 'BU Desk Calendar', '2024 desk calendar with BU campus photos.', 99.00, 'Merchandise', 'desk-calendar.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1055, 'BU Car Decal', 'BU logo car decal for alumni and students.', 60.00, 'Merchandise', 'car-decal.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1056, 'BU Socks (Pair)', 'Comfortable socks with BU colors.', 80.00, 'Merchandise', 'bu-socks.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1057, 'BU Scarf', 'Warm scarf with BU embroidery.', 150.00, 'Merchandise', 'bu-scarf.jpg', 50, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1058, 'BU Slippers', 'Comfy slippers with BU logo.', 120.00, 'Merchandise', 'bu-slippers.jpg', 80, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1059, 'BU Cap (Limited Edition)', 'Limited edition BU cap with special embroidery.', 350.00, 'Merchandise', 'bu-cap-limited.jpg', 40, 1, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1060, 'BU Phone Grip', 'Phone grip/stand with BU logo.', 70.00, 'Merchandise', 'phone-grip.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58'),
(1061, 'BU Luggage Tag', 'BU-branded luggage tag for travelers.', 60.00, 'Merchandise', 'luggage-tag.jpg', 100, 0, 0.00, 'active', '2025-07-22 12:22:58', '2025-07-22 12:22:58');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `review_text`, `status`, `created_at`) VALUES
(1, 1, 2, 5, 'Perfect fit and great quality!', 'approved', '2025-07-20 08:14:01'),
(2, 1, 3, 4, 'Love the design, very comfortable', 'approved', '2025-07-20 08:14:01'),
(3, 2, 2, 5, 'Keeps drinks hot for hours!', 'approved', '2025-07-20 08:14:01'),
(4, 5, 3, 5, 'Spacious and durable backpack', 'approved', '2025-07-20 08:14:01'),
(5, 9, 2, 5, 'My favorite hoodie ever!', 'approved', '2025-07-20 08:14:01'),
(6, 10, 3, 4, 'Great fit and comfortable material', 'approved', '2025-07-20 08:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `text` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `info` varchar(100) DEFAULT NULL,
  `rating` int(11) DEFAULT 5 CHECK (`rating` >= 1 and `rating` <= 5),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `text`, `author`, `info`, `rating`, `status`, `created_at`) VALUES
(1, 'The BUragon Hoodie is so comfy and the delivery was super fast!', 'Maria', '2nd Year Student', 5, 'active', '2025-07-20 08:14:01'),
(2, 'I love the exclusive student discounts. Shopping here is always a breeze!', 'John', '4th Year Engineering', 5, 'active', '2025-07-20 08:14:01'),
(3, 'Great quality and friendly support. I always find what I need for my classes!', 'Angela', '3rd Year Business Admin', 4, 'active', '2025-07-20 08:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('customer','admin','faculty') DEFAULT 'customer',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `student_id` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `status`, `student_id`, `phone`, `address`, `profile_pic`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@bicol-u.edu.ph', '$2y$12$example_hash', 'Admin', 'User', 'admin', 'active', NULL, NULL, NULL, NULL, '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(2, 'student1', 'student1@bicol-u.edu.ph', '$2y$12$example_hash', 'Maria', 'Santos', 'customer', 'active', '2021-001234', NULL, NULL, NULL, '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(3, 'student2', 'student2@bicol-u.edu.ph', '$2y$12$example_hash', 'John', 'Doe', 'customer', 'active', '2021-005678', NULL, NULL, NULL, '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(4, 'faculty1', 'faculty1@bicol-u.edu.ph', '$2y$12$example_hash', 'Dr. Angela', 'Cruz', 'faculty', 'active', NULL, NULL, NULL, NULL, '2025-07-20 08:14:01', '2025-07-20 08:14:01'),
(5, 'Charls', 'charlsbarquin2@gmail.com', '$2y$10$pfnNB1gudi9opFrw.0rQa./qjjJLgTFFX1gLjz0QEF7ldcRrWJ6cG', 'Charls', 'Barquin', 'customer', 'active', NULL, NULL, NULL, NULL, '2025-07-24 10:00:07', '2025-07-24 10:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 5, 2, '2025-07-25 04:02:01'),
(2, 5, 1002, '2025-07-27 05:52:13'),
(4, 5, 1011, '2025-07-28 02:46:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_event_date` (`event_date`),
  ADD KEY `idx_events_status` (`status`);

--
-- Indexes for table `homepage_slides`
--
ALTER TABLE `homepage_slides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_homepage_slides_sort_order` (`sort_order`),
  ADD KEY `idx_homepage_slides_status` (`status`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `discount_code` (`discount_code`),
  ADD KEY `idx_offers_valid_until` (`valid_until`),
  ADD KEY `idx_offers_status` (`status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_status` (`status`),
  ADD KEY `idx_products_featured` (`featured`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_product_reviews_product_id` (`product_id`),
  ADD KEY `idx_product_reviews_status` (`status`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `homepage_slides`
--
ALTER TABLE `homepage_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1062;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
