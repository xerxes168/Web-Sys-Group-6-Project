<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
// Start the session
session_start();
require_once 'admin_auth.php';

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

    // Prepare the statement to fetch user by email (email is used for login)
    $stmt = $conn->prepare("SELECT email, password, member_id, role FROM members WHERE email = ?");
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
                $_SESSION['member_id'] = $row['member_id']; //use member_id instead of email.
                $_SESSION['role'] = $row['role'];
            } 
            if (isset($_SESSION['member_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
                header("Location: admin_panel.php");
                exit;
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
<html lang="en">
    <head>
        <?php include "inc/head.inc.php"; ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Login Result</title>
        <link rel="stylesheet" href="css/sit_process.css">
    </head>

    <body>
        <!-- Navigation Bar -->
        <nav><?php include "inc/nav.inc.php"; ?></nav>

        <!-- Main Content -->
        <main>
            <section class="result-section" id="top">
                <div class="result-form">
                    <h1 style="text-align: center; margin-bottom: 20px; font-size: 20px">
                        <?php echo "Login Status"; ?>
                    </h1>
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
        </main>
        <script src="js/main.js"></script>
    </body>
</html>