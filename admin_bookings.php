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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Booking Management</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Skip to content link for keyboard users -->
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <header role="banner" aria-label="Site header">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main id="main-content" aria-label="Booking management">
        <div class="container admin-dashboard">
            <section class="admin-welcome" aria-labelledby="welcome-heading">
                <h1 id="welcome-heading">Booking Management</h1>
                <p>View and manage all bookings in the system.</p>
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
                        <li><a href="admin_credits.php">Credits Management</a></li>
                        <li><a href="admin_bookings.php" class="active" aria-current="page">Booking Reports</a></li>
                    </ul>
                </nav>
                
                <div class="admin-content" aria-labelledby="bookings-heading">
                    <h2 id="bookings-heading">All Bookings</h2>
                    
                    <form class="booking-filters" aria-labelledby="filter-heading">
                        <h3 id="filter-heading" class="sr-only">Filter Options</h3>
                        
                        <div class="filter-group">
                            <label for="status-filter">Filter by Status:</label>
                            <select id="status-filter" name="status">
                                <option value="all">All Bookings</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="date-filter">Filter by Date:</label>
                            <select id="date-filter" name="date">
                                <option value="all">All Dates</option>
                                <option value="today">Today</option>
                                <option value="this-week">This Week</option>
                                <option value="this-month">This Month</option>
                            </select>
                        </div>
                        
                        <button id="apply-filters" type="button" aria-controls="bookings-table">Apply Filters</button>
                    </form>
                    
                    <?php if (empty($bookings)): ?>
                        <p id="no-bookings-message">No bookings found.</p>
                    <?php else: ?>
                        <div class="table-responsive" aria-label="Bookings table" tabindex="0">
                            <table id="bookings-table" aria-live="polite">
                                <caption>List of all bookings with venue, member, and status information</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Venue</th>
                                        <th scope="col">Member</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
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
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>" role="status">
                                                <?php echo $booking['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($booking['status'] !== 'Cancelled'): ?>
                                                <form method="post" action="admin_bookings.php" class="cancel-form">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <button type="submit" name="cancel_booking" class="btn btn-cancel" 
                                                       aria-label="Cancel booking #<?php echo $booking['id']; ?> for <?php echo htmlspecialchars($booking['fname'] . ' ' . $booking['lname']); ?>"
                                                       data-confirm-message="Are you sure you want to cancel this booking?">
                                                       Cancel
                                                    </button>
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
    </main>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Client-side filtering with accessibility improvements
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const dateFilter = document.getElementById('date-filter');
            const applyButton = document.getElementById('apply-filters');
            const table = document.getElementById('bookings-table');
            
            // Function to announce filter results to screen readers
            function announceFilterResults(visibleCount, totalCount) {
                const liveRegion = document.createElement('div');
                liveRegion.setAttribute('aria-live', 'polite');
                liveRegion.setAttribute('role', 'status');
                liveRegion.classList.add('sr-only');
                liveRegion.textContent = `Showing ${visibleCount} of ${totalCount} bookings.`;
                
                document.body.appendChild(liveRegion);
                
                // Remove the live region after it's been announced
                setTimeout(() => {
                    document.body.removeChild(liveRegion);
                }, 1000);
            }
            
            applyButton.addEventListener('click', function() {
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;
                
                const rows = document.querySelectorAll('tbody tr');
                let visibleCount = 0;
                const totalCount = rows.length;
                
                rows.forEach(row => {
                    let showRow = true;
                    
                    // Status filtering
                    if (statusValue !== 'all') {
                        const rowStatus = row.getAttribute('data-status');
                        if (rowStatus !== statusValue) {
                            showRow = false;
                        }
                    }
                    
                    // Date filtering would go here if implemented
                    
                    row.style.display = showRow ? '' : 'none';
                    
                    if (showRow) {
                        visibleCount++;
                    }
                });
                
                // Announce the result to screen readers
                announceFilterResults(visibleCount, totalCount);
            });
            
            // Make the filters keyboard accessible
            statusFilter.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    applyButton.click();
                }
            });
            
            dateFilter.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    applyButton.click();
                }
            });
            
            // Improve the cancel confirmation
            const cancelForms = document.querySelectorAll('.cancel-form');
            
            cancelForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = form.querySelector('button[data-confirm-message]');
                    const message = button.getAttribute('data-confirm-message');
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>