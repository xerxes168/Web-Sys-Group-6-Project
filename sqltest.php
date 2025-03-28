<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
</head>
<body>
    <?php
    // Include the SQL requests file
    require_once 'inc/SQLrequests.inc.php';

    // Define the config file path
    $configFile = '/var/www/private/db-config.ini';

    // Call the handler functions
    $registerOutput = handleRegister($configFile);
    $loginOutput = handleLogin($configFile);
    $addBookingOutput = handleAddBooking($configFile);
    $currentBookingsOutput = handleListCurrentBookings($configFile);
    $availableBookingsOutput = handleListAvailableBookings($configFile);

    // Fetch venues dropdown and users data on page load or POST
    $conn = connectToDatabase($configFile);
    if ($conn) {
        $venuesDropdown = getVenuesDropdown($conn);
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $usersOutput = fetchUsersData($configFile);
        }
        $conn->close();
    } else {
        $venuesDropdown = '<option value="">No venues available (DB error)</option>';
        $usersOutput = '';
    }
    ?>

    <!-- Container for forms -->
    <div style="display: flex; justify-content: flex-end; gap: 20px; width: 90%; margin-top: 30px;">
        <!-- Registration Form -->
        <div style="width: 45%; border: 1px solid #ccc; padding: 20px;">
            <h2>Register</h2>
            <form method="POST" action="">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="register" value="Register" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $registerOutput; ?>
        </div>

        <!-- Login Form -->
        <div style="width: 45%; border: 1px solid #ccc; padding: 20px;">
            <h2>Login</h2>
            <form method="POST" action="">
                <label for="login-email">Email:</label>
                <input type="email" id="login-email" name="email" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="login-password">Password:</label>
                <input type="password" id="login-password" name="password" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="login" value="Login" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $loginOutput; ?>
        </div>

        <!-- Add Booking Form -->
        <div style="width: 45%; border: 1px solid #ccc; padding: 20px;">
            <h2>Add Booking</h2>
            <form method="POST" action="">
                <label for="user_id">User ID:</label>
                <input type="number" id="user_id" name="user_id" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="venue_id">Venue:</label>
                <select id="venue_id" name="venue_id" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;">
                    <?php echo $venuesDropdown; ?>
                </select><br>
                <label for="booking_date">Booking Date:</label>
                <input type="date" id="booking_date" name="booking_date" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <label for="details">Details:</label>
                <input type="text" id="details" name="details" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="add_booking" value="Add Booking" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $addBookingOutput; ?>
        </div>

        <!-- List Available Bookings Form -->
        <div style="width: 60%; border: 1px solid #ccc; padding: 20px;">
            <h2>List Available Bookings</h2>
            <form method="POST" action="">
                <label for="vId">Select Venue:</label>
                <select id="vId" name="vId" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;">
                    <?php echo $venuesDropdown; ?>
                </select><br>
                <input type="submit" name="show_available_bookings" value="Show Available Bookings" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $availableBookingsOutput; ?>
        </div>

        <!-- List Current Bookings Form -->
        <div style="width: 60%; border: 1px solid #ccc; padding: 20px;">
            <h2>List Current Bookings</h2>
            <form method="POST" action="">
                <label for="uId">Enter User ID:</label>
                <input type="number" id="uId" name="uId" required style="width: 90%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc;"><br>
                <input type="submit" name="show_current_bookings" value="Show Current Bookings" style="padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            </form>
            <?php echo $currentBookingsOutput; ?>
        </div>
    </div>

    <!-- Display users data on initial load -->
    <?php if (isset($usersOutput)) echo $usersOutput; ?>
</body>
</html>