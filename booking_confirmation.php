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
    header("Location: viewSports.php");
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
<html lang="en">
<head>
    <title>GatherSpot - Booking Confirmation</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        .confirmation-header {
            background-color: #e8f5e9;
            padding: 2rem 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .confirmation-header h1 {
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }
        .booking-id {
            display: inline-block;
            background-color: #757575;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            margin: 1rem 0;
        }
        .booking-details {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .detail-row {
            display: flex;
            margin-bottom: 1rem;
            align-items: baseline;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
            width: 140px;
        }
        .payment-summary {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .payment-amount {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .action-buttons a {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
        }
        .action-buttons i {
            margin-right: 0.5rem;
        }
        .blue-btn {
            background-color: #1976d2;
            color: white;
        }
        .blue-btn:hover {
            background-color: #1565c0;
            color: white;
        }
        .outline-btn {
            border: 1px solid #666;
            color: #666;
        }
        .outline-btn:hover {
            background-color: #f5f5f5;
            color: #333;
        }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    
    <main class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="confirmation-header">
                    <h1>Booking Confirmed!</h1>
                    <p>Your reservation has been successfully processed</p>
                    <div class="booking-id">Booking ID: #<?php echo $booking_id; ?></div>
                </div>
                
                <section>
                    <h2>Booking Details</h2>
                    <div class="booking-details">
                        <div class="detail-row">
                            <div class="detail-label">Sport:</div>
                            <div><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Venue:</div>
                            <div><?php echo htmlspecialchars($booking['venue_name']); ?> 
                                (<?php echo htmlspecialchars($booking['venue_location']); ?>)
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Date:</div>
                            <div><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Time:</div>
                            <div><?php echo htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']); ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Participants:</div>
                            <div><?php echo htmlspecialchars($booking['num_participants']); ?></div>
                        </div>
                    </div>
                </section>
                
                <section class="payment-summary">
                    <h2>Payment Summary</h2>
                    
                    <div class="detail-row">
                        <div class="detail-label">Amount Paid:</div>
                        <div class="payment-amount"><?php echo number_format($booking_cost, 2); ?> credits</div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-label">Remaining Balance:</div>
                        <div class="payment-amount"><?php echo number_format($remaining_credits, 2); ?> credits</div>
                    </div>
                </section>
                
                <?php if (!empty($booking['special_requests'])): ?>
                <section class="special-requests">
                    <h2>Special Requests</h2>
                    <p><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
                </section>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="index.php" class="blue-btn"><i class="fas fa-home"></i> Return to Home</a>
                    <a href="my_bookings.php" class="outline-btn"><i class="fas fa-list"></i> My Bookings</a>
                    <a href="javascript:window.print()" class="outline-btn"><i class="fas fa-print"></i> Print</a>
                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>
</body>
</html>