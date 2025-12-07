<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db_connect.php';
require_once __DIR__ . '/../functions/cart_functions.php';
$pdo = getDbConnection();

// Get cart count using the unified function
$cart_count = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($page_title) ? $page_title . ' | ' : '' ?>Bicol University E-Commerce</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    :root {
      --bu-blue: #003366;
      --bu-light-blue: #007BFF;
      --bu-orange: #FF6B35;
      --light-blue: #E6F2FF;
      --dark-text: #2D3748;
      --light-text: #F7FAFC;
      --gray-bg: #EDF2F7;
      --success-green: #48BB78;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    /* Notification Bar */
    .notification-bar {
      background-color: var(--bu-orange);
      color: white;
      padding: 8px 0;
      text-align: center;
      font-size: 14px;
      font-weight: 500;
    }

    .notification-bar a {
      color: white;
      text-decoration: underline;
      margin-left: 10px;
    }

    /* Top Bar */
    .top-bar {
      background-color: var(--bu-blue);
      color: white;
      padding: 12px 5%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      z-index: 100;
    }

    .top-left, .top-right {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .top-link {
      color: white;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .top-link:hover {
      color: var(--bu-orange);
      transform: translateY(-2px);
    }

    /* Main Navigation */
    .main-nav {
      background-color: white;
      padding: 15px 5%;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: flex-start; /* Start to allow brand-logo padding to work */
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 99;
      gap: 32px; /* Add space between brand-logo and nav-center */
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 28px; /* Increased gap for more space between logo and text */
      text-decoration: none;
      padding-right: 32px; /* Add space between brand and nav links */
    }

    .logo-img {
      height: 60px; /* Slightly larger for better visibility */
      width: auto;
      object-fit: contain;
      margin-right: 0; /* Remove any default margin */
      display: block;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 2px;
    }

    .brand-name {
      font-size: 22px;
      font-weight: 700;
      color: var(--bu-blue);
      line-height: 1;
    }

    .brand-tagline {
      font-size: 12px;
      color: var(--bu-orange);
      font-weight: 500;
      margin-top: 3px;
    }

    .nav-center {
      display: flex;
      align-items: center;
      gap: 38px; /* More space between nav links */
      margin-left: 0;
    }

    .nav-link {
      color: var(--dark-text);
      text-decoration: none;
      font-weight: 500;
      font-size: 16px;
      padding: 8px 0;
      position: relative;
      transition: all 0.3s ease;
    }

    .nav-link:hover {
      color: var(--bu-light-blue);
    }

    .nav-link:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background-color: var(--bu-light-blue);
      transition: width 0.3s ease;
    }

    .nav-link:hover:after {
      width: 100%;
    }

    .search-container {
      position: relative;
      width: 350px;
    }

    .search-input {
      width: 100%;
      padding: 10px 20px 10px 45px;
      border-radius: 30px;
      border: 1px solid #ddd;
      font-size: 14px;
      transition: all 0.3s ease;
      background-color: var(--gray-bg);
    }

    .search-input:focus {
      outline: none;
      border-color: var(--bu-light-blue);
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
    }

    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #718096;
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .nav-icon {
      position: relative;
      color: var(--dark-text);
      font-size: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .nav-icon:hover {
      color: var(--bu-light-blue);
      transform: translateY(-2px);
    }

    .badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: var(--bu-orange);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      font-weight: 600;
    }

    .user-dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      background-color: white;
      min-width: 200px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      border-radius: 8px;
      z-index: 1;
      padding: 10px 0;
      margin-top: 10px;
    }

    .dropdown-menu a {
      color: var(--dark-text);
      padding: 10px 20px;
      text-decoration: none;
      display: block;
      font-size: 14px;
      transition: all 0.2s;
    }

    .dropdown-menu a:hover {
      background-color: var(--light-blue);
      color: var(--bu-light-blue);
    }

    .user-dropdown:hover .dropdown-menu {
      display: block;
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Mobile Menu Toggle */
    .mobile-menu-btn {
      display: none;
      background: none;
      border: none;
      font-size: 24px;
      color: var(--dark-text);
      cursor: pointer;
    }

    /* Responsive Styles */
    @media (max-width: 1024px) {
      .search-container {
        width: 250px;
      }
      
      .nav-center {
        gap: 20px;
      }
    }

    @media (max-width: 768px) {
      .mobile-menu-btn {
        display: block;
      }
      
      .nav-center {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: white;
        flex-direction: column;
        gap: 0;
        padding: 15px 5%;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
      }
      
      .nav-center.active {
        display: flex;
      }
      
      .nav-link {
        padding: 12px 0;
        width: 100%;
        border-bottom: 1px solid #eee;
      }
      
      .search-container {
        width: 100%;
        margin: 15px 0;
      }
      
      .top-left, .top-right {
        gap: 15px;
      }
    }

    @media (max-width: 480px) {
      .top-bar {
        flex-direction: column;
        gap: 10px;
        padding: 10px 5%;
      }
      
      .brand-name {
        font-size: 18px;
      }
      
      .brand-tagline {
        font-size: 10px;
      }
      
      .logo-img {
        height: 50px;
      }
    }
  </style>
</head>
<body>

  <!-- Notification Bar -->
  <div class="notification-bar">
    <span>Welcome to Bicol University E-Commerce | <a href="<?php echo SITE_URL; ?>/pages/products/index.php">Shop Now</a></span>
  </div>

  <!-- Top Bar -->
  <div class="top-bar">
    <div class="top-left">
      <a href="<?php echo SITE_URL; ?>/pages/about.php" class="top-link">
        <i class="fas fa-info-circle"></i> About BU
      </a>
      <!-- Removed support and locations links -->
    </div>
    <div class="top-right">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo SITE_URL; ?>/pages/account.php" class="top-link">
          <i class="fas fa-user-circle"></i> My Account
        </a>
        <a href="<?php echo SITE_URL; ?>/logout" class="top-link">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      <?php else: ?>
        <a href="<?php echo SITE_URL; ?>/pages/login.php" class="top-link">
          <i class="fas fa-sign-in-alt"></i> Student Login
        </a>
        <a href="<?php echo SITE_URL; ?>/pages/register.php" class="top-link">
          <i class="fas fa-user-plus"></i> Register
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Main Navigation -->
  <nav class="main-nav">
    <button class="mobile-menu-btn" id="mobileMenuBtn">
      <i class="fas fa-bars"></i>
    </button>
    
    <a href="<?php echo SITE_URL; ?>/index.php" class="brand-logo">
      <img src="<?php echo SITE_URL; ?>/assets/images/logos/bu-logo.png" alt="Bicol University Logo" class="logo-img">
      <div class="brand-text">
        <div class="brand-name">BUragon</div>
        <div class="brand-tagline">Bicol University Official Store</div>
      </div>
    </a>
    
    <div class="nav-center" id="mainMenu">
      <a href="<?php echo SITE_URL; ?>/" class="nav-link">Home</a>
      <a href="<?php echo SITE_URL; ?>/pages/products/index.php" class="nav-link">Products</a>
      <a href="<?php echo SITE_URL; ?>/pages/categories.php" class="nav-link">Categories</a>
      <a href="<?php echo SITE_URL; ?>/pages/academic.php" class="nav-link">Academic</a>
      <a href="<?php echo SITE_URL; ?>/pages/merchandise.php" class="nav-link">Merchandise</a>
    </div>
    
    <div class="search-container">
      <i class="fas fa-search search-icon"></i>
      <input type="text" class="search-input" placeholder="Search products..." id="searchInput">
      <div class="search-results" id="searchResults"></div>
    </div>
    
    <div class="nav-right">
      <div class="user-dropdown" style="display: inline-block; position: relative;">
          <button class="user-dropdown-toggle" id="userDropdownToggle" aria-haspopup="true" aria-expanded="false" style="background: var(--gray-light); border: none; border-radius: 999px; padding: 10px 22px; font-weight: 600; color: var(--primary); cursor: pointer; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
              <i class="fas fa-user-circle" style="font-size: 1.3em;"></i>
              <?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : 'Account'; ?>
              <i class="fas fa-chevron-down" style="font-size: 0.9em;"></i>
          </button>
          <div class="user-dropdown-menu" id="userDropdownMenu" style="display: none; position: absolute; right: 0; top: 110%; background: #fff; box-shadow: 0 4px 18px rgba(0,0,0,0.10); border-radius: 10px; min-width: 180px; z-index: 100;">
              <a href="/bicol-university-ecommerce/pages/account.php" class="user-dropdown-link" style="display: block; padding: 12px 20px; color: var(--primary); text-decoration: none;">Account</a>
              <a href="/bicol-university-ecommerce/pages/account_orders.php" class="user-dropdown-link" style="display: block; padding: 12px 20px; color: var(--primary); text-decoration: none;">Orders</a>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <a href="/bicol-university-ecommerce/admin/index.php" class="user-dropdown-link" style="display: block; padding: 12px 20px; color: var(--secondary); text-decoration: none; font-weight: 700;">Admin Dashboard</a>
              <?php endif; ?>
              <a href="/bicol-university-ecommerce/logout.php" class="user-dropdown-link" style="display: block; padding: 12px 20px; color: #dc3545; text-decoration: none;">Logout</a>
          </div>
      </div>
      <script>
      // Improved Dropdown logic
      document.addEventListener('DOMContentLoaded', function() {
          var toggle = document.getElementById('userDropdownToggle');
          var menu = document.getElementById('userDropdownMenu');
          if (toggle && menu) {
              toggle.addEventListener('click', function(e) {
                  e.stopPropagation();
                  var expanded = toggle.getAttribute('aria-expanded') === 'true';
                  toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                  menu.style.display = expanded ? 'none' : 'block';
              });
              // Prevent closing when clicking inside the menu
              menu.addEventListener('click', function(e) {
                  e.stopPropagation();
              });
              // Close dropdown when clicking outside
              document.addEventListener('click', function() {
                  menu.style.display = 'none';
                  toggle.setAttribute('aria-expanded', 'false');
              });
              // Keyboard accessibility: close on Escape
              document.addEventListener('keydown', function(e) {
                  if (e.key === 'Escape') {
                      menu.style.display = 'none';
                      toggle.setAttribute('aria-expanded', 'false');
                  }
              });
          }
      });
      </script>
      
      <a href="<?php echo SITE_URL; ?>/pages/wishlist.php" class="nav-icon">
        <i class="fas fa-heart"></i>
      </a>
      
      <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="nav-icon">
        <i class="fas fa-shopping-cart"></i>
      </a>
    </div>
  </nav>

  <script>
    // Mobile Menu Toggle
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
      document.getElementById('mainMenu').classList.toggle('active');
    });

    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout = null;

    searchInput.addEventListener('input', function(e) {
      const query = e.target.value.trim();
      clearTimeout(searchTimeout);
      if (query.length > 2) {
        searchTimeout = setTimeout(() => {
          fetch('/bicol-university-ecommerce/api/products/search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
              if (Array.isArray(data) && data.length > 0) {
                searchResults.innerHTML = data.map(product => `
                  <a href=\"/bicol-university-ecommerce/pages/products/view.php?id=${product.id}\" class=\"search-result-item\">
                    <img src=\"/bicol-university-ecommerce/assets/images/products/${product.image ? product.image : 'default-product.jpg'}\" alt=\"${product.name}\" class=\"search-result-img\">
                    <span class=\"search-result-name\">${product.name}</span>
                    <span class=\"search-result-price\">₱${parseFloat(product.price).toFixed(2)}</span>
                  </a>
                `).join('');
                searchResults.style.display = 'block';
              } else {
                searchResults.innerHTML = '<div class="search-result-empty">No products found</div>';
                searchResults.style.display = 'block';
              }
            })
            .catch(() => {
              searchResults.innerHTML = '<div class="search-result-empty">Error searching products</div>';
              searchResults.style.display = 'block';
            });
        }, 250);
      } else {
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
      }
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.search-container')) {
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
      }
    });

    // Basic styles for search results
    const style = document.createElement('style');
    style.innerHTML = `
      .search-results {
        position: absolute;
        top: 110%;
        left: 0;
        width: 100%;
        background: #fff;
        box-shadow: 0 4px 18px rgba(0,0,0,0.10);
        border-radius: 10px;
        z-index: 200;
        max-height: 350px;
        overflow-y: auto;
        display: none;
      }
      .search-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        text-decoration: none;
        color: #222;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
      }
      .search-result-item:last-child {
        border-bottom: none;
      }
      .search-result-item:hover {
        background: #f7faff;
      }
      .search-result-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        background: #f0f0f0;
      }
      .search-result-name {
        flex: 1;
        font-weight: 500;
      }
      .search-result-price {
        color: #007BFF;
        font-weight: 600;
        font-size: 15px;
      }
      .search-result-empty {
        padding: 18px;
        color: #888;
        text-align: center;
      }
    `;
    document.head.appendChild(style);

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
  </script>
</body>
</html>