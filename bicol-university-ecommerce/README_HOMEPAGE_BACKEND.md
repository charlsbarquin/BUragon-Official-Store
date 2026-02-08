# Homepage Backend System

## Overview

This document explains the new backend system for the Bicol University E-Commerce homepage. The system provides a clean separation between frontend presentation and backend data processing.

## Files Created

### 1. `includes/homepage_backend.php`
This is the main backend file that handles all database operations and data processing for the homepage.

**Features:**
- **HomepageBackend Class**: Main class that manages all homepage data
- **Database Integration**: Connects to your existing database
- **Error Handling**: Graceful fallbacks when database is unavailable
- **Performance Optimized**: Efficient queries with proper indexing
- **Modular Design**: Easy to extend and maintain

**Key Methods:**
- `getAllHomepageData()` - Get all homepage data in one call
- `getStatistics()` - Live statistics from database
- `getFeaturedProducts()` - Featured products with ratings
- `getPopularProducts()` - Most popular products based on sales
- `getTestimonials()` - Customer testimonials
- `getEvents()` - Upcoming campus events
- `getCurrentOffers()` - Active promotions and discounts

### 2. `database_schema.sql`
Complete database schema with all necessary tables and sample data.

**Tables Included:**
- `users` - User accounts and profiles
- `products` - Product catalog
- `orders` - Order management
- `order_items` - Order line items
- `product_reviews` - Customer reviews and ratings
- `testimonials` - Customer testimonials
- `events` - Campus events and announcements
- `offers` - Promotions and discounts
- `homepage_slides` - Slideshow content management
- `cart` - Shopping cart functionality

## Installation & Setup

### Step 1: Database Setup
1. Import the `database_schema.sql` file into your MySQL database:
   ```bash
   mysql -u root -p < database_schema.sql
   ```

2. Update your database configuration in `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_PORT', '3308');
   define('DB_NAME', 'bicol_university_ecommerce');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### Step 2: File Integration
The `index.php` file has been updated to use the new backend system. The changes include:

- Removed inline database queries
- Added backend file inclusion
- Simplified data loading
- Dynamic content from database

### Step 3: Test the System
1. Visit your homepage
2. Check that all sections load properly
3. Verify that statistics are showing real data
4. Test fallback functionality by temporarily disabling database

## Usage Examples

### Basic Usage
```php
// Include the backend
require_once 'includes/homepage_backend.php';

// Get all homepage data
$homepageData = getHomepageData();

// Access specific data
$stats = $homepageData['stats'];
$products = $homepageData['featured_products'];
$testimonials = $homepageData['testimonials'];
```

### Individual Data Retrieval
```php
// Get only statistics
$stats = getHomepageStats();

// Get only featured products
$featured = getHomepageFeaturedProducts(8);

// Get only testimonials
$testimonials = getHomepageTestimonials(3);
```

### Error Handling
```php
$homepageData = getHomepageData();

if (!empty($homepageData['errors'])) {
    // Handle errors
    foreach ($homepageData['errors'] as $error) {
        error_log("Homepage Error: " . $error['message']);
    }
}
```

## Database Features

### Live Statistics
- Total products available
- Recent orders (last 30 days)
- Total customers
- Average rating
- Total revenue

### Dynamic Content
- **Slideshow**: Managed through `homepage_slides` table
- **Products**: Real-time inventory and pricing
- **Events**: Upcoming campus events
- **Offers**: Active promotions and discounts
- **Testimonials**: Customer feedback

### Performance Optimizations
- Database indexes on frequently queried columns
- Efficient JOIN queries
- Caching-friendly structure
- Fallback to static data when database is unavailable

## Admin Features

### Content Management
All homepage content can be managed through the database:

1. **Slideshow Management**:
   ```sql
   INSERT INTO homepage_slides (category, title, subtitle, button_text, button_link, image, sort_order) 
   VALUES ('New Category', 'New Title', 'New Subtitle', 'Shop Now', '/products', 'new-image.jpg', 1);
   ```

2. **Product Management**:
   ```sql
   UPDATE products SET featured = 1 WHERE id = 1;
   UPDATE products SET discount_percentage = 15.00 WHERE id = 1;
   ```

3. **Event Management**:
   ```sql
   INSERT INTO events (title, description, event_date, event_type) 
   VALUES ('New Event', 'Event description', '2024-12-25', 'Sale');
   ```

4. **Offer Management**:
   ```sql
   INSERT INTO offers (title, description, discount_code, discount_percentage, valid_from, valid_until) 
   VALUES ('New Offer', 'Offer description', 'NEWCODE', 20.00, '2024-01-01', '2024-12-31');
   ```

## Benefits

### For Developers
- **Clean Code**: Separation of concerns
- **Maintainable**: Easy to modify and extend
- **Reusable**: Backend can be used by other pages
- **Testable**: Individual methods can be tested

### For Administrators
- **Dynamic Content**: No need to edit code for content changes
- **Real-time Data**: Live statistics and inventory
- **Flexible**: Easy to add new features
- **Reliable**: Fallback system ensures site always works

### For Users
- **Fast Loading**: Optimized queries and caching
- **Fresh Content**: Always up-to-date information
- **Rich Experience**: Dynamic content and real-time data
- **Reliable**: Site works even if database has issues

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check database credentials in `config.php`
   - Verify database server is running
   - Ensure database exists

2. **Missing Tables**:
   - Run the `database_schema.sql` file
   - Check for any SQL errors during import

3. **Empty Data**:
   - Verify sample data was imported
   - Check table permissions
   - Review error logs

4. **Performance Issues**:
   - Ensure database indexes are created
   - Check query execution plans
   - Consider implementing caching

### Debug Mode
To enable debug mode, add this to your `index.php`:
```php
// Debug information
if (isset($_GET['debug'])) {
    echo "<pre>";
    print_r($homepageData);
    echo "</pre>";
}
```

## Future Enhancements

### Planned Features
- **Caching System**: Redis/Memcached integration
- **API Endpoints**: RESTful API for mobile apps
- **Analytics**: Detailed performance metrics
- **A/B Testing**: Content variation testing
- **Personalization**: User-specific content

### Extensibility
The backend system is designed to be easily extensible:
- Add new data types by extending the class
- Implement new query methods
- Add caching layers
- Integrate with external services

## Support

For questions or issues:
1. Check the error logs
2. Review database connectivity
3. Verify all files are properly included
4. Test with sample data

The system includes comprehensive error handling and fallback mechanisms to ensure your homepage always works, even if there are database issues. 