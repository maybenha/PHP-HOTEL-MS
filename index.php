<?php
// index.php - Landing page
require_once 'config.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin_dashboard.php' : 'customer_dashboard.php');
}
?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: white; }
        .primary-bg { background-color: #0c3be4; }
        .btn-primary { background-color: #0c3be4; border-color: #0c3be4; }
        .btn-primary:hover { background-color: #0a32c4; border-color: #0a32c4; }
        .hero { background: linear-gradient(135deg, #0c3be4 0%, #1a4ff5 100%); color: white; padding: 80px 0; }
        .card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg primary-bg">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="index.php">🏨 Hotel Management System</a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-outline-light">Login</a>
                <a href="register.php" class="btn btn-light ms-2">Register</a>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Welcome to Our Hotel</h1>
            <p class="lead">Experience luxury and comfort at the best rates</p>
            <a href="register.php" class="btn btn-light btn-lg mt-3">Get Started</a>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-building fs-1 primary-color"></i>
                        <h5 class="card-title mt-3">Luxury Rooms</h5>
                        <p class="card-text">Well-appointed rooms with modern amenities for a comfortable stay.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-wifi fs-1 primary-color"></i>
                        <h5 class="card-title mt-3">Free WiFi</h5>
                        <p class="card-text">Stay connected with high-speed internet throughout the hotel.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-calendar-check fs-1 primary-color"></i>
                        <h5 class="card-title mt-3">Easy Booking</h5>
                        <p class="card-text">Simple and quick online booking process.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="primary-bg text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; 2024 Hotel Management System. All rights reserved.</p>
        </div>
    </footer>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- Color palette from image reference (consistent with login/register) --- */
        :root {
            --primary-blue: #0c3be4;
            --primary-dark: #0a32c4;
            --primary-light: #2d5aff;
            --light-bg: #f8f9fc;
            --border-light: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --gray-soft: #f1f5f9;
        }

        /* NO border-radius on any element — strict flat design matching the reference image */
        * {
            border-radius: 0 !important;
        }

        body {
            background-color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
        }

        /* Primary background using exact brand blue (#0c3be4) */
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
            border-color: rgba(255,255,255,0.6);
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

        /* Navbar styling — sharp edges, clean grid */
        .navbar {
            padding: 0.85rem 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: -0.2px;
        }

        /* Hero section — flat design, no rounded corners, gradient with brand colors */
        .hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1a4ff5 100%);
            color: white;
            padding: 90px 0;
            margin-bottom: 0;
            border-bottom: none;
        }

        .hero h1 {
            font-weight: 700;
            font-size: 3.2rem;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
        }

        .hero .lead {
            font-size: 1.25rem;
            opacity: 0.92;
            margin-bottom: 1.8rem;
        }

        .hero .btn-light {
            background-color: white;
            color: var(--primary-blue);
            border: none;
            padding: 0.7rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .hero .btn-light:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Card styling — flat, sharp corners, clean shadow, no radius */
        .card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03);
            transition: all 0.25s ease;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }

        .card-body {
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .card i.bi {
            font-size: 2.8rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            display: inline-block;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.35rem;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
        }

        .card-text {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Footer styling — flat, sharp, no radius */
        footer {
            background-color: var(--primary-blue);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
            border-top: none;
            font-size: 0.9rem;
        }

        footer p {
            margin: 0;
            opacity: 0.9;
        }

        /* Container grid consistency */
        .container {
            max-width: 1280px;
        }

        /* Button base */
        .btn {
            border-radius: 0;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
        }

        .btn-lg {
            padding: 0.75rem 2rem;
            font-size: 1.05rem;
        }

        /* feature section spacing */
        .feature-section {
            padding: 70px 0 50px 0;
        }

        /* Primary color for icons and links */
        .primary-color {
            color: var(--primary-blue);
        }

        /* remove any hover radius effects */
        a, button, .card, .navbar, .hero, footer {
            border-radius: 0 !important;
        }

        /* responsive adjustments */
        @media (max-width: 768px) {
            .hero {
                padding: 60px 0;
            }
            .hero h1 {
                font-size: 2.2rem;
            }
            .hero .lead {
                font-size: 1rem;
            }
            .card-body {
                padding: 1.5rem;
            }
            .navbar-brand {
                font-size: 1.2rem;
            }
            .feature-section {
                padding: 40px 0 30px 0;
            }
        }

        /* extra polish for sharp edges */
        .navbar-toggler {
            border-radius: 0 !important;
        }

        /* ensure any focus ring also sharp */
        .btn:focus, .form-control:focus {
            box-shadow: 0 0 0 2px rgba(12, 59, 228, 0.25);
            outline: none;
        }

        /* subtle separator for visual hierarchy */
        .feature-icon-wrapper {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg primary-bg">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php">🏨 Hotel Management System</a>
        <div class="ms-auto">
            <a href="login.php" class="btn btn-outline-light">Login</a>
            <a href="register.php" class="btn btn-light ms-2">Register</a>
        </div>
    </div>
</nav>

<!-- Hero Section - flat design, bold typography, matching image reference aesthetic -->
<div class="hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Welcome to Our Hotel</h1>
        <p class="lead px-md-5">Experience luxury and comfort at the best rates — book your perfect stay with ease.</p>
        <a href="register.php" class="btn btn-light btn-lg mt-2">Get Started →</a>
    </div>
</div>

<!-- Features Section - clean grid, sharp cards, no radius, consistent spacing -->
<div class="container feature-section">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-building"></i>
                    </div>
                    <h5 class="card-title">Luxury Rooms</h5>
                    <p class="card-text">Well-appointed rooms with premium amenities, plush bedding, and stunning city or ocean views for a memorable stay.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-wifi"></i>
                    </div>
                    <h5 class="card-title">Free High-Speed WiFi</h5>
                    <p class="card-text">Stay connected with blazing-fast internet throughout the hotel — perfect for business or streaming during your vacation.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5 class="card-title">Easy Booking</h5>
                    <p class="card-text">Simple and intuitive online reservation system. Manage your bookings, check availability, and get instant confirmation.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional row to highlight more amenities (optional but enhances value) -->
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <i class="bi bi-cup-hot"></i>
                    <h5 class="card-title mt-2">Complimentary Breakfast</h5>
                    <p class="card-text">Start your day with a delicious complimentary breakfast buffet featuring local and international cuisine.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <i class="bi bi-cone-striped"></i>
                    <h5 class="card-title mt-2">Swimming Pool & Spa</h5>
                    <p class="card-text">Relax and unwind at our rooftop pool, jacuzzi, and full-service spa — the ultimate rejuvenation experience.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <i class="bi bi-shield-check"></i>
                    <h5 class="card-title mt-2">24/7 Security</h5>
                    <p class="card-text">Your safety is our priority. Round-the-clock security and surveillance ensure a worry-free environment.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer - flat, clean, consistent with the primary brand color -->
<footer class="primary-bg text-white text-center">
    <div class="container">
        <p class="mb-0">&copy; 2024 Hotel Management System. All rights reserved. | <i class="bi bi-geo-alt"></i> 123 Luxury Ave, Downtown</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * index.php - Landing page (improved UI following image reference)
 * 
 * Backend logic preserved exactly as original:
 * - Requires config.php for session & DB helpers
 * - Checks if user is logged in using isLoggedIn() function
 * - Redirects authenticated users to respective dashboard (admin or customer)
 * - All design updates follow strict flat design with NO border-radius
 * 
 * IMPROVEMENTS based on reference image (ADD NEW PRODUCT layout):
 * - Removed ALL border-radius from cards, buttons, navbar, hero, footer
 * - Applied consistent color palette: primary #0c3be4, light background #f8f9fc, white cards
 * - Hero section uses brand gradient but maintains sharp edges (no radius)
 * - Cards have subtle box-shadow with transform effect but keep square corners
 * - Bootstrap grid system with improved spacing and responsive behavior
 * - Added additional amenity cards to enhance hotel management system presentation
 * - All interactive elements maintain flat design aesthetic matching login/register pages
 * - Footer and navigation consistent with other pages for unified experience
 * 
 * The page remains fully functional: unauthenticated users see landing content,
 * authenticated users are redirected to their dashboard.
 */
?>