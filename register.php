<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include "inc/head.inc.php"; ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Register</title>
        <link rel="stylesheet" href="css/sit_register.css">
    </head>

    <body>
        <!-- Navigation Landmark -->
        <nav class="navbar">
            <?php include "inc/nav.inc.php";?>
        </nav>

        <!-- Main Content Landmark -->
        <main id="main-content">
            <section class="register-section" id="top">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12">
                            <div class="register-form">
                                <h1 style="text-align: center; margin-bottom: 20px;">Register</h1>
                                <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                                
                                <!-- Registration Form -->
                                <form action="process_register.php" method="POST">
                                    <div class="form-group">
                                        <label for="fname">First Name</label>
                                        <input type="text" class="form-control" id="fname" name="fname" 
                                            placeholder="Enter your first name" 
                                            pattern="[A-Za-z]+" 
                                            title="First name can only contain letters (no spaces or special characters)" 
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lname">Last Name</label>
                                        <input type="text" class="form-control" id="lname" name="lname" 
                                            placeholder="Enter your last name" 
                                            pattern="[A-Za-z]+" 
                                            title="Last name can only contain letters (no spaces or special characters)" 
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                            placeholder="Enter your email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd">Password</label>
                                        <input type="password" class="form-control" id="pwd" name="pwd" 
                                            placeholder="Create a password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="pwd_confirm">Confirm Password</label>
                                        <input type="password" class="form-control" id="pwd_confirm" name="pwd_confirm" 
                                            placeholder="Confirm your password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" 
                                            style="width: 100%; background: #5f52b0; border: none; padding: 12px;">
                                        Register
                                    </button>
                                </form>

                                <!-- Login Prompt -->
                                <p style="text-align: center; margin-top: 20px;">Already have an account? <a class="login_link" href="login.php">Log In</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <script src="js/main.js"></script>
    </body>
</html>