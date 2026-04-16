<?php
// customer_dashboard.php - Customer dashboard
require_once 'config.php';

if (!isLoggedIn() || !isCustomer()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

// Fetch user profile
$stmt = $pdo->prepare("SELECT * FROM user WHERE userId = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch user bookings
$stmt = $pdo->prepare("SELECT b.*, r.roomNumber, r.roomType, r.pricePerNight 
                       FROM bookinginfo b 
                       JOIN room r ON b.roomId = r.roomId 
                       WHERE b.userId = ? 
                       ORDER BY b.bookingDate DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

// Update profile
$profileSuccess = '';
$profileError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $dateOfBirth = $_POST['dateOfBirth'];
    
    $stmt = $pdo->prepare("UPDATE user SET fullName = ?, email = ?, phone = ?, address = ?, dateOfBirth = ? WHERE userId = ?");
    if ($stmt->execute([$fullName, $email, $phone, $address, $dateOfBirth, $userId])) {
        $_SESSION['fullName'] = $fullName;
        $profileSuccess = "Profile updated successfully!";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM user WHERE userId = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    } else {
        $profileError = "Failed to update profile!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- Color palette consistent with image reference (flat design, sharp corners) --- */
        :root {
            --primary-blue: #0c3be4;
            --primary-dark: #0a32c4;
            --primary-light: #2d5aff;
            --light-bg: #f8f9fc;
            --border-light: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --gray-soft: #f1f5f9;
            --pending-bg: #fef3c7;
            --pending-text: #b45309;
            --confirmed-bg: #d1fae5;
            --confirmed-text: #065f46;
            --cancelled-bg: #fee2e2;
            --cancelled-text: #991b1b;
        }

        /* NO border-radius on any element — strict flat design matching reference */
        * {
            border-radius: 0 !important;
        }

        body {
            background-color: var(--light-bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
        }

        /* Primary background using exact brand blue */
        .primary-bg {
            background-color: var(--primary-blue);
        }

        .primary-color {
            color: var(--primary-blue);
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

        .btn-secondary {
            background-color: #64748b;
            border-color: #64748b;
        }

        .btn-secondary:hover {
            background-color: #475569;
            border-color: #475569;
        }

        /* Navbar styling — sharp edges */
        .navbar {
            padding: 0.85rem 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: -0.2px;
        }

        /* Sidebar styling — flat, sharp corners, clean borders */
        .sidebar {
            background-color: white;
            border: 1px solid var(--border-light);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .nav-link {
            color: var(--text-dark);
            padding: 0.7rem 1rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: var(--gray-soft);
            color: var(--primary-blue);
        }

        .nav-link.active {
            background-color: var(--primary-blue);
            color: white;
        }

        .nav-link i {
            margin-right: 0.5rem;
        }

        /* Card styling — flat, sharp corners, subtle border */
        .card {
            background-color: white;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 2px solid var(--primary-blue);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-dark);
        }

        .card-header i {
            margin-right: 0.5rem;
            color: var(--primary-blue);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form controls — sharp edges */
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

        /* Alert styling — sharp edges */
        .alert {
            border-radius: 0;
            border-left: 4px solid;
            padding: 0.85rem 1.2rem;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
        }

        .alert-success {
            border-left-color: #10b981;
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #d1fae5;
        }

        .alert-danger {
            border-left-color: #ef4444;
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .alert-info {
            border-left-color: var(--primary-blue);
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        /* Status badges — flat, no radius */
        .status-pending {
            background-color: var(--pending-bg);
            color: var(--pending-text);
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            border: 1px solid #fde68a;
        }

        .status-confirmed {
            background-color: var(--confirmed-bg);
            color: var(--confirmed-text);
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            border: 1px solid #a7f3d0;
        }

        .status-cancelled {
            background-color: var(--cancelled-bg);
            color: var(--cancelled-text);
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            border: 1px solid #fecaca;
        }

        /* Room card styling with image support */
        .room-card {
            border: 1px solid var(--border-light);
            background: white;
            height: 100%;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
        }

        .room-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }

        .room-image-container {
            width: 100%;
            height: 180px;
            background-color: var(--gray-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 1rem;
            border: 1px solid var(--border-light);
        }

        .room-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .room-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            height: 100%;
        }

        .room-image-placeholder i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }

        .room-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 0.5rem 0;
        }

        .room-price small {
            font-size: 0.8rem;
            font-weight: normal;
            color: var(--text-muted);
        }

        /* Booking item styling */
        .booking-item {
            border: 1px solid var(--border-light);
            background: white;
            padding: 1rem;
            transition: all 0.2s;
        }

        .booking-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        /* Modal styling — sharp corners */
        .modal-content {
            border: 1px solid var(--border-light);
            border-radius: 0 !important;
        }

        .modal-header {
            background-color: var(--primary-blue);
            color: white;
            border-bottom: none;
            padding: 1rem 1.5rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .modal-footer {
            border-top: 1px solid var(--border-light);
            padding: 1rem 1.5rem;
        }

        /* Disabled input styling */
        input:disabled, input[disabled] {
            background-color: var(--gray-soft);
            color: var(--text-muted);
        }

        /* Profile avatar circle - but keeping sharp edges for surrounding elements */
        .avatar-circle {
            width: 70px;
            height: 70px;
            background-color: var(--gray-soft);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }

        .avatar-circle i {
            font-size: 2.5rem;
            color: var(--primary-blue);
        }

        /* Button base */
        .btn {
            border-radius: 0;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
        }

        /* Badge styling */
        .badge.bg-primary {
            background-color: var(--primary-blue) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                margin-bottom: 1rem;
            }
            .card-header {
                padding: 0.75rem 1rem;
            }
            .card-body {
                padding: 1rem;
            }
            .navbar-brand {
                font-size: 1.1rem;
            }
            .room-image-container {
                height: 140px;
            }
        }

        /* Separator */
        hr {
            border-color: var(--border-light);
            margin: 1rem 0;
        }

        /* Text utilities */
        .text-muted-small {
            font-size: 0.7rem;
            color: var(--text-muted);
        }
        
        /* Room amenities badge styling */
        .amenity-badge {
            background-color: var(--gray-soft);
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
            display: inline-block;
            border: 1px solid var(--border-light);
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
        <a class="navbar-brand text-white fw-bold" href="customer_dashboard.php">BayonBooking</a>
        <div class="ms-auto">
            <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['fullName']); ?></span>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar - sharp, flat design -->
        <div class="col-md-3">
            <div class="sidebar">
                <div class="text-center mb-4">
                    <div class="avatar-circle">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($user['fullName']); ?></h5>
                    <p class="text-muted small mb-0"><?php echo ucfirst($user['role']); ?></p>
                    <p class="text-muted-small mt-1">Member since <?php echo date('M Y', strtotime($user['createdAt'] ?? 'now')); ?></p>
                </div>
                <hr>
                <div class="nav flex-column">
                    <a href="#rooms" class="nav-link" data-bs-toggle="pill">
                        <i class="bi bi-building"></i> Browse Rooms
                    </a>
                    <a href="#profile" class="nav-link active" data-bs-toggle="pill">
                        <i class="bi bi-person"></i> My Profile
                    </a>
                    <a href="#bookings" class="nav-link" data-bs-toggle="pill">
                        <i class="bi bi-calendar-check"></i> My Bookings
                        <?php if(count($bookings) > 0): ?>
                            <span class="badge bg-primary float-end" style="border-radius: 0 !important;"><?php echo count($bookings); ?></span>
                        <?php endif; ?>
                    </a>
                    
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9">
            <div class="tab-content">
                <div class="tab-pane fade" id="rooms">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-building"></i> Available Rooms
                        </div>
                        <div class="card-body">
                            <?php
                            // Updated query to include roomImage field
                            $stmt = $pdo->prepare("SELECT * FROM room WHERE isAvailable = 1 ORDER BY pricePerNight ASC");
                            $stmt->execute();
                            $rooms = $stmt->fetchAll();
                            ?>
                            <?php if(count($rooms) == 0): ?>
                                <div class="alert alert-info">No rooms available at the moment. Please check back later.</div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach($rooms as $room): ?>
                                        <div class="col-md-6 col-lg-6">
                                            <div class="room-card p-0">
                                                <!-- Room Image Section - displays roomImage from database -->
                                                <div class="room-image-container">
                                                    <?php if(!empty($room['roomImage']) && file_exists($room['roomImage'])): ?>
                                                        <img src="<?php echo htmlspecialchars($room['roomImage']); ?>" alt="Room <?php echo htmlspecialchars($room['roomNumber']); ?>" class="room-image">
                                                    <?php elseif(!empty($room['roomImage']) && filter_var($room['roomImage'], FILTER_VALIDATE_URL)): ?>
                                                        <img src="<?php echo htmlspecialchars($room['roomImage']); ?>" alt="Room <?php echo htmlspecialchars($room['roomNumber']); ?>" class="room-image" onerror="this.onerror=null; this.parentElement.innerHTML=this.parentElement.innerHTML; this.remove();">
                                                    <?php else: ?>
                                                        <div class="room-image-placeholder">
                                                            <i class="bi bi-image"></i>
                                                            <span class="small">No Image</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Room Details -->
                                                <div class="p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h5 class="mb-0">🚪 Room #<?php echo htmlspecialchars($room['roomNumber']); ?></h5>
                                                            <span class="amenity-badge mt-1 d-inline-block"><?php echo htmlspecialchars($room['roomType']); ?></span>
                                                        </div>
                                                        <div class="room-price text-end">
                                                            $<?php echo number_format($room['pricePerNight'], 2); ?><small>/night</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr class="my-2">
                                                    
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-6">
                                                            <small><i class="bi bi-people"></i> Capacity: <?php echo $room['capacity']; ?></small>
                                                        </div>
                                                        <div class="col-6">
                                                            <small><i class="bi bi-bed"></i> Bed: <?php echo htmlspecialchars($room['bedType']); ?></small>
                                                        </div>
                                                        <div class="col-6">
                                                            <small><i class="bi bi-eye"></i> View: <?php echo htmlspecialchars($room['viewType']); ?></small>
                                                        </div>
                                                        <div class="col-6">
                                                            <small><i class="bi bi-wifi"></i> Free WiFi</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <small class="text-muted"><i class="bi bi-grid"></i> Amenities:</small>
                                                        <small class="d-block text-muted"><?php echo htmlspecialchars($room['amenities']); ?></small>
                                                    </div>
                                                    
                                                    <button class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#bookModal<?php echo $room['roomId']; ?>">
                                                        <i class="bi bi-calendar-plus"></i> Book Now
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Booking Modal - sharp corners, flat design -->
                                        <div class="modal fade" id="bookModal<?php echo $room['roomId']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="book_room.php">
                                                        <div class="modal-header primary-bg text-white">
                                                            <h5 class="modal-title"><i class="bi bi-door-closed"></i> Book Room #<?php echo htmlspecialchars($room['roomNumber']); ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="roomId" value="<?php echo $room['roomId']; ?>">
                                                            
                                                            <!-- Room image preview in modal -->
                                                            <?php if(!empty($room['roomImage']) && (file_exists($room['roomImage']) || filter_var($room['roomImage'], FILTER_VALIDATE_URL))): ?>
                                                                <div class="mb-3 text-center">
                                                                    <img src="<?php echo htmlspecialchars($room['roomImage']); ?>" alt="Room preview" style="max-width: 100%; max-height: 150px; border: 1px solid var(--border-light);">
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Check-in Date *</label>
                                                                <input type="date" name="checkInDate" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Check-out Date *</label>
                                                                <input type="date" name="checkOutDate" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Number of Guests *</label>
                                                                <input type="number" name="numberOfGuests" class="form-control" min="1" max="<?php echo $room['capacity']; ?>" value="1" required>
                                                                <small class="text-muted">Maximum capacity: <?php echo $room['capacity']; ?> guests</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Special Requests (optional)</label>
                                                                <textarea name="specialRequests" class="form-control" rows="2" placeholder="Any special requests? (e.g., extra pillow, late check-in)"></textarea>
                                                            </div>
                                                            <div class="alert alert-info mb-0">
                                                                <i class="bi bi-info-circle"></i> <strong>Price per night:</strong> $<?php echo number_format($room['pricePerNight'], 2); ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-person"></i> Personal Information
                        </div>
                        <div class="card-body">
                            <?php if(!empty($profileSuccess)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($profileSuccess); ?></div>
                            <?php endif; ?>
                            <?php if(!empty($profileError)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($profileError); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" name="fullName" class="form-control" value="<?php echo htmlspecialchars($user['fullName']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number *</label>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of Birth *</label>
                                        <input type="date" name="dateOfBirth" class="form-control" value="<?php echo $user['dateOfBirth']; ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address *</label>
                                        <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">ID Card Number</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['idCardNumber']); ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div class="tab-pane fade" id="bookings">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-calendar-check"></i> My Booking History
                        </div>
                        <div class="card-body">
                            <?php if(count($bookings) == 0): ?>
                                <div class="alert alert-info">You haven't made any bookings yet. <a href="#rooms" class="alert-link" data-bs-toggle="pill">Browse available rooms →</a></div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach($bookings as $booking): ?>
                                        <div class="col-12">
                                            <div class="booking-item">
                                                <div class="row align-items-start">
                                                    <div class="col-md-8">
                                                        <h6 class="mb-1"><i class="bi bi-door-closed"></i> Room #<?php echo htmlspecialchars($booking['roomNumber']); ?> — <?php echo htmlspecialchars($booking['roomType']); ?></h6>
                                                        <small class="text-muted">Booking ID: <?php echo $booking['bookingId']; ?></small><br>
                                                        <small><i class="bi bi-calendar"></i> Check-in: <?php echo date('d M Y', strtotime($booking['checkInDate'])); ?></small><br>
                                                        <small><i class="bi bi-calendar-x"></i> Check-out: <?php echo date('d M Y', strtotime($booking['checkOutDate'])); ?></small><br>
                                                        <small><i class="bi bi-people"></i> Guests: <?php echo $booking['numberOfGuests']; ?></small><br>
                                                        <strong class="primary-color">Total: $<?php echo number_format($booking['totalPrice'], 2); ?></strong>
                                                    </div>
                                                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                                        <span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                                        <br>
                                                        <small class="text-muted"><i class="bi bi-clock"></i> Booked: <?php echo date('d M Y', strtotime($booking['bookingDate'])); ?></small>
                                                    </div>
                                                    <?php if($booking['specialRequests']): ?>
                                                        <div class="col-12 mt-2">
                                                            <hr class="my-2">
                                                            <small><strong><i class="bi bi-chat"></i> Special Requests:</strong> <?php echo htmlspecialchars($booking['specialRequests']); ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Rooms Tab with roomImage support -->
                
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
