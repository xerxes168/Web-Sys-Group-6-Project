<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection function (same as your profile.php)
function getDbConnection() {
    $configFile = '/var/www/private/db-config.ini';
    if (!file_exists($configFile)) {
        return false;
    }
    $config = parse_ini_file($configFile);
    if ($config === false) {
        return false;
    }
    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );
    if ($conn->connect_error) {
        return false;
    }
    return $conn;
}

// Define the default profile picture path
$defaultPic = 'uploads/profile_pictures/default.jpeg';

// Check if a file was uploaded without errors
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    // Define allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
    $fileType = mime_content_type($fileTmpPath);

    if (!in_array($fileType, $allowedTypes)) {
        die("Error: Only JPG, PNG, and GIF files are allowed.");
    }

    // Define the upload directory (must be writable)
    $uploadDir = 'uploads/profile_pictures/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a unique file name using the user id and a timestamp
    $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $newFileName = $_SESSION['member_id'] . '_' . time() . '.' . $extension;
    $destPath = $uploadDir . $newFileName;

    // Move the file from the temporary location to the uploads folder
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Connect to the database
        $conn = getDbConnection();
        if ($conn) {
            // First, retrieve the current profile picture for the user
            $stmt = $conn->prepare("SELECT profile_picture FROM members WHERE member_id = ?");
            $stmt->bind_param("i", $_SESSION['member_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $oldPic = $defaultPic;
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (!empty($row['profile_picture'])) {
                    $oldPic = $row['profile_picture'];
                }
            }
            $stmt->close();

            // If the current profile picture is not the default, delete it from the server
            if ($oldPic !== $defaultPic && file_exists($oldPic)) {
                unlink($oldPic);
            }

            // Now update the user's record with the new file path
            $updateStmt = $conn->prepare("UPDATE members SET profile_picture = ? WHERE member_id = ?");
            $updateStmt->bind_param("si", $destPath, $_SESSION['member_id']);
            if ($updateStmt->execute()) {
                echo "Profile picture uploaded and updated successfully!";
            } else {
                echo "Database update failed: " . $conn->error;
            }
            $updateStmt->close();
            $conn->close();
        } else {
            echo "Error: Database connection failed.";
        }
    } else {
        echo "Error: Failed to move uploaded file.";
    }
} else {
    echo "Error: No file uploaded or an upload error occurred.";
}
?>
