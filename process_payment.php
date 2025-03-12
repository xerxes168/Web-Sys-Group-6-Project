<?php
session_start();

$booking_id = $_POST['booking_id'] ?? null;
if (!$booking_id || !isset($_SESSION['bookings'][$booking_id])) {
    die("Booking not found.");
}

// Simulate a payment process
$paymentSuccessful = (mt_rand(0, 1) === 1);

if ($paymentSuccessful) {
    // Update booking status to 'paid'
    $_SESSION['bookings'][$booking_id]['status'] = 'paid';
    echo "Payment processed and booking updated successfully.";
} else {
    echo "Payment failed. Please check your payment details and try again.";
}
?>
