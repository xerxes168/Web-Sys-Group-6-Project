<?php
session_start();

// Initialize variables
$errorMsg = "";
$success = true;
$conn = null;
$booking = null;

// Function to establish database connection
function getDbConnection()
{
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
        header("Location: viewSports.php");
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
    <title>HoopSpaces - Booking Confirmation</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        /* Main container styling */
        .booking-confirmation-container {
            max-width: 800px;
            margin: 60px auto 40px auto;
            padding: 0 15px;
        }

        /* Focus styles for accessibility */
        a:focus,
        button:focus {
            outline: 3px solid #4d90fe;
            outline-offset: 2px;
        }

        /* Success banner styling */
        .confirmation-banner {
            background-color: #e8f5e9;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .confirmation-banner h1 {
            color: #2e7d32;
            margin-bottom: 0.5rem;
            font-size: 28px;
        }

        .confirmation-banner p {
            font-size: 16px;
            color: #333;
            margin-bottom: 1rem;
        }

        /* Booking ID badge */
        .booking-id-badge {
            display: inline-block;
            background-color: #2e7d32;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            margin: 1rem 0;
            font-weight: bold;
        }

        /* Card styling */
        .info-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }

        .card-title {
            color: #333;
            font-size: 20px;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        /* Detail rows */
        .detail-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            align-items: baseline;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
            width: 140px;
            margin-right: 10px;
        }

        .detail-value {
            flex: 1;
            min-width: 200px;
            color: #333;
        }

        /* Payment info */
        .payment-amount {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        /* Special requests section */
        .special-requests-content {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-top: 10px;
            white-space: pre-line;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .action-button {
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .primary-button {
            background-color: #5f52b0;
            color: white;
        }

        .primary-button:hover,
        .primary-button:focus {
            background-color: #e8a430;
            color: white;
            text-decoration: none;
        }

        .secondary-button {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }

        .secondary-button:hover,
        .secondary-button:focus {
            background-color: #e0e0e0;
            color: #333;
            text-decoration: none;
        }

        .button-icon {
            margin-right: 0.5rem;
        }

        /* Print-specific styles */
        @media print {

            .action-buttons,
            .nav,
            footer {
                display: none !important;
            }

            .booking-confirmation-container {
                margin: 0;
                width: 100%;
            }

            .confirmation-banner,
            .info-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .detail-label {
                width: 100%;
                margin-bottom: 0.25rem;
            }

            .detail-value {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.75rem;
            }

            .action-button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <header role="banner">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main role="main" class="booking-confirmation-container">
        <!-- Success Banner -->
        <div class="confirmation-banner" role="alert" aria-live="polite">
            <h1>Booking Confirmed!</h1>
            <p>Your reservation has been successfully processed</p>
            <div class="booking-id-badge">
                Booking ID: #<?php echo $booking_id; ?>
            </div>
        </div>

        <!-- Booking Details Card -->
        <section class="info-card" aria-labelledby="booking-details-heading">
            <h2 id="booking-details-heading" class="card-title">Booking Details</h2>

            <div class="detail-row">
                <div class="detail-label">Sport:</div>
                <div class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['sport_type'])); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Venue:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($booking['venue_name']); ?>
                    (<?php echo htmlspecialchars($booking['venue_location']); ?>)
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Date:</div>
                <div class="detail-value"><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Time:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Participants:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['num_participants']); ?></div>
            </div>
            </div>
        </section>

        <!-- Payment Summary Card -->
        <section class="info-card" aria-labelledby="payment-summary-heading">
            <h2 class="card-title">Payment Summary</h2>

            <div class="detail-row">
                <div class="detail-label">Amount Paid:</div>
                <div class="detail-value">
                    <span class="payment-amount"><?php echo number_format($booking_cost, 2); ?> credits</span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Remaining Balance:</div>
                <div class="detail-value">
                    <span class="payment-amount"><?php echo number_format($remaining_credits, 2); ?> credits</span>
                </div>
            </div>
            </div>
        </section>

        <?php if (!empty($booking['special_requests'])): ?>
            <!-- Special Requests Card -->
            <section class="info-card" aria-labelledby="special-requests-heading">
                <h2 class="card-title">Special Requests</h2>
                <div class="special-requests-content">
                    <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Action Buttons -->
        <nav class="action-buttons" aria-label="Booking options">
            <a href="index.php" class="action-button primary-button">
                <i class="fa fa-home button-icon" aria-hidden="true"></i> Return to Home
            </a>
            <a href="mybookings.php" class="action-button secondary-button">
                <i class="fa fa-list button-icon" aria-hidden="true"></i> My Bookings
            </a>
            <button onclick="window.print()" class="action-button secondary-button">
                <i class="fa fa-print button-icon" aria-hidden="true"></i> Print
            </button>
        </nav>
    </main>


    <?php include "inc/footer.inc.php"; ?>
    <script src="js/main.js"></script>
</body>

</html>