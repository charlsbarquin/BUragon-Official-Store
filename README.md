# BUragon Official Store

![BUragon Logo](assets/images/logos/bu-logo.png)

A comprehensive e-commerce platform built specifically for Bicol University students, faculty, and alumni. The BUragon Official Store offers a wide range of university merchandise, academic supplies, and campus essentials with exclusive student discounts.

## 🌟 Features

### 🛒 Core E-commerce Functionality
- **User Authentication**: Secure registration and login system with role-based access (Customer, Faculty, Admin)
- **Product Catalog**: Extensive collection of academic supplies, merchandise, and campus essentials
- **Shopping Cart**: Persistent cart with quantity management and price calculations
- **Wishlist**: Save favorite items for later purchase
- **Checkout Process**: Secure checkout with multiple payment options
- **Order Management**: Complete order tracking and history

### 👨‍💼 Admin Panel
- **Dashboard**: Sales analytics and system overview
- **Product Management**: Add, edit, delete, and manage inventory
- **Order Processing**: View and update order status
- **User Management**: Manage customer accounts and permissions
- **Reports**: Sales, inventory, and customer analytics

### 🎨 User Experience
- **Responsive Design**: Mobile-first approach with modern UI
- **Product Reviews**: Customer ratings and feedback system
- **Search & Filtering**: Advanced product search with category filters
- **Newsletter**: Email subscription for updates and promotions
- **Live Statistics**: Real-time store metrics on homepage

### 📱 Additional Features
- **Homepage Slideshow**: Dynamic promotional banners
- **Testimonials**: Customer reviews and feedback
- **Campus Events**: University announcements and updates
- **Special Offers**: Discount codes and promotional campaigns
- **Contact Form**: Customer support and inquiries

## 🛠️ Technology Stack

### Backend
- **PHP 8.0+**: Server-side scripting
- **MySQL 8.0**: Database management
- **Composer**: Dependency management
- **PHPMailer**: Email functionality
- **Dompdf**: PDF generation for invoices

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Responsive styling with custom properties
- **JavaScript (ES6+)**: Interactive functionality
- **Font Awesome**: Icon library

### Development Tools
- **Git**: Version control
- **VS Code**: Code editor
- **phpMyAdmin**: Database administration
- **Browser DevTools**: Debugging and testing

## 📋 Prerequisites

Before running this project, ensure you have the following installed:

- **PHP 8.0 or higher**
- **MySQL 8.0 or higher**
- **Apache/Nginx web server**
- **Composer** (for dependency management)
- **Git** (for version control)

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/bicol-university-ecommerce.git
cd bicol-university-ecommerce
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE bicol_university_ecommerce;
```

2. Import the database schema:
```bash
mysql -u your_username -p bicol_university_ecommerce < database_schema.sql
```

3. Configure database connection in `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bicol_university_ecommerce');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Configure Email Settings
Update email configuration in `includes/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password');
```

### 5. Set Up Web Server
Configure your web server to point to the project root directory. Ensure the following directories are writable:
- `assets/images/products/` (for product uploads)
- `newsletter_subscribers.txt` (for newsletter subscriptions)

### 6. Access the Application
Open your browser and navigate to:
```
http://localhost/bicol-university-ecommerce/
```

## 📊 Database Schema

The application uses the following main tables:

- **users**: User accounts and authentication
- **products**: Product catalog with categories and pricing
- **orders**: Customer orders and transactions
- **order_items**: Individual order line items
- **cart**: Shopping cart items
- **wishlists**: User wishlists
- **product_reviews**: Customer reviews and ratings
- **testimonials**: Customer testimonials
- **events**: Campus events and announcements
- **offers**: Promotional offers and discounts
- **homepage_slides**: Homepage promotional slides
- **contact_messages**: Customer inquiries

## 🔗 API Endpoints

### Cart API
- `POST /api/cart/add.php` - Add item to cart
- `GET /api/cart/get.php` - Get cart contents
- `POST /api/cart/update.php` - Update cart item quantity
- `POST /api/cart/remove.php` - Remove item from cart

