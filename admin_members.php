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
$members = [];
$conn = null;

// Form data for add/edit member
$member = [
    'member_id' => '',
    'fname' => '',
    'lname' => '',
    'email' => '',
    'username' => '',
    'credit' => 0,
    'role' => 'Member'
];

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $conn;
    
    // Define the config file path
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
        // Handle add/edit member form submission
        if (isset($_POST['save_member'])) {
            // Get form data
            $member['fname'] = sanitize_input($_POST['fname']);
            $member['lname'] = sanitize_input($_POST['lname']);
            $member['email'] = sanitize_input($_POST['email']);
            $member['username'] = sanitize_input($_POST['username']);
            $member['credit'] = floatval($_POST['credit']);
            $member['role'] = sanitize_input($_POST['role']);
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            // Validate form data
            $isValid = true;
            
            if (empty($member['fname'])) {
                $errorMsg = "First name is required.";
                $isValid = false;
            } elseif (empty($member['lname'])) {
                $errorMsg = "Last name is required.";
                $isValid = false;
            } elseif (empty($member['email'])) {
                $errorMsg = "Email is required.";
                $isValid = false;
            } elseif (empty($member['username'])) {
                $errorMsg = "Username is required.";
                $isValid = false;
            } elseif (!filter_var($member['email'], FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "Invalid email format.";
                $isValid = false;
            }
            
            if ($isValid) {
                // Check if it's an update or a new member
                if (isset($_POST['member_id']) && !empty($_POST['member_id'])) {
                    // Update existing member
                    $member['member_id'] = intval($_POST['member_id']);
                    
                    // Check if password should be updated
                    if (!empty($password)) {
                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $conn->prepare("UPDATE members SET 
                                            fname = ?, 
                                            lname = ?, 
                                            email = ?, 
                                            username = ?, 
                                            credit = ?, 
                                            role = ?,
                                            password = ?
                                            WHERE member_id = ?");
                        
                        $stmt->bind_param("ssssdssi", 
                            $member['fname'], 
                            $member['lname'], 
                            $member['email'], 
                            $member['username'], 
                            $member['credit'], 
                            $member['role'],
                            $hashedPassword,
                            $member['member_id']
                        );
                    } else {
                        // Don't update password
                        $stmt = $conn->prepare("UPDATE members SET 
                                            fname = ?, 
                                            lname = ?, 
                                            email = ?, 
                                            username = ?, 
                                            credit = ?, 
                                            role = ?
                                            WHERE member_id = ?");
                        
                        $stmt->bind_param("ssssdsi", 
                            $member['fname'], 
                            $member['lname'], 
                            $member['email'], 
                            $member['username'], 
                            $member['credit'], 
                            $member['role'],
                            $member['member_id']
                        );
                    }
                    
                    if ($stmt->execute()) {
                        $successMsg = "Member updated successfully!";
                        // Reset form
                        $member = [
                            'member_id' => '',
                            'fname' => '',
                            'lname' => '',
                            'email' => '',
                            'username' => '',
                            'credit' => 0,
                            'role' => 'Member'
                        ];
                    } else {
                        $errorMsg = "Error updating member: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    // Add new member
                    if (empty($password)) {
                        $errorMsg = "Password is required for new members.";
                    } else {
                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $conn->prepare("INSERT INTO members (
                                            fname, 
                                            lname, 
                                            email, 
                                            username, 
                                            password, 
                                            credit, 
                                            role) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
                        
                        $stmt->bind_param("sssssds", 
                            $member['fname'], 
                            $member['lname'], 
                            $member['email'], 
                            $member['username'], 
                            $hashedPassword,
                            $member['credit'], 
                            $member['role']
                        );
                        
                        if ($stmt->execute()) {
                            $successMsg = "New member added successfully!";
                            // Reset form
                            $member = [
                                'member_id' => '',
                                'fname' => '',
                                'lname' => '',
                                'email' => '',
                                'username' => '',
                                'credit' => 0,
                                'role' => 'Member'
                            ];
                        } else {
                            $errorMsg = "Error adding member: " . $stmt->error;
                        }
                        
                        $stmt->close();
                    }
                }
            }
        }
        
        // Handle delete member
        if (isset($_POST['delete_member']) && isset($_POST['member_id'])) {
            $member_id = intval($_POST['member_id']);
            
            // Check if member has any bookings
            $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM sports_bookings WHERE user_id = ?");
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row['booking_count'] > 0) {
                $errorMsg = "Cannot delete member with existing bookings. Please cancel their bookings first.";
            } else {
                // No bookings, safe to delete
                $stmt = $conn->prepare("DELETE FROM members WHERE member_id = ?");
                $stmt->bind_param("i", $member_id);
                
                if ($stmt->execute()) {
                    $successMsg = "Member deleted successfully!";
                } else {
                    $errorMsg = "Error deleting member: " . $stmt->error;
                }
                
                $stmt->close();
            }
        }
        
        // Load members after form processing
        $result = $conn->query("SELECT * FROM members ORDER BY lname, fname");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
        }
        
        // If edit mode is requested, load member data
        if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
            $member_id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $member = $result->fetch_assoc();
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
} else {
    // Initial page load to load all members
    if (getDbConnection()) {
        $result = $conn->query("SELECT * FROM members ORDER BY lname, fname");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
        }
        $conn->close();
    }
}
// Check if add or edit mode
$addMode = isset($_GET['action']) && $_GET['action'] == 'add';
$editMode = isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']);
$showForm = $addMode || $editMode;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Member Management</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <header role="banner" aria-label="Site header">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main id="main-content" aria-label="Member management">
        <div class="container admin-dashboard">
            <section class="admin-welcome" aria-labelledby="welcome-heading">
                <h1 id="welcome-heading">Member Management</h1>
                <p>Add, edit, or remove members from the system.</p>
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
                        <li><a href="admin_venues.php">Manage Venues</a></li>
                        <li><a href="admin_members.php" class="active" aria-current="page">Manage Members</a></li>
                        <li><a href="admin_credits.php">Credits Management</a></li>
                        <li><a href="admin_bookings.php">Booking Reports</a></li>
                    </ul>
                </nav>
                
                <div class="admin-content" aria-label="Member Management Content">
                    <?php if ($showForm): ?>
                        <!-- Member Form (for Add/Edit) -->
                        <section aria-labelledby="form-heading">
                            <h2 id="form-heading"><?php echo $editMode ? 'Edit Member' : 'Add New Member'; ?></h2>
                            <form method="post" action="admin_members.php" novalidate>
                                <?php if ($editMode): ?>
                                    <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fname" class="form-label">First Name <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                            <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($member['fname']); ?>" required aria-required="true" aria-describedby="fname-error">
                                            <?php if(isset($errorMsg) && strpos($errorMsg, "First name") !== false): ?>
                                            <div id="fname-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lname" class="form-label">Last Name <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                            <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($member['lname']); ?>" required aria-required="true" aria-describedby="lname-error">
                                            <?php if(isset($errorMsg) && strpos($errorMsg, "Last name") !== false): ?>
                                            <div id="lname-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email']); ?>" required aria-required="true" aria-describedby="email-error">
                                            <?php if(isset($errorMsg) && (strpos($errorMsg, "Email") !== false || strpos($errorMsg, "email") !== false)): ?>
                                            <div id="email-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username" class="form-label">Username <span class="sr-only">(required)</span><span aria-hidden="true">*</span></label>
                                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($member['username']); ?>" required aria-required="true" aria-describedby="username-error">
                                            <?php if(isset($errorMsg) && strpos($errorMsg, "Username") !== false): ?>
                                            <div id="username-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password" class="form-label"><?php echo $editMode ? 'Password (leave blank to keep current)' : 'Password <span class="sr-only">(required)</span><span aria-hidden="true">*</span>'; ?></label>
                                            <input type="password" id="password" name="password" class="form-control" <?php echo $addMode ? 'required aria-required="true"' : ''; ?> aria-describedby="password-error">
                                            <?php if(isset($errorMsg) && strpos($errorMsg, "Password") !== false): ?>
                                            <div id="password-error" class="error-message" role="alert"><?php echo $errorMsg; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="credit" class="form-label">Credits</label>
                                            <input type="number" id="credit" name="credit" class="form-control" value="<?php echo htmlspecialchars($member['credit']); ?>" min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="role" class="form-label">Role</label>
                                            <select id="role" name="role" class="form-control">
                                                <option value="Member" <?php echo ($member['role'] === 'Member') ? 'selected' : ''; ?>>Member</option>
                                                <option value="Admin" <?php echo ($member['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-buttons">
                                    <a href="admin_members.php" class="btn btn-cancel">Cancel</a>
                                    <button type="submit" name="save_member" class="btn btn-save">Save Member</button>
                                </div>
                            </form>
                        </section>
                    <?php else: ?>
                        <section aria-labelledby="members-list-heading">
                            <div class="admin-header">
                                <h2 id="members-list-heading">Manage Members</h2>
                                <a href="admin_members.php?action=add" class="btn btn-add">Add New Member</a>
                            </div>
                            
                            <?php if (empty($members)): ?>
                                <p>No members found. Click "Add New Member" to create one.</p>
                            <?php else: ?>
                                <div class="table-responsive" aria-label="Members table" tabindex="0" aria-live="polite">
                                    <table>
                                        <caption>List of members with their details and available actions</caption>
                                        <thead>
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Username</th>
                                                <th scope="col">Credits</th>
                                                <th scope="col">Role</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($members as $m): ?>
                                            <tr>
                                                <td><?php echo $m['member_id']; ?></td>
                                                <td><?php echo htmlspecialchars($m['fname'] ?? '') . ' ' . htmlspecialchars($m['lname'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($m['email'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($m['username'] ?? ''); ?></td>
                                                <td class="credit-amount"><?php echo number_format($m['credit'] ?? 0, 2); ?></td>
                                                <td>
                                                    <span class="role-badge role-<?php echo strtolower($m['role'] ?? 'member'); ?>">
                                                        <?php echo $m['role'] ?? 'Member'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_members.php?id=<?php echo $m['member_id']; ?>" class="btn btn-edit">Edit</a>
                                                        
                                                        <?php if ($_SESSION['member_id'] != $m['member_id']): ?>
                                                        <form method="post" action="admin_members.php" class="delete-form">
                                                            <input type="hidden" name="member_id" value="<?php echo $m['member_id']; ?>">
                                                            <button type="submit" name="delete_member" class="btn btn-delete" 
                                                                aria-label="Delete <?php echo htmlspecialchars($m['fname'] ?? '') . ' ' . htmlspecialchars($m['lname'] ?? ''); ?>"
                                                                data-confirm-message="Are you sure you want to delete this member?">Delete</button>
                                                        </form>
                                                        <?php endif; ?>
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
    
    <!-- Add popup confirmation for deletion -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = form.querySelector('button[data-confirm-message]');
                const message = button.getAttribute('data-confirm-message');
                
                if (!message || !confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    });
    </script>
<script src="js/main.js"></script>
</body>
</html>