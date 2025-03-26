<?php
// Start the session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['member_id']);
$credit_balance = 0;

// Initialize variables
$errorMsg = "";
$success = true;
$conn = null;

// Function to establish database connection
function getDbConnection() {
    global $errorMsg, $success, $conn;
    
    // Define the config file path relative to this script
    $configFile = '/var/www/private/db-config.ini';

    // Check if the file exists before parsing
    if (!file_exists($configFile)) {
        $errorMsg .= "<li>Database configuration file not found.</li>";
        $success = false;
        return false;
    }

    // Read database config
    $config = parse_ini_file($configFile);
    if ($config === false) {
        $errorMsg .= "<li>Failed to parse database config file.</li>";
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
        $errorMsg .= "<li>Host Connection failed: " . $conn->connect_error . "</li>";
        $success = false;
        return false;
    }
    
    return true;
}

// Get user's credit balance if logged in
if ($isLoggedIn && getDbConnection()) {
    $member_id = $_SESSION['member_id'];
    $stmt = $conn->prepare("SELECT credit FROM members WHERE member_id = ?");

    if ($stmt) {
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $member_data = $result->fetch_assoc();
            $credit_balance = $member_data['credit'];
        }
        $stmt->close();
    }
    
    // Close the connection
    $conn->close();
}

// Define credit packages
$credit_packages = [
    [
        'id' => 'basic',
        'name' => 'Basic Package',
        'credits' => 50,
        'price' => 25.00,
        'description' => 'Perfect for casual users. Good for about 2-3 venue bookings.',
        'popular' => false
    ],
    [
        'id' => 'standard',
        'name' => 'Standard Package',
        'credits' => 100,
        'price' => 45.00,
        'description' => 'Our most popular option. Good for 4-6 venue bookings.',
        'popular' => true
    ],
    [
        'id' => 'premium',
        'name' => 'Premium Package',
        'credits' => 200,
        'price' => 80.00,
        'description' => 'Best value for frequent bookers. Good for 8-12 venue bookings.',
        'popular' => false
    ],
    [
        'id' => 'ultimate',
        'name' => 'Ultimate Package',
        'credits' => 500,
        'price' => 175.00,
        'description' => 'Maximum savings for regular bookers. Good for 20+ venue bookings.',
        'popular' => false
    ]
];

