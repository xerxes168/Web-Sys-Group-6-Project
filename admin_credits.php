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

// Determine if we're in the manage credits mode
$manageCredits = isset($_GET['action']) && $_GET['action'] == 'manage';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Credits Management</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f7fa;
        }
        .admin-dashboard {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-welcome {
            background: linear-gradient(to right, #5f52b0, #ff589e);
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-welcome h2 {
            margin-top: 0;
            font-weight: 600;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .stat-card h3 {
            color: #5f52b0;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }
        .admin-panel {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-sidebar {
            width: 250px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-sidebar li {
            margin-bottom: 10px;
        }
        .admin-sidebar a {
            display: block;
            padding: 10px 15px;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background-color: #5f52b0;
            color: white;
        }
        .admin-sidebar a.active {
            background-color: #5f52b0;
            font-weight: 600;
        }
        .admin-content {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .admin-header h1 {
            color: #333;
            margin: 0;
            font-size: 1.8rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        table th {
            background-color: #f9f9f9;
            color: #333;
            font-weight: 600;
        }
        .status-confirmed {
            color: #28a745;
            font-weight: 600;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: 600;
        }
        .btn-manage, .btn-add-credits, .btn-manage-credits {
            display: inline-block;
            background-color: #5f52b0;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-manage:hover, .btn-add-credits:hover, .btn-manage-credits:hover {
            background-color: #4a4098;
            color: white;
            text-decoration: none;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
        }
        .form-section {
            background-color: #f9f9fb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }
        .member-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .member-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .member-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .member-email {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .member-credits {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 15px;
        }
        .member-credits .label {
            font-size: 0.8rem;
            color: #666;
        }
        .member-credits .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5f52b0;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
    </style>
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
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <div class="admin-header">
                    <h1>Credits Management</h1>
                    <?php if (!$manageCredits): ?>
                        <a href="admin_credits.php?action=manage" class="btn-manage">Manage Credits</a>
                    <?php endif; ?>
                </div>
                
                <?php if ($manageCredits): ?>
                    <!-- Add Credits Form -->
                    <div class="form-section">
                        <h2>Add Credits to Member Account</h2>
                        <form method="post" action="admin_credits.php?action=manage">
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
                                <a href="admin_credits.php" class="btn-cancel">Cancel</a>
                                <button type="submit" name="add_credits" class="btn-add-credits">Add Credits</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Top Members by Credits -->
                    <h2>Member Credits Overview</h2>
                    <div class="member-cards">
                        <?php 
                        // Sort members by credit balance (highest first)
                        usort($members, function($a, $b) {
                            return $b['credit'] - $a['credit'];
                        });
                        
                        // Show only top 6
                        $topMembers = array_slice($members, 0, 6);
                        
                        foreach ($topMembers as $member): 
                        ?>
                            <div class="member-card">
                                <div class="member-name"><?php echo htmlspecialchars($member['fname'] . ' ' . $member['lname']); ?></div>
                                <div class="member-email"><?php echo htmlspecialchars($member['email']); ?></div>
                                <div class="member-credits">
                                    <div class="label">CREDIT BALANCE</div>
                                    <div class="value"><?php echo number_format($member['credit'], 2); ?></div>
                                </div>
                                <a href="admin_credits.php?action=manage&member=<?php echo $member['member_id']; ?>" class="btn-manage-credits">Manage Credits</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>