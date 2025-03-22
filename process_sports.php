<?php
// Start the session
session_start();

// Initialize variables
$sport_type = $event_date = $time_slot = $special_requests = "";
$num_participants = $venue_id = 0;
$errorMsg = "";
$success = true;
$conn = null;

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Sport Type (Required)
    if (empty($_POST["sport_type"])) {
        $errorMsg .= "<li>Sport type is required.</li>";
        $success = false;
    } else {
        $sport_type = sanitize_input($_POST["sport_type"]);
    }

    // Validate Number of Participants (Required, must be numeric and positive)
    if (empty($_POST["num_attendees"])) {
        $errorMsg .= "<li>Number of participants is required.</li>";
        $success = false;
    } else {
        $num_participants = filter_var($_POST["num_attendees"], FILTER_VALIDATE_INT);
        if ($num_participants === false || $num_participants < 1 || $num_participants > 50) {
            $errorMsg .= "<li>Number of participants must be between 1 and 50.</li>";
            $success = false;
        }
    }

    // Validate Event Date (Required, must be in the future)
    if (empty($_POST["event_date"])) {
        $errorMsg .= "<li>Event date is required.</li>";
        $success = false;
    } else {
        $event_date = sanitize_input($_POST["event_date"]);
        if (strtotime($event_date) < strtotime(date('Y-m-d'))) {
            $errorMsg .= "<li>Event date cannot be in the past.</li>";
            $success = false;
        }
    }

    // Validate Time Slot (Required)
    if (empty($_POST["time_slot"])) {
        $errorMsg .= "<li>Time slot is required.</li>";
        $success = false;
    } else {
        $time_slot = sanitize_input($_POST["time_slot"]);
    }

    // Validate Venue ID (Required)
    if (empty($_POST["venue_id"])) {
        $errorMsg .= "<li>Venue selection is required.</li>";
        $success = false;
    } else {
        $venue_id = filter_var($_POST["venue_id"], FILTER_VALIDATE_INT);
        if ($venue_id === false) {
            $errorMsg .= "<li>Invalid venue selected.</li>";
            $success = false;
        }
    }

    // Sanitize Optional Special Requests
    if (!empty($_POST["special_requests"])) {
        $special_requests = sanitize_input($_POST["special_requests"]);
    }

    // Check if user is logged in
    if (!isset($_SESSION['member_id'])) {
        $errorMsg .= "<li>You must be logged in to make a booking.</li>";
        $success = false;
    }

    // If validation passes, check venue availability and process booking
    if ($success) {
        processBooking();
    }
}

/**
 * Function to process booking after validation
 */
function processBooking() {
    global $conn, $sport_type, $num_participants, $event_date, $time_slot, 
           $venue_id, $special_requests, $errorMsg, $success;
    
    // Establish database connection
    if (!getDbConnection()) {
        return;
    }
    
    // Parse time slot to get start and end times
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
    
    if (!$stmt) {
        $errorMsg .= "<li>Database error: " . $conn->error . "</li>";
        $success = false;
        return;
    }
    
    $stmt->bind_param("isssssss", 
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
        $errorMsg .= "<li>This venue is already booked for the selected date and time.</li>";
        $success = false;
        $stmt->close();
        return;
    }
    
    $stmt->close();
    
    // Get venue hourly rate
    $stmt = $conn->prepare("SELECT hourly_rate FROM venues WHERE id = ?");
    if (!$stmt) {
        $errorMsg .= "<li>Database error: " . $conn->error . "</li>";
        $success = false;
        return;
    }
    
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $errorMsg .= "<li>Selected venue not found.</li>";
        $success = false;
        $stmt->close();
        return;
    }
    
    $venue = $result->fetch_assoc();
    $hourly_rate = $venue['hourly_rate'];
    $booking_cost = $hourly_rate * 2; // 2 hours per slot
    $stmt->close();
    
    // Check if user has enough credits
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("SELECT credit FROM members WHERE member_id = ?");
    
    if (!$stmt) {
        $errorMsg .= "<li>Database error: " . $conn->error . "</li>";
        $success = false;
        return;
    }
    
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $errorMsg .= "<li>User account not found.</li>";
        $success = false;
        $stmt->close();
        return;
    }
    
    $member = $result->fetch_assoc();
    $current_credits = $member['credit'];
    
    if ($current_credits < $booking_cost) {
        $errorMsg .= "<li>Insufficient credits. You need {$booking_cost} credits, but you only have {$current_credits}.</li>";
        $success = false;
        $stmt->close();
        return;
    }
    
    $stmt->close();
    
    // All checks passed, create booking
    $conn->begin_transaction();
    
    try {
        // Update member credits
        $stmt = $conn->prepare("UPDATE members SET credit = credit - ? WHERE member_id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("di", $booking_cost, $member_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update credits: " . $stmt->error);
        }
        $stmt->close();
        
        // Create booking record
        $stmt = $conn->prepare("INSERT INTO sports_bookings 
                              (user_id, venue_id, sport_type, num_participants, 
                              event_date, start_time, end_time, special_requests, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");
        
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("iisissss", 
            $member_id, 
            $venue_id, 
            $sport_type, 
            $num_participants, 
            $event_date, 
            $start_time, 
            $end_time, 
            $special_requests
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create booking: " . $stmt->error);
        }
        
        $booking_id = $stmt->insert_id;
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Save booking information to session
        $_SESSION['booking_success'] = true;
        $_SESSION['booking_id'] = $booking_id;
        $_SESSION['booking_cost'] = $booking_cost;
        $_SESSION['remaining_credits'] = $current_credits - $booking_cost;
        
        // Redirect to confirmation page
        header("Location: booking_confirmation.php");
        exit;
    } 
    catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $errorMsg .= "<li>Booking failed: " . $e->getMessage() . "</li>";
        $success = false;
    }
    
    // Close the connection
    $conn->close();
}

// If there were errors, save them to session and redirect back to form
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$success) {
    $_SESSION['booking_errors'] = explode('</li>', $errorMsg);
    // Clean up the array by removing empty elements and trimming <li> tags
    $_SESSION['booking_errors'] = array_filter(array_map(function($item) {
        return trim(str_replace('<li>', '', $item));
    }, $_SESSION['booking_errors']));
    
    $_SESSION['form_data'] = $_POST;
    header("Location: sports.php");
    exit;
}

// If accessed directly without POST, redirect to sports.php
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: sports.php");
    exit;
}
?>