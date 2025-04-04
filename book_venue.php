<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
// Start session and require database connection
session_start();

function getDbConnection()
{
    global $errorMsg, $success;

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

    return $conn;
}

// Connect to database
$conn = getDbConnection();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

// Get and validate URL parameters
$venue_id = isset($_GET['venue_id']) ? intval($_GET['venue_id']) : 0;
$sport_type = isset($_GET['sport_type']) ? $_GET['sport_type'] : '';

if ($venue_id <= 0 || empty($sport_type)) {
    header("Location: viewSports.php");
    exit;
}

// Format sport name for display
$sport_name = ucfirst($sport_type);

// Define time slots
$time_slots = [
    '08:00-10:00' => '8:00 AM - 10:00 AM',
    '10:00-12:00' => '10:00 AM - 12:00 PM',
    '14:00-16:00' => '2:00 PM - 4:00 PM',
    '16:00-18:00' => '4:00 PM - 6:00 PM',
    '18:00-20:00' => '6:00 PM - 8:00 PM',
];

// Initialize variables
$errorMsg = "";
$venue = null;
$user_credit = 0;

// Get venue details
$stmt = $conn->prepare("SELECT id, name, location, capacity, hourly_rate, description, sport_type
                      FROM venues 
                      WHERE id = ? AND suitable_for_sports = 1");
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $venue = $result->fetch_assoc();

    // Convert both strings to lowercase for case-insensitive comparison
    $venue_sport_type = strtolower(trim($venue['sport_type']));
    $requested_sport_type = strtolower(trim($sport_type));

// Validate that the requested sport_type matches the venue's supported sport
    if ($venue_sport_type !== $requested_sport_type) {
        // Redirect or show error
        header("Location: venues.php?sport_type=" . urlencode($venue['sport_type']) . "&error=invalid_sport");
        exit;
    }
} else {
    header("Location: viewSports.php");
    exit;
}
$stmt->close();

