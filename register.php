<?php
// register.php - User registration page with password show/hide toggle
require_once 'config.php'; // Assuming config.php contains session_start(), database connection etc.

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin_dashboard.php' : 'customer_dashboard.php');
}

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=php_hotel_ms", "root", "123456");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dateOfBirth = $_POST['dateOfBirth'];
    $address = trim($_POST['address']);
    $idCardNumber = trim($_POST['idCardNumber']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role'];

    // Validation
    if (empty($fullName) || empty($email) || empty($phone) || empty($dateOfBirth) || empty($address) || empty($idCardNumber) || empty($username) || empty($password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists) {
            $error = "Username already taken! Please choose another.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $emailExists = $stmt->fetchColumn();

            if ($emailExists) {
                $error = "Email already registered! Please use another email or login.";
            } else {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO user (fullName, email, phone, dateOfBirth, address, idCardNumber, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$fullName, $email, $phone, $dateOfBirth, $address, $idCardNumber, $username, $hashedPassword, $role])) {
                    $success = "Registration successful! You can now login.";
                    // Clear form data
                    $_POST = array();
                } else {
                    $error = "Registration failed! Please try again.";
                }
            }
        }
    }
}

// Dummy functions if config.php isn't provided
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() { return isset($_SESSION['user_id']); }
}
if (!function_exists('isAdmin')) {
    function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
}
if (!function_exists('redirect')) {
    function redirect($url) { header("Location: $url"); exit(); }
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
    <title>Register - Hotel Management System</title>
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
            --success-green: #10b981;
            --danger-red: #ef4444;
        }

        /* NO border-radius on any element — strict flat design */
        * {
            border-radius: 0 !important;
        }

        body {
            background-color: var(--light-bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
        }

        /* Primary background & buttons using exact brand blue (#0c3be4) */
        .primary-bg {
            background-color: var(--primary-blue);
        }

        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            transition: all 0.2s ease;
            font-weight: 500;
            letter-spacing: 0.3px;
            padding: 0.7rem 1rem;
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

        /* Card styling — flat, sharp, clean grid */
        .card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 1rem 1.8rem;
            border-bottom: none;
            font-weight: 600;
        }

        .card-header h4 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem 2rem;
        }

        /* Form controls — sharp corners, consistent border styling */
        .form-control, .form-select, textarea.form-control {
            border-radius: 0;
            border: 1px solid var(--border-light);
            padding: 0.6rem 0.9rem;
            background-color: #fff;
            transition: 0.15s ease;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus, textarea.form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(12, 59, 228, 0.2);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: var(--text-dark);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Password input group styling */
        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }

        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
            padding-right: 40px;
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

        /* Alert styling — sharp edges, clean feedback */
        .alert {
            border-radius: 0;
            border-left: 4px solid;
            padding: 0.85rem 1.2rem;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            background-color: #ffffff;
        }

        .alert-danger {
            border-left-color: var(--danger-red);
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            border-left-color: var(--success-green);
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #d1fae5;
        }

        /* Navbar specific styling – sharp edges, full width grid */
        .navbar {
            padding: 0.85rem 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: -0.2px;
        }

        /* Container grid: consistent with image layout */
        .container {
            max-width: 1280px;
        }

        /* Button styles */
        .btn {
            border-radius: 0;
            padding: 0.65rem 1rem;
            font-weight: 500;
        }

        .w-100 {
            width: 100%;
        }

        /* link primary color */
        .primary-color {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .primary-color:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* subtle placeholder style */
        ::placeholder {
            color: #a0aec0;
            font-size: 0.85rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            .navbar-brand {
                font-size: 1.2rem;
            }
            .form-label {
                font-size: 0.8rem;
            }
        }

        /* Ensure select dropdown has sharp edges */
        select.form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.9rem center;
            appearance: none;
        }

        /* textarea specific */
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
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
        <div class="col-md-9 col-lg-8">
            <div class="card">
                <div class="card-header primary-bg text-white">
                    <h4 class="mb-0">Create New Account</h4>
                </div>
                <div class="card-body">
                    <!-- Dynamic error and success alerts -->
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">FULL NAME <span class="text-danger">*</span></label>
                                <input type="text" name="fullName" class="form-control" placeholder="e.g., John M. Doe" value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>" required>
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">EMAIL ADDRESS <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="user@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PHONE NUMBER <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" placeholder="+1 234 567 8900" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                            </div>
                            
                            <!-- Date of Birth -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">DATE OF BIRTH <span class="text-danger">*</span></label>
                                <input type="date" name="dateOfBirth" class="form-control" value="<?php echo isset($_POST['dateOfBirth']) ? htmlspecialchars($_POST['dateOfBirth']) : ''; ?>" required>
                            </div>
                            
                            <!-- Address (full width) -->
                            <div class="col-12 mb-3">
                                <label class="form-label">ADDRESS <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Street, City, Postal Code, Country" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>
                            
                            <!-- ID Card Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID CARD NUMBER <span class="text-danger">*</span></label>
                                <input type="text" name="idCardNumber" class="form-control" placeholder="Passport / National ID" value="<?php echo isset($_POST['idCardNumber']) ? htmlspecialchars($_POST['idCardNumber']) : ''; ?>" required>
                            </div>
                            
                            <!-- Username -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">USERNAME <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" placeholder="unique username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                            
                            <!-- Password with toggle -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PASSWORD <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="minimum 6 characters" required>
                                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Show/Hide Password">
                                        <i class="fa-regular fa-eye-slash" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Confirm Password with toggle -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CONFIRM PASSWORD <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="re-enter password" required>
                                    <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Show/Hide Confirm Password">
                                        <i class="fa-regular fa-eye-slash" id="confirmPasswordIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Role Selection -->
                            <div class="col-md-12 mb-4">
                                <label class="form-label">REGISTER AS <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="customer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <small class="text-muted" style="font-size: 0.7rem; margin-top: 0.3rem; display: block;">Select "Admin" only if you have authorization.</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mt-2">REGISTER ACCOUNT</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="primary-color">Already have an account? Sign in →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password show/hide toggle functionality for both password fields
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle for Password field
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    passwordIcon.classList.remove('fa-eye');
                    passwordIcon.classList.add('fa-eye-slash');
                } else {
                    passwordIcon.classList.remove('fa-eye-slash');
                    passwordIcon.classList.add('fa-eye');
                }
            });
        }
        
        // Toggle for Confirm Password field
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');
        
        if (toggleConfirmPassword && confirmPasswordInput) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    confirmPasswordIcon.classList.remove('fa-eye');
                    confirmPasswordIcon.classList.add('fa-eye-slash');
                } else {
                    confirmPasswordIcon.classList.remove('fa-eye-slash');
                    confirmPasswordIcon.classList.add('fa-eye');
                }
            });
        }
    });
</script>
</body>
</html>