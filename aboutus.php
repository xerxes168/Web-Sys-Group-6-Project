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
<html lang="en">
<head>
    <?php include "inc/head.inc.php"; ?>
    <title>About Us - HoopSpaces</title>
    <link rel="stylesheet" href="css/aboutus.css">
</head>

<body>
    <?php 
        include "inc/nav.inc.php"; 
    ?> 

    <!-- About Us Section -->
    <section class="about-us">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="about-content">
                        <h1>About HoopSpaces</h1>
                        <p>At HoopSpaces, we are driven by a simple yet powerful mission: to make sports event booking easy, accessible, and enjoyable for everyone. Whether you're planning a friendly game of basketball with friends, hosting a company volleyball tournament, or organizing a local sports event, we’ve got you covered. Our platform is designed to connect sports enthusiasts, teams, and event planners with the best sports facilities available in Singapore.

                            With a wide variety of venues at your fingertips, including indoor basketball courts, tennis courts, badminton halls, and more, we ensure that booking your next event is quick, easy, and hassle-free. No more endless searching or navigating complicated booking systems—just select the venue, pick your time, and you’re good to go!

                            At HoopSpaces, we’re not just about sports; we’re about community, health, and bringing people together through the power of play. Whether you’re an athlete, a casual player, or someone organizing a corporate wellness day, we believe everyone deserves access to great sports spaces. Our platform is dedicated to supporting the growth of local sports culture, ensuring that every game, tournament, or team-building activity is memorable and seamless.

                            We aim to be your go-to partner for booking sports venues. Our commitment is to provide you with personalized support, allowing you to focus on what matters most—your event and the people you're connecting with.</p>
                        
                        <!-- History Section -->
                        <div class="history">
                            <h3>Our History</h3>
                            <p>The story of HoopSpaces began with a passion for sports and a frustration with the complexity of booking sports venues. Founders were avid sports enthusiasts who found it difficult to find a reliable, easy-to-use platform for booking sports facilities in Singapore. What started as a simple idea to make sports venue booking more accessible eventually grew into the full-fledged platform that is now HoopSpaces.

                                The idea was born out of the belief that sports and physical activity should be a part of everyone’s life, regardless of their background or skill level. However, booking sports spaces was often a daunting and time-consuming task. That’s when HoopSpaces was conceived, as a one-stop solution for people to book venues for their sports events quickly and easily. As we continue to expand and refine our services, we are committed to providing the best 
                                experience for all our users—whether you’re an individual looking for a basketball court, a group planning a tennis match, or a company organizing a team-building event. Our goal is simple: to make sports more accessible, to keep people active, and to foster a sense of community through shared physical experiences.</p>
                        </div>

                        <!-- Why Us Section -->
                        <div class="container">
                        <h2>Why Choose HoopSpaces?</h2>
                        <div class="choose-list">
                            <div class="choose-item">
                                <h3>Wide Selection of Venues</h3>
                                <p>From badminton courts to basketball arenas, we offer a diverse range of sports spaces for all your needs.</p>
                            </div>
                            <div class="choose-item">
                                <h3>Easy Booking Process</h3>
                                <p>Skip the paperwork and phone calls. Our platform allows you to browse, book, and pay for your venue all in one place.</p>
                            </div>
                            <div class="choose-item">
                                <h3>Trusted Partners</h3>
                                <p>We work with top-rated sports venues in Singapore, ensuring that your event will be held in top-quality facilities.</p>
                            </div>
                            <div class="choose-item">
                                <h3>Flexible Options</h3>
                                <p>Whether you're planning a casual meetup or a large-scale event, we have the right venue for you.</p>
                            </div>
                            <div class="choose-item">
                                <h3>Supportive Team</h3>
                                <p>We’re here to help you every step of the way. From helping you choose the perfect venue to answering any last-minute questions, we ensure a smooth experience.</p>
                            </div>
                        </div>
                    </div>

    <!-- Team Section (Horizontal Scrolling) -->
    <section class="team">
        <div class="container">
            <h2>Meet the Team</h2>
            <div class="team-members">
                <div class="team-member">
                    <img src="img/team1.jpg" alt="John Doe" class="team-photo">
                    <h3>John Doe</h3>
                    <p>Founder & CEO</p>
                    <p>With over 15 years of experience in the event management industry, John brings his passion for making events unforgettable to every project we take on.</p>
                </div>
                <div class="team-member">
                    <img src="img/team2.jpg" alt="Jane Smith" class="team-photo">
                    <h3>Jane Smith</h3>
                    <p>Event Coordinator</p>
                    <p>Jane ensures that every event is smoothly coordinated, from initial contact to the final farewell. She thrives on helping clients plan stress-free events.</p>
                </div>
                <div class="team-member">
                    <img src="img/team3.jpg" alt="David Lee" class="team-photo">
                    <h3>David Lee</h3>
                    <p>Marketing Manager</p>
                    <p>David leads our marketing efforts to reach more event planners and make GatherSpot the top choice for booking event spaces in Singapore.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'inc/footer.inc.php'; ?>

    <script src="js/main.js"></script>
</body>
</html>
