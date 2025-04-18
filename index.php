<?php
// Start the session at the very beginning of the file
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Now you can access session variables anywhere in this file
// You might want to add some debugging code to see what's in the session
// This can be removed later
if (isset($_SESSION['email']) || isset($_SESSION['member_id'])) {
    // User is logged in
    $loggedIn = true;
    $userEmail = $_SESSION['email'] ?? 'Unknown';
    $userId = $_SESSION['member_id'] ?? 'Unknown';
    
    // For debugging - you can remove this once confirmed working
    // echo "Logged in as: " . htmlspecialchars($userEmail) . " (ID: " . htmlspecialchars($userId) . ")";
} else {
    // User is not logged in
    $loggedIn = false;
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HoopSpaces</title>
<!-- 
Avalon Template 
http://www.templatemo.com/tm-513-avalon
-->
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <?php include "inc/head.inc.php"; ?> 
    </head>

<body>
    <?php include "inc/nav.inc.php"; ?> 
    <?php include "inc/header.inc.php"; ?> 
<main>
    <section class="about-us" id=about-us-section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="about-us-left-content">
                        <div class="icon"><img src="img/about-us icon.png" alt=""></div>
                        <h2>About Us</h2>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="about-us-right-content">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <h2>About<em> Us</em></h2>
                                <p>HoopSpaces connects individuals, teams, and event planners with the most suitable sports event booking spaces across Singapore. Whether you're organizing a friendly match, team-building session, or a large-scale tournament, we offer a diverse range of venues to meet your needs. </p>
                                <p> Choose HoopSpaces for</p>
                                <ul>
                                    <li>+ Wide variety of sports venues</li>
                                    <li>+ Access to trusted venues in Singapore</li>
                                    <li>+ Personalized support for smooth bookings</li>
                                    <li>Let's make your next game or event unforgettable!</li>
                                </ul>
                                <div class="discover-button">
                                    <a href="aboutus.php" class="discover-button">Discover More</a>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <img src="img/aboutus.jpg" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="how-to-book" id="how-to-book-section">
        <div class="booking-container">
            <div class="booking-image-wrapper">
                <img src="img/reservation.jpg" alt="Booking Guide" class="booking-image">
            </div>
        <div class="booking-content-overlay">
                <h2 class="booking-title">How to Book a Venue</h2>
                <div class="booking-divider">
                    <span class="divider-line"></span>
                    <span class="divider-icon">🏆</span>
                    <span class="divider-line"></span>
                </div>
                
                <div class="booking-steps">
                    <div class="booking-step">
                        <div class="step-circle">1</div>
                        <div class="step-content">
                            <h3>Select a Venue</h3>
                            <p>Browse our selection of event spaces and choose the perfect venue.</p>
                        </div>
                    </div>
                    
                    <div class="booking-step">
                        <div class="step-circle">2</div>
                        <div class="step-content">
                            <h3>Pick a Date & Time</h3>
                            <p>Use our calendar to check availability and pick a slot that suits you.</p>
                        </div>
                    </div>
                    
                    <div class="booking-step">
                        <div class="step-circle">3</div>
                        <div class="step-content">
                            <h3>Confirm Your Booking</h3>
                            <p>Provide your details and submit your reservation request.</p>
                        </div>
                    </div>
                    
                    <div class="booking-step">
                        <div class="step-circle">4</div>
                        <div class="step-content">
                            <h3>Receive Confirmation</h3>
                            <p>We'll send you a confirmation email with your booking details.</p>
                        </div>
                    </div>
                </div>
                
                <div class="booking-action">
                    <a href="viewSports.php" class="booking-button">Start Booking</a>
                </div>
            </div>
        </div>
    </section>
        
        <section class="services" id="services-section">
        <div class="container-fluid">
            <div class="row">
                <!-- Sports Service -->
                <div class="col-md-12">
                    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                        <div class="flipper first-service">
                            <div class="front">
                                <div class="icon">
                                    <img src="img/sports-icon.png" alt="">
                                </div>
                                <h4>Join the Fitness Revolution!</h4>
                            </div>
                            <div class="back">
                                <p>Book venues suitable for sports to play with your family, friends or colleagues! 
                                Example venues: Badminton Court, Tennis Court, Indoor Basketball Court and many more!
                                It's time to take charge of your health and well-being. Whether you're looking to get 
                                stronger, boost your energy, or relieve stress, exercise is the key. Join us today and experience 
                                the power of regular movement in a supportive, motivating environment. Let’s make fitness fun and 
                                achievable—together!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<section class="contact-details" id="contact-details-section">
  <div class="container-fluid">
    <div class="contact-details-container">
      <!-- Main title spanning full width -->
      <h2 class="contact-main-title">HoopSpaces</h2>
      
      <!-- Row with two columns -->
      <div class="row">
        <!-- Left column -->
        <div class="col-md-6">
          <div class="contact-details-content">
            <h3 class="contact-subtitle">Email</h3>
            <p class="contact-type">For Venue Enquiries:</p>
            <p class="contact-info">support@hoopspaces.com</p>
            <p class="contact-type">For Corporate Bookings:</p>
            <p class="contact-info">corporate@hoopspaces.com</p>
          </div>
        </div>
        
        <!-- Right column -->
        <div class="col-md-6">
          <div class="contact-details-content">
            <h3 class="contact-subtitle">HoopSpaces Main Office</h3>
            <p class="contact-address">
              Sunshine Tower, 8 Raffles Avenue<br>
              Singapore 039802
            </p>
            <p class="contact-hours-title">Operating hours:</p>
            <p class="contact-hours">
              Mon - Fri: 9AM - 6PM (By Appointment Only*)<br>
              (Lunch Hours: 12pm - 1pm)
            </p>
            <p class="contact-type">For Venue Enquiries: +65 8473 4567</p>
            <p class="contact-type">For Corporate Bookings: +65 9482 5432</p>
            <p class="contact-hours-title">Operating hours:</p>
            <p class="contact-hours">
              Sat - Sun: 10AM - 5PM (By Appointment Only*)
            </p>
            <p class="contact-type">For Weekend Bookings: +65 9083 4567</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</main>
    <?php include "inc/footer.inc.php"; ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="js/vendor/bootstrap.min.js"></script>
    
    <script src="js/datepicker.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript">
    </script>

</body>
</html>