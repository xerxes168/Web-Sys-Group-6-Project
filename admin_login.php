<?php
// Include authentication functions
require_once 'admin_auth.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if already logged in as admin
if (isset($_SESSION['member_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header("Location: admin_panel.php");
    exit;
}

// Initialize variables
$errorMsg = "";
$email = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password']; // Don't sanitize password
    
    // Validate input
    if (empty($email) || empty($password)) {
        $errorMsg = "Please enter both email and password.";
    } else {
        // Authenticate user
        $user = authenticateAdmin($email, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['member_id'] = $user['member_id'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect to admin panel
            header("Location: admin_panel.php");
            exit;
        } else {
            $errorMsg = "Invalid email or password, or you don't have admin privileges.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Admin Login</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 450px;
            margin: 80px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #5f52b0;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-logo {
            max-width: 100px;
            margin-bottom: 20px;
        }
        .login-form {
            margin-bottom: 20px;
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background-color: #5f52b0;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .login-button:hover {
            background-color: #4a4098;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        .login-footer a {
            color: #5f52b0;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <?php if (file_exists("images/logo.png")): ?>
                    <img src="images/logo.png" alt="HoopSpaces Logo" class="login-logo">
                <?php endif; ?>
                <h1>Admin Login</h1>
                <p>Enter your credentials to access the admin dashboard</p>
            </div>
            
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>
            
            <div class="login-form">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="login-button">Log In</button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Return to <a href="index.php">Home Page</a></p>
            </div>
        </div>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
</body>
</html>