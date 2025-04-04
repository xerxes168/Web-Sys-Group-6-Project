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
$conn = null;

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;
    
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

// Get statistics for dashboard
$stats = [
    'total_venues' => 0,
    'total_members' => 0,
    'total_bookings' => 0,
    'recent_bookings' => []
];

if (getDbConnection()) {
    // Get total number of venues
    $result = $conn->query("SELECT COUNT(*) as total FROM venues");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_venues'] = $row['total'];
    }
    
    // Get total number of members
    $result = $conn->query("SELECT COUNT(*) as total FROM members WHERE role = 'Member'");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_members'] = $row['total'];
    }
    
    // Get total number of bookings
    $result = $conn->query("SELECT COUNT(*) as total FROM sports_bookings");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_bookings'] = $row['total'];
    }
    
    // Get recent bookings
    $stmt = $conn->prepare("SELECT b.id, b.event_date, b.start_time, v.name as venue_name, 
                          m.fname, m.lname, m.email, b.status 
                          FROM sports_bookings b 
                          JOIN venues v ON b.venue_id = v.id 
                          JOIN members m ON b.user_id = m.member_id 
                          ORDER BY b.created_at DESC LIMIT 5");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $stats['recent_bookings'][] = $row;
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Admin Panel</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Welcome to Admin Panel</h2>
            <p>Manage venues, users, and bookings from this central dashboard.</p>
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
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Venues</h3>
                <div class="number"><?php echo $stats['total_venues']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Members</h3>
                <div class="number"><?php echo $stats['total_members']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="number"><?php echo $stats['total_bookings']; ?></div>
            </div>
        </div>
        
        <div class="admin-panel">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="admin_panel.php" class="active">Dashboard</a></li>
                    <li><a href="admin_venues.php">Manage Venues</a></li>
                    <li><a href="admin_members.php">Manage Members</a></li>
                    <li><a href="admin_credits.php">Credits Management</a></li>
                    <li><a href="admin_bookings.php">Booking Reports</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <div class="admin-header">
                    <h1>Quick Actions</h1>
                    <div class="admin-action-buttons">
                        <a href="admin_venues.php?action=add">Add New Venue</a>
                        <a href="admin_credits.php?action=manage">Manage Credits</a>
                    </div>
                </div>
                
                <div class="recent-bookings">
                    <h2>Recent Bookings</h2>
                    <?php if (empty($stats['recent_bookings'])): ?>
                        <p>No recent bookings found.</p>
                    <?php else: ?>
                        <table class="booking-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Member</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent_bookings'] as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($booking['event_date'])); ?>
                                        <?php echo date('g:i A', strtotime($booking['start_time'])); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['venue_name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['fname'] . ' ' . $booking['lname']); ?><br>
                                        <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td class="status-<?php echo strtolower($booking['status']); ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>