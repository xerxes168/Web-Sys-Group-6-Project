<?php
// Start the session
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$errorMsg = "";
$success = true;
$conn = null;
$booking = null;

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $success, $conn;
    
    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        $errorMsg .= "<li>Database configuration file not found.</li>";
        $success = false;
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg .= "<li>Failed to parse database config file.</li>";
        $success = false;
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
        $errorMsg .= "<li>Host Connection failed: " . $conn->connect_error . "</li>";
        $success = false;
        return false;
    }
    
    return true;
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    header("Location: mybookings.php");
    exit;
}

// Get booking details from database
if (getDbConnection()) {
    // Get the current member ID
    $member_id = $_SESSION['member_id'];
    
    // Prepare SQL to get the booking details
    $stmt = $conn->prepare("SELECT b.*, v.name as venue_name, v.location as venue_location, v.hourly_rate, v.description, v.amenities 
                           FROM sports_bookings b
                           JOIN venues v ON b.venue_id = v.id
                           WHERE b.id = ? AND b.user_id = ?");
    
    if ($stmt) {
        // Bind parameters and execute
        $stmt->bind_param("ii", $booking_id, $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If booking not found or doesn't belong to user
        if ($result->num_rows === 0) {
            header("Location: mybookings.php");
            exit;
        }
        
        // Fetch booking details
        $booking = $result->fetch_assoc();
        $stmt->close();
    } else {
        $errorMsg = "Failed to retrieve booking details. Please try again later.";
        $success = false;
    }
    
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - Booking Details</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        .booking-details-card {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .booking-header {
            background: linear-gradient(to right, #5f52b0, #ff589e);
            color: white;
            padding: 20px;
            position: relative;
        }
        .booking-header h3 {
            margin-bottom: 5px;
        }
        .booking-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
        }
        .badge-confirmed {
            background-color: #28a745;
            color: white;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .booking-body {
            padding: 25px;
            background-color: #fff;
        }
        .detail-section {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-section h4 {
            color: #5f52b0;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            width: 30%;
            font-weight: 600;
            color: #555;
        }
        .detail-value {
            width: 70%;
        }
        .venue-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .amenity-tag {
            background-color: #f1f1f1;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }
        .booking-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .special-requests {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .booking-timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #5f52b0;
            z-index: 1;
        }
        .timeline-item:after {
            content: '';
            position: absolute;
            left: -24px;
            top: 12px;
            width: 1px;
            height: 100%;
            background-color: #ddd;
        }
        .timeline-item:last-child:after {
            display: none;
        }
        .timeline-date {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }
        .timeline-content {
            font-weight: 500;
        }
        .status-separator {
            display: inline-block;
            margin: 0 5px;
            font-weight: normal;
            color: #888;
        }
        .venue-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .venue-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .map-container {
            width: 100%;
            height: 300px;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 20px;
        }
        .map-placeholder {
            width: 100%;
            height: 100%;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            font-style: italic;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        .btn-print {
            background-color: #5f52b0;
            color: white;
            border: none;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include "inc/nav.inc.php"; ?>

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-12">
                <a href="mybookings.php" class="btn btn-sm btn-back mb-3">
                    <i class="fa fa-arrow-left"></i> Back to My Bookings
                </a>
                
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger">
                        <?php echo $errorMsg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($booking): ?>
                    <div class="booking-details-card">
                        <div class="booking-header">
                            <h3>Booking #<?php echo $booking['id']; ?></h3>
                            <p><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></p>
                            <?php
                            $badge_class = '';
                            switch ($booking['status']) {
                                case 'confirmed':
                                    $badge_class = 'badge-confirmed';
                                    break;
                                case 'cancelled':
                                    $badge_class = 'badge-cancelled';
                                    break;
                                default:
                                    $badge_class = 'badge-pending';
                            }
                            ?>
                            <span class="booking-badge <?php echo $badge_class; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                        <div class="booking-body">
                            <div class="detail-section">
                                <h4>Sport Details</h4>
                                <div class="detail-row">
                                    <div class="detail-label">Sport Type</div>
                                    <div class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Participants</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['num_participants']); ?> person(s)</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Date</div>
                                    <div class="detail-value"><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Time</div>
                                    <div class="detail-value">
                                        <?php echo date('g:i A', strtotime($booking['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($booking['end_time'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4>Venue Information</h4>
                                <!-- Placeholder for venue image - replace with actual image when available -->
                                <div class="venue-image">
                                    <img src="img/venue-placeholder.jpg" alt="Venue Image">
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Venue Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['venue_name']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Location</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($booking['venue_location']); ?></div>
                                </div>
                                <?php if (isset($booking['description']) && !empty($booking['description'])): ?>
                                <div class="detail-row">
                                    <div class="detail-label">Description</div>
                                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($booking['description'])); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($booking['amenities']) && !empty($booking['amenities'])): ?>
                                <div class="detail-row">
                                    <div class="detail-label">Amenities</div>
                                    <div class="detail-value">
                                        <div class="venue-amenities">
                                            <?php
                                            $amenities = explode(',', $booking['amenities']);
                                            foreach ($amenities as $amenity) {
                                                echo '<span class="amenity-tag">' . htmlspecialchars(trim($amenity)) . '</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Map placeholder - replace with actual map when available -->
                                <div class="map-container">
                                    <div class="map-placeholder">
                                        <p>Map would be displayed here</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4>Payment Details</h4>
                                <div class="detail-row">
                                    <div class="detail-label">Hourly Rate</div>
                                    <div class="detail-value"><?php echo number_format($booking['hourly_rate'], 2); ?> credits/hour</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Duration</div>
                                    <div class="detail-value">2 hours</div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Total Cost</div>
                                    <div class="detail-value"><strong><?php echo number_format($booking['hourly_rate'] * 2, 2); ?> credits</strong></div>
                                </div>
                            </div>
                            
                            <?php if (!empty($booking['special_requests'])): ?>
                            <div class="detail-section">
                                <h4>Special Requests</h4>
                                <div class="special-requests">
                                    <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-section">
                                <h4>Booking Status</h4>
                                <div class="booking-timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-date">
                                            <?php echo date('M j, Y h:i A', strtotime($booking['created_at'] ?? date('Y-m-d H:i:s'))); ?>
                                        </div>
                                        <div class="timeline-content">
                                            Booking Created
                                        </div>
                                    </div>
                                    
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date">
                                            <?php echo date('M j, Y h:i A', strtotime($booking['confirmed_at'] ?? date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($booking['created_at'] ?? date('Y-m-d H:i:s')))))); ?>
                                        </div>
                                        <div class="timeline-content">
                                            Booking Confirmed
                                        </div>
                                    </div>
                                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date">
                                            <?php echo date('M j, Y h:i A', strtotime($booking['cancelled_at'] ?? date('Y-m-d H:i:s', strtotime('+1 day', strtotime($booking['created_at'] ?? date('Y-m-d H:i:s')))))); ?>
                                        </div>
                                        <div class="timeline-content">
                                            Booking Cancelled
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status'] == 'confirmed' && strtotime($booking['event_date']) > time()): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-date">
                                            <?php echo date('M j, Y', strtotime($booking['event_date'])); ?>
                                            <?php echo date('h:i A', strtotime($booking['start_time'])); ?>
                                        </div>
                                        <div class="timeline-content">
                                            Scheduled Start Time
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="booking-actions">
                                <div>
                                    <a href="mybookings.php" class="btn btn-back">
                                        <i class="fa fa-arrow-left"></i> Back
                                    </a>
                                </div>
                                
                                <div>
                                    <button onclick="window.print();" class="btn btn-print">
                                        <i class="fa fa-print"></i> Print
                                    </button>
                                    
                                    <?php if ($booking['status'] != 'cancelled'): ?>
                                        <?php
                                        // Calculate if it's still possible to cancel (e.g., not in the past)
                                        $event_datetime = strtotime($booking['event_date'] . ' ' . $booking['start_time']);
                                        $now = time();
                                        $can_cancel = $event_datetime > $now;
                                        ?>
                                        
                                        <?php if ($can_cancel): ?>
                                            <a href="my-bookings.php?cancel=<?php echo $booking['id']; ?>" class="btn btn-cancel">
                                                <i class="fa fa-times"></i> Cancel Booking
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Booking not found or you don't have permission to view it.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>