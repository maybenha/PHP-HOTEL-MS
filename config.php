<?php
// config.php - Database configuration and connection
session_start();

$host = 'localhost';
$dbname = 'php_hotel_ms';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper function to check if user is customer
function isCustomer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to display alert messages
function displayAlert($message, $type = 'success') {
    $alertClass = $type === 'success' ? 'alert-success' : 'alert-danger';
    return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}
?>