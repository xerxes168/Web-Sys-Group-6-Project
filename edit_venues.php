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
    
    // Define the config file path relative to this script
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
        $suitable_for_birthday = isset($_POST['suitable_for_birthday']) ? 1 : 0;
        $suitable_for_networking = isset($_POST['suitable_for_networking']) ? 1 : 0;
        $suitable_for_seminar = isset($_POST['suitable_for_seminar']) ? 1 : 0;
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
                                suitable_for_birthday = ?, 
                                suitable_for_networking = ?, 
                                suitable_for_seminar = ?, 
                                description = ?,
                                amenities = ?,
                                image_url = ?,
                                sport_type = ?
                                WHERE id = ?");
            
            $stmt->bind_param("ssiidiiisssssi", 
                $name, 
                $location, 
                $capacity, 
                $is_available,
                $hourly_rate, 
                $suitable_for_sports, 
                $suitable_for_birthday,
                $suitable_for_networking,
                $suitable_for_seminar,
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
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f7fa;
        }
        .admin-dashboard {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-welcome {
            background: linear-gradient(to right, #5f52b0, #ff589e);
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-welcome h2 {
            margin-top: 0;
            font-weight: 600;
        }
        .admin-panel {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-sidebar {
            width: 220px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0;
            overflow: hidden;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-sidebar li {
            margin: 0;
        }
        .admin-sidebar a {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background-color: #f0f2f5;
            border-left-color: #5f52b0;
            color: #5f52b0;
        }
        .admin-sidebar a.active {
            background-color: #f0f2f5;
            font-weight: 600;
        }
        .admin-content {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .form-buttons button, .form-buttons a {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-save {
            background-color: #28a745;
        }
        .btn-cancel {
            background-color: #6c757d;
        }
        .checkbox-group {
            margin-bottom: 5px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
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
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="suitable_for_birthday" name="suitable_for_birthday" value="1" <?php echo $venue['suitable_for_birthday'] ? 'checked' : ''; ?>>
                                            <label for="suitable_for_birthday">Birthday Celebrations</label>
                                        </div>
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="suitable_for_networking" name="suitable_for_networking" value="1" <?php echo $venue['suitable_for_networking'] ? 'checked' : ''; ?>>
                                            <label for="suitable_for_networking">Networking Events</label>
                                        </div>
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="suitable_for_seminar" name="suitable_for_seminar" value="1" <?php echo $venue['suitable_for_seminar'] ? 'checked' : ''; ?>>
                                            <label for="suitable_for_seminar">Seminars/Workshops</label>
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
</body>
</html>