// Handle form submission to redirect to payment processing
$payment_package = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buy_credits']) && $isLoggedIn) {
    $package_id = $_POST['package_id'];
    
    // Find the selected package
    foreach ($credit_packages as $package) {
        if ($package['id'] == $package_id) {
            $payment_package = $package;
            break;
        }
    }
    
    // If valid package selected, redirect to payment page
    if ($payment_package) {
        // Store package in session for payment processing
        $_SESSION['payment_package'] = $payment_package;
        header("Location: process_credit_purchase.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - Credits</title>
    <?php include "inc/head.inc.php"; ?>
    <style>
        .credits-header {
            background: linear-gradient(to right, #5f52b0, #ff589e);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        .credits-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .credits-balance {
            background-color: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            margin-top: 20px;
        }
        .credits-balance h3 {
            margin: 0;
            font-size: 1.8rem;
        }
        .credits-intro {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 40px;
        }
        .price-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 50px;
        }
        .price-card {
            width: 280px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .price-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .price-card.popular {
            border: 3px solid #ff589e;
            position: relative;
        }

        .popular-badge {
            position: absolute;
            top: -5px;
            right: 10px;
            background-color: #ff589e;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 20px;
            z-index: 10;
        }
        .card-header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .card-header h3 {
            margin: 0;
            color: #5f52b0;
        }
        .card-body {
            padding: 20px;
            text-align: center;
        }
        .credit-amount {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin: 15px 0;
        }
        .credit-amount span {
            font-size: 1.2rem;
            color: #666;
        }
        .price {
            font-size: 1.8rem;
            margin: 15px 0;
            color: #ff589e;
        }
        .price-card p {
            color: #666;
            margin-bottom: 20px;
            min-height: 60px;
        }
        .btn-buy {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #5f52b0;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .price-card.popular .btn-buy {
            background-color: #ff589e;
        }
        .btn-buy:hover {
            background-color: #4a4098;
            transform: translateY(-2px);
        }
        .price-card.popular .btn-buy:hover {
            background-color: #ff417e;
        }
        .login-prompt {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto 40px;
        }
        .login-prompt h3 {
            color: #5f52b0;
            margin-bottom: 15px;
        }
        .btn-login {
            display: inline-block;
            padding: 10px 25px;
            background-color: #ff589e;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 15px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #ff417e;
            text-decoration: none;
            color: white;
        }
        .how-it-works {
            background-color: #f8f9fa;
            padding: 50px 0;
            margin-top: 50px;
        }
        .how-it-works h2 {
            text-align: center;
            margin-bottom: 40px;
            color: #5f52b0;
        }
        .step-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-start;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background-color: #5f52b0;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .step-content {
            flex-grow: 1;
        }
        .step-content h3 {
            margin-top: 0;
            color: #333;
        }
        .faq-section {
            padding: 50px 0;
        }
        .faq-section h2 {
            text-align: center;
            margin-bottom: 40px;
            color: #5f52b0;
        }
        .faq-item {
            margin-bottom: 20px;
        }
        .faq-question {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            position: relative;
        }
        .faq-question:after {
            content: '+';
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 1.2rem;
        }
        .faq-question.active:after {
            content: '-';
        }
        .faq-answer {
            padding: 15px 20px;
            display: none;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php include "inc/nav.inc.php"; ?>

    <!-- Credits Header -->
    <section class="credits-header">
        <div class="container">
            <h1>GatherSpot Credits</h1>
            <p>The simple way to book venues for all your activities</p>
            
            <?php if ($isLoggedIn): ?>
            <div class="credits-balance">
                <h3>Your Balance: <?php echo number_format($credit_balance, 2); ?> credits</h3>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="container">
        <!-- Introduction -->
        <div class="credits-intro">
            <h2>Simple Pricing, No Surprises</h2>
            <p>GatherSpot credits work like a digital wallet for all your bookings. Purchase credits in advance and use them whenever you're ready to book a venue. The more credits you buy, the more you save!</p>
        </div>
        
        <!-- Login Prompt for Non-Logged In Users -->
        <?php if (!$isLoggedIn): ?>
        <div class="login-prompt">
            <h3>Ready to get started?</h3>
            <p>You need to be logged in to purchase credits. Already have an account? Log in to continue. New to GatherSpot? Register now to start booking venues!</p>
            <a href="login.php" class="btn-login">Log In</a>
            <a href="register.php" class="btn-login" style="background-color: #5f52b0; margin-left: 10px;">Register</a>
        </div>
        <?php endif; ?>
        
        <!-- Pricing Cards -->
        <div class="price-cards">
            <?php foreach ($credit_packages as $package): ?>
            <div class="price-card <?php echo ($package['popular']) ? 'popular' : ''; ?>">
                <?php if ($package['popular']): ?>
                <div class="popular-badge">MOST POPULAR</div>
                <?php endif; ?>
                
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                </div>
                
                <div class="card-body">
                    <div class="credit-amount">
                        <?php echo number_format($package['credits']); ?> <span>credits</span>
                    </div>
                    
                    <div class="price">
                        $<?php echo number_format($package['price'], 2); ?>
                    </div>
                    
                    <p><?php echo htmlspecialchars($package['description']); ?></p>
                    
                    <?php if ($isLoggedIn): ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                        <button type="submit" name="buy_credits" class="btn-buy">Buy Now</button>
                    </form>
                    <?php else: ?>
                    <button class="btn-buy" disabled title="Please log in to make a purchase">Log In to Buy</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- How It Works Section -->
        <div class="how-it-works">
            <div class="container">
                <h2>How It Works</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3>Purchase Credits</h3>
                                <p>Choose a credit package that suits your needs. The more credits you buy, the bigger the discount.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>Browse Venues</h3>
                                <p>Explore our wide range of venues for sports, events, and gatherings. Find the perfect space for your needs.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>Book with Credits</h3>
                                <p>When you find the perfect venue, book it instantly using your credits. No need to enter payment details again.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="step-card">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h3>Enjoy Your Booking</h3>
                                <p>Arrive at your booked venue and enjoy your activity! Your booking details will be in your account.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="container">
                <h2>Frequently Asked Questions</h2>
                
                <div class="faq-item">
                    <div class="faq-question">What are GatherSpot Credits?</div>
                    <div class="faq-answer">
                        <p>GatherSpot Credits are our digital currency used for booking venues on our platform. They work like a digital wallet, allowing you to pre-purchase credits and use them whenever you want to book a venue. This simplifies the payment process and lets you take advantage of bulk discounts.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Do credits expire?</div>
                    <div class="faq-answer">
                        <p>No, your GatherSpot Credits never expire. Once purchased, they remain in your account until you use them.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Can I get a refund for unused credits?</div>
                    <div class="faq-answer">
                        <p>Credits are non-refundable but can be used for any venue booking on GatherSpot. If you have special circumstances, please contact our customer support team to discuss your situation.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">How many credits do I need for a typical booking?</div>
                    <div class="faq-answer">
                        <p>The credit cost for bookings varies depending on the venue, time slot, and duration. Most sports venue bookings range from 20-50 credits for a 2-hour session. Event spaces may range from 50-200 credits depending on size and amenities.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Can I share my credits with friends?</div>
                    <div class="faq-answer">
                        <p>Currently, credits are tied to your individual account and cannot be transferred. However, you can book venues for group activities and invite friends to join you.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <?php include "inc/footer.inc.php"; ?>
    
    <script>
        // Toggle FAQ answers
        document.addEventListener('DOMContentLoaded', function() {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    this.classList.toggle('active');
                    const answer = this.nextElementSibling;
                    
                    if (answer.style.display === 'block') {
                        answer.style.display = 'none';
                    } else {
                        answer.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>