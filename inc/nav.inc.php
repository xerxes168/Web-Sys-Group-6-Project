<?php
// Ensure session is started in this include file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['member_id']);
$memberName = $isLoggedIn && isset($_SESSION['fname']) ? $_SESSION['fname'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoopSpaces</title>
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-menu">
            <div class="nav-logo">
                <img src="img/logo.png" alt="HoopSpaces Logo" class="logo-img">
            </div>

            <!-- Hamburger Button -->
            <div class="hamburger" onclick="toggleMenu()">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>

            <ul class="nav-menu-items">
                <li class="nav-menu-item home-item">
                    <a href="index.php">Home</a>
                </li>
                <li class="nav-menu-item">
                    <a href="aboutus.php">About Us</a>
                </li>
                <li class="nav-menu-item">
                    <a href="viewSports.php">Make a Booking</a>
                </li>
                <li class="nav-menu-item">
                    <a href="credits.php">Credits</a>
                </li>
                <li class="nav-menu-item">
                    <a href="contactus.php">Contact Us</a>
                </li>
            </ul>
        </div>

        <div class="nav-actions">
            <?php if ($isLoggedIn): ?>
                <div class="nav-user dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa fa-user-circle"></i> 
                        <?php echo htmlspecialchars($memberName ? $memberName : 'My Account'); ?>
                    </a>
                    <ul class="dropdown-menu user-menu">
                        <li><a href="profile.php"><i class="fa fa-user"></i> My Profile</a></li>
                        <li><a href="mybookings.php"><i class="fa fa-calendar"></i> My Bookings</a></li>
                        <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <button class="nav-button" onclick="location.href='register.php'">Register/Login</button>
            <?php endif; ?>
        </div>
    </nav>


    <!-- Include JavaScript files at the end of body for better performance -->
    <script defer src="js/dropdown.js"></script>
    <script defer src="js/navbar.js"></script>
</body>
</html>
