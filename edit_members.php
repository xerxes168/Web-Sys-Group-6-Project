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
$conn = null;
$member = null;

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;

    $configFile = '/var/www/private/db-config.ini';

    if (!file_exists($configFile)) {
        $errorMsg = "Database configuration file not found.";
        return false;
    }

    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg = "Failed to parse database config file.";
        return false;
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        $errorMsg = "Host Connection failed: " . $conn->connect_error;
        return false;
    }
    
    return true;
}

// Check if member ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_members.php");
    exit;
}

$member_id = intval($_GET['id']);

// Load member data
if (getDbConnection()) {
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
    } else {
        $errorMsg = "Member not found.";
    }
    
    $stmt->close();
    
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_member'])) {
        function sanitize_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
        
        // Get form data
        $fname = sanitize_input($_POST['fname']);
        $lname = sanitize_input($_POST['lname']);
        $email = sanitize_input($_POST['email']);
        $username = sanitize_input($_POST['username'] ?? ''); // Handle null username
        $credit = floatval($_POST['credit']);
        $role = sanitize_input($_POST['role']);
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validate form data
        $isValid = true;
        
        if (empty($fname)) {
            $errorMsg = "First name is required.";
            $isValid = false;
        } elseif (empty($lname)) {
            $errorMsg = "Last name is required.";
            $isValid = false;
        } elseif (empty($email)) {
            $errorMsg = "Email is required.";
            $isValid = false;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Invalid email format.";
            $isValid = false;
        }
        
        if ($isValid) {
            // Check if password should be updated
            if (!empty($password)) {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE members SET 
                                    fname = ?, 
                                    lname = ?, 
                                    email = ?, 
                                    username = ?, 
                                    credit = ?, 
                                    role = ?,
                                    password = ?
                                    WHERE member_id = ?");
                
                $stmt->bind_param("ssssdssi", 
                    $fname, 
                    $lname, 
                    $email, 
                    $username, 
                    $credit, 
                    $role,
                    $hashedPassword,
                    $member_id
                );
            } else {
                // Don't update password
                $stmt = $conn->prepare("UPDATE members SET 
                                    fname = ?, 
                                    lname = ?, 
                                    email = ?, 
                                    username = ?, 
                                    credit = ?, 
                                    role = ?
                                    WHERE member_id = ?");
                
                $stmt->bind_param("ssssdsi", 
                    $fname, 
                    $lname, 
                    $email, 
                    $username, 
                    $credit, 
                    $role,
                    $member_id
                );
            }
            
            if ($stmt->execute()) {
                $successMsg = "Member updated successfully!";
                
                // Reload member data after update
                $stmt->close();
                $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
                $stmt->bind_param("i", $member_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $member = $result->fetch_assoc();
                }
                
                $stmt->close();
            } else {
                $errorMsg = "Error updating member: " . $stmt->error;
            }
        }
    }
    
    $conn->close();
} else {
    $errorMsg = "Could not connect to database.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Edit Member</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Edit Member</h2>
            <p>Update member details and access rights.</p>
        </div>
        
        <div class="admin-panel">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="admin_panel.php">Dashboard</a></li>
                    <li><a href="admin_venues.php">Manage Venues</a></li>
                    <li><a href="admin_members.php" class="active">Manage Members</a></li>
                    <li><a href="admin_credits.php">Credits Management</a></li>
                    <li><a href="admin_bookings.php">Booking Reports</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
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
                
                <?php if ($member): ?>
                    <div class="form-section">
                        <h2>Edit Member: <?php echo htmlspecialchars($member['fname'] ?? '') . ' ' . htmlspecialchars($member['lname'] ?? ''); ?></h2>
                        
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fname" class="form-label">First Name *</label>
                                        <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($member['fname'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lname" class="form-label">Last Name *</label>
                                        <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($member['lname'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($member['username'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="form-label">Password (leave blank to keep current)</label>
                                        <input type="password" id="password" name="password" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="credit" class="form-label">Credits</label>
                                        <input type="number" id="credit" name="credit" class="form-control" value="<?php echo htmlspecialchars($member['credit'] ?? 0); ?>" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="role" class="form-label">Role</label>
                                        <select id="role" name="role" class="form-control">
                                            <option value="Member" <?php echo (($member['role'] ?? '') === 'Member') ? 'selected' : ''; ?>>Member</option>
                                            <option value="Admin" <?php echo (($member['role'] ?? '') === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-buttons">
                                <a href="admin_members.php" class="btn-cancel">Cancel</a>
                                <button type="submit" name="save_member" class="btn-save">Save Member</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Member not found. <a href="admin_members.php">Return to member list</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>