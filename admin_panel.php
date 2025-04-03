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
    <style>
        .admin-dashboard {
            padding: 20px;
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .stat-card h3 {
            color: #5f52b0;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 2rem;
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
        }
        .admin-header h1 {
            color: #5f52b0;
            margin: 0;
        }
        .admin-action-buttons {
            display: flex;
            gap: 10px;
        }
        .admin-action-buttons a {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #5f52b0;
            transition: background-color 0.3s;
        }
        .admin-action-buttons a:hover {
            background-color: #4a4098;
        }
        .recent-bookings {
            margin-top: 30px;
        }
        .booking-table {
            width: 100%;
            border-collapse: collapse;
        }
        .booking-table th, .booking-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .booking-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .booking-table tr:hover {
            background-color: #f8f9fa;
        }
        .status-confirmed {
            color: #28a745;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
        .admin-welcome {
            background: linear-gradient(to right, #5f52b0, #ff589e);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .admin-welcome h2 {
            margin-top: 0;
        }
    </style>
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
        
        <!-- Stats Overview -->
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
        
        <!-- Admin Panel -->
        <div class="admin-panel">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="admin_panel.php" class="active">Dashboard</a></li>
                    <li><a href="admin_venues.php">Manage Venues</a></li>
                    <li><a href="admin_members.php">Manage Members</a></li>
                    <li><a href="admin_credits.php">Credits Management</a></li>
                    <li><a href="admin_bookings.php">Booking Reports</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
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
                
                <!-- Recent Bookings -->
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