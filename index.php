<?php
// Start the session at the very beginning of the file
session_start();

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
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HoopSpaces</title>
<!-- 
Avalon Template 
http://www.templatemo.com/tm-513-avalon
-->
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <?php 
        include "inc/head.inc.php"; 
        ?> 
        
    </head>

<body>
    <?php 
        include "inc/nav.inc.php"; 
    ?> 

    <?php 
        include "inc/header.inc.php"; 
    ?> 

    <section class="about-us" id=about-us-section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="about-us-left-content">
                        <div class="icon"><img src="img/about-us icon.png" alt=""></div>
                        <h4>About Us</h4>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="about-us-right-content">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <h2>About<em> Us</em></h2>
                                <p>GatherSpot connects with event planners to rent the most suitable sport or event venues in Singapore for any occasion. Whether you're planning a birthday party, gathering or corporate seminar, we have a diverse selection of venues to suit your needs. </p>
                                <p> Choose GatherSpot for</p>
                                <ul>
                                    <li>+ Wide variety of venues</li>
                                    <li>+ Access to trusted venues in Singapore</li>
                                    <li>+ Personalized event support</li>
                                    <li>Let's make a memorable experience</li>
                                </ul>
                                <div class="pink-button">
                                    <a href="aboutus.php" class="scroll-link">Discover More</a>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <img src="img/about-us-image.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how-to-book" id="how-to-book-section">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Left Image -->
            <div class="col-md-5">
                <img src="img/reservation.jpg" alt="Booking Guide" class="booking-image">
            </div>

            <!-- Right Steps -->
            <div class="col-md-7 booking-steps">
                <h2 class="section-title">How to Book a Venue</h2>
                <div class="step">
                    <div class="icon">1</div>
                    <div class="text">
                        <h4>Select a Venue</h4>
                        <p>Browse our selection of event spaces and choose the perfect venue.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="icon">2</div>
                    <div class="text">
                        <h4>Pick a Date & Time</h4>
                        <p>Use our calendar to check availability and pick a slot that suits you.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="icon">3</div>
                    <div class="text">
                        <h4>Confirm Your Booking</h4>
                        <p>Provide your details and submit your reservation request.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="icon">4</div>
                    <div class="text">
                        <h4>Receive Confirmation</h4>
                        <p>We’ll send you a confirmation email with your booking details.</p>
                    </div>
                </div>

                <div class="booking-button">
                    <a href="#" class="btn btn-booking">Start Booking</a>
                </div>
                </div>
            </div>
        </div>
    </section>


      
    
    <section class="services" id="services-section">
    <div class="container-fluid">
        <div class="row">
            <!-- Sports Service -->
            <div class="col-md-6">
                <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                    <div class="flipper first-service">
                        <div class="front">
                            <div class="icon">
                                <img src="img/sports-icon.png" alt="">
                            </div>
                            <h4>Sports</h4>
                        </div>
                        <div class="back">
                            <p>Book venues suitable for sports to play with your family, friends or colleagues! 
                                Example venues: Badminton Court, Tennis Court, Indoor Basketball Court and many more! 
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Networking/Gathering Service -->
            <div class="col-md-6">
                <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                    <div class="flipper second-service">
                        <div class="front">
                            <div class="icon">
                                <img src="img/GathNet-icon.png" alt="">
                            </div>
                            <h4>Networking/Gathering</h4>
                        </div>
                        <div class="back">
                            <p>Perfect for business networking events, meetups, and social gatherings. Book spaces with the right ambiance for professional mingling and creating new connections.
                                Example Venues: Conference rooms, Event halls, Hotel lounges and many more!
                            </p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-us" id="contact-section"> 
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <form id="contact" action="" method="post">
                        <div class="row">
                            <div class="col-md-4">
                              <fieldset>
                                <input name="name" type="text" class="form-control" id="name" placeholder="Your name..." required="">
                              </fieldset>
                            </div>
                            <div class="col-md-4">
                              <fieldset>
                                <input name="email" type="email" class="form-control" id="email" placeholder="Your email..." required="">
                              </fieldset>
                            </div>
                             <div class="col-md-4">
                              <fieldset>
                                <input name="subject" type="text" class="form-control" id="subject" placeholder="Subject..." required="">
                              </fieldset>
                            </div>
                            <div class="col-md-12">
                              <fieldset>
                                <textarea name="message" rows="6" class="form-control" id="message" placeholder="Your message..." required=""></textarea>
                              </fieldset>
                            </div>
                            <div class="col-md-6">
                              <fieldset>
                                <button type="submit" id="form-submit" class="btn">Send</button>
                              </fieldset>
                            </div>
                        </div>
                    </form>
                </div>
    </section>

    <div id="reservation-popup" class="reservation-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <h2 id="event-title">Event Title</h2>
            
            <div class="detail-row">
                <label>Date:</label>
                <input type="text" id="datepicker" placeholder="Select Date" readonly>
            </div>
            
            <!-- <div class="detail-row">
                <label>Location:</label>
                <select id="location-select">
                <option value="main-hall">Main Hall</option>
                <option value="conference-room">Conference Room</option>
                <option value="outdoor-space">Outdoor Space</option>
                <option value="studio">Studio Space</option>
                </select>
            </div> -->
            
            <div class="detail-row">
                <label>Time Slot:</label>
                <select id="timeslot-select">
                <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                <option value="afternoon">Afternoon (12:00 PM - 4:00 PM)</option>
                <option value="evening">Evening (4:00 PM - 8:00 PM)</option>
                </select>
            </div>
            
            <div class="detail-row">
                <label>Number of Guests:</label>
                <input type="number" id="guest-count" min="1" max="100" value="1">
            </div>
            
            <div class="detail-row">
                <label>Special Requests:</label>
                <textarea id="special-requests" placeholder="Any specific requirements?"></textarea>
            </div>
            </div>
            
            <div class="popup-buttons">
            <button id="cancel-reservation" class="cancel-button">Cancel</button>
            <button id="confirm-reservation" class="confirm-button">Confirm Reservation</button>
            </div>
        </div>
        </div>

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