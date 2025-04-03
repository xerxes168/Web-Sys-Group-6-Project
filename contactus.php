<?php
// Start the session at the very beginning of the file
session_start();

// Now you can access session variables anywhere in this file
if (isset($_SESSION['email']) || isset($_SESSION['member_id'])) {
    // User is logged in
    $loggedIn = true;
    $userEmail = $_SESSION['email'] ?? 'Unknown';
    $userId = $_SESSION['member_id'] ?? 'Unknown';
} else {
    // User is not logged in
    $loggedIn = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "inc/head.inc.php"; ?>
    <title>Contact Us - HoopSpaces</title>
    <link rel="stylesheet" href="css/contactus.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php 
        include "inc/nav.inc.php"; 
    ?> 
<main>
    <section class="contact-us" id="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="section-title">Contact Us</h1>
                    <div class="contact-info">
                        <h3>HoopSpaces</h3>
                        <h4>Address</h4>
                        <p>Sunshine Tower<br>
                        8 Raffles Avenue, Singapore 039802</p>

                        <h4>Main Contact</h4>
                        <p>For Venue Enquiries: +65 8473 4567</p>
                        <p>For Corporate Bookings: +65 9482 5432</p>
                        <p>For Weekend Bookings: +65 9083 4567</p>
                        
                        <h4>Connect with Us</h4>
                        <div class="social-links">
                            <a href="#" class="social-icon" target="_blank" aria-label="Facebook">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" class="social-icon" target="_blank" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" class="social-icon" target="_blank" aria-label="TikTok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            <a href="#" class="social-icon" target="_blank" aria-label="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="social-icon" target="_blank" aria-label="Telegram">
                                <i class="fab fa-telegram"></i>
                            </a>
                        </div>

                        
                        <h3> Walk In </h3>
                        <h4>Operating Hours:</h4>
                        <p>Mon - Fri: 9AM - 6PM (By Appointment Only)</p>
                        <p> Break Hours: 12PM - 1PM </p>
                        <p>Sat - Sun: 10AM - 5PM (By Appointment Only)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'inc/footer.inc.php'; ?>

<script src="js/main.js"></script>
</body>
</html>
