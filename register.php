<!DOCTYPE html>
<html lang="en">
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
    </head>

    <body>
        <!-- Navigation Bar -->
        <?php include "inc/nav.inc.php";?>

        <!-- Register Section -->
        <section class="register-section" id="top">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4 col-sm-8 col-sm-offset-2 col-xs-12">
                        <div class="register-form">
                            <h2 style="text-align: center; margin-bottom: 20px;">Register for GatherSpot</h2>
                            <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                            
                            <!-- Registration Form -->
                            <form action="process_register.php" method="POST">
                                <div class="form-group">
                                    <label for="fname">First Name</label>
                                    <input type="text" class="form-control" id="fname" name="fname" placeholder="Enter your first name">
                                </div>
                                <div class="form-group">
                                    <label for="lname">Last Name</label>
                                    <input type="text" class="form-control" id="lname" name="lname" placeholder="Enter your last name" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Password</label>
                                    <input type="password" class="form-control" id="pwd" name="pwd" placeholder="Create a password" required>
                                </div>
                                <div class="form-group">
                                    <label for="pwd_confirm">Confirm Password</label>
                                    <input type="password" class="form-control" id="pwd_confirm" name="pwd_confirm" placeholder="Confirm your password" required>
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%; background: #ff589e; border: none; padding: 12px;">Register</button>
                            </form>

                            <!-- Login Prompt -->
                            <p style="text-align: center; margin-top: 20px;">Already have an account? <a href="login.php" style="color: #ff589e;">Log In</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>