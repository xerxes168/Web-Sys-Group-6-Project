<?php
// Start the session
session_start();

// Include authentication functions
require_once 'admin_auth.php';

// Check if user is logged in and is an admin
checkAdminAuth();

// Initialize variables
$errorMsg = "";
$successMsg = "";
$venues = [];
$conn = null;

// Venue form data for add/edit
$venue = [
    'id' => '',
    'name' => '',
    'location' => '',
    'capacity' => '',
    'is_available' => 1,
    'hourly_rate' => '',
    'suitable_for_sports' => 1,
    'suitable_for_birthday' => 0,
    'suitable_for_networking' => 0,
    'suitable_for_seminar' => 0,
    'description' => '',
    'amenities' => '',
    'image_url' => '',
    'sport_type' => 'Basketball'
];

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;
    
    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        $errorMsg = "Database configuration file not found.";
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg = "Failed to parse database config file.";
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
        $errorMsg = "Host Connection failed: " . $conn->connect_error;
        return false;
    }
    
    return true;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input function
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    // Connect to database
    if (getDbConnection()) {
        // Handle Add/Edit Venue form submission
        if (isset($_POST['save_venue'])) {
            // Get form data
            $venue['name'] = sanitize_input($_POST['name']);
            $venue['location'] = sanitize_input($_POST['location']);
            $venue['capacity'] = intval($_POST['capacity']);
            $venue['is_available'] = isset($_POST['is_available']) ? 1 : 0;
            $venue['hourly_rate'] = floatval($_POST['hourly_rate']);
            $venue['suitable_for_sports'] = isset($_POST['suitable_for_sports']) ? 1 : 0;
            $venue['suitable_for_birthday'] = isset($_POST['suitable_for_birthday']) ? 1 : 0;
            $venue['suitable_for_networking'] = isset($_POST['suitable_for_networking']) ? 1 : 0;
            $venue['suitable_for_seminar'] = isset($_POST['suitable_for_seminar']) ? 1 : 0;
            $venue['description'] = sanitize_input($_POST['description']);
            $venue['amenities'] = sanitize_input($_POST['amenities']);
            $venue['image_url'] = sanitize_input($_POST['image_url']);
            $venue['sport_type'] = sanitize_input($_POST['sport_type']);
            
            // Validate form data
            $isValid = true;
            
            if (empty($venue['name'])) {
                $errorMsg = "Venue name is required.";
                $isValid = false;
            } elseif (empty($venue['location'])) {
                $errorMsg = "Location is required.";
                $isValid = false;
            } elseif ($venue['capacity'] <= 0) {
                $errorMsg = "Capacity must be greater than zero.";
                $isValid = false;
            } elseif ($venue['hourly_rate'] <= 0) {
                $errorMsg = "Hourly rate must be greater than zero.";
                $isValid = false;
            }
            
            if ($isValid) {
                // Check if it's an update or a new venue
                if (isset($_POST['venue_id']) && !empty($_POST['venue_id'])) {
                    // Update existing venue
                    $venue['id'] = intval($_POST['venue_id']);
                    
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
                        $venue['name'], 
                        $venue['location'], 
                        $venue['capacity'], 
                        $venue['is_available'],
                        $venue['hourly_rate'], 
                        $venue['suitable_for_sports'], 
                        $venue['suitable_for_birthday'],
                        $venue['suitable_for_networking'],
                        $venue['suitable_for_seminar'],
                        $venue['description'],
                        $venue['amenities'],
                        $venue['image_url'],
                        $venue['sport_type'],
                        $venue['id']
                    );
                    
                    if ($stmt->execute()) {
                        $successMsg = "Venue updated successfully!";
                        // Reset form
                        $venue = [
                            'id' => '',
                            'name' => '',
                            'location' => '',
                            'capacity' => '',
                            'is_available' => 1,
                            'hourly_rate' => '',
                            'suitable_for_sports' => 1,
                            'suitable_for_birthday' => 0,
                            'suitable_for_networking' => 0,
                            'suitable_for_seminar' => 0,
                            'description' => '',
                            'amenities' => '',
                            'image_url' => '',
                            'sport_type' => 'Basketball'
                        ];
                    } else {
                        $errorMsg = "Error updating venue: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    // Add new venue
                    $stmt = $conn->prepare("INSERT INTO venues (
                                        name, 
                                        location, 
                                        capacity, 
                                        is_available,
                                        hourly_rate, 
                                        suitable_for_sports, 
                                        suitable_for_birthday,
                                        suitable_for_networking,
                                        suitable_for_seminar,
                                        description,
                                        amenities,
                                        image_url,
                                        sport_type) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->bind_param("ssiidiiiissss", 
                        $venue['name'], 
                        $venue['location'], 
                        $venue['capacity'], 
                        $venue['is_available'],
                        $venue['hourly_rate'], 
                        $venue['suitable_for_sports'], 
                        $venue['suitable_for_birthday'],
                        $venue['suitable_for_networking'],
                        $venue['suitable_for_seminar'],
                        $venue['description'],
                        $venue['amenities'],
                        $venue['image_url'],
                        $venue['sport_type']
                    );
                    
                    if ($stmt->execute()) {
                        $successMsg = "New venue added successfully!";
                        // Reset form
                        $venue = [
                            'id' => '',
                            'name' => '',
                            'location' => '',
                            'capacity' => '',
                            'is_available' => 1,
                            'hourly_rate' => '',
                            'suitable_for_sports' => 1,
                            'suitable_for_birthday' => 0,
                            'suitable_for_networking' => 0,
                            'suitable_for_seminar' => 0,
                            'description' => '',
                            'amenities' => '',
                            'image_url' => '',
                            'sport_type' => 'Basketball'
                        ];
                    } else {
                        $errorMsg = "Error adding venue: " . $stmt->error;
                    }
                    
                    $stmt->close();
                }
            }
        }
        
        // Handle delete venue
        if (isset($_POST['delete_venue']) && isset($_POST['venue_id'])) {
            $venue_id = intval($_POST['venue_id']);
            
            // First check if venue has any bookings
            $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM sports_bookings WHERE venue_id = ?");
            $stmt->bind_param("i", $venue_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['booking_count'] > 0) {
                // Venue has bookings, set to unavailable instead of deleting
                $stmt = $conn->prepare("UPDATE venues SET is_available = 0 WHERE id = ?");
                $stmt->bind_param("i", $venue_id);
                
                if ($stmt->execute()) {
                    $successMsg = "Venue has existing bookings and cannot be deleted. It has been set as unavailable instead.";
                } else {
                    $errorMsg = "Error updating venue status: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                // No bookings, safe to delete
                $stmt = $conn->prepare("DELETE FROM venues WHERE id = ?");
                $stmt->bind_param("i", $venue_id);
                
                if ($stmt->execute()) {
                    $successMsg = "Venue deleted successfully!";
                } else {
                    $errorMsg = "Error deleting venue: " . $stmt->error;
                }
                
                $stmt->close();
            }
        }
        
        // Load venues after form processing
        $result = $conn->query("SELECT * FROM venues ORDER BY name");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $venues[] = $row;
            }
        }
        
        // If edit mode is requested, load venue data
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
            $venue_id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
            $stmt->bind_param("i", $venue_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $venue = $result->fetch_assoc();
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
} else {
    // Initial page load - load all venues
    if (getDbConnection()) {
        $result = $conn->query("SELECT * FROM venues ORDER BY name");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $venues[] = $row;
            }
        }
        $conn->close();
    }
}

