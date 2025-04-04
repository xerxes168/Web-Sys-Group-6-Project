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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoopSpaces - Process Payment</title>
    <?php include "inc/head.inc.php"; ?>
    <link rel="stylesheet" href="css/process_credit_purchase.css">
</head>

<body>
    <!-- Skip to content link for keyboard users -->
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <header role="banner">
        <?php include "inc/nav.inc.php"; ?>
    </header>

    <main id="main-content" aria-label="Payment processing">
        <div class="payment-container">
            <section class="payment-header" aria-labelledby="checkout-heading">
                <h1 id="checkout-heading">Secure Checkout</h1>
                <p>Complete your payment to add credits to your account</p>
            </section>
            
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger" role="alert" aria-live="assertive">
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($paymentComplete): ?>
                <!-- Success Message -->
                <section class="payment-form" aria-labelledby="success-heading">
                    <div class="success-message" role="status" aria-live="polite">
                        <div class="success-icon" aria-hidden="true">
                            <i class="fa fa-check"></i>
                        </div>
                        <h2 id="success-heading">Payment Successful!</h2>
                        <p><?php echo number_format($package['credits']); ?> credits have been added to your account.</p>
                        <a href="credits.php" class="btn-continue">Continue</a>
                    </div>
                </section>
            <?php else: ?>
                <!-- Payment Form -->
                <section class="payment-form" aria-labelledby="payment-heading">
                    <h2 id="payment-heading" class="sr-only">Payment Details</h2>
                    
                    <!-- Package Summary -->
                    <section class="package-summary" aria-labelledby="summary-heading">
                        <h3 id="summary-heading"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <dl class="package-details">
                            <div class="package-row">
                                <dt>Credits:</dt>
                                <dd><?php echo number_format($package['credits']); ?> credits</dd>
                            </div>
                            <div class="package-row">
                                <dt>Price:</dt>
                                <dd>$<?php echo number_format($package['price'], 2); ?></dd>
                            </div>
                            <div class="package-row total">
                                <dt>Total:</dt>
                                <dd>$<?php echo number_format($package['price'], 2); ?></dd>
                            </div>
                        </dl>
                    </section>
                    
                    <!-- Payment Methods -->
                    <section class="payment-methods" aria-labelledby="methods-heading">
                        <h3 id="methods-heading">Payment Methods</h3>
                        <div class="payment-options" role="radiogroup" aria-label="Select payment method">
                            <div class="payment-method active" role="radio" aria-checked="true" tabindex="0">
                                <img src="img/credit-card-icon.png" alt="Credit Card" onerror="this.src='https://www.visa.com.sg/dam/VCOM/regional/ve/romania/blogs/hero-image/visa-logo-800x450.jpg'">
                                <span class="sr-only">Credit Card</span>
                            </div>
                            <div class="payment-method" role="radio" aria-checked="false" tabindex="0">
                                <img src="img/paypal-icon.png" alt="PayPal" onerror="this.src='https://i.pcmag.com/imagery/reviews/068BjcjwBw0snwHIq0KNo5m-15..v1602794215.png'">
                                <span class="sr-only">PayPal</span>
                            </div>
                            <div class="payment-method" role="radio" aria-checked="false" tabindex="0">
                                <img src="img/google-pay-icon.png" alt="Google Pay" onerror="this.src='https://www.flyhighenglish.com/wp-content/uploads/2019/04/Payment-Logos.jpg'">
                                <span class="sr-only">Google Pay</span>
                            </div>
                        </div>
                    </section>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" aria-labelledby="card-heading" novalidate>
                        <h3 id="card-heading">Card Details</h3>
                        
                        <div class="form-group">
                            <label for="card_name" id="label_card_name">Cardholder Name</label>
                            <input type="text" class="form-control" id="card_name" name="card_name" aria-labelledby="label_card_name" aria-required="true" placeholder="John Doe" required>
                            <div id="card_name_error" class="error-message" role="alert"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card_number" id="label_card_number">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" aria-labelledby="label_card_number" aria-required="true" aria-describedby="card_number_desc" placeholder="1234 5678 9012 3456" maxlength="16" required>
                            <div id="card_number_desc" class="sr-only">Enter 16-digit card number without spaces</div>
                            <div id="card_number_error" class="error-message" role="alert"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry_date" id="label_expiry">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry_date" name="expiry_date" aria-labelledby="label_expiry" aria-describedby="expiry_desc" aria-required="true" placeholder="MM/YY" maxlength="5" required>
                                <div id="expiry_desc" class="sr-only">Enter expiry date in format MM/YY</div>
                                <div id="expiry_date_error" class="error-message" role="alert"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv" id="label_cvv">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" aria-labelledby="label_cvv" aria-describedby="cvv_desc" aria-required="true" placeholder="123" maxlength="3" required>
                                <div id="cvv_desc" class="sr-only">Enter 3-digit security code from back of card</div>
                                <div id="cvv_error" class="error-message" role="alert"></div>
                            </div>
                        </div>
                        
                        <button type="submit" name="process_payment" class="btn-payment">Complete Payment</button>
                        
                        <div class="secure-badge" aria-hidden="true">
                            <i class="fa fa-lock"></i> Secure payment. Your payment information is encrypted.
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </main>
    
    <footer role="contentinfo">
        <?php include "inc/footer.inc.php"; ?>
    </footer>
    
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
            
            // Client-side validation
            const paymentForm = document.querySelector('form');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Clear previous error messages
                    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
                    
                    // Validate card name
                    const cardName = document.getElementById('card_name');
                    if (!cardName.value.trim()) {
                        document.getElementById('card_name_error').textContent = 'Cardholder name is required.';
                        isValid = false;
                    }
                    
                    // Validate card number
                    const cardNumber = document.getElementById('card_number');
                    if (cardNumber.value.length !== 16 || !/^\d+$/.test(cardNumber.value)) {
                        document.getElementById('card_number_error').textContent = 'Card number must be 16 digits.';
                        isValid = false;
                    }
                    
                    // Validate expiry date
                    const expiryDate = document.getElementById('expiry_date');
                    if (!expiryDate.value.match(/^(0[1-9]|1[0-2])\/\d{2}$/)) {
                        document.getElementById('expiry_date_error').textContent = 'Expiry date must be in MM/YY format.';
                        isValid = false;
                    }
                    
                    // Validate CVV
                    const cvv = document.getElementById('cvv');
                    if (cvv.value.length !== 3 || !/^\d+$/.test(cvv.value)) {
                        document.getElementById('cvv_error').textContent = 'CVV must be 3 digits.';
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }
            
            // Handle payment method selection with keyboard support
            const paymentMethods = document.querySelectorAll('.payment-method');
            if (paymentMethods.length > 0) {
                paymentMethods.forEach(method => {
                    method.addEventListener('click', function() {
                        selectPaymentMethod(this);
                    });
                    
                    method.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            selectPaymentMethod(this);
                        }
                    });
                });
            }
            
            function selectPaymentMethod(selectedMethod) {
                // Remove active class from all methods
                paymentMethods.forEach(m => {
                    m.classList.remove('active');
                    m.setAttribute('aria-checked', 'false');
                });
                
                // Add active class to selected method
                selectedMethod.classList.add('active');
                selectedMethod.setAttribute('aria-checked', 'true');
            }
        });
    </script>
</body>
</html>