<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include "inc/head.inc.php"; ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Log In</title>
        <link rel="stylesheet" href="css/sit_login.css">
    </head>

    <body>
        <!-- Navigation -->
        <nav class="navbar">
            <?php include "inc/nav.inc.php";?>
        </nav>

        <!-- Main Content -->
        <main id="main-content">
            <section class="login-section" id="top">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12">
                            <div class="login-form">
                                <h1 style="text-align: center; margin-bottom: 20px;">Login</h1>
                                <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                                
                                <!-- Login Form -->
                                <form action="process_login.php" method="POST">
                                    <div class="form-group">
                                        <label for="username">Email</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="width: 100%; background: #ff589e; border: none; padding: 12px;">Log In</button>
                                </form>

                                <!-- Sign Up Prompt -->
                                <p style="text-align: center; margin-top: 20px;">Don't have an account? <a class="login_link" href="register.php">Sign Up</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <script src="js/main.js"></script>
    </body>
    
</html>