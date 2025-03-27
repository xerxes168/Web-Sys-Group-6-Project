<?php
session_start();

// ------------------------------------------
// Error handling / DB connection logic
// ------------------------------------------
$errorMsg = "";
$success = true;

function getDbConnection()
{
    global $errorMsg, $success;
    $configFile = '/var/www/private/db-config.ini'; // Path to your .ini
    if (!file_exists($configFile)) {
        $errorMsg .= "Database configuration file not found.";
        $success = false;
        return false;
    }
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg .= "Failed to parse database config file.";
        $success = false;
        return false;
    }
    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );
    if ($conn->connect_error) {
        $errorMsg .= "Connection failed: " . $conn->connect_error;
        $success = false;
        return false;
    }
    return $conn;
}

// ------------------------------------------
// Connect to DB
// ------------------------------------------
$conn = getDbConnection();
if (!$conn) {
    die("Database error: " . $errorMsg);
}

// ------------------------------------------
// Handle booking form submission
// ------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_slots']) && isset($_POST['slot_ids'])) {
    $member_id = $_SESSION['member_id'] ?? null;
    if (!$member_id) {
        echo "<p style='color:red;'>Please log in to book slots.</p>";
    } else {
        // Get the selected venue from POST
        $venue_id = $_POST['venue_id'] ?? null;
        if (!$venue_id) {
            echo "<p style='color:red;'>Venue not selected.</p>";
        } else {
            // Retrieve the hourly_rate for this venue from venues_test
            $stmt_rate = $conn->prepare("SELECT hourly_rate FROM venues_test WHERE id = ?");
            $stmt_rate->bind_param("i", $venue_id);
            $stmt_rate->execute();
            $result_rate = $stmt_rate->get_result();
            if ($result_rate->num_rows > 0) {
                $row_rate = $result_rate->fetch_assoc();
                $hourly_rate = $row_rate['hourly_rate'];
            } else {
                $hourly_rate = 0;
            }
            $stmt_rate->close();

            // Calculate total cost: for each booked slot, cost = hourly_rate.
            // (Adjust multiplication here if each slot spans multiple hours.)
            $num_slots = count($_POST['slot_ids']);
            $total_cost = $num_slots * $hourly_rate;

            // Retrieve user's current credit balance
            $stmt = $conn->prepare("SELECT credit FROM members WHERE member_id = ?");
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $credit_balance = $row['credit'];
            } else {
                $credit_balance = 0;
            }
            $stmt->close();

            if ($credit_balance < $total_cost) {
                echo "<p style='color:red;'>Insufficient credits. You need $total_cost credits, but you have $credit_balance credits.</p>";
            } else {
                // Book each selected slot
                $stmt = $conn->prepare("UPDATE time_slots_test SET is_booked = 1, member_id = ? WHERE id = ?");
                foreach ($_POST['slot_ids'] as $slot_id) {
                    $stmt->bind_param("ii", $member_id, $slot_id);
                    $stmt->execute();
                }
                $stmt->close();

                // Deduct the total cost from the member's credit balance
                $stmt = $conn->prepare("UPDATE members SET credit = credit - ? WHERE member_id = ?");
                $stmt->bind_param("ii", $total_cost, $member_id);
                $stmt->execute();
                $stmt->close();

                echo "<p style='color:green;'>Booking successful! Total cost: $total_cost credits.</p>";
            }
        }
    }
}

// ------------------------------------------
// Get flow control parameters from GET
// ------------------------------------------
$sport         = $_GET['sport']      ?? null;
$venue_id      = $_GET['venue_id']   ?? null;
$selected_date = $_GET['date']       ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Flow with Credits</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { margin: 20px; }
        h2 { margin-top: 30px; }
        .btn-option {
            background-color: #f4bc51;
            border: none;
            color: #fff;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-option:hover {
            background-color: #e8a430;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<?php
// =================================================
// STEP 0: Select Sport
// =================================================
if (!$sport) {
    echo "<h2>Select a Sport</h2>";
    $sql = "SELECT DISTINCT sport_type FROM venues_test ORDER BY sport_type ASC";
    $result = $conn->query($sql);
    $defaultSports = [
        'basketball' => 'Basketball',
        'volleyball' => 'Volleyball',
        'badminton'  => 'Badminton',
        'soccer'     => 'Soccer'
    ];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $s = htmlspecialchars($row['sport_type']);
            echo "<p><a class='btn-option' href='?sport=$s'>$s</a></p>";
        }
    } else {
        foreach ($defaultSports as $key => $name) {
            echo "<p><a class='btn-option' href='?sport=$key'>$name</a></p>";
        }
    }

// =================================================
// STEP 1: Sport selected -> Show venues for that sport
// =================================================
} else if ($sport && !$venue_id) {
    echo "<h2>Select a Venue for " . htmlspecialchars($sport) . "</h2>";
    $stmt = $conn->prepare("SELECT id, name FROM venues_test WHERE sport_type = ? ORDER BY name ASC");
    $stmt->bind_param("s", $sport);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $vid = $row['id'];
            $vname = htmlspecialchars($row['name']);
            echo "<p><a class='btn-option' href='?sport=" . urlencode($sport) . "&venue_id=$vid'>$vname</a></p>";
        }
    } else {
        echo "<p>No venues found for this sport.</p>";
    }

// =================================================
// STEP 2: Venue selected -> Show available dates for that venue
// =================================================
} else if ($venue_id && !$selected_date) {
    echo "<h2>Select a Date</h2>";
    $sql = "
        SELECT DISTINCT slot_date 
        FROM time_slots_test
        WHERE is_booked = 0
          AND venue_id = ?
        ORDER BY slot_date ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $d = $row['slot_date'];
            $formatted = date('D, j M Y', strtotime($d));
            echo "<p><a class='btn-option' href='?sport=" . urlencode($sport) . "&venue_id=$venue_id&date=$d'>$formatted</a></p>";
        }
    } else {
        echo "<p>No available dates for this venue.</p>";
    }

// =================================================
// STEP 3: Venue + Date selected -> Show available time slots
// =================================================
} else if ($venue_id && $selected_date) {
    echo "<h2>Available Time Slots on " . date('D, j M Y', strtotime($selected_date)) . "</h2>";
    $sql = "
        SELECT id, slot_time
        FROM time_slots_test
        WHERE is_booked = 0
          AND venue_id = ?
          AND slot_date = ?
        ORDER BY slot_time ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $venue_id, $selected_date);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        echo "<form method='POST'>";
        while ($row = $res->fetch_assoc()) {
            $slot_id = $row['id'];
            $slot_time = date('g:i A', strtotime($row['slot_time']));
            echo "<div>";
            echo "<label>";
            echo "<input type='checkbox' name='slot_ids[]' value='$slot_id'> $slot_time";
            echo "</label>";
            echo "</div>";
        }
        // Preserve selection state via hidden fields
        echo "<input type='hidden' name='sport' value='" . htmlspecialchars($sport) . "'>";
        echo "<input type='hidden' name='venue_id' value='$venue_id'>";
        echo "<input type='hidden' name='date' value='$selected_date'>";
        echo "<button class='btn btn-primary mt-2' type='submit' name='book_slots'>Book Selected Slots</button>";
        echo "</form>";
    } else {
        echo "<p>No available slots for this date.</p>";
    }
}
?>

</body>
</html>
<?php
$conn->close();
?>
