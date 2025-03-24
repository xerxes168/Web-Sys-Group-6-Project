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
        <title>Avalon full-width responsive template</title>
<!-- 
Avalon Template 
http://www.templatemo.com/tm-513-avalon
-->
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="css/fontAwesome.css">
        <link rel="stylesheet" href="css/hero-slider.css">
        <link rel="stylesheet" href="css/owl-carousel.css">
        <link rel="stylesheet" href="css/datepicker.css">
        <link rel="stylesheet" href="css/templatemo-style.css">

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
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
                                <p>GatherSpot connects with event planners to rent the most suitable event venues in Singapore for gatherings~ </p>
                                <ul>
                                    <li>+ Aenean eget ex faucibus, tempor nibh vel.</li>
                                    <li>+ Bibendum tortor. Suspendisse a diam viverra.</li>
                                    <li>+ Finibus ipsum et, imperdiet felis.</li>
                                    <li>+ Donec laoreet efficitur ultrices sit amet enim.</li>
                                </ul>
                                <div class="pink-button">
                                    <a href="#" class="scroll-link" data-id="events-section">Discover More</a>
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

    <section class="events" id="events-section">
        <div class="content-wrapper">
            <div class="inner-container container-fluid">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="section-heading">
                            <div class="filter-categories">
                                <ul class="project-filter">
                                    <li class="filter" data-filter="all"><span>Today</span></li>
                                    <li class="filter" data-filter="design"><span>Tomorrow</span></li>
                                    <li class="filter" data-filter="start"><span>Near Me</span></li>
                                    <!-- <li class="filter" data-filter="web"><span>TBC</span></li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-10 col-sm-12 col-md-offset-1">
                        <div class="projects-holder">
                            <div class="event-list">
                                <ul>
                                    <li class="project-item first-child mix web">
                                        <ul class="event-item web">
                                            <li>
                                                <div class="date">
                                                    <span>12<br>May</span>
                                                </div>
                                            </li>
                                            <li>
                                                <h4>Play! Pickle</h4>
                                                <div class="web">
                                                    <span>Punggol</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="time">
                                                    <span>8:00 AM - 11:00 AM<br>Saturday</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="white-button">
                                                    <a href="#">Reserve</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="project-item second-child mix design">
                                        <ul class="event-item design">
                                            <li>
                                                <div class="date">
                                                    <span>24<br>April</span>
                                                </div>
                                            </li>
                                            <li>
                                                <h4>HERE</h4>
                                                <div class="design">
                                                    <span>Farrer Park</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="time">
                                                    <span>03:00 PM - 07:00 PM<br>Tuesday</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="white-button">
                                                    <a href="#">Reserve</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="project-item third-child mix start design">
                                        <ul class="event-item start">
                                            <li>
                                                <div class="date">
                                                    <span>30<br>Mar</span>
                                                </div>
                                            </li>
                                            <li>
                                                <h4>core hammock stiller</h4>
                                                <div class="app">
                                                    <span>App Start Up, Design Meeting</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="time">
                                                    <span>03:30 PM - 09:30 PM<br>Friday</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="white-button">
                                                    <a href="#">Reserve</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="project-item fourth-child mix web">
                                        <ul class="event-item web">
                                            <li>
                                                <div class="date">
                                                    <span>22<br>Mar</span>
                                                </div>
                                            </li>
                                            <li>
                                                <h4>palo santo art party</h4>
                                                <div class="web">
                                                    <span>Web Conferences</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="time">
                                                    <span>10:00 AM - 05:00 PM<br>Thursday</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="white-button">
                                                    <a href="#">Reserve</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="project-item fivth-child mix start web">
                                        <ul class="event-item start">
                                            <li>
                                                <div class="date">
                                                    <span>16<br>Mar</span>
                                                </div>
                                            </li>
                                            <li>
                                                <h4>Paleo craft beer copper</h4>
                                                <div class="app">
                                                    <span>App Start Up, Web Conferences</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="time">
                                                    <span>11:30 AM - 04:30 PM<br>Friday</span>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="white-button">
                                                    <a href="#">Reserve</a>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </section>


    <section class="testimonial" id="testimonial-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial-image"></div>
                </div>
                <div class="col-md-8">
                    <div id="owl-testimonial" class="owl-carousel owl-theme">
                        <div class="item col-md-12">
                            <img src="img/author_01.png" alt="Steven Walker">
                            <span>Web Designer</span>
                            <h4>Steven Walker</h4>
                            <br>
                            <p><em>"</em>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cura bitur et sem blandit, rhoncus ante, varius libero. Cras elemen tum tincidunt ullamcorper sed vehic ula dictum.<em>"</em></p>
                        </div>
                        <div class="item col-md-12">
                            <img src="img/author_02.png" alt="Johnny Smith">
                            <span>Web Developer</span>
                            <h4>Johnny Smith</h4>
                            <br>
                            <p><em>"</em>Morbi elit est, pharetra ac enim a, faucibus dignissim augue. Quisque erat erat, placerat non pulvinar eget, sodales eget ex. Cras pulvinar purus et rutrum faucibus.<em>"</em></p>
                        </div>
                        <div class="item col-md-12">
                            <img src="img/author_03.png" alt="William Smoker">
                            <span>Managing Director</span>
                            <h4>William Smoker</h4>
                            <br>
                            <p><em>"</em>Praesent luctus lacinia erat, quis lacinia ipsum varius a. Nullam a velit mollis, suscipit felis vitae, dictum libero hendrerit nibh quis sodales gravida ornare ultricies viverra.<em>"</em></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>      
    
    <section class="services" id="services-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                        <div class="flipper first-service">
                            <div class="front">
                                <div class="icon">
                                    <img src="img/heart-icon.png" alt="">
                                </div>
                                <h4>Aliquam finibus est</h4>
                            </div>
                            <div class="back">
                                <p>Donec malesuada eu est in mattis. Aenean velit eros, blandit et tortor non, viverra hendrerit velit. Maecenas ut orci nec velit convallis lobortis sit amet ut magna. Ut rhoncus suscipit arcu, sed facilisis risus.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                        <div class="flipper second-service">
                            <div class="front">
                                <div class="icon">
                                    <img src="img/home-icon.png" alt="">
                                </div>
                                <h4>Nullam sed turpis</h4>
                            </div>
                            <div class="back">
                                <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas vel diam porttitor, fermentum ante et, ornare elit. Morbi nec diam ex. Pellentesque habitant morbi tristique senectus.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                        <div class="flipper third-service">
                            <div class="front">
                                <div class="icon">
                                    <img src="img/revision-icon.png" alt="">
                                </div>
                                <h4>Sed in luctus</h4>
                            </div>
                            <div class="back">
                                <p>Mauris congue ex eu enim suscipit, in suscipit est efficitur. Donec quis orci malesuada nunc lobortis aliquet et ut lacus. Sed erat magna, fringilla ac imperdiet in, pulvinar quis ante. Maecenas eleifend, sem vitae tristique.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
                        <div class="flipper fourth-service">
                            <div class="front">
                                <div class="icon">
                                    <img src="img/chat-icon.png" alt="">
                                </div>
                                <h4>Fusce congue ipsum</h4>
                            </div>
                            <div class="back">
                                <p>Donec venenatis erat at leo dictum, at dictum eros volutpat. Phasellus in dui sed purus varius hendrerit. Duis ac enim ac orci efficitur condimentum vel eget purus. Sed vel massa nulla. Curabitur consequat sem ac velit sollicitudin ornare.</p>
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

    <footer>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <p>Copyright &copy; 2018 Your Company 
                    
                    - <a rel="nofollow" href="http://www.templatemo.com/tm-513-avalon" target="_parent">Avalon</a> 
                    by <a rel="nofollow" href="http://www.html5max.com" target="_parent">HTML5 Max</a></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="js/vendor/bootstrap.min.js"></script>
    
    <script src="js/datepicker.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript">
    $(document).ready(function() 
	{
        // navigation click actions 
        $('.scroll-link').on('click', function(event){
            event.preventDefault();
            var sectionID = $(this).attr("data-id");
            scrollToID('#' + sectionID, 750);
        });
        // scroll to top action
        $('.scroll-top').on('click', function(event) {
            event.preventDefault();
            $('html, body').animate({scrollTop:0}, 'slow');         
        });
        // mobile nav toggle
        $('#nav-toggle').on('click', function (event) {
            event.preventDefault();
            $('#main-nav').toggleClass("open");
        });
    });
    // scroll function
    function scrollToID(id, speed){
        var offSet = 0;
        var targetOffset = $(id).offset().top - offSet;
        var mainNav = $('#main-nav');
        $('html,body').animate({scrollTop:targetOffset}, speed);
        if (mainNav.hasClass("open")) {
            mainNav.css("height", "1px").removeClass("in").addClass("collapse");
            mainNav.removeClass("open");
        }
    }
    if (typeof console === "undefined") {
        console = {
            log: function() { }
        };
    }
    </script>

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

</body>
</html>