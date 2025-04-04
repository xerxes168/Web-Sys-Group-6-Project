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
    
    // Define the config file path
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists
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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Booking Details</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/booking-details.css">
</head>

<body>
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    <header role="banner" aria-label="Site header">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main id="main-content" aria-label="Booking details">
        <div class="container mt-5 mb-5">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="Breadcrumb">
                        <a href="mybookings.php" class="btn btn-sm btn-back mb-3">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to My Bookings
                        </a>
                    </nav>
                    
                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger" role="alert" aria-live="assertive">
                            <?php echo $errorMsg; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($booking): ?>
                        <article class="booking-details-card">
                            <header class="booking-header">
                                <h1>Booking #<?php echo $booking['id']; ?></h1>
                                <p><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></p>
                                <?php
                                $badge_class = '';
                                $badge_text = '';
                                switch ($booking['status']) {
                                    case 'confirmed':
                                        $badge_class = 'badge-confirmed';
                                        $badge_text = 'Confirmed';
                                        break;
                                    case 'cancelled':
                                        $badge_class = 'badge-cancelled';
                                        $badge_text = 'Cancelled';
                                        break;
                                    default:
                                        $badge_class = 'badge-pending';
                                        $badge_text = 'Pending';
                                }
                                ?>
                                <span class="booking-badge <?php echo $badge_class; ?>" role="status" aria-label="Booking status: <?php echo $badge_text; ?>">
                                    <?php echo ucfirst($badge_text); ?>
                                </span>
                            </header>
                            
                            <div class="booking-body">
                                <section class="detail-section" aria-labelledby="sport-details-heading">
                                    <h2 id="sport-details-heading">Sport Details</h2>
                                    <dl class="detail-list">
                                        <dt class="detail-label">Sport Type</dt>
                                        <dd class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></dd>
                                        
                                        <dt class="detail-label">Participants</dt>
                                        <dd class="detail-value"><?php echo htmlspecialchars($booking['num_participants']); ?> person(s)</dd>
                                        
                                        <dt class="detail-label">Date</dt>
                                        <dd class="detail-value"><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></dd>
                                        
                                        <dt class="detail-label">Time</dt>
                                        <dd class="detail-value">
                                            <?php echo date('g:i A', strtotime($booking['start_time'])); ?> - 
                                            <?php echo date('g:i A', strtotime($booking['end_time'])); ?>
                                        </dd>
                                    </dl>
                                </section>
                                
                                <section class="detail-section" aria-labelledby="venue-info-heading">
                                    <h2 id="venue-info-heading">Venue Information</h2>
                                    
                                    <figure class="venue-image">
                                        <img src="img/venue-placeholder.jpg" alt="Image of <?php echo htmlspecialchars($booking['venue_name']); ?> venue">
                                    </figure>
                                    
                                    <dl class="detail-list">
                                        <dt class="detail-label">Venue Name</dt>
                                        <dd class="detail-value"><?php echo htmlspecialchars($booking['venue_name']); ?></dd>
                                        
                                        <dt class="detail-label">Location</dt>
                                        <dd class="detail-value"><?php echo htmlspecialchars($booking['venue_location']); ?></dd>
                                        
                                        <?php if (isset($booking['description']) && !empty($booking['description'])): ?>
                                        <dt class="detail-label">Description</dt>
                                        <dd class="detail-value"><?php echo nl2br(htmlspecialchars($booking['description'])); ?></dd>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($booking['amenities']) && !empty($booking['amenities'])): ?>
                                        <dt class="detail-label">Amenities</dt>
                                        <dd class="detail-value">
                                            <ul class="venue-amenities">
                                                <?php
                                                $amenities = explode(',', $booking['amenities']);
                                                foreach ($amenities as $amenity) {
                                                    echo '<li class="amenity-tag">' . htmlspecialchars(trim($amenity)) . '</li>';
                                                }
                                                ?>
                                            </ul>
                                        </dd>
                                        <?php endif; ?>
                                    </dl>
                                    
                                    <div class="map-container" aria-label="Venue location map">
                                        <div class="map-placeholder" role="img" aria-label="Map placeholder">
                                            <p>Map would be displayed here</p>
                                        </div>
                                    </div>
                                </section>
                                
                                <section class="detail-section" aria-labelledby="payment-details-heading">
                                    <h2 id="payment-details-heading">Payment Details</h2>
                                    <dl class="detail-list">
                                        <dt class="detail-label">Hourly Rate</dt>
                                        <dd class="detail-value"><?php echo number_format($booking['hourly_rate'], 2); ?> credits/hour</dd>
                                        
                                        <dt class="detail-label">Duration</dt>
                                        <dd class="detail-value">2 hours</dd>
                                        
                                        <dt class="detail-label">Total Cost</dt>
                                        <dd class="detail-value"><strong><?php echo number_format($booking['hourly_rate'] * 2, 2); ?> credits</strong></dd>
                                    </dl>
                                </section>
                                
                                <?php if (!empty($booking['special_requests'])): ?>
                                <section class="detail-section" aria-labelledby="special-requests-heading">
                                    <h2 id="special-requests-heading">Special Requests</h2>
                                    <div class="special-requests">
                                        <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                                    </div>
                                </section>
                                <?php endif; ?>
                                
                                <section class="detail-section" aria-labelledby="booking-status-heading">
                                    <h2 id="booking-status-heading">Booking Status</h2>
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
                                </section>
                                
                                <div class="booking-actions" aria-label="Booking actions">
                                    <div>
                                        <a href="mybookings.php" class="btn btn-back">
                                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
                                        </a>
                                    </div>
                                    
                                    <div>
                                        <button onclick="window.print();" class="btn btn-print">
                                            <i class="fa fa-print" aria-hidden="true"></i> Print
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
                                                    <i class="fa fa-times" aria-hidden="true"></i> Cancel Booking
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            Booking not found or you don't have permission to view it.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
]
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Add keyboard support for buttons
        document.addEventListener('keydown', function(event) {
            if(event.key === 'Enter' || event.key === ' ') {
                if(document.activeElement.tagName === 'BUTTON') {
                    event.preventDefault();
                    document.activeElement.click();
                }
            }
        });
        
        // Announce page title to screen readers
        document.addEventListener('DOMContentLoaded', function() {
            const title = document.querySelector('h1').textContent;
            const liveRegion = document.createElement('div');
            liveRegion.setAttribute('role', 'status');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.className = 'sr-only';
            liveRegion.textContent = "Viewing " + title;
            document.body.appendChild(liveRegion);
            
            setTimeout(function() {
                document.body.removeChild(liveRegion);
            }, 1000);
        });
    </script>
<script src="js/main.js"></script>
</body>
</html>