### Product API
- `GET /api/products/search.php` - Search products
- `GET /api/products/quickview.php` - Quick product preview

### Wishlist API
- `POST /api/wishlist/toggle.php` - Add/remove from wishlist

### Contact API
- `POST /api/contact.php` - Submit contact form

### Stripe Payment API
- `POST /api/stripe_create_intent.php` - Create payment intent

## 👨‍💼 Admin Panel Usage

Access the admin panel at `/admin/index.php` with admin credentials.

### Product Management
1. Navigate to **Products > List** to view all products
2. Use **Add Product** to create new items
3. Edit existing products with **Edit** action
4. Manage inventory levels and pricing

### Order Processing
1. View all orders in **Orders > List**
2. Update order status (Pending → Processing → Shipped → Delivered)
3. Generate and download invoices using **Download Invoice**

### User Management
1. Access **Users > List** to view all registered users
2. Edit user information and permissions
3. Manage account status (Active/Inactive/Suspended)

### Reports
- **Sales Report**: Monthly/quarterly sales analytics
- **Inventory Report**: Stock levels and reorder alerts

## 📁 Project Structure

```
bicol-university-ecommerce/
├── admin/                    # Admin panel pages
│   ├── index.php            # Admin dashboard
│   ├── orders/              # Order management
│   ├── products/            # Product management
│   └── users/               # User management
├── api/                     # API endpoints
│   ├── cart/                # Cart operations
│   ├── products/            # Product APIs
│   └── wishlist/            # Wishlist operations
├── assets/                  # Static assets
│   ├── css/                 # Stylesheets
│   ├── images/              # Images and media
│   └── js/                  # JavaScript files
├── classes/                 # PHP classes
│   ├── Cart.php            # Cart functionality
│   ├── Order.php           # Order management
│   ├── Product.php         # Product operations
│   └── User.php            # User management
├── functions/               # Utility functions
├── includes/                # Include files
│   ├── config.php          # Configuration
│   ├── db_connect.php      # Database connection
│   ├── header.php          # HTML header
│   └── footer.php          # HTML footer
├── pages/                   # Public pages
│   ├── about.php           # About page
│   ├── cart.php            # Shopping cart
│   ├── checkout.php        # Checkout process
│   ├── login.php           # User login
│   ├── products/           # Product pages
│   └── account/            # User account pages
├── composer.json           # PHP dependencies
├── database_schema.sql     # Database structure
├── index.php              # Homepage
└── README.md              # This file
```

## 🔧 Configuration

### Environment Variables
Create a `.env` file in the root directory for sensitive configuration:

```env
DB_HOST=localhost
DB_NAME=bicol_university_ecommerce
DB_USER=your_db_user
DB_PASS=your_db_password

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password

SITE_URL=http://localhost/bicol-university-ecommerce
```

### File Permissions
Ensure proper permissions for file uploads:
```bash
chmod 755 assets/images/products/
chmod 644 assets/images/products/*.*
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration and login
- [ ] Product browsing and search
- [ ] Add to cart and checkout
- [ ] Order placement and tracking
- [ ] Admin panel functionality
- [ ] Mobile responsiveness

### Sample Test Data
The database schema includes sample data for testing:
- Admin user: `admin` / password from hash
- Sample products across categories
- Test orders and reviews

## 🚀 Deployment

### Production Deployment
1. Set up production database
2. Configure domain and SSL certificate
3. Update configuration files for production
4. Enable caching and optimization
5. Set up backup procedures

### Performance Optimization
- Enable PHP opcode caching (OPcache)
- Implement database query caching
- Use CDN for static assets
- Compress images and minify CSS/JS

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Use meaningful commit messages
- Test thoroughly before submitting PR
- Update documentation for new features

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- **Email**: cecb2023-3381-92168@bicol-u.edu.ph
- **Issues**: [GitHub Issues](https://github.com/charlsbarquin)

## 🙏 Acknowledgments

- Bicol University administration for support
- Open source community for tools and libraries
- Contributors and testers

---

**Built with ❤️ for the Bicol University Community**

*Last updated: December 2025*
