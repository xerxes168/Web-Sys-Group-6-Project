<?php
// Start the session
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    
    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
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
                    // Update only the credits column
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
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Credits Management</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Credits Management</h2>
            <p>Add or manage credits for members in your system.</p>
        </div>
        
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <?php echo $successMsg; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-panel">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="admin_panel.php">Dashboard</a></li>
                    <li><a href="admin_venues.php">Manage Venues</a></li>
                    <li><a href="admin_members.php">Manage Members</a></li>
                    <li><a href="admin_credits.php" class="active">Credits Management</a></li>
                    <li><a href="admin_bookings.php">Booking Reports</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <div class="admin-header">
                    <h1>Add Credits to Member Account</h1>
                </div>
                
                <div class="form-section">
                    <form method="post" action="admin_credits.php">
                        <div class="form-group">
                            <label for="member_id" class="form-label">Select Member *</label>
                            <select id="member_id" name="member_id" class="form-control" required>
                                <option value="">-- Select Member --</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['member_id']; ?>">
                                        <?php echo htmlspecialchars($member['fname'] . ' ' . $member['lname'] . ' (' . $member['email'] . ')'); ?>
                                        - Current: <?php echo number_format($member['credit'], 2); ?> credits
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="form-label">Amount to Add *</label>
                                    <input type="number" id="amount" name="amount" class="form-control" min="1" step="0.01" required>
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
                            <button type="submit" name="add_credits" class="btn-add-credits">Add Credits</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>