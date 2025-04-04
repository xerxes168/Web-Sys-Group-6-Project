<?php
// Start the session
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Check if payment package is set in session
if (!isset($_SESSION['payment_package'])) {
    header("Location: credits.php");
    exit;
}

$package = $_SESSION['payment_package'];

// Initialize variables
$errorMsg = "";
$success = true;
$paymentComplete = false;
$conn = null;

// Process payment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    // Validate card details
    $cardNumber = $_POST['card_number'] ?? '';
    $cardName = $_POST['card_name'] ?? '';
    $expiryDate = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    if (strlen($cardNumber) !== 16 || !is_numeric($cardNumber)) {
        $errorMsg .= "Invalid card number. Must be 16 digits.<br>";
        $success = false;
    }
    
    if (empty($cardName)) {
        $errorMsg .= "Cardholder name is required.<br>";
        $success = false;
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiryDate)) {
        $errorMsg .= "Invalid expiry date. Format should be MM/YY.<br>";
        $success = false;
    }
    
    if (strlen($cvv) !== 3 || !is_numeric($cvv)) {
        $errorMsg .= "Invalid CVV. Must be 3 digits.<br>";
        $success = false;
    }
    
    // If validation passes, process payment
    if ($success) {
        // Connect to database and update user credits
        if (getDbConnection()) {
            $member_id = $_SESSION['member_id'];
            $credits_to_add = $package['credits'];
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update user credits
                $stmt = $conn->prepare("UPDATE members SET credit = credit + ? WHERE member_id = ?");
                $stmt->bind_param("di", $credits_to_add, $member_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update credits: " . $stmt->error);
                }
                
                $stmt->close();
                
                // Commit transaction
                $conn->commit();
                $paymentComplete = true;
                
                // Clear the package from session
                unset($_SESSION['payment_package']);
            } 
            catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $errorMsg = "Transaction failed: " . $e->getMessage();
                $success = false;
            }
            
            // Close connection
            $conn->close();
        }
    }
}

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $success, $conn;

    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists
    if (!file_exists($configFile)) {
        $errorMsg .= "Database configuration file not found.<br>";
        $success = false;
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg .= "Failed to parse database config file.<br>";
        $success = false;
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
        $errorMsg .= "Host Connection failed: " . $conn->connect_error . "<br>";
        $success = false;
        return false;
    }
    
    return true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Process Payment</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/process_credit_purchase.css">
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="payment-container">
        <div class="payment-header">
            <h2>Secure Checkout</h2>
            <p>Complete your payment to add credits to your account</p>
        </div>
        
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($paymentComplete): ?>
            <!-- Success Message -->
            <div class="payment-form">
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fa fa-check"></i>
                    </div>
                    <h2>Payment Successful!</h2>
                    <p><?php echo number_format($package['credits']); ?> credits have been added to your account.</p>
                    <a href="credits.php" class="btn-continue">Continue</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Payment Form -->
            <div class="payment-form">
                <!-- Package Summary -->
                <div class="package-summary">
                    <div class="package-name"><?php echo htmlspecialchars($package['name']); ?></div>
                    <div class="package-details">
                        <span>Credits:</span>
                        <span><?php echo number_format($package['credits']); ?> credits</span>
                    </div>
                    <div class="package-details">
                        <span>Price:</span>
                        <span>$<?php echo number_format($package['price'], 2); ?></span>
                    </div>
                    <div class="package-details">
                        <span>Total:</span>
                        <span>$<?php echo number_format($package['price'], 2); ?></span>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="payment-methods">
                    <div class="payment-method active">
                        <img src="img/credit-card-icon.png" alt="Credit Card" onerror="this.src='https://www.visa.com.sg/dam/VCOM/regional/ve/romania/blogs/hero-image/visa-logo-800x450.jpg'">
                    </div>
                    <div class="payment-method">
                        <img src="img/paypal-icon.png" alt="PayPal" onerror="this.src='https://i.pcmag.com/imagery/reviews/068BjcjwBw0snwHIq0KNo5m-15..v1602794215.png'">
                    </div>
                    <div class="payment-method">
                        <img src="img/google-pay-icon.png" alt="Google Pay" onerror="this.src='https://www.flyhighenglish.com/wp-content/uploads/2019/04/Payment-Logos.jpg'">
                    </div>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="card_name">Cardholder Name</label>
                        <input type="text" class="form-control" id="card_name" name="card_name" placeholder="John Doe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="16" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="process_payment" class="btn-payment">Complete Payment</button>
                    
                    <div class="secure-badge">
                        <i class="fa fa-lock"></i> Secure payment. Your payment information is encrypted.
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Format card number as user types
            const cardNumberInput = document.getElementById('card_number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    // Remove non-digits
                    let value = this.value.replace(/\D/g, '');
                    
                    // Limit to 16 digits
                    value = value.substring(0, 16);
                    
                    // Update the input value
                    this.value = value;
                });
            }
            
            // Format expiry date as user types (MM/YY)
            const expiryInput = document.getElementById('expiry_date');
            if (expiryInput) {
                expiryInput.addEventListener('input', function(e) {
                    // Remove non-digits
                    let value = this.value.replace(/\D/g, '');
                    
                    // Add slash after 2 digits
                    if (value.length > 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    
                    // Update the input value
                    this.value = value;
                });
            }
            
            // Ensure CVV is numbers only
            const cvvInput = document.getElementById('cvv');
            if (cvvInput) {
                cvvInput.addEventListener('input', function(e) {
                    // Remove non-digits
                    this.value = this.value.replace(/\D/g, '');
                });
            }
            
            // Handle payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method');
            if (paymentMethods.length > 0) {
                paymentMethods.forEach(method => {
                    method.addEventListener('click', function() {
                        // Remove active class from all methods
                        paymentMethods.forEach(m => m.classList.remove('active'));
                        
                        // Add active class to clicked method
                        this.classList.add('active');
                    });
                });
            }
        });
    </script>
</body>
</html>