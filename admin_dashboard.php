
<?php
// admin_dashboard.php - Admin dashboard (Full width, all booking details visible)
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

// Fetch admin profile
$stmt = $pdo->prepare("SELECT * FROM user WHERE userId = ?");
$stmt->execute([$userId]);
$admin = $stmt->fetch();

// Update profile
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
        $stmt = $pdo->prepare("SELECT * FROM user WHERE userId = ?");
        $stmt->execute([$userId]);
        $admin = $stmt->fetch();
    }
}

// Handle room operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        $roomImage = !empty($_POST['roomImage']) ? $_POST['roomImage'] : null;
        $stmt = $pdo->prepare("INSERT INTO room (roomNumber, roomType, pricePerNight, capacity, bedType, viewType, amenities, roomImage, isAvailable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['roomNumber'], $_POST['roomType'], $_POST['pricePerNight'], $_POST['capacity'], $_POST['bedType'], $_POST['viewType'], $_POST['amenities'], $roomImage, isset($_POST['isAvailable']) ? 1 : 0]);
        $_SESSION['success'] = "Room added successfully!";
    } elseif (isset($_POST['edit_room'])) {
        $roomImage = !empty($_POST['roomImage']) ? $_POST['roomImage'] : null;
        $stmt = $pdo->prepare("UPDATE room SET roomNumber = ?, roomType = ?, pricePerNight = ?, capacity = ?, bedType = ?, viewType = ?, amenities = ?, roomImage = ?, isAvailable = ? WHERE roomId = ?");
        $stmt->execute([$_POST['roomNumber'], $_POST['roomType'], $_POST['pricePerNight'], $_POST['capacity'], $_POST['bedType'], $_POST['viewType'], $_POST['amenities'], $roomImage, isset($_POST['isAvailable']) ? 1 : 0, $_POST['roomId']]);
        $_SESSION['success'] = "Room updated successfully!";
    } elseif (isset($_POST['delete_room'])) {
        $stmt = $pdo->prepare("DELETE FROM room WHERE roomId = ?");
        $stmt->execute([$_POST['roomId']]);
        $_SESSION['success'] = "Room deleted successfully!";
    } elseif (isset($_POST['update_booking'])) {
        $stmt = $pdo->prepare("UPDATE bookinginfo SET status = ? WHERE bookingId = ?");
        $stmt->execute([$_POST['status'], $_POST['bookingId']]);
        $_SESSION['success'] = "Booking status updated!";
    }
    redirect('admin_dashboard.php');
}

// Fetch all rooms
$rooms = $pdo->query("SELECT * FROM room ORDER BY roomNumber")->fetchAll();

