<?php
// Start the session
session_start();

// Initialize variables
$username = $password = "";
$errorMsg = "";
$success = true;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Username/Email (Required)
    if (empty($_POST["username"])) {
        $errorMsg .= "<li>Username or Email is required.</li>";
        $success = false;
    } else {
        $username = sanitize_input($_POST["username"]);
    }

    // Validate Password (Required)
    if (empty($_POST["password"])) {
        $errorMsg .= "<li>Password is required.</li>";
        $success = false;
    } else {
        $password = $_POST["password"];
    }

    // If no validation errors, attempt login
    if ($success) {
        verifyLogin();
    }
}

/**
 * Function to verify login credentials against the database
 */
function verifyLogin() {
    global $username, $password, $errorMsg, $success;

    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        $errorMsg .= "<li>Database configuration file not found.</li>";
        $success = false;
        return;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg .= "<li>Failed to parse database config file.</li>";
        $success = false;
        return;
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    // Check connection
    if ($conn->connect_error) {
        $errorMsg .= "<li>Host Connection failed: " . $conn->connect_error . "</li>";
        $success = false;
        return;
    }

    // Prepare the statement to fetch user by email (assuming email is used for login)
    $stmt = $conn->prepare("SELECT email, password FROM world_of_pets_members WHERE email = ?");
    if (!$stmt) {
        $errorMsg .= "<li>Prepare failed: (" . $conn->errno . ") " . $conn->error . "</li>";
        $success = false;
    } else {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Login successful, set session variable
                $_SESSION['email'] = $row['email'];
            } else {
                $errorMsg .= "<li>Invalid email or password.</li>";
                $success = false;
            }
        } else {
            $errorMsg .= "<li>Invalid email or password.</li>";
            $success = false;
        }
        $stmt->close();
    }

    $conn->close();
}

/**
 * Helper function to sanitize input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>GatherSpot - Login Result</title>
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
        <link rel="stylesheet" href="css/sit_process.css"> <!-- Reusing sit_process.css -->

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
    </head>

    <body>
        <!-- Navigation Bar -->
        <?php include "inc/nav.inc.php";?>

        <!-- Result Section -->
        <section class="result-section" id="top">
            <div class="result-form">
                <h2 style="text-align: center; margin-bottom: 20px;">
                    <?php echo $success ? "Login Successful" : "Login Failed"; ?>
                </h2>
                <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
                    <?php if ($success) { ?>
                        <div class="success-message">
                            Login successful! <br>
                            <a href="index.php">Go to homepage</a>
                        </div>
                    <?php } else { ?>
                        <div class="error-message">
                            <p>The following errors occurred:</p>
                            <ul><?php echo $errorMsg; ?></ul>
                            <a href="login.php">Try again</a>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="error-message">
                        No login attempt made. <br>
                        <a href="login.php">Go to login</a>
                    </div>
                <?php } ?>
            </div>
        </section>
    </body>
</html>