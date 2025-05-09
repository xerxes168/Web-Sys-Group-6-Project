<?php
// Start the session
session_start();

// Include authentication functions
require_once 'admin_auth.php';

// Check if user is logged in and is an admin
checkAdminAuth();

// Initialize variables
$errorMsg = "";
$successMsg = "";
$members = [];
$conn = null;

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;
    
    // Define the config file path
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists
    if (!file_exists($configFile)) {
        $errorMsg = "Database configuration file not found.";
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg = "Failed to parse database config file.";
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
        $errorMsg = "Host Connection failed: " . $conn->connect_error;
        return false;
    }
    
    return true;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input function
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    // Connect to database
    if (getDbConnection()) {
        // Handle add credits form submission
        if (isset($_POST['add_credits'])) {
            $member_id = intval($_POST['member_id']);
            $amount = floatval($_POST['amount']);
            $reason = sanitize_input($_POST['reason']);
            
            // Validate
            if ($member_id <= 0) {
                $errorMsg = "Please select a valid member.";
            } elseif ($amount <= 0) {
                $errorMsg = "Amount must be greater than zero.";
            } else {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Update credits column
                    $stmt = $conn->prepare("UPDATE members SET credit = credit + ? WHERE member_id = ?");
                    $stmt->bind_param("di", $amount, $member_id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update member credits: " . $stmt->error);
                    }
                    
                    $stmt->close();
                    
                    // Commit transaction
                    $conn->commit();
                    $successMsg = "Successfully added " . number_format($amount, 2) . " credits to member account.";
                } 
                catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    $errorMsg = $e->getMessage();
                }
            }
        }
        
        // Load members for dropdown
        $stmt = $conn->prepare("SELECT member_id, fname, lname, email, credit FROM members ORDER BY lname, fname");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
} else {
    // Initial page load
    if (getDbConnection()) {
        // Load members for dropdown
        $stmt = $conn->prepare("SELECT member_id, fname, lname, email, credit FROM members ORDER BY lname, fname");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Credits Management</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <header role="banner" aria-label="Site header">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main id="main-content" aria-label="Credits management">
        <div class="container admin-dashboard">
            <section class="admin-welcome" aria-labelledby="welcome-heading">
                <h1 id="welcome-heading">Credits Management</h1>
                <p>Add or manage credits for members in your system.</p>
            </section>
            
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger" role="alert" aria-live="assertive">
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success" role="alert" aria-live="polite">
                    <?php echo $successMsg; ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-panel">
                <nav class="admin-sidebar" aria-label="Admin Navigation">
                    <ul>
                        <li><a href="admin_panel.php">Dashboard</a></li>
                        <li><a href="admin_venues.php">Manage Venues</a></li>
                        <li><a href="admin_members.php">Manage Members</a></li>
                        <li><a href="admin_credits.php" class="active" aria-current="page">Credits Management</a></li>
                        <li><a href="admin_bookings.php">Booking Reports</a></li>
                    </ul>
                </nav>
                
                <div class="admin-content" aria-labelledby="form-heading">
                    <h2 id="form-heading">Add Credits to Member Account</h2>
                    
                    <div class="form-section">
                        <form method="post" action="admin_credits.php" novalidate>
                            <div class="form-group">
                                <label for="member_id" class="form-label">Select Member <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                <select id="member_id" name="member_id" class="form-control" required aria-required="true" aria-describedby="member-error">
                                    <option value="">-- Select Member --</option>
                                    <?php foreach ($members as $member): ?>
                                        <option value="<?php echo $member['member_id']; ?>">
                                            <?php echo htmlspecialchars($member['fname'] . ' ' . $member['lname'] . ' (' . $member['email'] . ')'); ?>
                                            - Current: <?php echo number_format($member['credit'], 2); ?> credits
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if(isset($errorMsg) && strpos($errorMsg, "member") !== false): ?>
                                <div id="member-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount" class="form-label">Amount to Add <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                        <input type="number" id="amount" name="amount" class="form-control" min="1" step="0.01" required aria-required="true" aria-describedby="amount-error">
                                        <?php if(isset($errorMsg) && strpos($errorMsg, "Amount") !== false): ?>
                                        <div id="amount-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reason" class="form-label">Reason</label>
                                        <input type="text" id="reason" name="reason" class="form-control" placeholder="Administrative adjustment, promotional credits, etc.">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" name="add_credits" class="btn btn-add-credits">Add Credits</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include "inc/footer.inc.php"; ?>
<script src="js/main.js"></script>
</body>
</html>