// Determine if we're in add/edit mode
$addMode = isset($_GET['action']) && $_GET['action'] == 'add';
$editMode = isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']);
$showForm = $addMode || $editMode;

// Sport types array
$sportTypes = ['Basketball', 'Volleyball', 'Badminton', 'Soccer', 'Tennis', 'Table Tennis', 'Other'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Manage Venues</title>
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
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .admin-header h1 {
            color: #333;
            margin: 0;
            font-size: 1.8rem;
        }
        .venue-table {
            width: 100%;
            border-collapse: collapse;
        }
        .venue-table th, .venue-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .venue-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .venue-table tr:hover {
            background-color: #f8f9fa;
        }
        .btn-add {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #5f52b0;
            transition: background-color 0.3s;
        }
        .btn-add:hover {
            background-color: #4a4098;
            text-decoration: none;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons a, .action-buttons button {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #5f52b0;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .action-buttons .btn-edit {
            background-color: #17a2b8;
        }
        .action-buttons .btn-delete {
            background-color: #dc3545;
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
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            text-align: center;
        }
        .bg-success {
            background-color: #28a745;
            color: white;
        }
        .bg-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Venue Management</h2>
            <p>Add, edit, or remove venues from the system.</p>
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
                
                <!-- Venue Form (for Add/Edit) -->
                <?php if ($showForm): ?>
                    <div class="form-section">
                        <h2><?php echo $editMode ? 'Edit Venue' : 'Add New Venue'; ?></h2>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . ($editMode ? '?action=edit&id=' . $venue['id'] : ''); ?>">
                            <?php if ($editMode): ?>
                                <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                            <?php endif; ?>
                            
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
                    <!-- Venues List -->
                    <div class="admin-header">
                        <h1>Manage Venues</h1>
                        <a href="admin_venues.php?action=add" class="btn-add">Add New Venue</a>
                    </div>
                    
                    <?php if (empty($venues)): ?>
                        <p>No venues found. Click "Add New Venue" to create one.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="venue-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Capacity</th>
                                        <th>Rate</th>
                                        <th>Sport Type</th>
                                        <th>Available</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($venues as $v): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($v['name']); ?></td>
                                        <td><?php echo htmlspecialchars($v['location']); ?></td>
                                        <td><?php echo htmlspecialchars($v['capacity']); ?></td>
                                        <td><?php echo htmlspecialchars($v['hourly_rate']); ?> credits</td>
                                        <td><?php echo htmlspecialchars($v['sport_type']); ?></td>
                                        <td>
                                            <?php if($v['is_available']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Unavailable</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_venues.php?id=<?php echo $v['id']; ?>" class="btn-edit">Edit</a>
                                                <form method="post" action="admin_venues.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this venue?');">
                                                    <input type="hidden" name="venue_id" value="<?php echo $v['id']; ?>">
                                                    <button type="submit" name="delete_venue" class="btn-delete">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>