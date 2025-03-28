<?php
// SQLrequests.inc.php

// Function to establish database connection
function connectToDatabase($configFile) {
    if (!file_exists($configFile)) {
        echo "<p style='color:red;'>Database configuration file not found.</p>";
        return null;
    }

    $config = parse_ini_file($configFile);
    if ($config === false) {
        echo "<p style='color:red;'>Failed to parse database config file.</p>";
        return null;
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        echo "<p style='color:red;'>Host Connection failed: " . $conn->connect_error . "</p>";
        return null;
    }

    echo "<p style='color:green;'>Successfully connected to the database!</p>";
    return $conn;
}

// Function to get venues for dropdown
function getVenuesDropdown($conn) {
    $options = '<option value="">Select Venue</option>';
    $sql = "SELECT id, name FROM venues";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
        }
    }
    return $options;
}

// Register function
function handleRegister($configFile) {
    $output = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $name = $conn->real_escape_string($_POST['name']);
            $email = $conn->real_escape_string($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                $output .= "<p style='color:green;'>Registration successful!</p>";
            } else {
                $output .= "<p style='color:red;'>Error: " . $conn->error . "</p>";
            }
            $conn->close();
        }
    }
    return $output;
}

// Login function
function handleLogin($configFile) {
    $output = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $email = $conn->real_escape_string($_POST['email']);
            $password = $_POST['password'];

            $sql = "SELECT * FROM users WHERE email='$email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $output .= "<p style='color:green;'>Login successful!</p>";
                } else {
                    $output .= "<p style='color:red;'>Invalid email or password.</p>";
                }
            } else {
                $output .= "<p style='color:red;'>No user found with that email.</p>";
            }
            $conn->close();
        }
    }
    return $output;
}

// Add Booking function
function handleAddBooking($configFile) {
    $output = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_booking'])) {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $userId = $conn->real_escape_string($_POST['user_id']);
            $venueId = $conn->real_escape_string($_POST['venue_id']);
            $bookingDate = $conn->real_escape_string($_POST['booking_date']);
            $details = $conn->real_escape_string($_POST['details']);

            $sql = "INSERT INTO bookings (user_id, venue_id, booking_date, details) VALUES ('$userId', '$venueId', '$bookingDate', '$details')";
            if ($conn->query($sql) === TRUE) {
                $output .= "<p style='color:green;'>Booking added successfully!</p>";
            } else {
                $output .= "<p style='color:red;'>Error: " . $conn->error . "</p>";
            }
            $conn->close();
        }
    }
    return $output;
}

// List Current Bookings function
function handleListCurrentBookings($configFile) {
    $output = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['show_current_bookings'])) {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $userId = $conn->real_escape_string($_POST['uId']);

            $sql = "SELECT * FROM bookings WHERE user_id = '$userId'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $output .= "<h2>Bookings for User ID: $userId</h2>";
                $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    $output .= "<tr><td>" . $row["id"] . "</td><td>" . $row["booking_date"] . "</td><td>" . $row["details"] . "</td></tr>";
                }
                $output .= "</table>";
            } else {
                $output .= "<p style='color:red;'>No bookings found for User ID: $userId.</p>";
            }
            $conn->close();
        } else {
            $output .= "<h4 style='color:red;'>DB Connection failed: generating sample table</h4>";
            $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
            $output .= "<tr><td>01</td><td>01-01-1995</td><td>Badminton Court</td></tr>";
            $output .= "</table>";
        }
    }
    return $output;
}

// List Available Bookings function
function handleListAvailableBookings($configFile) {
    $output = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['show_available_bookings'])) {
        $conn = connectToDatabase($configFile);
        if ($conn) {
            $venueId = $conn->real_escape_string($_POST['vId']);

            $sql = "SELECT * FROM bookings WHERE venue_id = '$venueId'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $output .= "<h2>Bookings for Venue ID: $venueId</h2>";
                $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    $output .= "<tr><td>" . $row["id"] . "</td><td>" . $row["booking_date"] . "</td><td>" . $row["details"] . "</td></tr>";
                }
                $output .= "</table>";
            } else {
                $output .= "<p style='color:red;'>No bookings found for Venue ID: $venueId.</p>";
            }
            $conn->close();
        } else {
            $output .= "<h4 style='color:red;'>DB Connection failed: generating sample table</h4>";
            $output .= "<table border='1'><tr><th>Booking ID</th><th>Date</th><th>Details</th></tr>";
            $output .= "<tr><td>01</td><td>01-01-1995</td><td>Badminton Court</td></tr>";
            $output .= "</table>";
        }
    }
    return $output;
}

// Fetch users data function
function fetchUsersData($configFile) {
    $output = '';
    $conn = connectToDatabase($configFile);
    if ($conn) {
        $sql = "SELECT id, name, email FROM users";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $output .= "<h2>Users Data:</h2>";
            $output .= "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th></tr>";
            while ($row = $result->fetch_assoc()) {
                $output .= "<tr><td>" . $row["id"] . "</td><td>" . $row["name"] . "</td><td>" . $row["email"] . "</td></tr>";
            }
            $output .= "</table>";
        } else {
            $output .= "<p>No records found in the 'users' table.</p>";
        }
        $conn->close();
    }
    return $output;
}
?>