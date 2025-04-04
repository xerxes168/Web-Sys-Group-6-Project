<?php
session_start();

$loggedIn = isset($_SESSION['member_id']);
$userEmail = $_SESSION['email'] ?? 'Unknown';
$userId = $_SESSION['member_id'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "inc/head.inc.php"; ?>
    <title>About Us - HoopSpaces</title>
    <link rel="stylesheet" href="css/aboutus.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?> 

<main>
    <!-- About Us Section -->
    <section class="about-us">
        <div class="container" tabindex="0">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="about-content">
                        <h1>About HoopSpaces</h1>

                        <p>At HoopSpaces, we are driven by a simple yet powerful mission: to make sports event booking easy, accessible, and enjoyable for everyone.</p>

                        <p>Whether you're planning a friendly game of basketball with friends, hosting a company volleyball tournament, or organizing a local sports event, we've got you covered. Our platform is designed to connect sports enthusiasts, teams, and event planners with the best sports facilities available in Singapore.</p>

                        <p>With a wide variety of venues at your fingertips, including indoor basketball courts, tennis courts, badminton halls, and more, we ensure that booking your next event is quick, easy, and hassle-free.</p>

                        <p>At HoopSpaces, we're not just about sports; we're about community, health, and bringing people together through the power of play.</p>

                        <p>We aim to be your go-to partner for booking sports venues. Our commitment is to provide you with personalized support, allowing you to focus on what matters most—your event and the people you're connecting with.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section class="history">
        <div class="container" tabindex="0">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <h2>Our History</h2>
                    <p>The story of HoopSpaces began with a passion for sports and a frustration with the complexity of booking sports venues. Founders were avid sports enthusiasts who found it difficult to find a reliable, easy-to-use platform for booking sports facilities in Singapore.</p>
                    <p>The idea was born out of the belief that sports and physical activity should be a part of everyone's life, regardless of their background or skill level. However, booking sports spaces was often a daunting and time-consuming task.</p>
                    <p>That's when HoopSpaces was conceived, as a one-stop solution for people to book venues for their sports events quickly and easily. As we continue to expand and refine our services, we are committed to providing the best experience for all our users.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container" tabindex="0">
            <div class="row justify-content-center">
                <div class="col-md-10 text-center">
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
                            <p>We're here to help you every step of the way. From helping you choose the perfect venue to answering any last-minute questions, we ensure a smooth experience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team">
        <div class="container" tabindex="0">
            <div class="row justify-content-center">
                <div class="col-md-10 text-center">
                    <h2>Meet the Team</h2>
                    <div class="team-members">
                        <div class="team-member">
                            <h3>Tan Zheng Liang</h3>
                            <p>Founder & CEO</p>
                            <p>With over 15 years of experience in the event management industry, John brings his passion for making events unforgettable to every project we take on.</p>
                        </div>
                        <div class="team-member">
                            <h3>Alexi Kizhakkepurathu George</h3>
                            <p>Event Coordinator</p>
                            <p>Jane ensures that every event is smoothly coordinated, from initial contact to the final farewell. She thrives on helping clients plan stress-free events.</p>
                        </div>
                        <div class="team-member">
                            <h3>Elsia Teo Yu Ning</h3>
                            <p>Marketing Manager</p>
                            <p>David leads our marketing efforts to reach more event planners and make HoopSpaces the top choice for booking event spaces in Singapore.</p>
                        </div>
                        <div class="team-member">
                            <h3>Lim Sheng Yang</h3>
                            <p>Operations Manager</p>
                            <p>Lim Sheng Yang oversees the day-to-day operations of HoopSpaces, ensuring that everything runs smoothly. With a keen eye for detail, he manages logistics and ensures an excellent user experience for our clients.</p>
                        </div>
                        <div class="team-member">
                            <h3>Lee Jian Yu</h3>
                            <p>Customer Support Manager</p>
                            <p>Lee Jian Yu provides outstanding customer support to our users, ensuring that every question is answered and every issue is resolved promptly. With a passion for helping others, Jian Yu makes sure our clients have the best experience possible when using HoopSpaces.</p>
                        </div>
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