// Get user credit balance
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT credit FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $user_credit = $user_data['credit'];
}
$stmt->close();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';
    $time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';
    $num_participants = isset($_POST['num_participants']) ? intval($_POST['num_participants']) : 0;
    $special_requests = isset($_POST['special_requests']) ? $_POST['special_requests'] : '';

    // Validation
    if (empty($event_date) || strtotime($event_date) < strtotime(date('Y-m-d'))) {
        $errorMsg .= "<li>Please select a valid future date.</li>";
    }

    if (empty($time_slot) || !array_key_exists($time_slot, $time_slots)) {
        $errorMsg .= "<li>Please select a valid time slot.</li>";
    }

    if ($num_participants <= 0 || $num_participants > $venue['capacity']) {
        $errorMsg .= "<li>Number of participants must be between 1 and " . $venue['capacity'] . ".</li>";
    }

    // Calculate booking cost (2 hours per slot)
    $booking_cost = $venue['hourly_rate'] * 2;

    // Check if user has enough credit
    if ($user_credit < $booking_cost) {
        $errorMsg .= "<li>Insufficient credits. You need $booking_cost credits, but you only have $user_credit.</li>";
    }

    // If validation passes, process the booking
    if (empty($errorMsg)) {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Extract start and end times from time slot
            list($start_time, $end_time) = explode('-', $time_slot);

            // Check venue availability
            $stmt = $conn->prepare("SELECT id FROM sports_bookings 
                                  WHERE venue_id = ? 
                                  AND event_date = ? 
                                  AND status != 'cancelled'
                                  AND (
                                     (? BETWEEN start_time AND end_time) OR
                                     (? BETWEEN start_time AND end_time) OR
                                     (start_time BETWEEN ? AND ?) OR
                                     (end_time BETWEEN ? AND ?)
                                  )");

            $stmt->bind_param(
                "isssssss",
                $venue_id,
                $event_date,
                $start_time,
                $end_time,
                $start_time,
                $end_time,
                $start_time,
                $end_time
            );

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                throw new Exception("This venue is already booked for the selected date and time.");
            }
            $stmt->close();

            // Deduct credits from user
            $stmt = $conn->prepare("UPDATE members SET credit = credit - ? WHERE member_id = ?");
            $stmt->bind_param("di", $booking_cost, $member_id);
            $stmt->execute();
            $stmt->close();

            // Create booking record
            $stmt = $conn->prepare("INSERT INTO sports_bookings 
                                  (user_id, venue_id, sport_type, num_participants, 
                                  event_date, start_time, end_time, special_requests, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");

            $stmt->bind_param(
                "iisissss",
                $member_id,
                $venue_id,
                $sport_type,
                $num_participants,
                $event_date,
                $start_time,
                $end_time,
                $special_requests
            );

            $stmt->execute();
            $booking_id = $stmt->insert_id;
            $stmt->close();

            // Create transaction record
            $description = "Booking for " . $sport_type . " at " . $venue['name'] . " on " . $event_date;
            $stmt = $conn->prepare("INSERT INTO transactions 
                                  (member_id, booking_id, amount, type, description) 
                                  VALUES (?, ?, ?, 'debit', ?)");

            $stmt->bind_param("iids", $member_id, $booking_id, $booking_cost, $description);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();

            // Store booking details in session for confirmation page
            $_SESSION['booking_success'] = true;
            $_SESSION['booking_id'] = $booking_id;
            $_SESSION['booking_cost'] = $booking_cost;
            $_SESSION['remaining_credits'] = $user_credit - $booking_cost;

            // Redirect to confirmation page
            header("Location: booking_confirmation.php");
            exit;

        } catch (Exception $e) {
            // Rollback in case of error
            $conn->rollback();
            $errorMsg .= "<li>" . $e->getMessage() . "</li>";
        }
    }
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Book Venue</title>
    <?php include "inc/head.inc.php"; ?>

    <style>
        .booking-container {
                max-width: 1200px !important;
                padding: 60px 0 !important;
                margin: 0 auto !important;
                display: block !important;
                /* If flexbox is not needed */
            }

            .booking-card {
                background-color: #fff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                margin-bottom: 30px;
                height: 100%;
            }

            .venue-details {
                padding: 25px;
                border-bottom: 1px solid #f0f0f0;
            }

            .venue-name {
                font-size: 22px;
                margin-bottom: 10px;
                color: #333;
                font-weight: 600;
            }

            .venue-location {
                color: #777;
                margin-bottom: 15px;
                font-size: 14px;
            }

            .venue-location i {
                margin-right: 5px;
                color: #f4bc51;
            }

            .venue-description {
                margin-bottom: 20px;
                color: #555;
                line-height: 1.6;
            }

            .venue-amenities {
                margin-top: 20px;
            }

            .venue-amenities span {
                display: inline-block;
                background-color: #f8f9fa;
                padding: 6px 12px;
                margin-right: 8px;
                margin-bottom: 8px;
                border-radius: 20px;
                font-size: 13px;
                color: #555;
            }

            .venue-price {
                font-size: 18px;
                font-weight: 700;
                color: #f4bc51;
                margin-top: 20px;
                padding-top: 15px;
                border-top: 1px solid #f0f0f0;
            }

            .booking-form {
                padding: 25px;
            }

            .booking-form h3 {
                margin-bottom: 25px;
                color: #333;
                font-weight: 600;
                font-size: 20px;
            }

            .form-group {
                margin-bottom: 25px;
            }

            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #444;
            }

            .credit-info {
                background-color: #f0f8ff;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
                border-left: 4px solid #4d94ff;
            }

            .credit-info p {
                margin-bottom: 10px;
                color: #333;
            }

            .time-slots {
                margin-top: 12px;
            }

            .timeslot-radio {
                margin-bottom: 10px;
                padding: 12px 15px;
                border: 1px solid #eee;
                border-radius: 5px;
                transition: all 0.2s ease;
            }

            .timeslot-radio:hover {
                background-color: #f9f9f9;
                border-color: #ddd;
            }

            .timeslot-radio input[type="radio"] {
                margin-right: 10px;
                vertical-align: middle;
            }

            .btn-book {
                background-color: #f4bc51;
                color: #fff;
                padding: 12px 30px;
                border-radius: 30px;
                border: none;
                font-weight: 600;
                font-size: 15px;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .btn-book:hover {
                background-color: #e8a430;
                transform: translateY(-2px);
                box-shadow: 0 3px 10px rgba(232, 164, 48, 0.3);
            }

            .btn-cancel {
                background-color: #e9ecef;
                color: #495057;
                padding: 12px 30px;
                border-radius: 30px;
                border: none;
                font-weight: 600;
                font-size: 15px;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                margin-right: 15px;
                text-decoration: none;
                display: inline-block;
            }

            .error-message {
                background-color: #fff3f3;
                color: #d63031;
                padding: 20px;
                border-left: 4px solid #d63031;
                border-radius: 4px;
                margin-bottom: 30px;
            }

            .section-heading {
                text-align: center;
                margin-bottom: 50px;
            }

            .section-heading .line-dec {
                width: 60px;
                height: 3px;
                background-color: #f4bc51;
                margin: 0 auto 20px;
            }
    </style>

</head>

<body>
    <?php include "inc/nav.inc.php"; ?>


    <div class="container booking-container">
        <div class="section-heading">
            <h2>Book <?php echo htmlspecialchars($sport_name); ?> Venue</h2>
            <div class="line-dec"></div>
            <p>Complete the form below to book your venue</p>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div class="error-message">
                <h4>Please correct the following errors:</h4>
                <ul><?php echo $errorMsg; ?></ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Venue Details Column -->
            <div class="col-md-4">
                <div class="booking-card">
                    <div class="venue-details">
                        <h3 class="venue-name"><?php echo htmlspecialchars($venue['name']); ?></h3>
                        <p class="venue-location">
                            <i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($venue['location']); ?>
                        </p>
                        <p class="venue-description"><?php echo htmlspecialchars($venue['description']); ?></p>

                        <!-- Venue Amenities -->
                        <?php if (!empty($venue['amenities'])): ?>
                            <div class="venue-amenities">
                                <?php
                                $amenities = explode(',', $venue['amenities']);
                                foreach ($amenities as $amenity):
                                    $amenity = trim($amenity);
                                    if (!empty($amenity)):
                                        ?>
                                        <span><i class="fa fa-check"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                        <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php endif; ?>

                        <p class="venue-price">
                            Price: $<?php echo number_format($venue['hourly_rate'], 2); ?> per hour
                            <br>
                            <small>(Each booking is for 2 hours)</small>
                        </p>
                    </div>
                </div>

                <!-- Credit Information -->
                <div class="credit-info">
                    <p><strong>Your credit balance:</strong> $<?php echo number_format($user_credit, 2); ?></p>
                    <p><strong>Booking cost:</strong> $<?php echo number_format($venue['hourly_rate'] * 2, 2); ?> (2
                        hours)</p>
                    <p><strong>Balance after booking:</strong>
                        $<?php echo number_format($user_credit - ($venue['hourly_rate'] * 2), 2); ?></p>
                </div>
            </div>

            <!-- Booking Form Column -->
            <div class="col-md-8">
                <div class="booking-card">
                    <div class="booking-form">
                        <h3>Booking Details</h3>

                        <form id="booking-form" method="POST" action="">
                            <input type="hidden" id="venue_id" value="<?php echo $venue_id; ?>">

                            <!-- Event Date -->
                            <div class="form-group">
                                <label for="event_date">Event Date</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>">
                            </div>

                            <!-- Time Slots -->
                            <div class="form-group">
                                <label>Time Slot</label>
                                <div class="time-slots">
                                    <?php foreach ($time_slots as $slot_value => $slot_display): ?>
                                        <div class="timeslot-radio">
                                            <label>
                                                <input type="radio" name="time_slot" value="<?php echo $slot_value; ?>"
                                                    <?php echo (isset($_POST['time_slot']) && $_POST['time_slot'] === $slot_value) ? 'checked' : ''; ?> required>
                                                <?php echo $slot_display; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Number of Participants -->
                            <div class="form-group">
                                <label for="num_participants">Number of Participants</label>
                                <input type="number" class="form-control" id="num_participants" name="num_participants"
                                    min="1" max="<?php echo $venue['capacity']; ?>" required
                                    value="<?php echo isset($_POST['num_participants']) ? htmlspecialchars($_POST['num_participants']) : '1'; ?>">
                                <small class="form-text text-muted">Maximum capacity: <?php echo $venue['capacity']; ?>
                                    people</small>
                            </div>

                            <!-- Special Requests -->
                            <div class="form-group">
                                <label for="special_requests">Special Requests (Optional)</label>
                                <textarea class="form-control" id="special_requests" name="special_requests"
                                    rows="3"><?php echo isset($_POST['special_requests']) ? htmlspecialchars($_POST['special_requests']) : ''; ?></textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-group text-center">
                                <a href="venues.php?sport_type=<?php echo urlencode($sport_type); ?>"
                                    class="btn-cancel">Cancel</a>
                                <button type="submit" class="btn-book">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "inc/footer.inc.php"; ?>

    <!-- JavaScript Files -->
    <script src="js/vendor/jquery.min.js"></script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/datepicker.js"></script>
    <script src="js/booking-scripts.js"></script>
</body>

</html>