<?php
// Ensure session is started in this include file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['member_id']);
$memberName = $isLoggedIn && isset($_SESSION['fname']) ? $_SESSION['fname'] : '';
?>
<html>
    <body>
    <script src="js/dropdown.js"></script>
    </body>

<div class="navbar">
    <div class="nav-menu">
        <div class="nav-logo">GatherSpot</div>
        <ul class="nav-menu-items">
            <li class="nav-menu-item home-item">
                <a href="index.php">Home</a>
            </li>
            <li class="nav-menu-item">
                <a href="#about-us-section">About Us</a>
            </li>
            <li class="nav-menu-item dropdown">
                <a href="#" class="dropdown-toggle">Make a Booking</a>
                <ul class="dropdown-menu">
                <li><a href="sports.php">Sports</a></li>
                    <li><a href="#">Birthday</a></li>
                    <li><a href="#">Networking/Gathering</a></li>
                    <li><a href="#">Seminar/Workshop</a></li>
                </ul>
            </li>
            <li class="nav-menu-item">
                <a href="#">How to Book?</a>
            </li>
            <li class="nav-menu-item">
                <a href="#contact-section" class="scroll-link" data-id="contact-section">Contact Us</a>
            </li>
        </ul>
    </div>
    <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
            <!-- User is logged in - show member options -->
            <div class="nav-user dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class="fa fa-user-circle"></i> 
                    <?php echo htmlspecialchars($memberName ? $memberName : 'My Account'); ?>
                </a>
                <ul class="dropdown-menu user-menu">
                    <li><a href="profile.php"><i class="fa fa-user"></i> My Profile</a></li>
                    <li><a href="myBookings.php"><i class="fa fa-calendar"></i> My Bookings</a></li>
                    <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <!-- User is not logged in - show login and registration options -->
            <button class="nav-button" onclick="location.href='register.php'">Register/Login</button>
        <?php endif; ?>
    </div>
</div>
</html>