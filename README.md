# 🐉 BUragon Official Store

> The official online merchandise store for University of Bicol, celebrating school spirit and serving the UB community since 2022.

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue?logo=php)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange?logo=mysql)](https://mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-success)](https://github.com/charlsbarquin/BUragon-Official-Store)
[![GitHub Stars](https://img.shields.io/github/stars/charlsbarquin/BUragon-Official-Store?style=social)](https://github.com/charlsbarquin/BUragon-Official-Store)

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-key-features)
- [Tech Stack](#-technology-stack)
- [Quick Start](#-quick-start-local-development)
- [Project Structure](#-directory-structure)
- [Admin Access](#-admin-development-only)
- [API Endpoints](#-api-endpoints)
- [Usage Examples](#-usage-examples)
- [Deployment](#-deployment-production)
- [Security](#-security--secrets)
- [Contributing](#-contributing--code-style)
- [License](#-license--credits)

---

## 🎯 Overview

**BUragon** is a comprehensive, production-ready e-commerce platform designed specifically for University of Bicol to distribute official merchandise and campus essentials. Built with **vanilla PHP** and **MySQL**, it provides a scalable solution for university merchandise sales with integrated payment processing, admin management tools, and customer engagement features.

### What is BUragon?

BUragon (University of Bicol Dragon) is the official merchandise store for University of Bicol students, alumni, faculty, and staff. The platform enables seamless online shopping with:

- 🛍️ **Complete e-commerce functionality** — product catalog, cart, checkout, order management
- 💳 **Multiple payment methods** — PayMongo and Stripe integrations with webhook support
- 📊 **Admin dashboard** — manage products, orders, users, reports, and analytics
- 📧 **Customer engagement** — newsletter, contact forms, email notifications
- 🎁 **Special features** — wishlist, student discounts, seasonal promotions, faculty picks
- 📄 **Automated invoicing** — PDF generation with DomPDF
- 🔐 **Secure authentication** — password hashing, session management, CSRF protection

Perfect for universities, organizations, and small-to-medium businesses looking for a flexible, maintainable e-commerce solution.

---

## ✨ Key Features

### For Customers
- ✅ User registration with email verification and student discount eligibility
- ✅ Advanced product search, filtering, and categorization
- ✅ Shopping cart with add/update/remove functionality
- ✅ Wishlist for saving favorite items
- ✅ Secure checkout with address validation
- ✅ Multiple payment options (PayMongo & Stripe)
- ✅ Order tracking and history
- ✅ PDF invoice generation and download
- ✅ Newsletter subscription
- ✅ Account management and profile customization

### For Administrators
- ✅ Complete product management (CRUD, inventory, pricing, discounts)
- ✅ Order management and fulfillment tracking
- ✅ User management with role-based access
- ✅ Content modules: slides, testimonials, events, offers
- ✅ Sales and inventory reports
- ✅ Settings and configuration panel
- ✅ Restricted admin login with security key

### Technical Features
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ RESTful API endpoints for AJAX operations
- ✅ Cookie-based and session-based cart persistence
- ✅ File upload handling with validation
- ✅ Email notifications via PHPMailer/SMTP
- ✅ Database backed-up and structured queries
- ✅ Prepared statements to prevent SQL injection
- ✅ CORS and security headers configured

---

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+** — Server-side logic and request handling
- **MySQL 5.7+** — Relational database
- **Apache/Nginx** — Web server with mod_rewrite support

### Frontend
- **HTML5** — Semantic markup
- **CSS3** — Custom styling with responsive design
- **JavaScript (Vanilla)** — Client-side interactivity
- **Font Awesome 6** — Icon library
- **Google Fonts** — Typography

### Libraries & Dependencies
- **Composer** — Dependency management
- **PHPMailer** — SMTP email sending
- **DomPDF** — PDF generation for invoices
- **PayMongo PHP SDK** — Payment processing
- **Stripe SDK** — Alternative payment gateway

### Development
- **XAMPP / WAMP / MAMP** — Local development environment
- **Git** — Version control
- **GitHub** — Repository hosting

---

## 🚀 Quick Start (Local Development)

### Prerequisites
- **PHP 7.4+** with extensions: `mysqli`, `pdo`, `mbstring`, `gd`, `openssl`, `json`
- **MySQL 5.7+** or MariaDB
- **Composer** (dependency manager)
- **XAMPP**, **WAMP**, or **MAMP** (or equivalent webserver)
- **Git** (for cloning)

### Installation Steps

#### 1️⃣ Clone the Repository
```bash
git clone https://github.com/charlsbarquin/BUragon-Official-Store.git
cd bicol-university-ecommerce
```

#### 2️⃣ Install Dependencies
```bash
composer install
```

#### 3️⃣ Create Database
Create a new MySQL database:
```bash
mysql -u root -p
CREATE DATABASE bicol_university_ecommerce;
EXIT;
```

#### 4️⃣ Import Database Schema
If a SQL dump is provided:
```bash
mysql -u root -p bicol_university_ecommerce < bicol_university_ecommerce.sql
```

#### 5️⃣ Configure Application
Edit `includes/config.php`:
```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bicol_university_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password

// SMTP Configuration (for emails)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');  // Gmail App Password

// Payment Keys (use test keys for development)
define('PAYMONGO_SECRET', 'sk_test_...');
define('STRIPE_SECRET', 'sk_test_...');
```

#### 6️⃣ Set Upload Permissions
Ensure these directories are writable:
```bash
# Windows (using PowerShell as Admin)
icacls "C:\xampp\htdocs\bicol-university-ecommerce\uploads" /grant:r "${env:USERNAME}:(OI)(CI)F" /T

# Linux/Mac
chmod -R 755 uploads/
chmod -R 755 assets/images/
```

#### 7️⃣ Start Development Server
Place the project in your webserver root (XAMPP: `C:\xampp\htdocs\`), then:

**XAMPP/WAMP:**
- Start Apache and MySQL via control panel

**Built-in PHP Server:**
```bash
php -S localhost:8000
# Visit: http://localhost:8000/bicol-university-ecommerce
```

#### 8️⃣ Access the Application
- **Frontend:** http://localhost/bicol-university-ecommerce
- **Admin Panel:** http://localhost/bicol-university-ecommerce/admin
- **Test Customer:** Any account you create during registration
- **Test Admin:** See [Admin Access](#-admin-development-only) below

---

## 👥 Admin (Development Only)

### ⚠️ Security Warning
**DO NOT use these credentials in production!** Create proper admin accounts via:
1. Database insertion
2. Admin creation script
3. Environment variables

### Default Test Credentials
- **Admin Email/Username:** `admin2`
- **Password:** `admin123`
- **Quick Access Key:** `admin123` (appended as `?admin=admin123`)

### Access Admin Panel
1. Navigate to: http://localhost/bicol-university-ecommerce/admin/login.php
2. Enter test credentials above
3. Or use quick access: http://localhost/bicol-university-ecommerce/admin/index.php?admin=admin123

### Admin Features Available
- 📦 Product Management (Add/Edit/Delete/Feature)
- 📋 Order Management (View/Update Status/Delete)
- 👤 User Management (View/Edit/Manage Roles)
- 🎨 Content Management (Slides, Testimonials, Events, Offers)
- 📊 Reports (Sales, Inventory)
- ⚙️ Settings (Site Configuration)

---

## 📂 Directory Structure

```
bicol-university-ecommerce/
├── admin/                      # Admin panel
│   ├── index.php              # Dashboard
│   ├── login.php              # Admin login
│   ├── products/              # Product management
│   ├── orders/                # Order management
│   ├── users/                 # User management
│   ├── content/               # Content modules
│   └── includes/              # Admin header/footer
├── api/                        # JSON AJAX endpoints
│   ├── cart/                  # Cart operations
│   ├── products/              # Product search/quickview
│   ├── wishlist/              # Wishlist toggles
│   ├── newsletter/            # Newsletter subscribe
│   ├── contact.php            # Contact form
│   └── paymongo_*.php         # PayMongo webhooks
├── assets/                     # Static files
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   └── images/                # Images and logos
├── classes/                    # PHP classes
│   ├── Cart.php               # Shopping cart logic
│   ├── Order.php              # Order management
│   ├── Product.php            # Product operations
│   └── User.php               # User authentication
├── functions/                  # Utility functions
│   ├── cart_functions.php
│   ├── product_functions.php
│   ├── user_functions.php
│   └── ...
├── includes/                   # Core includes
│   ├── config.php            # Configuration (EDIT THIS!)
│   ├── db_connect.php        # Database connection
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   ├── auth_functions.php    # Authentication helpers
│   └── mail_helper.php       # Email sending
├── pages/                      # Frontend pages
│   ├── index.php             # Homepage
│   ├── products/             # Product pages
│   ├── cart.php              # Shopping cart
│   ├── checkout.php          # Checkout process
│   ├── account.php           # User account
│   └── ... 20+ more pages
├── uploads/                    # User uploads (writable)
│   ├── profile_pics/
│   └── ...
├── vendor/                     # Composer dependencies
│   ├── phpmailer/            # Email library
│   ├── dompdf/               # PDF generation
│   └── ... (other packages)
├── composer.json             # Dependency manifest
├── composer.lock             # Dependency lock file
├── README.md                 # This file
├── LICENSE                   # MIT License
└── index.php                # Entry point
```

---

## 🔌 API Endpoints

### Public Endpoints (No Auth Required)
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/products/search.php?q=term` | Search products |
| POST | `/api/products/quickview.php` | Get product details |
| POST | `/api/wishlist/toggle.php` | Add/remove from wishlist |
| GET | `/api/wishlist/get.php` | Get wishlist items |
| POST | `/api/newsletter/subscribe.php` | Subscribe to newsletter |
| POST | `/api/contact.php` | Submit contact form |

### Payment Endpoints
| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/paymongo_create_intent.php` | Create payment intent |
| POST | `/api/paymongo_check_status.php` | Check payment status |
| POST | `/api/paymongo_success.php` | Payment success webhook |
| POST | `/api/stripe_create_intent.php` | Stripe payment intent |

### Admin Endpoints (Requires Auth)
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/admin/search.php?q=term` | Search admin content |

---

## 💡 Usage Examples

### Example 1: Create an Order via API
```bash
curl -X POST "http://localhost/bicol-university-ecommerce/api/cart/add.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "product_id=5&quantity=2" \
  -c cookies.txt

# Then proceed to checkout.php in browser
```

### Example 2: Create PayMongo Payment
```bash
curl -X POST "http://localhost/bicol-university-ecommerce/api/paymongo_create_intent.php" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50000,
    "currency": "PHP",
    "order_id": 12
  }'
```

### Example 3: User Registration
Navigate to `pages/register.php` and fill the form:
- Email (use `@bicol-u.edu.ph` for student discount)
- Password (min 8 chars, 1 uppercase, 1 number)
- First/Last Name

---

## 📦 Deployment (Production)

### Pre-Deployment Checklist

- [ ] Remove all test credentials from `includes/config.php`
- [ ] Rotate API keys (PayMongo, Stripe, Gmail App Password)
- [ ] Set up production MySQL database and user
- [ ] Configure HTTPS with valid SSL certificate
- [ ] Enable error logging (disable display in production)
- [ ] Set up automated database backups
- [ ] Configure firewall and security headers
- [ ] Test all payment flows with live keys (in sandbox mode first)
- [ ] Set up monitoring and uptime alerts
- [ ] Prepare disaster recovery plan

### Environment Variables (Recommended)
Instead of hardcoding secrets in `config.php`, use environment variables:

```php
// In includes/config.php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'bicol_university_ecommerce');
define('PAYMONGO_SECRET', getenv('PAYMONGO_SECRET'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
// ... etc
```

Then set variables in:
- **Apache:** `.htaccess` or vhost config
- **Nginx:** Environment file or systemd service
- **cPanel:** Environment Variables section

### Apache VirtualHost Example
```apache
<VirtualHost *:443>
    ServerName store.ub.edu.ph
    ServerAlias www.store.ub.edu.ph
    DocumentRoot /var/www/bicol-university-ecommerce
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/store.ub.edu.ph.crt
    SSLCertificateKeyFile /etc/ssl/private/store.ub.edu.ph.key
    
    <Directory /var/www/bicol-university-ecommerce>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Enable mod_rewrite
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>
    
    # Error & Access logs
    ErrorLog ${APACHE_LOG_DIR}/ub-store-error.log
    CustomLog ${APACHE_LOG_DIR}/ub-store-access.log combined
    
    # Security headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

### Nginx Configuration Example
```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name store.ub.edu.ph www.store.ub.edu.ph;
    return 301 https://$server_name$request_uri;
}

# HTTPS server block
server {
    listen 443 ssl http2;
    server_name store.ub.edu.ph www.store.ub.edu.ph;
    root /var/www/bicol-university-ecommerce;
    index index.php;

    # SSL certificates
    ssl_certificate /etc/ssl/certs/store.ub.edu.ph.crt;
    ssl_certificate_key /etc/ssl/private/store.ub.edu.ph.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    location ~ /vendor {
        deny all;
    }
    location ~ /uploads {
        location ~ \.php$ { deny all; }
    }

    # Logging
    access_log /var/log/nginx/ub-store-access.log combined;
    error_log /var/log/nginx/ub-store-error.log warn;
}
```

### Database Backup (Cron Job)
```bash
# Add to crontab (crontab -e)
# Daily backup at 2 AM
0 2 * * * mysqldump -u dbuser -p'PASSWORD' bicol_university_ecommerce > /backups/db-$(date +\%Y-\%m-\%d).sql

# Weekly backup rotation
0 3 0 * * find /backups -name "db-*.sql" -mtime +7 -delete
```

---

## 🔐 Security & Secrets

### ⚠️ Critical Security Practices

**DO NOT commit these to git:**
- Passwords (DB, email, API keys)
- API keys (PayMongo, Stripe, Google)
- Private encryption keys
- SMTP credentials
- Admin access codes

### Recommended Setup

1. **Use Environment Variables**
```php
// includes/config.php
define('DB_PASS', getenv('DB_PASS') ?: '');
define('PAYMONGO_SECRET', getenv('PAYMONGO_SECRET'));
define('STRIPE_SECRET', getenv('STRIPE_SECRET'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
```

2. **Store Secrets Securely**
   - Use a `.env` file (loaded by the app, committed to `.gitignore`)
   - Use OS environment variables (Docker, systemd, etc.)
   - Use a secrets manager (AWS Secrets Manager, HashiCorp Vault, etc.)

3. **Key Rotation**
   - Rotate API keys every 90 days
   - Immediately rotate if leaked
   - Keep old keys for gradual migration

4. **Database Security**
   - Use strong passwords (20+ chars, mixed case, symbols)
   - Create restricted DB user (not root)
   - Use encrypted connections (SSL for remote DB)
   - Regular backups stored securely

5. **File Upload Validation**
```php
// Validate MIME type, size, and extension
$allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB
$file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array(mime_content_type($tmp), $allowed_mimes)) {
    die('Invalid file type');
}
if (filesize($tmp) > $max_size) {
    die('File too large');
}
// Store with randomized name
$new_name = bin2hex(random_bytes(16)) . '.' . $file_ext;
```

6. **SQL Injection Prevention**
Always use prepared statements:
```php
// ✅ SECURE
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ VULNERABLE
$result = $pdo->query("SELECT * FROM users WHERE email = '$email'");
```

---

## 🤝 Contributing & Code Style

We welcome contributions! See `CONTRIBUTING.md` for detailed guidelines.

### Key Points
- Follow **PSR-12** code style for PHP
- Use conventional commit messages: `type(scope): short description`
- Write tests for new features
- Include screenshots for UI changes
- Open PR against `main` branch
- Do not include secrets or credentials

### Contribution Types
We welcome:
- 🐛 Bug reports and fixes
- ✨ New features
- 📚 Documentation improvements
- 🎨 UI/UX enhancements
- 🚀 Performance optimizations
- 🔒 Security improvements

### Getting Started
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and test
4. Commit with conventional messages
5. Push to your fork
6. Open a Pull Request

---

## 📝 License & Credits

### License
This project is licensed under the **MIT License** — see [LICENSE](LICENSE) file for details.

### Project Owner & Maintainer
- **Name:** Charls Barquin
- **GitHub:** [@charlsbarquin](https://github.com/charlsbarquin)
- **Email:** charlsbarquin2@gmail.com

### Credits
- **University of Bicol** — For inspiring this project
- **Third-Party Libraries:**
  - PHPMailer — Email sending
  - DomPDF — PDF generation
  - PayMongo SDK — Payment processing
  - Font Awesome — Icon library
  - Google Fonts — Typography

For full credits, see [CREDITS.md](CREDITS.md)

---

## 🗺️ Roadmap

### Current Release (v1.0)
- ✅ Core e-commerce functionality
- ✅ Payment integrations (PayMongo, Stripe)
- ✅ Admin dashboard
- ✅ Email notifications

### Upcoming Features
- Mobile app (React Native) — v2.0
- Analytics dashboard with advanced reports
- AI-powered product recommendations
- Multi-language support
- Affiliate program
- Super admin marketplace (multi-vendor) — v3.0

### How to Contribute
- Pick issues labeled `help wanted` or `good first issue`
- Discuss feature ideas in Issues before implementing
- Submit PRs with tests and documentation

---

## 📞 Support & Contact

- **Issues & Bug Reports:** [GitHub Issues](https://github.com/charlsbarquin/BUragon-Official-Store/issues)
- **Discussions:** [GitHub Discussions](https://github.com/charlsbarquin/BUragon-Official-Store/discussions)
- **Email:** charlsbarquin2@gmail.com
- **Documentation:** See `docs/` folder (coming soon)

---

## 📄 Additional Resources

- [Changelog](CHANGELOG.md) — Version history and updates
- [Contributing Guidelines](CONTRIBUTING.md) — How to contribute
- [Credits](CREDITS.md) — Third-party acknowledgments
- [Security Policy](SECURITY.md) — Report vulnerabilities responsibly

---

**Made with ❤️ for the University of Bicol community**

⭐ **If you find this project helpful, please consider starring it on GitHub!**
