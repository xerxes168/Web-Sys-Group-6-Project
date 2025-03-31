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
        // Bind parameters and execute
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

// Process booking cancellation if requested
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
            
            $refund_percentage = ($hours_difference >= 24) ? 1.0 : 0.5; // 100% if >= 24 hours, 50% otherwise
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
            // Rollback on error
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
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - My Bookings</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        .booking-card {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .booking-card:hover {
            transform: translateY(-5px);
        }
        .booking-header {
            background: linear-gradient(to right, #5f52b0, #ff589e);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
        }
        .booking-body {
            padding: 20px;
            background-color: #fff;
        }
        .booking-details {
            margin-bottom: 15px;
        }
        .booking-details .row {
            margin-bottom: 8px;
        }
        .booking-label {
            font-weight: 600;
            color: #555;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
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
        .booking-actions {
            margin-top: 15px;
            text-align: right;
        }
        .booking-actions button, .booking-actions a {
            margin-left: 10px;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        .btn-details {
            background-color: #5f52b0;
            color: white;
            border: none;
        }
        .empty-bookings {
            text-align: center;
            padding: 50px;
            background: #f9f9f9;
            border-radius: 10px;
            margin-top: 20px;
        }
        .empty-bookings i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        #modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        #cancel-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 500px;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            z-index: 1001;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .booking-date {
            background: #f5f5f5;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: 600;
            color: #333;
        }
        .confirmation-badge {
            display: inline-block;
            margin-left: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include "inc/nav.inc.php"; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-calendar"></i> My Bookings</h2>
                <p>View and manage all your venue reservations.</p>
                
                <?php if (isset($_SESSION['booking_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            echo $_SESSION['booking_message']; 
                            unset($_SESSION['booking_message']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger">
                        <?php echo $errorMsg; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="filters">
                    <div class="row">
                        <div class="col-md-3">
                            <select id="status-filter" class="form-control">
                                <option value="all">All Bookings</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="sport-filter" class="form-control">
                                <option value="all">All Sports</option>
                                <option value="basketball">Basketball</option>
                                <option value="volleyball">Volleyball</option>
                                <option value="badminton">Badminton</option>
                                <option value="soccer">Soccer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Date Range</span>
                                </div>
                                <input type="date" id="date-from" class="form-control">
                                <input type="date" id="date-to" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button id="apply-filters" class="btn btn-primary w-100">Apply</button>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="empty-bookings">
                        <i class="fa fa-calendar-times-o"></i>
                        <h4>No Bookings Found</h4>
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
                    
                    <?php foreach ($bookings_by_date as $date => $date_bookings): ?>
                        <div class="booking-date">
                            <?php echo date('l, F j, Y', strtotime($date)); ?>
                            <?php
                            // Check if date is in the past
                            $is_past = strtotime($date) < strtotime(date('Y-m-d'));
                            if ($is_past) {
                                echo '<span class="confirmation-badge badge badge-secondary">Past</span>';
                            } else {
                                echo '<span class="confirmation-badge badge badge-info">Upcoming</span>';
                            }
                            ?>
                        </div>
                        
                        <?php foreach ($date_bookings as $booking): ?>
                            <div class="booking-card" data-status="<?php echo htmlspecialchars($booking['status']); ?>" data-sport="<?php echo htmlspecialchars($booking['sport_type']); ?>">
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
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span><?php echo date('g:i A', strtotime($booking['start_time'])); ?> - <?php echo date('g:i A', strtotime($booking['end_time'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-body">
                                    <div class="booking-details">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="booking-label">Sport Type</div>
                                                <div><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="booking-label">Venue</div>
                                                <div><?php echo htmlspecialchars($booking['venue_name']); ?></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="booking-label">Location</div>
                                                <div><?php echo htmlspecialchars($booking['venue_location']); ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="booking-label">Participants</div>
                                                <div><?php echo htmlspecialchars($booking['num_participants']); ?> person(s)</div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="booking-label">Cost</div>
                                                <div><?php echo number_format($booking['hourly_rate'] * 2, 2); ?> credits</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="booking-label">Reserved On</div>
                                                <div><?php echo date('M j, Y', strtotime($booking['created_at'] ?? date('Y-m-d'))); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-actions">
                                        <?php if ($booking['status'] != 'cancelled'): ?>
                                            <?php
                                            // Calculate if it's still possible to cancel (e.g., not in the past)
                                            $event_datetime = strtotime($booking['event_date'] . ' ' . $booking['start_time']);
                                            $now = time();
                                            $can_cancel = $event_datetime > $now;
                                            ?>
                                            
                                            <?php if ($can_cancel): ?>
                                                <button class="btn btn-cancel" onclick="showCancelModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['venue_name']); ?>', '<?php echo $booking['event_date']; ?>', '<?php echo $booking['start_time']; ?>')">
                                                    <i class="fa fa-times"></i> Cancel Booking
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <a href="bookingdetails.php?id=<?php echo $booking['id']; ?>" class="btn btn-details">
                                            <i class="fa fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Cancellation Modal -->
    <div id="modal-backdrop"></div>
    <div id="cancel-modal">
        <h4>Confirm Cancellation</h4>
        <p>Are you sure you want to cancel your booking for <span id="modal-venue"></span> on <span id="modal-date"></span> at <span id="modal-time"></span>?</p>
        
        <div id="refund-policy">
            <p><strong>Refund Policy:</strong></p>
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
        // Function to show the cancellation modal
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
            hours = hours ? hours : 12; // Hour '0' should be '12'
            document.getElementById('modal-time').textContent = hours + ':' + minutes + ' ' + ampm;
            
            // Show the modal
            document.getElementById('modal-backdrop').style.display = 'block';
            document.getElementById('cancel-modal').style.display = 'block';
        }
        
        // Function to hide the modal
        function hideModal() {
            document.getElementById('modal-backdrop').style.display = 'none';
            document.getElementById('cancel-modal').style.display = 'none';
        }
        
        // Close modal if backdrop is clicked
        document.getElementById('modal-backdrop').addEventListener('click', hideModal);
        
        // Filter functionality
        document.getElementById('apply-filters').addEventListener('click', function() {
            const statusFilter = document.getElementById('status-filter').value;
            const sportFilter = document.getElementById('sport-filter').value;
            const dateFromFilter = document.getElementById('date-from').value;
            const dateToFilter = document.getElementById('date-to').value;
            
            const bookingCards = document.querySelectorAll('.booking-card');
            
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
                
                // TODO: Implement date range filtering
                // This would need additional attributes on the card or a more complex method
                
                // Show or hide the card
                card.style.display = show ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
<?php
// Close database connection if still open
if ($conn) {
    $conn->close();
}
?>