<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>GatherSpot - Log In</title>
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
        <link rel="stylesheet" href="css/sit_login.css">

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
        <script src="js/sit_validator.js"></script>
    </head>

    <body>
        <!-- Navigation Bar -->
        <div class="navbar">
            <div class="nav-menu">
                <div class="nav-logo">GatherSpot</div>
                <ul class="nav-menu-items">
                    <li class="nav-menu-item home-item">
                        <a href="index.php" class="scroll-link" data-id="top">Home</a>
                    </li>
                    <li class="nav-menu-item">About Us</li>
                    <li class="nav-menu-item dropdown">
                        <a href="#" class="dropdown-toggle">Make a Booking</a>
                        <ul class="dropdown-menu">
                            <li><a href="#about-section">Sports</a></li>
                            <li><a href="#team-section">Birthday</a></li>
                            <li><a href="#mission-section">Networking/Gathering</a></li>
                            <li><a href="#mission-section">Seminar/Workshop</a></li>
                        </ul>
                    </li>
                    <li class="nav-menu-item">How to Book?</li>
                    <li class="nav-menu-item">Help</li>
                    <li class="nav-menu-item">
                        <a href="#contact-section" class="scroll-link" data-id="contact-section">Contact Us</a>
                    </li>
                </ul>
            </div>  
            <div class="nav-actions">
                <div class="nav-login"><a href="register.php" style="color:rgb(255, 255, 255);">Sign Up</a></div>
                <button class="nav-button">Get Started</button>
            </div>
        </div>

        <!-- Login Section -->
        <section class="login-section" id="top">
            <div class="login-form">
                <h2 style="text-align: center; margin-bottom: 20px;">Log In to GatherSpot</h2>
                <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                
                <!-- Login Form -->
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username or email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="form-group" style="text-align: right;">
                        <a href="#" style="color: #ff589e;">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; background: #ff589e; border: none; padding: 12px;">Log In</button>
                </form>

                <!-- Sign Up Prompt -->
                <p style="text-align: center; margin-top: 20px;">Don't have an account? <a href="register.php" style="color: #ff589e;">Sign Up</a></p>
            </div>
        </section>
    </body>
</html>