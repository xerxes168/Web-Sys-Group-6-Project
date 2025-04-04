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
$bookings = [];
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
        // Handle cancel booking
        if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
            $booking_id = intval($_POST['booking_id']);
            
            // Update booking status to cancelled
            $stmt = $conn->prepare("UPDATE sports_bookings SET status = 'Cancelled' WHERE id = ?");
            $stmt->bind_param("i", $booking_id);
            
            if ($stmt->execute()) {
                $successMsg = "Booking #" . $booking_id . " has been cancelled successfully.";
            } else {
                $errorMsg = "Error cancelling booking: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        // Load all bookings
        $stmt = $conn->prepare("SELECT b.id, b.event_date, b.start_time, b.end_time, b.status, 
                              v.name as venue_name, v.location as venue_location, 
                              m.fname, m.lname, m.email, m.member_id
                              FROM sports_bookings b 
                              JOIN venues v ON b.venue_id = v.id 
                              JOIN members m ON b.user_id = m.member_id 
                              ORDER BY b.event_date DESC, b.start_time DESC");
        
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
} else {
    // Initial page load
    if (getDbConnection()) {
        // Load all bookings
        $stmt = $conn->prepare("SELECT b.id, b.event_date, b.start_time, b.end_time, b.status, 
                              v.name as venue_name, v.location as venue_location, 
                              m.fname, m.lname, m.email, m.member_id
                              FROM sports_bookings b 
                              JOIN venues v ON b.venue_id = v.id 
                              JOIN members m ON b.user_id = m.member_id 
                              ORDER BY b.event_date DESC, b.start_time DESC");
        
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
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
    <title>HoopSpaces - Booking Management</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Booking Management</h2>
            <p>View and manage all bookings in the system.</p>
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
                    <li><a href="admin_credits.php">Credits Management</a></li>
                    <li><a href="admin_bookings.php" class="active">Booking Reports</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <div class="admin-header">
                    <h1>All Bookings</h1>
                </div>
                
                <div class="booking-filters">
                    <label for="status-filter">Filter by Status:</label>
                    <select id="status-filter">
                        <option value="all">All Bookings</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Pending">Pending</option>
                    </select>
                    
                    <label for="date-filter">Filter by Date:</label>
                    <select id="date-filter">
                        <option value="all">All Dates</option>
                        <option value="today">Today</option>
                        <option value="this-week">This Week</option>
                        <option value="this-month">This Month</option>
                    </select>
                    
                    <button id="apply-filters">Apply Filters</button>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Venue</th>
                                    <th>Member</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr data-status="<?php echo $booking['status']; ?>">
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($booking['event_date'])); ?></td>
                                    <td>
                                        <?php echo date('g:i A', strtotime($booking['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($booking['end_time'])); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['venue_name']); ?><br>
                                        <small><?php echo htmlspecialchars($booking['venue_location']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['fname'] . ' ' . $booking['lname']); ?><br>
                                        <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td class="status-<?php echo strtolower($booking['status']); ?>">
                                        <?php echo $booking['status']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($booking['status'] !== 'Cancelled'): ?>
                                            <form method="post" action="admin_bookings.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="cancel_booking" class="btn btn-cancel">Cancel</button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Client-side filtering
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const dateFilter = document.getElementById('date-filter');
            const applyButton = document.getElementById('apply-filters');
            
            applyButton.addEventListener('click', function() {
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;
                
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    let showRow = true;
                    
                    // Status filtering
                    if (statusValue !== 'all') {
                        const rowStatus = row.getAttribute('data-status');
                        if (rowStatus !== statusValue) {
                            showRow = false;
                        }
                    }
                    row.style.display = showRow ? '' : 'none';
                });
            });
        });
    </script>
</body>
</html>