<!DOCTYPE html>
<html lang="en">
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
        <?php include "inc/nav.inc.php";?>

        <!-- Login Section -->
        <section class="login-section" id="top">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12">
                        <div class="login-form">
                            <h2 style="text-align: center; margin-bottom: 20px;">Log In to GatherSpot</h2>
                            <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                            
                            <!-- Login Form -->
                            <form action="process_login.php" method="POST">
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
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>