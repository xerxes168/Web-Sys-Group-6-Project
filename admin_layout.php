<?php
// admin_layout.php - A standard layout file for all admin pages

// Start the session
session_start();

// Include authentication functions
require_once 'admin_auth.php';

// Check if user is logged in and is an admin
checkAdminAuth();

// Initialize variables
$errorMsg = "";
$successMsg = "";
$conn = null;
$pageTitle = $pageTitle ?? "Admin Panel"; // Default page title
$activePage = $activePage ?? "dashboard"; // Default active page

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

// Common function to close database connection
function closeDbConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - <?php echo $pageTitle; ?></title>
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
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
        }
        .stat-card h3 {
            color: #5f52b0;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        .admin-action-buttons {
            display: flex;
            gap: 10px;
        }
        .admin-action-buttons a {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #5f52b0;
            transition: background-color 0.3s;
        }
        .admin-action-buttons a:hover {
            background-color: #4a4098;
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
        .data-table, .venue-table, .booking-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td, 
        .venue-table th, .venue-table td, 
        .booking-table th, .booking-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th, .venue-table th, .booking-table th {
            background-color: #f9f9f9;
            color: #333;
            font-weight: 600;
        }
        .data-table tr:hover, .venue-table tr:hover, .booking-table tr:hover {
            background-color: #f8f9fa;
        }
        .status-confirmed {
            color: #28a745;
            font-weight: 600;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: 600;
        }
        .status-pending {
            color: #ffc107;
            font-weight: 600;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        .pagination a {
            color: #5f52b0;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #5f52b0;
            color: white;
            border: 1px solid #5f52b0;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-form input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            background-color: #5f52b0;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
            margin-bottom: 8px;
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
        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-primary, .btn-add {
            color: white;
            background-color: #5f52b0;
            border: none;
            padding: 8px 16px;
        }
        .btn-primary:hover, .btn-add:hover {
            background-color: #4a4098;
            color: white;
        }
        .btn-edit {
            color: white;
            background-color: #17a2b8;
            border: none;
        }
        .btn-edit:hover {
            background-color: #138496;
            color: white;
        }
        .btn-danger, .btn-delete, .btn-cancel {
            color: white;
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover, .btn-delete:hover {
            background-color: #c82333;
            color: white;
        }
        .btn-save {
            color: white;
            background-color: #28a745;
            border: none;
            padding: 8px 16px;
        }
        .btn-save:hover {
            background-color: #218838;
            color: white;
        }
        .btn-cancel {
            background-color: #6c757d;
            border: none;
            padding: 8px 16px;
            text-decoration: none;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
            color: white;
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
        .role-admin {
            background-color: #5f52b0;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-member {
            background-color: #17a2b8;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .credit-amount {
            font-weight: bold;
            color: #28a745;
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
        .booking-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            background-color: #f9f9fb;
            padding: 15px;
            border-radius: 8px;
            align-items: center;
        }
        .booking-filters select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .booking-filters button {
            padding: 8px 16px;
            background-color: #5f52b0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .booking-filters button:hover {
            background-color: #4a4098;
        }
        .member-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .member-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .member-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .member-email {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .member-credits {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container admin-dashboard">
        <div class="admin-welcome">
            <h2>Welcome to <?php echo $pageTitle; ?></h2>
            <p><?php echo $pageDescription ?? "Manage your HoopSpaces resources from this administrative interface."; ?></p>
        </div>
        
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
        
        <div class="admin-panel">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="admin_panel.php" class="<?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="admin_venues.php" class="<?php echo $activePage == 'venues' ? 'active' : ''; ?>">Manage Venues</a></li>
                    <li><a href="admin_members.php" class="<?php echo $activePage == 'members' ? 'active' : ''; ?>">Manage Members</a></li>
                    <li><a href="admin_credits.php" class="<?php echo $activePage == 'credits' ? 'active' : ''; ?>">Credits Management</a></li>
                    <li><a href="admin_bookings.php" class="<?php echo $activePage == 'bookings' ? 'active' : ''; ?>">Booking Reports</a></li>
                </ul>
            </div>
            
            <div class="admin-content">
                <?php 
                // This is where page-specific content will go
                include $contentFile ?? "admin_content/dashboard.php"; 
                ?>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <?php closeDbConnection(); ?>
</body>
</html>