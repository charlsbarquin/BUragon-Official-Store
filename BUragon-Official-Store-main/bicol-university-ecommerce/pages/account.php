<?php
require_once '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$pdo = getDbConnection();

// Fetch user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$profile_success = $profile_error = '';
$password_success = $password_error = '';
$pic_success = $pic_error = '';

// Check if default avatar exists, otherwise use a placeholder
$default_avatar = '../assets/images/default-avatar.png';
if (!file_exists(__DIR__ . '/../assets/images/default-avatar.png')) {
    $default_avatar = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjQ0NDIi8+CjxwYXRoIGQ9Ik0yMCA4MEMyMCA2NS45NTQzIDMxLjQ1NDMgNTQuNSA0NSA1NC41SDU1QzY4LjU0NTcgNTQuNSA4MCA2NS45NTQzIDgwIDgwVjEwMEgyMFY4MFoiIGZpbGw9IiNDQ0MiLz4KPC9zdmc+';
}

$profile_pic_url = $user['profile_pic'] ? '../uploads/profile_pics/' . htmlspecialchars($user['profile_pic']) : $default_avatar;

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pic'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $pic_error = 'Only JPG, PNG, and WebP files are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $pic_error = 'File size must be 5MB or less.';
        } else {
            $new_name = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/profile_pics/' . $new_name;
            
            // Create directory if it doesn't exist
            if (!is_dir(__DIR__ . '/../uploads/profile_pics/')) {
                mkdir(__DIR__ . '/../uploads/profile_pics/', 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Optionally delete old pic if not default
                if ($user['profile_pic'] && file_exists(__DIR__ . '/../uploads/profile_pics/' . $user['profile_pic'])) {
                    @unlink(__DIR__ . '/../uploads/profile_pics/' . $user['profile_pic']);
                }
                $pdo->prepare('UPDATE users SET profile_pic=? WHERE id=?')->execute([$new_name, $user_id]);
                $pic_success = 'Profile picture updated successfully!';
                $profile_pic_url = '../uploads/profile_pics/' . $new_name;
                // Refresh user info
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            } else {
                $pic_error = 'Failed to upload file. Please try again.';
            }
        }
    } else {
        $pic_error = 'No file selected or upload error.';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (!$first_name || !$last_name || !$email) {
        $profile_error = 'First name, last name, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Invalid email address.';
    } else {
        // Email uniqueness check
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $check->execute([$email, $user_id]);
        if ($check->fetch()) {
            $profile_error = 'That email address is already in use.';
        } else {
            $update = $pdo->prepare('UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=? WHERE id=?');
            $update->execute([$first_name, $last_name, $email, $phone, $address, $user_id]);
            $profile_success = 'Profile updated successfully!';
            // Refresh user info
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if (!$current || !$new || !$confirm) {
        $password_error = 'All password fields are required.';
    } elseif ($new !== $confirm) {
        $password_error = 'New passwords do not match.';
    } elseif (strlen($new) < 8) {
        $password_error = 'New password must be at least 8 characters.';
    } elseif (!password_verify($current, $user['password_hash'])) {
        $password_error = 'Current password is incorrect.';
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$new_hash, $user_id]);
        $password_success = 'Password changed successfully!';
    }
}

// Get user's order count for dashboard
$order_count = 0;
try {
    $order_stmt = $pdo->prepare('SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?');
    $order_stmt->execute([$user_id]);
    $order_count = $order_stmt->fetch()['order_count'];
} catch (PDOException $e) {
    // Table might not exist, set count to 0
    $order_count = 0;
}

