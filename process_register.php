<?php
// Start the session (if needed)
session_start();

// Initialize variables
$fname = $lname = $email = $password = "";
$errorMsg = "";
$success = true;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate First Name (Required)
    if (empty($_POST["fname"])) {
        $errorMsg .= "<li>First name is required.</li>";
        $success = false;
    } else {
        $fname = sanitize_input($_POST["fname"]);
    }

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

    // Hash Password and Save to DB if Validation Passed
    if ($success) {
        // Check if email is already registered
        if (isEmailRegistered($email)) {
            $errorMsg .= "<li>Email already registered.</li>";
            $success = false;
        } else {
            $pwd_hashed = password_hash($password, PASSWORD_DEFAULT);
            saveMemberToDB($fname, $lname, $email, $pwd_hashed);
        }
    }
}

/**
 * Function to check if an email is already registered
 * Returns true if email exists, false if not
 */
function isEmailRegistered($email) {
    // Use __DIR__ for a relative path
    $configFile = __DIR__ . '/../private/db-config.ini'; // Adjust path as needed

    if (!file_exists($configFile)) {
        return false; // Assume not registered if config is missing
    }

    $config = parse_ini_file($configFile);
    if ($config === false) {
        return false; // Assume not registered if config can't be parsed
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        $conn->close();
        return false; // Assume not registered if connection fails
    }

    $stmt = $conn->prepare("SELECT email FROM members WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        $conn->close();
        return $exists;
    }

    $conn->close();
    return false; // Assume not registered if query fails
}

/**
 * Function to save the user's data into the database
 * Modified to accept parameters instead of using globals
 */
function saveMemberToDB($fname, $lname, $email, $pwd_hashed)
{
    global $errorMsg, $success; // Still need these for feedback

    // Use __DIR__ for a relative path
    $configFile = __DIR__ . '/../private/db-config.ini'; // Adjust path as needed

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

    // Prepare the statement for insertion
    $stmt = $conn->prepare(
        "INSERT INTO members (fname, lname, email, password) VALUES (?, ?, ?, ?)"
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
<html lang="en">
    <head>
        <?php include "inc/head.inc.php"; ?>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Registration Result</title>

        <link rel="stylesheet" href="css/sit_process.css">
    </head>

    <body>
        <nav class="navbar">
            <?php include "inc/nav.inc.php";?>
        </nav>


        <!-- Main Content -->
        <main>
            <section class="result-section" id="top">
                <div class="result-form">
                    <h1 style="text-align: center; margin-bottom: 20px; font-size: 20px">
                        <?php echo "Registration Status"; ?>
                    </h1>
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
        </main>
        <script src="js/main.js"></script>
    </body>
</html>