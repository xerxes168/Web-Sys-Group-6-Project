<?php
// Start the session
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include authentication functions
require_once 'admin_auth.php';

// Check if user is logged in and is an admin
checkAdminAuth();

// Initialize variables
$errorMsg = "";
$successMsg = "";
$conn = null;
$venue = null;

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;

    $configFile = '/var/www/private/db-config.ini';

    if (!file_exists($configFile)) {
        $errorMsg = "Database configuration file not found.";
        return false;
    }

    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg = "Failed to parse database config file.";
        return false;
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        $errorMsg = "Host Connection failed: " . $conn->connect_error;
        return false;
    }
    
    return true;
}

// Check if venue ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_venues.php");
    exit;
}

$venue_id = intval($_GET['id']);

// Load venue data
if (getDbConnection()) {
    $stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $venue = $result->fetch_assoc();
    } else {
        $errorMsg = "Venue not found.";
    }
    
    $stmt->close();
    
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_venue'])) {
        function sanitize_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
        
        // Get form data
        $name = sanitize_input($_POST['name']);
        $location = sanitize_input($_POST['location']);
        $capacity = intval($_POST['capacity']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $hourly_rate = floatval($_POST['hourly_rate']);
        $suitable_for_sports = isset($_POST['suitable_for_sports']) ? 1 : 0;
        $description = sanitize_input($_POST['description']);
        $amenities = sanitize_input($_POST['amenities']);
        $image_url = sanitize_input($_POST['image_url']);
        $sport_type = sanitize_input($_POST['sport_type']);
        
        // Validate form data
        $isValid = true;
        
        if (empty($name)) {
            $errorMsg = "Venue name is required.";
            $isValid = false;
        } elseif (empty($location)) {
            $errorMsg = "Location is required.";
            $isValid = false;
        } elseif ($capacity <= 0) {
            $errorMsg = "Capacity must be greater than zero.";
            $isValid = false;
        } elseif ($hourly_rate <= 0) {
            $errorMsg = "Hourly rate must be greater than zero.";
            $isValid = false;
        }
        
        if ($isValid) {
            // Update venue
            $stmt = $conn->prepare("UPDATE venues SET 
                                name = ?, 
                                location = ?, 
                                capacity = ?, 
                                is_available = ?, 
                                hourly_rate = ?, 
                                suitable_for_sports = ?, 
                                description = ?,
                                amenities = ?,
                                image_url = ?,
                                sport_type = ?
                                WHERE id = ?");
            
            $stmt->bind_param("ssiidissssi", 
                $name, 
                $location, 
                $capacity, 
                $is_available,
                $hourly_rate, 
                $suitable_for_sports, 
                $description,
                $amenities,
                $image_url,
                $sport_type,
                $venue_id
            );
            
            if ($stmt->execute()) {
                $successMsg = "Venue updated successfully!";
                
                // Reload venue data after update
                $stmt->close();
                $stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
                $stmt->bind_param("i", $venue_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $venue = $result->fetch_assoc();
                }
                
                $stmt->close();
            } else {
                $errorMsg = "Error updating venue: " . $stmt->error;
            }
        }
    }
    
    $conn->close();
} else {
    $errorMsg = "Could not connect to database.";
}

// Sport types array
$sportTypes = ['Basketball', 'Volleyball', 'Badminton', 'Soccer', 'Tennis', 'Table Tennis', 'Other'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Edit Venue</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Edit Venue</h2>
            <p>Update venue details.</p>
        </div>
        
        <div class="admin-panel">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="admin_panel.php">Dashboard</a></li>
                    <li><a href="admin_venues.php" class="active">Manage Venues</a></li>
                    <li><a href="admin_members.php">Manage Members</a></li>
                    <li><a href="admin_credits.php">Credits Management</a></li>
                    <li><a href="admin_bookings.php">Booking Reports</a></li>
                    <li><a href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger">
                        <?php echo $errorMsg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success">
                        <?php echo $successMsg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($venue): ?>
                    <div class="form-section">
                        <h2>Edit Venue: <?php echo htmlspecialchars($venue['name']); ?></h2>
                        
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Venue Name *</label>
                                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($venue['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location" class="form-label">Location *</label>
                                        <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($venue['location']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="capacity" class="form-label">Capacity *</label>
                                        <input type="number" id="capacity" name="capacity" class="form-control" value="<?php echo htmlspecialchars($venue['capacity']); ?>" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="hourly_rate" class="form-label">Hourly Rate (in credits) *</label>
                                        <input type="number" id="hourly_rate" name="hourly_rate" class="form-control" value="<?php echo htmlspecialchars($venue['hourly_rate']); ?>" min="1" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sport_type" class="form-label">Sport Type</label>
                                        <select id="sport_type" name="sport_type" class="form-control">
                                            <?php foreach ($sportTypes as $type): ?>
                                                <option value="<?php echo $type; ?>" <?php echo ($venue['sport_type'] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amenities" class="form-label">Amenities (comma-separated)</label>
                                        <input type="text" id="amenities" name="amenities" class="form-control" value="<?php echo htmlspecialchars($venue['amenities']); ?>" placeholder="Parking, Restrooms, Lockers, Showers, etc.">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image_url" class="form-label">Image URL</label>
                                        <input type="text" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($venue['image_url']); ?>" placeholder="https://example.com/image.jpg">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($venue['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Venue Availability</label>
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="is_available" name="is_available" value="1" <?php echo $venue['is_available'] ? 'checked' : ''; ?>>
                                            <label for="is_available">Available for booking</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Suitable For (select all that apply)</label>
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="suitable_for_sports" name="suitable_for_sports" value="1" <?php echo $venue['suitable_for_sports'] ? 'checked' : ''; ?>>
                                            <label for="suitable_for_sports">Sports</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-buttons">
                                <a href="admin_venues.php" class="btn-cancel">Cancel</a>
                                <button type="submit" name="save_venue" class="btn-save">Save Venue</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Venue not found. <a href="admin_venues.php">Return to venue list</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
<script src="js/main.js"></script>
</body>
</html>