// Fetch all bookings with COMPLETE user and room info (all fields)
$bookings = $pdo->query("SELECT 
                            b.*, 
                            u.fullName, 
                            u.email, 
                            u.phone,
                            u.address as customerAddress,
                            u.idCardNumber,
                            r.roomNumber, 
                            r.roomType,
                            r.pricePerNight,
                            r.capacity,
                            r.bedType,
                            r.viewType,
                            r.amenities
                         FROM bookinginfo b 
                         JOIN user u ON b.userId = u.userId 
                         JOIN room r ON b.roomId = r.roomId 
                         ORDER BY b.bookingDate DESC")->fetchAll();

// Fetch all customers
$customers = $pdo->query("SELECT * FROM user WHERE role = 'customer' ORDER BY fullName")->fetchAll();

// Get customer booking history
$customerBookings = [];
foreach($customers as $customer) {
    $stmt = $pdo->prepare("SELECT b.*, r.roomNumber, r.roomType FROM bookinginfo b JOIN room r ON b.roomId = r.roomId WHERE b.userId = ? ORDER BY b.bookingDate DESC");
    $stmt->execute([$customer['userId']]);
    $customerBookings[$customer['userId']] = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- Color palette consistent with image reference (flat design, sharp corners, FULL WIDTH) --- */
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

        /* NO border-radius on any element — strict flat design */
        * {
            border-radius: 0 !important;
        }

        body {
            background-color: var(--light-bg);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
            overflow-x: auto;
        }

        /* FULL WIDTH CONTAINER OVERRIDE */
        .container-fluid-custom {
            width: 100%;
            padding-right: 1.5rem;
            padding-left: 1.5rem;
            margin-right: auto;
            margin-left: auto;
        }

        /* Remove max-width from container to make it full width */
        .container {
            max-width: 100% !important;
            width: 100%;
            padding: 0 1.5rem;
        }

        .primary-bg { background-color: var(--primary-blue); }
        .primary-color { color: var(--primary-blue); }

        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            transition: all 0.2s ease;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-outline-light { border-color: rgba(255,255,255,0.6); color: white; }
        .btn-outline-light:hover { background-color: white; color: var(--primary-blue); border-color: white; }
        .btn-secondary { background-color: #64748b; border-color: #64748b; }
        .btn-secondary:hover { background-color: #475569; border-color: #475569; }
        .btn-warning { background-color: #f59e0b; border-color: #f59e0b; color: white; }
        .btn-warning:hover { background-color: #d97706; border-color: #d97706; }
        .btn-danger { background-color: #ef4444; border-color: #ef4444; }
        .btn-danger:hover { background-color: #dc2626; border-color: #dc2626; }
        .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }

        /* Navbar */
        .navbar { padding: 0.85rem 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 700; font-size: 1.4rem; letter-spacing: -0.2px; }

        /* Sidebar - Full width adjustment */
        .sidebar { background-color: white; border: 1px solid var(--border-light); padding: 1.5rem; margin-bottom: 1.5rem; }
        .nav-link { color: var(--text-dark); padding: 0.7rem 1rem; margin-bottom: 0.25rem; transition: all 0.2s; font-weight: 500; }
        .nav-link:hover { background-color: var(--gray-soft); color: var(--primary-blue); }
        .nav-link.active { background-color: var(--primary-blue); color: white; }
        .nav-link i { margin-right: 0.5rem; }

        /* Cards */
        .card { background-color: white; border: 1px solid var(--border-light); box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1.5rem; }
        .card-header { background-color: white; border-bottom: 2px solid var(--primary-blue); padding: 1rem 1.5rem; font-weight: 600; font-size: 1rem; color: var(--text-dark); }
        .card-header i { margin-right: 0.5rem; color: var(--primary-blue); }
        .card-body { padding: 1.5rem; }

        /* Form controls */
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
        .form-label { font-weight: 500; margin-bottom: 0.4rem; color: var(--text-dark); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px; }
        .form-check-input { border-radius: 0; border: 1px solid var(--border-light); }
        .form-check-input:checked { background-color: var(--primary-blue); border-color: var(--primary-blue); }

        /* Alerts */
        .alert { border-radius: 0; border-left: 4px solid; padding: 0.85rem 1.2rem; font-size: 0.9rem; margin-bottom: 1.25rem; }
        .alert-success { border-left-color: #10b981; background-color: #ecfdf5; color: #065f46; border: 1px solid #d1fae5; }
        .alert-danger { border-left-color: #ef4444; background-color: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }

        /* Tables - Full width with all columns visible */
        .table { border-collapse: collapse; width: 100%; min-width: 1400px; }
        .table-bordered { border: 1px solid var(--border-light); }
        .table-bordered th, .table-bordered td { border: 1px solid var(--border-light); padding: 0.75rem; vertical-align: middle; }
        .table thead th { background-color: var(--primary-blue); color: white; font-weight: 600; border-color: var(--primary-dark); white-space: nowrap; }
        .table tbody tr:hover { background-color: var(--gray-soft); }
        .table-responsive { overflow-x: auto; width: 100%; }

        /* Room Image Preview */
        .room-image-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 1px solid var(--border-light);
            background-color: var(--gray-soft);
        }
        .room-image-preview-lg {
            max-width: 100%;
            max-height: 150px;
            border: 1px solid var(--border-light);
        }
        .current-image-container {
            background-color: var(--gray-soft);
            padding: 0.5rem;
            text-align: center;
            border: 1px solid var(--border-light);
        }

        /* Status badges */
        .status-pending { background-color: var(--pending-bg); color: var(--pending-text); padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; display: inline-block; border: 1px solid #fde68a; }
        .status-confirmed { background-color: var(--confirmed-bg); color: var(--confirmed-text); padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; display: inline-block; border: 1px solid #a7f3d0; }
        .status-cancelled { background-color: var(--cancelled-bg); color: var(--cancelled-text); padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; display: inline-block; border: 1px solid #fecaca; }
        .badge.bg-success { background-color: #10b981 !important; }
        .badge.bg-danger { background-color: #ef4444 !important; }
        .badge.bg-primary { background-color: var(--primary-blue) !important; }

        /* Modal */
        .modal-content { border: 1px solid var(--border-light); }
        .modal-header { background-color: var(--primary-blue); color: white; border-bottom: none; padding: 1rem 1.5rem; }
        .modal-header .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
        .modal-footer { border-top: 1px solid var(--border-light); padding: 1rem 1.5rem; }

        /* Avatar */
        .avatar-circle { width: 70px; height: 70px; background-color: var(--gray-soft); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; }
        .avatar-circle i { font-size: 2.5rem; color: var(--primary-blue); }

        .btn { border-radius: 0; padding: 0.6rem 1.2rem; font-weight: 500; }
        .customer-card { border: 1px solid var(--border-light); background: white; margin-bottom: 1.5rem; padding: 1.25rem; }
        .customer-card:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

        @media (max-width: 768px) {
            .sidebar { margin-bottom: 1rem; }
            .card-header { padding: 0.75rem 1rem; }
            .card-body { padding: 1rem; }
            .navbar-brand { font-size: 1.1rem; }
            .table-responsive { font-size: 0.85rem; }
        }
        hr { border-color: var(--border-light); margin: 1rem 0; }
        .text-muted-small { font-size: 0.7rem; color: var(--text-muted); }
        .d-flex.gap-2 { gap: 0.5rem; }
        .form-select-sm { font-size: 0.8rem; padding: 0.25rem 0.5rem; }
        
        /* Full width adjustment for row */
        .row {
            margin-left: 0;
            margin-right: 0;
        }
        
        /* Fixed sidebar width, main content takes remaining full width */
        .col-md-3 {
            flex: 0 0 260px;
            max-width: 260px;
        }
        .col-md-9 {
            flex: 1;
            max-width: calc(100% - 260px);
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
        <a class="navbar-brand text-white fw-bold" href="admin_dashboard.php">BayonBooking - Admin</a>
        <div class="ms-auto">
            <span class="text-white me-3"><i class="bi bi-shield-lock"></i> <?php echo htmlspecialchars($_SESSION['fullName']); ?></span>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="sidebar">
                <div class="text-center mb-4">
                    <div class="avatar-circle"><i class="bi bi-shield-lock"></i></div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($admin['fullName']); ?></h5>
                    <p class="text-muted small mb-0">Administrator</p>
                    <p class="text-muted-small mt-1">System Manager</p>
                </div>
                <hr>
                <div class="nav flex-column">
                    <a href="#profile" class="nav-link active" data-bs-toggle="pill"><i class="bi bi-person"></i> My Profile</a>
                    <a href="#rooms" class="nav-link" data-bs-toggle="pill"><i class="bi bi-building"></i> Manage Rooms <span class="badge bg-primary float-end"><?php echo count($rooms); ?></span></a>
                    <a href="#bookings" class="nav-link" data-bs-toggle="pill"><i class="bi bi-calendar-check"></i> Manage Bookings <span class="badge bg-primary float-end"><?php echo count($bookings); ?></span></a>
                    <a href="#customers" class="nav-link" data-bs-toggle="pill"><i class="bi bi-people"></i> Customers <span class="badge bg-primary float-end"><?php echo count($customers); ?></span></a>
                </div>
            </div>
        </div>

        <!-- Main Content - FULL WIDTH -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-person"></i> Administrator Profile</div>
                        <div class="card-body">
                            <?php if(isset($profileSuccess)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($profileSuccess); ?></div>
                            <?php endif; ?>
                            <form method="POST" action="">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="fullName" class="form-control" value="<?php echo htmlspecialchars($admin['fullName']); ?>" required></div>
                                    <div class="col-md-6"><label class="form-label">Email Address *</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required></div>
                                    <div class="col-md-6"><label class="form-label">Phone Number *</label><input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>" required></div>
                                    <div class="col-md-6"><label class="form-label">Date of Birth *</label><input type="date" name="dateOfBirth" class="form-control" value="<?php echo $admin['dateOfBirth']; ?>" required></div>
                                    <div class="col-12"><label class="form-label">Address *</label><textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($admin['address']); ?></textarea></div>
                                    <div class="col-md-6"><label class="form-label">Username</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled></div>
                                    <div class="col-md-6"><label class="form-label">Role</label><input type="text" class="form-control" value="Administrator" disabled></div>
                                </div>
                                <div class="mt-4"><button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Rooms Management Tab with roomImage support -->
                <div class="tab-pane fade" id="rooms">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span><i class="bi bi-building"></i> Room Management</span>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal"><i class="bi bi-plus-circle"></i> Add New Room</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr><th>Image</th><th>Room #</th><th>Type</th><th>Price/Night</th><th>Capacity</th><th>Bed Type</th><th>View</th><th>Status</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($rooms as $room): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php if(!empty($room['roomImage']) && (file_exists($room['roomImage']) || filter_var($room['roomImage'], FILTER_VALIDATE_URL))): ?>
                                                    <img src="<?php echo htmlspecialchars($room['roomImage']); ?>" class="room-image-preview" alt="Room image" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2364748b\' stroke-width=\'1\'%3E%3Crect x=\'2\' y=\'2\' width=\'20\' height=\'20\'/%3E%3C/svg%3E'">
                                                <?php else: ?>
                                                    <div class="room-image-preview d-inline-flex align-items-center justify-content-center bg-light"><i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($room['roomNumber']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($room['roomType']); ?></td>
                                            <td>$<?php echo number_format($room['pricePerNight'], 2); ?></td>
                                            <td><?php echo $room['capacity']; ?></td>
                                            <td><?php echo htmlspecialchars($room['bedType']); ?></td>
                                            <td><?php echo htmlspecialchars($room['viewType']); ?></td>
                                            <td><span class="badge bg-<?php echo $room['isAvailable'] ? 'success' : 'danger'; ?>"><?php echo $room['isAvailable'] ? 'Available' : 'Booked'; ?></span></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editRoomModal<?php echo $room['roomId']; ?>"><i class="bi bi-pencil"></i> Edit</button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this room permanently?');"><input type="hidden" name="roomId" value="<?php echo $room['roomId']; ?>"><button type="submit" name="delete_room" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button></form>
                                            </td>
                                        </tr>

                                        <!-- Edit Room Modal with roomImage -->
                                        <div class="modal fade" id="editRoomModal<?php echo $room['roomId']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header primary-bg text-white"><h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Room #<?php echo htmlspecialchars($room['roomNumber']); ?></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="roomId" value="<?php echo $room['roomId']; ?>">
                                                            <div class="row g-3">
                                                                <div class="col-md-6"><label class="form-label">Room Number</label><input type="text" name="roomNumber" class="form-control" value="<?php echo htmlspecialchars($room['roomNumber']); ?>" required></div>
                                                                <div class="col-md-6"><label class="form-label">Room Type</label><select name="roomType" class="form-select" required><option value="Standard" <?php echo $room['roomType'] == 'Standard' ? 'selected' : ''; ?>>Standard</option><option value="Deluxe" <?php echo $room['roomType'] == 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option><option value="Suite" <?php echo $room['roomType'] == 'Suite' ? 'selected' : ''; ?>>Suite</option><option value="Presidential" <?php echo $room['roomType'] == 'Presidential' ? 'selected' : ''; ?>>Presidential</option></select></div>
                                                                <div class="col-md-6"><label class="form-label">Price per Night ($)</label><input type="number" step="0.01" name="pricePerNight" class="form-control" value="<?php echo $room['pricePerNight']; ?>" required></div>
                                                                <div class="col-md-6"><label class="form-label">Capacity (persons)</label><input type="number" name="capacity" class="form-control" value="<?php echo $room['capacity']; ?>" required></div>
                                                                <div class="col-md-6"><label class="form-label">Bed Type</label><select name="bedType" class="form-select" required><option value="Single" <?php echo $room['bedType'] == 'Single' ? 'selected' : ''; ?>>Single</option><option value="Double" <?php echo $room['bedType'] == 'Double' ? 'selected' : ''; ?>>Double</option><option value="Queen" <?php echo $room['bedType'] == 'Queen' ? 'selected' : ''; ?>>Queen</option><option value="King" <?php echo $room['bedType'] == 'King' ? 'selected' : ''; ?>>King</option></select></div>
                                                                <div class="col-md-6"><label class="form-label">View Type</label><select name="viewType" class="form-select" required><option value="City View" <?php echo $room['viewType'] == 'City View' ? 'selected' : ''; ?>>City View</option><option value="Ocean View" <?php echo $room['viewType'] == 'Ocean View' ? 'selected' : ''; ?>>Ocean View</option><option value="Garden View" <?php echo $room['viewType'] == 'Garden View' ? 'selected' : ''; ?>>Garden View</option><option value="Pool View" <?php echo $room['viewType'] == 'Pool View' ? 'selected' : ''; ?>>Pool View</option></select></div>
                                                                <div class="col-12"><label class="form-label">Amenities</label><textarea name="amenities" class="form-control" rows="2" placeholder="WiFi, TV, AC, Mini-bar, etc."><?php echo htmlspecialchars($room['amenities']); ?></textarea></div>
                                                                
                                                                <!-- Room Image Field -->
                                                                <div class="col-12">
                                                                    <label class="form-label">Room Image URL</label>
                                                                    <input type="text" name="roomImage" class="form-control" placeholder="https://example.com/room-image.jpg or /uploads/room.jpg" value="<?php echo htmlspecialchars($room['roomImage'] ?? ''); ?>">
                                                                    <small class="text-muted">Enter image URL (local path or external link). Leave empty to keep current or remove.</small>
                                                                </div>
                                                                <?php if(!empty($room['roomImage'])): ?>
                                                                <div class="col-12">
                                                                    <div class="current-image-container">
                                                                        <label class="form-label">Current Image Preview:</label><br>
                                                                        <?php if(file_exists($room['roomImage']) || filter_var($room['roomImage'], FILTER_VALIDATE_URL)): ?>
                                                                            <img src="<?php echo htmlspecialchars($room['roomImage']); ?>" class="room-image-preview-lg" alt="Current room image" onerror="this.onerror=null; this.outerHTML='<div class=\'text-muted\'><i class=\'bi bi-image\'></i> Image not found</div>'">
                                                                        <?php else: ?>
                                                                            <div class="text-muted"><i class="bi bi-image"></i> Image path saved but not accessible</div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <?php endif; ?>
                                                                
                                                                <div class="col-12"><div class="form-check"><input type="checkbox" name="isAvailable" class="form-check-input" id="availableCheck<?php echo $room['roomId']; ?>" <?php echo $room['isAvailable'] ? 'checked' : ''; ?>><label class="form-check-label" for="availableCheck<?php echo $room['roomId']; ?>">Room Available for Booking</label></div></div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="edit_room" class="btn btn-primary">Save Changes</button></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Room Modal with roomImage -->
                <div class="modal fade" id="addRoomModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header primary-bg text-white"><h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Room</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Room Number *</label><input type="text" name="roomNumber" class="form-control" placeholder="e.g., 101" required></div>
                                        <div class="col-md-6"><label class="form-label">Room Type *</label><select name="roomType" class="form-select" required><option value="Standard">Standard</option><option value="Deluxe">Deluxe</option><option value="Suite">Suite</option><option value="Presidential">Presidential</option></select></div>
                                        <div class="col-md-6"><label class="form-label">Price per Night ($) *</label><input type="number" step="0.01" name="pricePerNight" class="form-control" placeholder="0.00" required></div>
                                        <div class="col-md-6"><label class="form-label">Capacity (persons) *</label><input type="number" name="capacity" class="form-control" placeholder="2" required></div>
                                        <div class="col-md-6"><label class="form-label">Bed Type *</label><select name="bedType" class="form-select" required><option value="Single">Single</option><option value="Double">Double</option><option value="Queen">Queen</option><option value="King">King</option></select></div>
                                        <div class="col-md-6"><label class="form-label">View Type *</label><select name="viewType" class="form-select" required><option value="City View">City View</option><option value="Ocean View">Ocean View</option><option value="Garden View">Garden View</option><option value="Pool View">Pool View</option></select></div>
                                        <div class="col-12"><label class="form-label">Amenities</label><textarea name="amenities" class="form-control" rows="2" placeholder="WiFi, TV, Air Conditioning, Mini-bar, Safe, etc."></textarea></div>
                                        <div class="col-12"><label class="form-label">Room Image URL</label><input type="text" name="roomImage" class="form-control" placeholder="https://example.com/room-photo.jpg or /uploads/room-image.jpg"><small class="text-muted">Enter image URL (local path or external link). Optional.</small></div>
                                        <div class="col-12"><div class="form-check"><input type="checkbox" name="isAvailable" class="form-check-input" id="availableCheck" checked><label class="form-check-label" for="availableCheck">Room Available for Booking</label></div></div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_room" class="btn btn-primary">Add Room</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Bookings Management Tab - FULL DETAILS TABLE -->
                <div class="tab-pane fade" id="bookings">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-calendar-check"></i> Manage Customer Bookings - Complete Details</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" style="min-width: 1800px;">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>ID Card</th>
                                            <th>Customer Address</th>
                                            <th>Room #</th>
                                            <th>Room Type</th>
                                            <th>Price/Night</th>
                                            <th>Bed Type</th>
                                            <th>View</th>
                                            <th>Amenities</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Nights</th>
                                            <th>Guests</th>
                                            <th>Total Price</th>
                                            <th>Special Requests</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($bookings as $booking): 
                                            $checkIn = new DateTime($booking['checkInDate']);
                                            $checkOut = new DateTime($booking['checkOutDate']);
                                            $nights = $checkIn->diff($checkOut)->days;
                                        ?>
                                        <tr>
                                            <td><strong>#<?php echo $booking['bookingId']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($booking['fullName']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['idCardNumber'] ?? 'N/A'); ?></td>
                                            <td><small><?php echo htmlspecialchars(substr($booking['customerAddress'] ?? '', 0, 50)); ?></small></td>
                                            <td><?php echo htmlspecialchars($booking['roomNumber']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['roomType']); ?></td>
                                            <td>$<?php echo number_format($booking['pricePerNight'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($booking['bedType']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['viewType']); ?></td>
                                            <td><small><?php echo htmlspecialchars(substr($booking['amenities'] ?? '', 0, 40)); ?></small></td>
                                            <td><?php echo date('d M Y', strtotime($booking['checkInDate'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($booking['checkOutDate'])); ?></td>
                                            <td class="text-center"><?php echo $nights; ?></td>
                                            <td class="text-center"><?php echo $booking['numberOfGuests']; ?></td>
                                            <td><strong>$<?php echo number_format($booking['totalPrice'], 2); ?></strong></td>
                                            <td><small><?php echo htmlspecialchars(substr($booking['specialRequests'] ?? '', 0, 50)); ?></small></td>
                                            <td><small><?php echo date('d M Y', strtotime($booking['bookingDate'])); ?></small></td>
                                            <td><span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                            <td>
                                                <form method="POST" class="d-flex gap-2 align-items-center flex-nowrap">
                                                    <input type="hidden" name="bookingId" value="<?php echo $booking['bookingId']; ?>">
                                                    <select name="status" class="form-select form-select-sm" style="width: 110px;">
                                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_booking" class="btn btn-primary btn-sm">Update</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if(count($bookings) == 0): ?>
                                        <tr>
                                            <td colspan="21" class="text-center text-muted">No bookings found.</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customers Tab -->
                <div class="tab-pane fade" id="customers">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-people"></i> All Customers & Booking History</div>
                        <div class="card-body">
                            <?php foreach($customers as $customer): ?>
                                <div class="customer-card">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                        <div>
                                            <h5 class="mb-1"><i class="bi bi-person-circle primary-color"></i> <?php echo htmlspecialchars($customer['fullName']); ?></h5>
                                            <small class="text-muted"><i class="bi bi-envelope"></i> <?php echo $customer['email']; ?></small><br>
                                            <small class="text-muted"><i class="bi bi-telephone"></i> <?php echo $customer['phone']; ?></small><br>
                                            <small class="text-muted"><i class="bi bi-card-text"></i> ID Card: <?php echo $customer['idCardNumber']; ?></small><br>
                                            <small class="text-muted"><i class="bi bi-geo-alt"></i> Address: <?php echo htmlspecialchars($customer['address']); ?></small>
                                        </div>
                                        <span class="badge bg-primary">Customer</span>
                                    </div>
                                    <?php if(count($customerBookings[$customer['userId']]) > 0): ?>
                                        <h6 class="mt-3 mb-2"><i class="bi bi-calendar-check"></i> Booking History:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Booking ID</th>
                                                        <th>Room</th>
                                                        <th>Check-in</th>
                                                        <th>Check-out</th>
                                                        <th>Guests</th>
                                                        <th>Total</th>
                                                        <th>Status</th>
                                                        <th>Special Requests</th>
                                                        <th>Booked On</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($customerBookings[$customer['userId']] as $booking): ?>
                                                    <tr>
                                                        <td>#<?php echo $booking['bookingId']; ?></td>
                                                        <td><?php echo htmlspecialchars($booking['roomNumber']); ?><br><small class="text-muted"><?php echo $booking['roomType']; ?></small></td>
                                                        <td><?php echo date('d M Y', strtotime($booking['checkInDate'])); ?></td>
                                                        <td><?php echo date('d M Y', strtotime($booking['checkOutDate'])); ?></td>
                                                        <td><?php echo $booking['numberOfGuests']; ?></td>
                                                        <td>$<?php echo number_format($booking['totalPrice'], 2); ?></td>
                                                        <td><span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                                        <td><small><?php echo htmlspecialchars(substr($booking['specialRequests'] ?? '', 0, 50)); ?></small></td>
                                                        <td><?php echo date('d M Y', strtotime($booking['bookingDate'])); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mt-2"><i class="bi bi-info-circle"></i> No booking history for this customer.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
