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
$bookings = [];

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

// Get user's bookings from database
if (getDbConnection()) {
    // Get the current member ID
    $member_id = $_SESSION['member_id'];
    
    // Prepare SQL to get all bookings for this user
    $stmt = $conn->prepare("SELECT b.*, v.name as venue_name, v.location as venue_location, v.hourly_rate 
                           FROM sports_bookings b
                           JOIN venues v ON b.venue_id = v.id
                           WHERE b.user_id = ?
                           ORDER BY b.event_date DESC, b.start_time ASC");
    
    if ($stmt) {
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all bookings
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        $stmt->close();
    } else {
        $errorMsg = "Failed to retrieve bookings. Please try again later.";
        $success = false;
    }
    
    // Close the connection
    $conn->close();
}

// Process booking cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    if (getDbConnection()) {
        // Start a transaction
        $conn->begin_transaction();
        
        try {
            // Get booking details to verify it belongs to this user and get the cost
            $stmt = $conn->prepare("SELECT b.*, v.hourly_rate 
                                  FROM sports_bookings b
                                  JOIN venues v ON b.venue_id = v.id
                                  WHERE b.id = ? AND b.user_id = ? AND b.status != 'cancelled'");
            
            $stmt->bind_param("ii", $booking_id, $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Booking not found or already cancelled.");
            }
            
            $booking = $result->fetch_assoc();
            $stmt->close();
            
            // Calculate refund amount (full refund if cancellation is at least 24 hours before event)
            $event_datetime = strtotime($booking['event_date'] . ' ' . $booking['start_time']);
            $now = time();
            $hours_difference = ($event_datetime - $now) / 3600;
            
            $refund_percentage = ($hours_difference >= 24) ? 1.0 : 0.5; // 100% if more than or equals to 24 hours, 50% otherwise
            $booking_cost = $booking['hourly_rate'] * 2; // 2 hours per slot
            $refund_amount = $booking_cost * $refund_percentage;
            
            // Update booking status
            $stmt = $conn->prepare("UPDATE sports_bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->bind_param("i", $booking_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to cancel booking: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Refund credits to user
            if ($refund_amount > 0) {
                $stmt = $conn->prepare("UPDATE members SET credit = credit + ? WHERE member_id = ?");
                $stmt->bind_param("di", $refund_amount, $member_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to process refund: " . $stmt->error);
                }
                
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Set success message and redirect to refresh the page
            $_SESSION['booking_message'] = "Your booking has been cancelled. " . 
                                          ($refund_amount > 0 ? number_format($refund_amount, 2) . " credits have been refunded to your account." : "No refund was issued due to late cancellation.");
            
            header("Location: mybookings.php");
            exit;
        } 
        catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Error: " . $e->getMessage();
            $success = false;
        }
        
        // Close the connection
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - My Bookings</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/mybookings.css">
</head>

<body>
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <header role="banner" aria-label="Site header">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main id="main-content" aria-label="My bookings">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-12">
                    <h1><i class="fa fa-calendar" aria-hidden="true"></i> My Bookings</h1>
                    <p>View and manage all your venue reservations.</p>
                    
                    <?php if (isset($_SESSION['booking_message'])): ?>
                        <div class="alert alert-success" role="alert" aria-live="polite">
                            <?php 
                                echo $_SESSION['booking_message']; 
                                unset($_SESSION['booking_message']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger" role="alert" aria-live="assertive">
                            <?php echo $errorMsg; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <section aria-labelledby="filter-heading">
                        <h2 id="filter-heading" class="sr-only">Filter Options</h2>
                        <div class="filters">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="status-filter">Status:</label>
                                    <select id="status-filter" class="form-control" aria-label="Filter by booking status">
                                        <option value="all">All Bookings</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="sport-filter">Sport Type:</label>
                                    <select id="sport-filter" class="form-control" aria-label="Filter by sport type">
                                        <option value="all">All Sports</option>
                                        <option value="basketball">Basketball</option>
                                        <option value="volleyball">Volleyball</option>
                                        <option value="badminton">Badminton</option>
                                        <option value="soccer">Soccer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <fieldset>
                                        <legend>Date Range</legend>
                                        <div class="input-group">
                                            <label for="date-from" class="sr-only">From</label>
                                            <input type="date" id="date-from" class="form-control" aria-label="Start date">
                                            
                                            <label for="date-to" class="sr-only">To</label>
                                            <input type="date" id="date-to" class="form-control" aria-label="End date">
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-md-2">
                                    <button id="apply-filters" class="btn btn-primary w-100" aria-label="Apply filters">Apply</button>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <section aria-labelledby="bookings-heading">
                        <h2 id="bookings-heading" class="sr-only">Booking List</h2>
                        <?php if (empty($bookings)): ?>
                            <div class="empty-bookings" role="status">
                                <i class="fa fa-calendar-times-o" aria-hidden="true"></i>
                                <h3>No Bookings Found</h3>
                                <p>You haven't made any bookings yet.</p>
                                <a href="sports.php" class="btn btn-primary mt-3">Book a Sports Venue</a>
                            </div>
                        <?php else: ?>
                            <!-- Group bookings by date -->
                            <?php
                            $bookings_by_date = [];
                            foreach ($bookings as $booking) {
                                $date = $booking['event_date'];
                                if (!isset($bookings_by_date[$date])) {
                                    $bookings_by_date[$date] = [];
                                }
                                $bookings_by_date[$date][] = $booking;
                            }
                            
                            // Sort dates in descending order (newest first)
                            krsort($bookings_by_date);
                            ?>
                            
                            <div class="bookings-list" aria-live="polite">
                                <?php foreach ($bookings_by_date as $date => $date_bookings): ?>
                                    <div class="booking-date">
                                        <h3><?php echo date('l, F j, Y', strtotime($date)); ?></h3>
                                        <?php
                                        // Check if date is in the past
                                        $is_past = strtotime($date) < strtotime(date('Y-m-d'));
                                        if ($is_past) {
                                            echo '<span class="confirmation-badge badge badge-secondary" role="status">Past</span>';
                                        } else {
                                            echo '<span class="confirmation-badge badge badge-info" role="status">Upcoming</span>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <?php foreach ($date_bookings as $booking): ?>
                                        <article class="booking-card" data-status="<?php echo htmlspecialchars($booking['status']); ?>" data-sport="<?php echo htmlspecialchars($booking['sport_type']); ?>">
                                            <div class="booking-header">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <span class="mr-2">Booking #<?php echo $booking['id']; ?></span>
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
                                                        <span class="badge <?php echo $badge_class; ?>" role="status"><?php echo ucfirst($booking['status']); ?></span>
                                                    </div>
                                                    <div class="col-md-4 text-right">
                                                        <span><?php echo date('g:i A', strtotime($booking['start_time'])); ?> - <?php echo date('g:i A', strtotime($booking['end_time'])); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="booking-body">
                                                <dl class="booking-details">
                                                    <dt class="booking-label">Sport Type</dt>
                                                    <dd class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></dd>
                                                    
                                                    <dt class="booking-label">Venue</dt>
                                                    <dd class="detail-value"><?php echo htmlspecialchars($booking['venue_name']); ?></dd>
                                                    
                                                    <dt class="booking-label">Location</dt>
                                                    <dd class="detail-value"><?php echo htmlspecialchars($booking['venue_location']); ?></dd>
                                                    
                                                    <dt class="booking-label">Participants</dt>
                                                    <dd class="detail-value"><?php echo htmlspecialchars($booking['num_participants']); ?> person(s)</dd>
                                                    
                                                    <dt class="booking-label">Cost</dt>
                                                    <dd class="detail-value"><?php echo number_format($booking['hourly_rate'] * 2, 2); ?> credits</dd>
                                                    
                                                    <dt class="booking-label">Reserved On</dt>
                                                    <dd class="detail-value"><?php echo date('M j, Y', strtotime($booking['created_at'] ?? date('Y-m-d'))); ?></dd>
                                                </dl>
                                                
                                                <div class="booking-actions">
                                                    <?php if ($booking['status'] != 'cancelled'): ?>
                                                        <?php
                                                        // Check if booking can be cancelled
                                                        $event_datetime = strtotime($booking['event_date'] . ' ' . $booking['start_time']);
                                                        $now = time();
                                                        $can_cancel = $event_datetime > $now;
                                                        ?>
                                                        
                                                        <?php if ($can_cancel): ?>
                                                            <button class="btn btn-cancel" 
                                                                    onclick="showCancelModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['venue_name']); ?>', '<?php echo $booking['event_date']; ?>', '<?php echo $booking['start_time']; ?>')"
                                                                    aria-label="Cancel booking for <?php echo htmlspecialchars($booking['venue_name']); ?>">
                                                                <i class="fa fa-times" aria-hidden="true"></i> Cancel Booking
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <a href="bookingdetails.php?id=<?php echo $booking['id']; ?>" class="btn btn-details"
                                                       aria-label="View details for booking #<?php echo $booking['id']; ?>">
                                                        <i class="fa fa-eye" aria-hidden="true"></i> View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Cancellation Popup -->
    <div id="modal-backdrop" role="presentation"></div>
    <div id="cancel-modal" role="dialog" aria-labelledby="modal-title" aria-modal="true" hidden>
        <h4 id="modal-title">Confirm Cancellation</h4>
        <p>Are you sure you want to cancel your booking for <span id="modal-venue"></span> on <span id="modal-date"></span> at <span id="modal-time"></span>?</p>
        
        <div id="refund-policy">
            <h5>Refund Policy:</h5>
            <ul>
                <li>100% refund if cancelled at least 24 hours before the event</li>
                <li>50% refund if cancelled less than 24 hours before the event</li>
                <li>No refund for no-shows or cancellations after the event start time</li>
            </ul>
        </div>
        
        <form action="mybookings.php" method="post" id="cancel-form">
            <input type="hidden" name="booking_id" id="modal-booking-id">
            <input type="hidden" name="cancel_booking" value="1">
            
            <div class="text-right mt-4">
                <button type="button" class="btn btn-secondary" onclick="hideModal()">Never mind</button>
                <button type="submit" class="btn btn-danger">Yes, Cancel Booking</button>
            </div>
        </form>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Function to show the cancellation popup
        function showCancelModal(bookingId, venueName, eventDate, startTime) {
            document.getElementById('modal-booking-id').value = bookingId;
            document.getElementById('modal-venue').textContent = venueName;
            
            // Format the date
            let formattedDate = new Date(eventDate);
            let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('modal-date').textContent = formattedDate.toLocaleDateString('en-US', options);
            
            // Format the time
            let timeParts = startTime.split(':');
            let hours = parseInt(timeParts[0]);
            let minutes = timeParts[1];
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            document.getElementById('modal-time').textContent = hours + ':' + minutes + ' ' + ampm;
            
            // Show the popup
            document.getElementById('modal-backdrop').style.display = 'block';
            const modal = document.getElementById('cancel-modal');
            modal.style.display = 'block';
            modal.removeAttribute('hidden');
            
            // Set focus to the first button in the modal
            setTimeout(() => {
                modal.querySelector('button').focus();
            }, 100);
        }
        
        // Function to hide the popup
        function hideModal() {
            document.getElementById('modal-backdrop').style.display = 'none';
            const modal = document.getElementById('cancel-modal');
            modal.style.display = 'none';
            modal.setAttribute('hidden', 'hidden');
        }
        
        // Close popup if backdrop is clicked
        document.getElementById('modal-backdrop').addEventListener('click', hideModal);
        
        // Handle Escape key to close modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.getElementById('cancel-modal').style.display === 'block') {
                hideModal();
            }
        });
        
        // Filter functionality
        document.getElementById('apply-filters').addEventListener('click', function() {
            const statusFilter = document.getElementById('status-filter').value;
            const sportFilter = document.getElementById('sport-filter').value;
            const dateFromFilter = document.getElementById('date-from').value;
            const dateToFilter = document.getElementById('date-to').value;
            
            const bookingCards = document.querySelectorAll('.booking-card');
            let visibleCount = 0;
            const totalCount = bookingCards.length;
            
            bookingCards.forEach(card => {
                let show = true;
                
                // Apply status filter
                if (statusFilter !== 'all' && card.dataset.status !== statusFilter) {
                    show = false;
                }
                
                // Apply sport filter
                if (sportFilter !== 'all' && card.dataset.sport !== sportFilter) {
                    show = false;
                }
                
                // Show or hide the card
                card.style.display = show ? 'block' : 'none';
                if (show) visibleCount++;
            });
            
            // Announce results to screen readers
            const liveRegion = document.createElement('div');
            liveRegion.setAttribute('role', 'status');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.className = 'sr-only';
            liveRegion.textContent = `Showing ${visibleCount} of ${totalCount} bookings`;
            document.body.appendChild(liveRegion);
            
            setTimeout(() => {
                document.body.removeChild(liveRegion);
            }, 1000);
        });
    </script>
<script src="js/main.js"></script>
</body>
</html>
<?php
// Close database connection if still open
if ($conn) {
    $conn->close();
}
?>