<?php
// Start the session
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables for password change
$passwordSuccess = false;
$passwordError = "";

// Database connection function
function getDbConnection() {
    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        return false;
    }

    // Create connection
    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    // Check connection
    if ($conn->connect_error) {
        return false;
    }

    return $conn;
}

// Get user data from database
function getUserData($conn, $member_id) {
    $stmt = $conn->prepare("SELECT fname, lname, email, credit FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Handle password change if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = "All password fields are required";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = "New passwords do not match";
    } elseif (strlen($newPassword) < 6) {
        $passwordError = "New password must be at least 6 characters long";
    } else {
        // Get database connection
        $conn = getDbConnection();
        
        if ($conn) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM members WHERE member_id = ?");
            $stmt->bind_param("i", $_SESSION['member_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($currentPassword, $row['password'])) {
                    // Current password is correct, update to new password
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $updateStmt = $conn->prepare("UPDATE members SET password = ? WHERE member_id = ?");
                    $updateStmt->bind_param("si", $hashedNewPassword, $_SESSION['member_id']);
                    
                    if ($updateStmt->execute()) {
                        $passwordSuccess = true;
                    } else {
                        $passwordError = "Failed to update password: " . $conn->error;
                    }
                    
                    $updateStmt->close();
                } else {
                    $passwordError = "Current password is incorrect";
                }
            }
            
            $stmt->close();
            $conn->close();
        } else {
            $passwordError = "Database connection failed";
        }
    }
}

// Get user data
$userData = null;
$conn = getDbConnection();
if ($conn) {
    $userData = getUserData($conn, $_SESSION['member_id']);
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<?php include "inc/head.inc.php"; ?>
    
<link rel="stylesheet" href="css/myProfile.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php include "inc/nav.inc.php"; ?>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="row">
                <!-- User Info Card -->
                <div class="col-md-4">
                    <div class="profile-card">
                        <div class="profile-header">
                            <h3>My Profile</h3>
                        </div>
                        <div class="profile-body">
                            <?php if ($userData): ?>
                                <div class="profile-info">
                                    <h4>Personal Information</h4>
                                    
                                    <div class="info-item">
                                        <label>Name</label>
                                        <p><?php echo htmlspecialchars($userData['fname'] . ' ' . $userData['lname']); ?></p>
                                    </div>
                                    
                                    <div class="info-item">
                                        <label>Email</label>
                                        <p><?php echo htmlspecialchars($userData['email']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="credits-box">
                                    <h3><?php echo number_format($userData['credit'], 2); ?></h3>
                                    <p>Available Credits</p>
                                </div>
                                
                                <a href="credits.php" class="topup-button">
                                    <i class="fa fa-plus-circle"></i> Top Up Credits
                                </a>
                                
                                <a href="mybookings.php" class="bookings-button">
                                    <i class="fa fa-calendar"></i> My Bookings
                                </a>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    Failed to load user profile information. Please try again later.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password Card -->
                <div class="col-md-8">
                    <div class="profile-card">
                        <div class="profile-header">
                            <h3>Change Password</h3>
                        </div>
                        <div class="profile-body">
                            <?php if ($passwordSuccess): ?>
                                <div class="alert alert-success">
                                    <i class="fa fa-check-circle"></i> Your password has been successfully updated.
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($passwordError): ?>
                                <div class="alert alert-danger">
                                    <i class="fa fa-exclamation-circle"></i> <?php echo $passwordError; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                            <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <button type="submit" name="change_password" class="btn-change-password">
                                        <i class="fa fa-lock"></i> Update Password
                                    </button>
                                </div>
                            </form>
                            
                            <div style="margin-top: 40px;">
                                <h4 style="color: #5f52b0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">Account Security Tips</h4>
                                <ul style="padding-left: 20px; color: #555;">
                                    <li>Use a strong password that includes uppercase and lowercase letters, numbers, and special characters.</li>
                                    <li>Don't reuse passwords across multiple sites.</li>
                                    <li>Change your password regularly.</li>
                                    <li>Never share your password with others.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include "inc/footer.inc.php"; ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
</body>
</html>