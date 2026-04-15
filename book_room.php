<?php
// book_room.php - Process booking
require_once 'config.php';

if (!isLoggedIn() || !isCustomer()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $roomId = $_POST['roomId'];
    $checkInDate = $_POST['checkInDate'];
    $checkOutDate = $_POST['checkOutDate'];
    $numberOfGuests = $_POST['numberOfGuests'];
    $specialRequests = $_POST['specialRequests'];

    // Validate dates
    if (strtotime($checkInDate) >= strtotime($checkOutDate)) {
        $_SESSION['error'] = "Check-out date must be after check-in date!";
        redirect('customer_dashboard.php');
    }

    if (strtotime($checkInDate) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = "Check-in date cannot be in the past!";
        redirect('customer_dashboard.php');
    }

    // Check if room is available
    $stmt = $pdo->prepare("SELECT * FROM room WHERE roomId = ? AND isAvailable = 1");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        $_SESSION['error'] = "Room is not available!";
        redirect('customer_dashboard.php');
    }

    if ($numberOfGuests > $room['capacity']) {
        $_SESSION['error'] = "Number of guests exceeds room capacity!";
        redirect('customer_dashboard.php');
    }

    // Check for overlapping bookings
    $stmt = $pdo->prepare("SELECT * FROM bookinginfo WHERE roomId = ? AND status != 'cancelled' AND ((checkInDate <= ? AND checkOutDate >= ?) OR (checkInDate <= ? AND checkOutDate >= ?) OR (checkInDate >= ? AND checkOutDate <= ?))");
    $stmt->execute([$roomId, $checkOutDate, $checkInDate, $checkInDate, $checkOutDate, $checkInDate, $checkOutDate]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Room is already booked for selected dates!";
        redirect('customer_dashboard.php');
    }

    // Calculate total price
    $days = (strtotime($checkOutDate) - strtotime($checkInDate)) / (60 * 60 * 24);
    $totalPrice = $room['pricePerNight'] * $days;

    // Create booking
    $stmt = $pdo->prepare("INSERT INTO bookinginfo (userId, roomId, checkInDate, checkOutDate, totalPrice, status, specialRequests, numberOfGuests) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)");
    
    if ($stmt->execute([$userId, $roomId, $checkInDate, $checkOutDate, $totalPrice, $specialRequests, $numberOfGuests])) {
        $_SESSION['success'] = "Booking request submitted successfully! Awaiting admin approval.";
    } else {
        $_SESSION['error'] = "Failed to create booking!";
    }
    
    redirect('customer_dashboard.php');
}
?>