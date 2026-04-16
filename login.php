<?php
// login.php - User login page with password show/hide toggle
require_once 'config.php'; // Assuming config.php contains session_start(), isLoggedIn(), redirect(), displayAlert() etc.

$error = '';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin_dashboard.php' : 'customer_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Placeholder for database connection, replace with your actual $pdo or database access
    // For demonstration, let's assume $pdo is available from config.php or defined here.
    // Example: $pdo = new PDO("mysql:host=localhost;dbname=your_db", "user", "pass");

    // --- IMPORTANT: Replace this with your actual database connection and query ---
    // This is a minimal example; in a real app, you'd ensure $pdo is properly initialized.
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=php_hotel_ms", "root", "123456"); // Adjust DSN, username, password
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
    // --- End of placeholder ---


    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['userId'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['fullName'] = $user['fullName'];
        
        if ($user['role'] === 'admin') {
            redirect('admin_dashboard.php');
        } else {
            redirect('customer_dashboard.php');
        }
    } else {
        $error = "Invalid username or password!";
    }
}

// Dummy functions if config.php isn't provided for testing this snippet
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() { return isset($_SESSION['user_id']); }
}
if (!function_exists('isAdmin')) {
    function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
}
if (!function_exists('redirect')) {
    function redirect($url) { header("Location: $url"); exit(); }
}
if (!function_exists('displayAlert')) {
    function displayAlert($message, $type) {
        return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . $message . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Color palette from image reference --- */
        :root {
            --primary-blue: #0c3be4;
            --primary-dark: #0a32c4;
            --light-bg: #f8f9fc;
            --border-light: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        * {
            border-radius: 0 !important; /* No radius for any shape — strict from requirement */
        }

        body {
            background-color: var(--light-bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
        }

        /* Primary background & buttons using the exact brand blue (#0c3be4) */
        .primary-bg {
            background-color: var(--primary-blue);
        }

        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            transition: all 0.2s ease;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline-light {
            border-color: rgba(255,255,255,0.5);
            color: white;
        }

        .btn-outline-light:hover {
            background-color: white;
            color: var(--primary-blue);
            border-color: white;
        }

        .btn-light {
            background-color: white;
            color: var(--primary-blue);
            border: 1px solid white;
        }

        .btn-light:hover {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
            color: var(--primary-dark);
        }

        /* Card styling — flat design, sharp corners, clean grid */
        .card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: none;
            font-weight: 600;
            border: 0;
        }

        .card-header h4 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem 1.8rem;
        }

        /* form controls — sharp, no radius, consistent border */
        .form-control {
            border-radius: 0;
            border: 1px solid var(--border-light);
            padding: 0.6rem 0.9rem;
            background-color: #fff;
            transition: 0.15s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(12, 59, 228, 0.2);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        /* Password input group styling */
        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
        }

        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }

        .password-toggle {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            z-index: 10;
            background: transparent;
            border: none;
            padding: 0 12px;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--primary-blue);
        }

        .password-toggle:focus {
            outline: none;
        }

        /* Adjust form-control padding when toggle is present */
        .input-group .form-control {
            padding-right: 40px;
        }

        /* alert styling — sharp edges, clean */
        .alert {
            border-radius: 0;
            border-left: 4px solid;
            padding: 0.8rem 1rem;
            font-size: 0.9rem;
        }

        .alert-danger {
            background-color: #fff5f5;
            border-color: #feb2b2;
            color: #c53030;
            border-left-color: #e53e3e;
        }

        /* primary color for text links */
        .primary-color {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .primary-color:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Navbar specific styling – sharp, no radius, full grid alignment */
        .navbar {
            padding: 0.85rem 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: -0.2px;
        }

        /* Container grid: consistent with image style, responsive */
        .container {
            max-width: 1280px;
        }

        /* simple utilities */
        .w-100 {
            width: 100%;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .ms-auto {
            margin-left: auto;
        }

        .ms-2 {
            margin-left: 0.5rem;
        }

        /* button sharp and clean */
        .btn {
            border-radius: 0;
            padding: 0.6rem 1rem;
            font-weight: 500;
        }

        /* For better mobile responsiveness */
        @media (max-width: 576px) {
            .card-body {
                padding: 1.5rem;
            }
            .navbar-brand {
                font-size: 1.2rem;
            }
        }

        /* Ensuring all elements have border-radius removed completely */
        button, input, select, textarea, .card, .card-header, .card-footer, .alert, .btn, .navbar, .dropdown-menu, .modal-content {
            border-radius: 0 !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg primary-bg">
    <div class="container">
      <a href="index.php" class="navbar-brand">
        <img src="bayon_logo.png" alt="Hotel Logo" 
        style="width: 120px; height: auto; display: block; margin: 0 auto;">
    </a>
        <a class="navbar-brand text-white fw-bold" href="index.php">BayonBooking</a>
        <div class="ms-auto">
             <a href="index.php" class="btn btn-outline-light">Back to Dashboard</a>
            <a href="login.php" class="btn btn-outline-light">Login</a>
            <a href="register.php" class="btn btn-light ms-2">Register</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header primary-bg text-white">
                    <h4 class="mb-0">Login to Your Account</h4>
                </div>
                <div class="card-body">
                    <!-- Error alert placeholder using same displayAlert structure -->
                    <!-- Inline dynamic error handling matching the original PHP logic -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                                <button type="button" class="password-toggle" id="togglePassword" aria-label="Show/Hide Password">
                                    <i class="fa-regular fa-eye-slash" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="register.php" class="primary-color">Don't have an account? Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password show/hide toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle the eye icon (open/closed)
                if (type === 'password') {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }
    });
</script>
</body>
</html>