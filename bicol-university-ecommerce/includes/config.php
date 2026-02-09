<?php
// includes/config.php

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'bicol_university_ecommerce');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');

// Site Configuration
define('SITE_NAME', 'Bicol University E-Commerce');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/bicol-university-ecommerce');
define('ADMIN_EMAIL', 'admin@bicol-u.edu.ph');

// Security Configuration
define('PASSWORD_COST', 12);
