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
    'description' => '',
    'amenities' => '',
    'image_url' => '',
    'sport_type' => 'Basketball'
];

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;
    
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists
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
                                        description = ?,
                                        amenities = ?,
                                        image_url = ?,
                                        sport_type = ?
                                        WHERE id = ?");
                    
                    $stmt->bind_param("ssiidissssi", 
                        $venue['name'], 
                        $venue['location'], 
                        $venue['capacity'], 
                        $venue['is_available'],
                        $venue['hourly_rate'], 
                        $venue['suitable_for_sports'], 
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
                                        description,
                                        amenities,
                                        image_url,
                                        sport_type) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->bind_param("ssiidissss", 
                        $venue['name'], 
                        $venue['location'], 
                        $venue['capacity'], 
                        $venue['is_available'],
                        $venue['hourly_rate'], 
                        $venue['suitable_for_sports'],
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
    // Load all venues on admin venues page
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

// Check if in add/edit mode
$addMode = isset($_GET['action']) && $_GET['action'] == 'add';
$editMode = isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']);
$showForm = $addMode || $editMode;

// Sport types array
$sportTypes = ['Basketball', 'Volleyball', 'Badminton', 'Soccer', 'Tennis', 'Table Tennis', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Manage Venues</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <header role="banner" aria-label="Site header">
        <?php include "inc/nav.inc.php"; ?>
    </header>
    
    <main id="main-content" aria-label="Venue management">
        <div class="container admin-dashboard">
            <section class="admin-welcome" aria-labelledby="welcome-heading">
                <h1 id="welcome-heading">Venue Management</h1>
                <p>Add, edit, or remove venues from the system.</p>
            </section>
            
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger" role="alert" aria-live="assertive">
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success" role="alert" aria-live="polite">
                    <?php echo $successMsg; ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-panel">
                <nav class="admin-sidebar" aria-label="Admin Navigation">
                    <ul>
                        <li><a href="admin_panel.php">Dashboard</a></li>
                        <li><a href="admin_venues.php" class="active" aria-current="page">Manage Venues</a></li>
                        <li><a href="admin_members.php">Manage Members</a></li>
                        <li><a href="admin_credits.php">Credits Management</a></li>
                        <li><a href="admin_bookings.php">Booking Reports</a></li>
                    </ul>
                </nav>
                
                <div class="admin-content">
                    <!-- Venue form for add/edit -->
                    <?php if ($showForm): ?>
                        <section aria-labelledby="venue-form-heading">
                            <h2 id="venue-form-heading"><?php echo $editMode ? 'Edit Venue' : 'Add New Venue'; ?></h2>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . ($editMode ? '?action=edit&id=' . $venue['id'] : ''); ?>" novalidate>
                                <?php if ($editMode): ?>
                                    <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                <?php endif; ?>
                                
                                <!-- Basic Venue Information -->
                                <fieldset>
                                    <legend>Basic Information</legend>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name" class="form-label">Venue Name <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($venue['name']); ?>" required aria-required="true" aria-describedby="name-error">
                                                <?php if(isset($errorMsg) && strpos($errorMsg, "name") !== false): ?>
                                                <div id="name-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="location" class="form-label">Location <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($venue['location']); ?>" required aria-required="true" aria-describedby="location-error">
                                                <?php if(isset($errorMsg) && strpos($errorMsg, "location") !== false): ?>
                                                <div id="location-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="capacity" class="form-label">Capacity <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                                <input type="number" id="capacity" name="capacity" class="form-control" value="<?php echo htmlspecialchars($venue['capacity']); ?>" min="1" required aria-required="true" aria-describedby="capacity-error">
                                                <?php if(isset($errorMsg) && strpos($errorMsg, "capacity") !== false): ?>
                                                <div id="capacity-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="hourly_rate" class="form-label">Hourly Rate (Credits) <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                                <input type="number" id="hourly_rate" name="hourly_rate" class="form-control" value="<?php echo htmlspecialchars($venue['hourly_rate']); ?>" min="0.01" step="0.01" required aria-required="true" aria-describedby="rate-error">
                                                <?php if(isset($errorMsg) && strpos($errorMsg, "rate") !== false): ?>
                                                <div id="rate-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sport_type" class="form-label">Sport Type</label>
                                                <select id="sport_type" name="sport_type" class="form-control">
                                                    <?php foreach ($sportTypes as $type): ?>
                                                    <option value="<?php echo $type; ?>" <?php echo ($venue['sport_type'] === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="is_available" name="is_available" <?php echo $venue['is_available'] ? 'checked' : ''; ?>>
                                            <label for="is_available">Venue is available for booking</label>
                                        </div>
                                    </div>
                                </fieldset>
                                
                                <!-- Venue Description -->
                                <fieldset>
                                    <legend>Description and Amenities</legend>
                                    
                                    <div class="form-group">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($venue['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="amenities" class="form-label">Amenities</label>
                                        <textarea id="amenities" name="amenities" class="form-control" rows="3" placeholder="List amenities separated by commas"><?php echo htmlspecialchars($venue['amenities']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="image_url" class="form-label">Image URL</label>
                                        <input type="url" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($venue['image_url']); ?>" placeholder="https://example.com/image.jpg">
                                    </div>
                                </fieldset>
                                
                                <!-- Venue Purpose -->
                                <fieldset>
                                    <legend>Venue Purpose</legend>
                                    
                                    <p>This venue is suitable for (check all that apply):</p>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="suitable_for_sports" name="suitable_for_sports" <?php echo $venue['suitable_for_sports'] ? 'checked' : ''; ?>>
                                                <label for="suitable_for_sports">Sports</label>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                
                                <div class="form-buttons">
                                    <a href="admin_venues.php" class="btn btn-cancel">Cancel</a>
                                    <button type="submit" name="save_venue" class="btn btn-save">Save Venue</button>
                                </div>
                            </form>
                        </section>
                    <?php else: ?>
                        <!-- Venues List -->
                        <section aria-labelledby="venue-list-heading">
                            <div class="admin-header">
                                <h2 id="venue-list-heading">Manage Venues</h2>
                                <a href="admin_venues.php?action=add" class="btn btn-add">Add New Venue</a>
                            </div>
                            
                            <?php if (empty($venues)): ?>
                                <p id="no-venues-message">No venues found. Click "Add New Venue" to create one.</p>
                            <?php else: ?>
                                <div class="table-responsive" aria-label="Venues table" tabindex="0">
                                    <table class="venue-table">
                                        <caption>List of venues with their details and available actions</caption>
                                        <thead>
                                            <tr>
                                                <th scope="col">Name</th>
                                                <th scope="col">Location</th>
                                                <th scope="col">Capacity</th>
                                                <th scope="col">Rate</th>
                                                <th scope="col">Sport Type</th>
                                                <th scope="col">Available</th>
                                                <th scope="col">Actions</th>
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
                                                    <span class="status-badge <?php echo $v['is_available'] ? 'bg-success' : 'bg-danger'; ?>" role="status">
                                                        <?php echo $v['is_available'] ? 'Available' : 'Unavailable'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_venues.php?id=<?php echo $v['id']; ?>" class="btn btn-edit">Edit</a>
                                                        <form method="post" action="admin_venues.php" class="delete-form">
                                                            <input type="hidden" name="venue_id" value="<?php echo $v['id']; ?>">
                                                            <button type="submit" name="delete_venue" class="btn btn-delete" 
                                                                aria-label="Delete <?php echo htmlspecialchars($v['name']); ?>"
                                                                data-confirm-message="Are you sure you want to delete this venue?">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Delete confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('.delete-form');
            
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = form.querySelector('button[data-confirm-message]');
                    const message = button.getAttribute('data-confirm-message');
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
<script src="js/main.js"></script>
</body>
</html>