// Get user's wishlist count
$wishlist_count = 0;
try {
    $wishlist_stmt = $pdo->prepare('SELECT COUNT(*) as wishlist_count FROM wishlists WHERE user_id = ?');
    $wishlist_stmt->execute([$user_id]);
    $wishlist_count = $wishlist_stmt->fetch()['wishlist_count'];
} catch (PDOException $e) {
    // Table might not exist, set count to 0
    $wishlist_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Bicol University E-Commerce</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #ff6b00;
            --light-bg: #f8fafc;
            --dark-text: #222;
            --light-text: #666;
            --success-color: #48BB78;
            --error-color: #dc3545;
            --warning-color: #ffc107;
            --border-color: #e1e8ed;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
            --border-radius-lg: 16px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #e6f2ff 0%, #fff5e6 100%);
            animation: heroGradient 8s ease-in-out infinite alternate;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
            color: var(--dark-text);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
        }

        @keyframes heroGradient {
            0% { background: linear-gradient(120deg, #e6f2ff 0%, #fff5e6 100%); }
            100% { background: linear-gradient(120deg, #fff5e6 0%, #e6f2ff 100%); }
        }

        .account-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .account-header {
            text-align: center;
            margin-bottom: 40px;
            color: var(--dark-text);
        }

        .account-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .account-header p {
            font-size: 1.1rem;
            color: var(--dark-text);
        }

        .account-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
            flex: 1;
        }

        .account-sidebar {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 30px;
            box-shadow: var(--shadow-lg);
            height: fit-content;
        }

        .profile-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-pic-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .profile-pic-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .profile-pic-upload:hover {
            background: #e55a2b;
            transform: scale(1.1);
        }

        .user-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .user-email {
            color: var(--dark-text);
            font-size: 0.95rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #e6f2ff 100%);
            border-radius: 15px;
            border: 1px solid #e1e8ed;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--light-text);
            font-weight: 500;
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--dark-text);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            border-color: var(--secondary);
            background: var(--secondary);
            color: white;
            transform: translateX(5px);
        }

        .action-btn i {
            font-size: 1.2rem;
            width: 20px;
        }

        .account-main {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .tab-navigation {
            display: flex;
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
        }

        .tab-btn {
            flex: 1;
            padding: 20px;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-text);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-btn.active {
            color: var(--primary);
            background: white;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--secondary);
        }

        .tab-content {
            padding: 40px;
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .form-section h3 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--light-text);
            cursor: pointer;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: var(--secondary);
        }

        .password-strength {
            margin-top: 10px;
        }

        .strength-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .strength-text {
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: var(--secondary);
            color: white;
            font-weight: 600;
            text-decoration: none;
            border: none;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
            font-size: 1.1rem;
            cursor: pointer;
        }

        .btn:hover, .btn:focus {
            transform: scale(1.06);
            box-shadow: 0 6px 24px rgba(0,0,0,0.13);
            background: var(--secondary);
            color: #fff;
        }

        .btn-secondary {
            background: white;
            color: var(--secondary);
            border: 2px solid var(--secondary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(72, 187, 120, 0.3);
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            background: var(--light-bg);
            transition: all 0.3s ease;
        }

        .file-upload:hover .file-upload-label {
            border-color: var(--secondary);
            background: rgba(255, 107, 0, 0.05);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h3 {
            color: var(--primary);
            margin-bottom: 15px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .modal-actions .btn {
            flex: 1;
        }

        @media (max-width: 768px) {
            .account-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .tab-navigation {
                flex-direction: column;
            }

            .tab-content {
                padding: 20px;
            }

            .account-header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .account-container {
                padding: 0 15px;
            }

            .tab-content {
                padding: 15px;
            }

            .modal-content {
                padding: 20px;
            }
        }

        /* Ensure footer stays at bottom */
        .account-container {
            min-height: calc(100vh - 40px); /* Account for body padding */
        }

        /* Add spacing before footer */
        .account-grid {
            margin-bottom: 60px;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid var(--primary-color);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="account-container">
        <div class="account-header">
            <h1><i class="fas fa-user-circle"></i> My Account</h1>
            <p>Manage your profile, security, and preferences</p>
        </div>

        <div class="account-grid">
            <!-- Sidebar -->
            <div class="account-sidebar">
                <div class="profile-section">
                    <div class="profile-pic-container">
                        <img src="<?php echo $profile_pic_url; ?>" alt="Profile Picture" class="profile-pic" id="profilePic">
                        <button class="profile-pic-upload" onclick="document.getElementById('profilePicInput').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $order_count; ?></span>
                        <div class="stat-label">Orders</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $wishlist_count; ?></span>
                        <div class="stat-label">Wishlist</div>
                    </div>
                </div>

                <div class="quick-actions">
                    <a href="account_orders.php" class="action-btn">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My Orders</span>
                    </a>
                    <a href="wishlist.php" class="action-btn">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <a href="logout.php" class="action-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="account-main">
                <div class="tab-navigation">
                    <button class="tab-btn active" data-tab="profile">
                        <i class="fas fa-user"></i> Profile
                    </button>
                    <button class="tab-btn" data-tab="security">
                        <i class="fas fa-shield-alt"></i> Security
                    </button>
                </div>

                <!-- Profile Tab -->
                <div class="tab-content active" id="profile-tab">
                    <form method="post" enctype="multipart/form-data" id="profilePicForm" style="display: none;">
                        <input type="file" name="profile_pic" id="profilePicInput" accept="image/jpeg,image/png,image/webp">
                    </form>

                    <?php if ($pic_success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $pic_success; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($pic_error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $pic_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="profileForm">
                        <div class="form-section">
                            <h3><i class="fas fa-user-edit"></i> Personal Information</h3>
                            
                            <?php if ($profile_success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo $profile_success; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($profile_error): ?>
                                <div class="alert alert-error">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $profile_error; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" required 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" required 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab -->
                <div class="tab-content" id="security-tab">
                    <form method="post" id="passwordForm">
                        <div class="form-section">
                            <h3><i class="fas fa-lock"></i> Change Password</h3>
                            
                            <?php if ($password_success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($password_error): ?>
                                <div class="alert alert-error">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="current_password">Current Password *</label>
                                    <div class="password-container">
                                        <input type="password" id="current_password" name="current_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password *</label>
                                    <div class="password-container">
                                        <input type="password" id="new_password" name="new_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" id="strengthFill"></div>
                                        </div>
                                        <div class="strength-text" id="strengthText">Enter a password</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password *</label>
                                    <div class="password-container">
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modalTitle">Confirm Action</h3>
            <p id="modalMsg">Are you sure you want to proceed?</p>
            <div class="modal-actions">
                <button id="modalConfirmBtn" class="btn btn-primary">Yes, Confirm</button>
                <button id="modalCancelBtn" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                button.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });

        // Profile picture upload
        document.getElementById('profilePicInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                document.getElementById('profilePicForm').submit();
            }
        });

        // Password toggle functionality
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            const icon = toggle.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength meter
        const newPassword = document.getElementById('new_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        if (newPassword && strengthFill && strengthText) {
            newPassword.addEventListener('input', function() {
                const password = this.value;
                let score = 0;
                let feedback = [];

                if (password.length >= 8) {
                    score++;
                    feedback.push('Length ✓');
                }
                if (/[A-Z]/.test(password)) {
                    score++;
                    feedback.push('Uppercase ✓');
                }
                if (/[a-z]/.test(password)) {
                    score++;
                    feedback.push('Lowercase ✓');
                }
                if (/[0-9]/.test(password)) {
                    score++;
                    feedback.push('Number ✓');
                }
                if (/[^A-Za-z0-9]/.test(password)) {
                    score++;
                    feedback.push('Special character ✓');
                }

                const percentage = (score / 5) * 100;
                strengthFill.style.width = percentage + '%';

                let color, text;
                if (score === 0) {
                    color = '#e9ecef';
                    text = 'Enter a password';
                } else if (score <= 2) {
                    color = '#dc3545';
                    text = 'Weak';
                } else if (score <= 3) {
                    color = '#ffc107';
                    text = 'Fair';
                } else if (score <= 4) {
                    color = '#48BB78';
                    text = 'Good';
                } else {
                    color = '#ff6b00';
                    text = 'Strong';
                }

                strengthFill.style.backgroundColor = color;
                strengthText.textContent = text + ' - ' + feedback.join(', ');
                strengthText.style.color = color;
            });
        }

        // Form validation and confirmation
        let pendingForm = null;
        const modal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMsg = document.getElementById('modalMsg');
        const modalConfirmBtn = document.getElementById('modalConfirmBtn');
        const modalCancelBtn = document.getElementById('modalCancelBtn');

        function showModal(title, msg, onConfirm) {
            modalTitle.textContent = title;
            modalMsg.textContent = msg;
            modal.style.display = 'flex';
            
            modalConfirmBtn.onclick = function() {
                modal.style.display = 'none';
                if (onConfirm) onConfirm();
            };
            
            modalCancelBtn.onclick = function() {
                modal.style.display = 'none';
                pendingForm = null;
            };
            
            modalConfirmBtn.focus();
        }

        // Profile form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const emailInput = this.querySelector('input[name="email"]');
            const originalEmail = '<?php echo htmlspecialchars($user['email']); ?>';
            
            if (emailInput && emailInput.value !== originalEmail) {
                e.preventDefault();
                pendingForm = this;
                showModal(
                    'Confirm Email Change',
                    'Are you sure you want to change your email address? This will be used for future logins.',
                    function() {
                        pendingForm.submit();
                    }
                );
            }
        });

        // Password form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('New passwords do not match!');
                return;
            }
            
            if (newPass.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return;
            }
            
            e.preventDefault();
            pendingForm = this;
            showModal(
                'Confirm Password Change',
                'Are you sure you want to change your password? You will need to use the new password for future logins.',
                function() {
                    pendingForm.submit();
                }
            );
        });

        // Modal accessibility
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modal.style.display = 'none';
            }
        });

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    </script>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
