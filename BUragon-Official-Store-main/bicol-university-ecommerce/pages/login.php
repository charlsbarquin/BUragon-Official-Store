<?php
session_start();
require_once '../includes/db_connect.php';
$pdo = getDbConnection();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($username_email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username_email, $username_email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $errors[] = 'Your account is not active.';
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                // Optionally set a persistent cookie for 'remember me'
                if ($remember) {
                    setcookie('remember_me', $user['id'], time() + (86400 * 30), "/");
                }
                // Redirect to homepage or admin panel
                header('Location: ../index.php');
                exit;
            }
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BUragon | Bicol University Official Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #ff6b00;
            --light-bg: #f8fafc;
            --dark-text: #222;
            --light-text: #666;
            --border-color: #d1d5db;
            --danger-red: #dc3545;
            --success-green: #28a745;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e6f2ff 100%);
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
            margin: 0;
        }
        
        .login-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 20px 0;
        }
        
        .login-container {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.7s cubic-bezier(.39,.575,.56,1.000);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: auto; /* Ensures horizontal centering */
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Rest of your existing styles remain the same */
        .login-header {
            background: var(--primary);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .logo {
            height: 70px;
            margin-bottom: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .login-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .login-content {
            padding: 40px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.4s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-red);
            border-left: 4px solid var(--danger-red);
        }
        
        .alert-icon {
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--light-bg);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
            outline: none;
            background-color: #fff;
        }
        
        .input-group {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--light-text);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
        }
        
        .toggle-password:hover {
            color: var(--primary);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }
        
        .form-check-label {
            font-size: 0.95rem;
            color: var(--dark-text);
        }
        
        .forgot-password {
            font-size: 0.95rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .forgot-password:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .btn {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 24px;
            font-size: 1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            border: none;
            width: 100%;
        }
        
        .btn-primary {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 700;
            background-color: var(--secondary);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }
        
        .btn-primary:hover {
            background-color: #e05d00;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 0, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-spinner {
            display: none;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.95rem;
            color: var(--light-text);
        }
        
        .register-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
            transition: color 0.2s;
        }
        
        .register-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media (max-width: 600px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-content {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 25px 20px;
            }
            
            .logo {
                height: 60px;
            }
        }
        
        /* Input error state */
        .is-invalid {
            border-color: var(--danger-red) !important;
        }
        
        /* Decorations */
        .login-decoration {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 107, 0, 0.1);
            z-index: -1;
        }
        
        .decoration-1 {
            top: -50px;
            right: -50px;
        }
        
        .decoration-2 {
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <img src="../assets/images/logos/bu-logo.png" alt="Bicol University Logo" class="logo">
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Bicol University Official Store</p>
            </div>
            
            <div class="login-content">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle alert-icon"></i>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <p style="margin-bottom: 5px;"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="loginForm" novalidate>
                    <div class="form-group">
                        <label for="username_email" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username_email" name="username_email" 
                               value="<?php echo htmlspecialchars($_POST['username_email'] ?? ''); ?>" 
                               required aria-required="true">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required aria-required="true">
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                                   <?php if (!empty($_POST['remember'])) echo 'checked'; ?>>
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="loginBtn">
                        <span>Login</span>
                        <i class="fas fa-spinner btn-spinner"></i>
                    </button>
                </form>
                
                <div class="login-footer">
                    Don't have an account? 
                    <a href="register.php" class="register-link">Register here</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            this.setAttribute('aria-label', 'Hide password');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            this.setAttribute('aria-label', 'Show password');
        }
    });
    
    // Form submission handling
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        const spinner = btn.querySelector('.btn-spinner');
        const username = document.getElementById('username_email');
        const password = document.getElementById('password');
        let isValid = true;
        
        // Basic validation
        if (username.value.trim() === '') {
            username.classList.add('is-invalid');
            isValid = false;
        } else {
            username.classList.remove('is-invalid');
        }
        
        if (password.value.trim() === '') {
            password.classList.add('is-invalid');
            isValid = false;
        } else {
            password.classList.remove('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        btn.setAttribute('disabled', 'disabled');
        btn.querySelector('span').textContent = 'Logging in...';
        spinner.style.display = 'block';
    });
    
    // Accessibility: allow Enter to toggle password
    document.querySelector('.toggle-password').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
    </script>
</body>
</html>