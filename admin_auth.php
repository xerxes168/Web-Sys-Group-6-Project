<?php
// Authentication functions for admin access

/**
 * Get database connection
 * @return mysqli|false Database connection or false on failure
 */
function authgetDbConnection() {
    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
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
        return false;
    }
    
    return $conn;
}

/**
 * Authenticate admin user
 * @param string $email User email
 * @param string $password User password
 * @return array|false User data on success, false on failure
 */
function authenticateAdmin($email, $password) {
    $conn = authgetDbConnection();
    if (!$conn) {
        return false;
    }
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT member_id, fname, lname, email, password, role FROM members WHERE email = ?");
    if (!$stmt) {
        $conn->close();
        return false;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if user has admin role
            if ($user['role'] === 'Admin') {
                $stmt->close();
                $conn->close();
                return $user;
            }
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Start session and check admin authentication
 * @param bool $redirect Whether to redirect non-admins
 * @return bool True if user is admin, false otherwise
 */
function checkAdminAuth($redirect = true) {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in and has admin role
    $isAdmin = isset($_SESSION['member_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    
    // Redirect if requested and user is not admin
    if ($redirect && !$isAdmin) {
        header("Location: login.php");
        exit;
    }
    
    return $isAdmin;
}

/**
 * Log out admin user
 */
function logoutAdmin() {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Unset admin-specific session variables
    unset($_SESSION['role']);
    
    // Optional: destroy the entire session
    // session_destroy();
    
    // Redirect to login page
    header("Location: admin_login.php");
    exit;
}

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>