<?php
session_start();

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

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Check if there's a successful booking in session
if (!isset($_SESSION['booking_success']) || !isset($_SESSION['booking_id'])) {
    header("Location: sports.php");
    exit;
}

// Get booking details from session
$booking_id = $_SESSION['booking_id'];
$booking_cost = $_SESSION['booking_cost'];
$remaining_credits = $_SESSION['remaining_credits'];

// Connect to database and get booking details
if (getDbConnection()) {
    $stmt = $conn->prepare("SELECT b.*, v.name as venue_name, v.location as venue_location
                           FROM sports_bookings b
                           JOIN venues v ON b.venue_id = v.id
                           WHERE b.id = ? AND b.user_id = ?");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("ii", $booking_id, $_SESSION['member_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    // If booking not found or doesn't belong to user
    if ($result->num_rows === 0) {
        $conn->close();
        header("Location: sports.php");
        exit;
    }

    $booking = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
} else {
    // If database connection failed
    die("Failed to connect to database. Please try again later.");
}

// Clear session variables after retrieval
unset($_SESSION['booking_success']);
unset($_SESSION['booking_id']);
unset($_SESSION['booking_cost']);
unset($_SESSION['remaining_credits']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - Booking Confirmation</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/templatemo-style.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3>Booking Confirmed!</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            Your booking has been successfully confirmed.
                        </div>
                        
                        <?php if ($booking): ?>
                        <h4 class="mt-4">Booking Details</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>Booking ID:</th>
                                <td>#<?php echo $booking_id; ?></td>
                            </tr>
                            <tr>
                                <th>Sport:</th>
                                <td><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></td>
                            </tr>
                            <tr>
                                <th>Venue:</th>
                                <td><?php echo htmlspecialchars($booking['venue_name']); ?> (<?php echo htmlspecialchars($booking['venue_location']); ?>)</td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td><?php echo date('F j, Y', strtotime($booking['event_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td><?php echo htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']); ?></td>
                            </tr>
                            <tr>
                                <th>Participants:</th>
                                <td><?php echo htmlspecialchars($booking['num_participants']); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                        </table>
                        
                        <h4 class="mt-4">Payment Summary</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>Amount Paid:</th>
                                <td><?php echo number_format($booking_cost, 2); ?> credits</td>
                            </tr>
                            <tr>
                                <th>Remaining Balance:</th>
                                <td><?php echo number_format($remaining_credits, 2); ?> credits</td>
                            </tr>
                        </table>
                        
                        <?php if (!empty($booking['special_requests'])): ?>
                        <h4 class="mt-4">Special Requests</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            Could not retrieve booking details. Please contact support.
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-center">
                            <a href="index.php" class="btn btn-primary">Return to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>