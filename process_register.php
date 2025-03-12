<?php
// Start the session (if needed)
session_start();

// Initialize variables
$fname = $lname = $email = $password = "";
$errorMsg = "";
$success = true;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Last Name (Required)
    if (empty($_POST["lname"])) {
        $errorMsg .= "<li>Last name is required.</li>";
        $success = false;
    } else {
        $lname = sanitize_input($_POST["lname"]);
    }

    // Validate Email (Required & Proper Format)
    if (empty($_POST["email"])) {
        $errorMsg .= "<li>Email is required.</li>";
        $success = false;
    } else {
        $email = sanitize_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg .= "<li>Invalid email format.</li>";
            $success = false;
        }
    }

    // Validate Password (Required & Match Confirmation)
    if (empty($_POST["pwd"]) || empty($_POST["pwd_confirm"])) {
        $errorMsg .= "<li>Password and confirmation are required.</li>";
        $success = false;
    } else {
        $password = $_POST["pwd"];
        $password_confirm = $_POST["pwd_confirm"];
        if ($password !== $password_confirm) {
            $errorMsg .= "<li>Passwords do not match.</li>";
            $success = false;
        } elseif (strlen($password) < 6) {
            $errorMsg .= "<li>Password must be at least 6 characters long.</li>";
            $success = false;
        }
    }

    // Sanitize Optional First Name
    if (!empty($_POST["fname"])) {
        $fname = sanitize_input($_POST["fname"]);
    }

    // Hash Password and Save to DB if Validation Passed
    if ($success) {
        $pwd_hashed = password_hash($password, PASSWORD_DEFAULT);
        // saveMemberToDB(); // Uncomment this line to save to database
    }
}

/**
 * Function to save the user's data into the database
 */
function saveMemberToDB()
{
    global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;

    // Define the config file path relative to this script
    $configFile = __DIR__ . '/private/db-config.ini';

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

    // Prepare the statement
    $stmt = $conn->prepare(
        "INSERT INTO world_of_pets_members (fname, lname, email, password) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        $errorMsg .= "<li>Prepare failed: (" . $conn->errno . ") " . $conn->error . "</li>";
        $success = false;
    } else {
        $stmt->bind_param("ssss", $fname, $lname, $email, $pwd_hashed);
        if (!$stmt->execute()) {
            $errorMsg .= "<li>Execute failed: (" . $stmt->errno . ") " . $stmt->error . "</li>";
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
        <title>GatherSpot - Registration Result</title>
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
        <link rel="stylesheet" href="css/sit_process.css">

        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
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

        <!-- Result Section -->
        <section class="result-section" id="top">
            <div class="result-form">
                <h2 style="text-align: center; margin-bottom: 20px;">
                    <?php echo $success ? "Registration Successful" : "Registration Failed"; ?>
                </h2>
                <div class="line-dec" style="width: 50px; height: 3px; background: #ff589e; margin: 0 auto 20px;"></div>
                
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
                    <?php if ($success) { ?>
                        <div class="success-message">
                            Registration successful! <br>
                            <a href="login.php">Log in here</a>
                        </div>
                    <?php } else { ?>
                        <div class="error-message">
                            <p>The following errors occurred:</p>
                            <ul><?php echo $errorMsg; ?></ul>
                            <a href="register.php">Try again</a>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="error-message">
                        No registration attempt made. <br>
                        <a href="register.php">Go to registration</a>
                    </div>
                <?php } ?>
            </div>
        </section>
    </body>
</html>