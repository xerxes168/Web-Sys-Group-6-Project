<?php
session_start();
$min = 10000;
$max = 50000;
$randomCents = rand($min, $max);
$total_amount = $randomCents / 100;
$booking_id = rand(1000, 9999);
$_SESSION['bookings'][$booking_id] = [
    'total_amount' => $total_amount,
    'status'       => 'pending'
];
header("Location: payment.php?booking_id=" . $booking_id);
exit;
?>
