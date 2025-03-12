<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>GatherSpot - Register</title>
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
        <link rel="stylesheet" href="css/sit_register.css">

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
        <script src="js/sit_validator.js"></script>

        <style>
            /* Full viewport height, no scrolling */
            html, body {
                height: 100vh;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }

            /* Fixed navbar at the top */
            .navbar {
                position: fixed;
                top: 0;
                width: 100%;
                z-index: 1000;
                padding: 10px 0; /* Consistent padding */
            }

            /* Main container takes full height */
            .register-section {
                height: 100vh; /* Full viewport height */
                display: flex;
                justify-content: center; /* Center horizontally */
                align-items: center; /* Center vertically */
                background: #f7f7f7;
                padding-top: 80px; /* Adjusted for taller navbar with dropdown */
                box-sizing: border-box; /* Include padding in height calculation */
            }

            /* Register form styling */
            .register-form {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px; /* Limit width for readability */
            }

            /* Ensure form elements fit within the container */
            .form-group {
                margin-bottom: 15px;
            }

            /* Responsive adjustments for smaller screens */
            @media (max-width: 767px) {
                .register-form {
                    padding: 15px;
                    max-width: 90%; /* Allow form to shrink on very small screens */
                }
                .navbar {
                    padding: 5px 0; /* Reduce navbar padding on mobile */
                }
                .nav-menu-items {
                    display: block; /* Stack items vertically on mobile if needed */
                }
                .nav-menu-item {
                    margin: 5px 0;
                }
                .dropdown-menu {
                    position: static; /* Prevent dropdown from overflowing */
                    box-shadow: none;
                }
            }
        </style>
    </head>

    <body>
        <!-- Navigation Bar -->
        <div class="navbar">
            <div class="nav-menu">
                <div class="nav-logo">GatherSpot</div>
                <ul class="nav-menu-items">
                    <li class="nav-menu-item home-item">
                        <a href="#top" class="scroll-link" data-id="top">Home</a>
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
                <div class="nav-login">Log In</div>
                <button class="nav-button">Get Started</button>
            </div>
        </div>

        <!-- Register Section -->
        <section class="register-section" id="top">
            <div class="register-form">
                <h2 style="text-align: center; margin-bottom: 20px;">Register for GatherSpot</h2>
                <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                
                <!-- Registration Form -->
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; background: #ff589e; border: none; padding: 12px;">Register</button>
                </form>

                <!-- Login Prompt -->
                <p style="text-align: center; margin-top: 20px;">Already have an account? <a href="login.html" style="color: #ff589e;">Log In</a></p>
            </div>
        </section>
    </body>
</html>