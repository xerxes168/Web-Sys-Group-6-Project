<?php
session_start();

// Initialize variables
$errorMsg = "";
$success = true;
$conn = null;

// Establish database connection
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

// Define time slots - simplified, fewer options
$time_slots = [
    '08:00-10:00' => '8:00 AM - 10:00 AM',
    '10:00-12:00' => '10:00 AM - 12:00 PM',
    '14:00-16:00' => '2:00 PM - 4:00 PM',
    '16:00-18:00' => '4:00 PM - 6:00 PM',
    '18:00-20:00' => '6:00 PM - 8:00 PM',
];

// Get form data if there were errors
$errors = $_SESSION['booking_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Clear session variables after retrieval
unset($_SESSION['booking_errors']);
unset($_SESSION['form_data']);

// Connect to database
$venues_result = null;
$credit_balance = 0;

if (getDbConnection()) {
    // Fetch available venues
    // For venues
    $stmt = $conn->prepare("SELECT id, name, capacity, hourly_rate FROM venues WHERE suitable_for_sports = 1");
    if ($stmt) {
        $stmt->execute();
        $venues_result = $stmt->get_result();
        $stmt->close();
    } else {
        $errorMsg .= "<li>Error preparing venues query: " . $conn->error . "</li>";
        $success = false;
    }

    // Get user's credit balance if logged in
    if (isset($_SESSION['member_id'])) {
        $member_id = $_SESSION['member_id'];
        $stmt = $conn->prepare("SELECT credit FROM members WHERE member_id = ?");

        if ($stmt) {
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $member_data = $result->fetch_assoc();
                $credit_balance = $member_data['credit'];
            }
            $stmt->close();
        }
    }
}

// Helper function to sanitize input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - Book Sports Venue</title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2>Book Your Sports Venue</h2>
                <p>Select your preferred sport type, venue, and time</p>
            </div>
        </div>

        <!-- Display errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Login requirement message -->
        <?php if (!isset($_SESSION['member_id'])): ?>
            <div class="alert alert-warning">
                You must be <a href="login.php">logged in</a> to book a venue.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Your credit balance: <?php echo number_format($credit_balance, 2); ?> credits
            </div>
        <?php endif; ?>

        <!-- Booking form -->
        <form action="process_sports.php" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Event Details</h5>
                        </div>
                        <div class="card-body">
                            <!-- Sport type selection -->
                            <div class="form-group mb-3">
                                <label for="sport_type">Sport Type</label>
                                <select class="form-control" id="sport_type" name="sport_type" required>
                                    <option value="">-- Select Sport --</option>
                                    <option value="basketball" <?php echo (isset($form_data['sport_type']) && $form_data['sport_type'] == 'basketball') ? 'selected' : ''; ?>>Basketball
                                    </option>
                                    <option value="volleyball" <?php echo (isset($form_data['sport_type']) && $form_data['sport_type'] == 'volleyball') ? 'selected' : ''; ?>>Volleyball
                                    </option>
                                    <option value="badminton" <?php echo (isset($form_data['sport_type']) && $form_data['sport_type'] == 'badminton') ? 'selected' : ''; ?>>Badminton</option>
                                    <option value="soccer" <?php echo (isset($form_data['sport_type']) && $form_data['sport_type'] == 'soccer') ? 'selected' : ''; ?>>Soccer</option>
                                </select>
                            </div>

                            <!-- Number of participants -->
                            <div class="form-group mb-3">
                                <label for="num_attendees">Number of Participants</label>
                                <input type="number" class="form-control" id="num_attendees" name="num_attendees"
                                    min="1" max="50" required
                                    value="<?php echo htmlspecialchars($form_data['num_attendees'] ?? ''); ?>">
                            </div>

                            <!-- Special requests -->
                            <div class="form-group mb-3">
                                <label for="special_requests">Special Requests (Optional)</label>
                                <textarea class="form-control" id="special_requests" name="special_requests"
                                    rows="3"><?php echo htmlspecialchars($form_data['special_requests'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Schedule & Venue</h5>
                        </div>
                        <div class="card-body">
                            <!-- Event date -->
                            <div class="form-group mb-3">
                                <label for="event_date">Event Date</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required
                                    value="<?php echo htmlspecialchars($form_data['event_date'] ?? ''); ?>">
                            </div>

                            <!-- Time slot -->
                            <div class="form-group mb-3">
                                <label for="time_slot">Time Slot</label>
                                <select class="form-control" id="time_slot" name="time_slot" required>
                                    <option value="">-- Select Time Slot --</option>
                                    <?php foreach ($time_slots as $value => $display): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($form_data['time_slot']) && $form_data['time_slot'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($display); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Venue selection -->
                            <div class="form-group mb-3">
                                <label for="venue_id">Select Venue</label>
                                <select class="form-control" id="venue_id" name="venue_id" required>
                                    <option value="">-- Select a Venue --</option>
                                    <?php
                                    if ($venues_result && $venues_result->num_rows > 0) {
                                        while ($venue = $venues_result->fetch_assoc()):
                                            ?>
                                            <option value="<?php echo $venue['id']; ?>" <?php echo (isset($form_data['venue_id']) && $form_data['venue_id'] == $venue['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($venue['name']); ?>
                                                (Capacity: <?php echo $venue['capacity']; ?>,
                                                $<?php echo $venue['hourly_rate']; ?>/hour)
                                            </option>
                                        <?php
                                        endwhile;
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Submit button -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary" <?php echo !isset($_SESSION['member_id']) ? 'disabled' : ''; ?>>
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include "inc/footer.inc.php"; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('event_date').min = today;
        });
    </script>
</body>

</html>

<?php
// Close database connection
if ($conn) {
    $conn->close();
}
?>

