<?php
session_start();

// Initialize variables
$errorMsg = "";
$success = true;
$conn = null;

// Get sport type from URL parameter
$sport_type = isset($_GET['sport_type']) ? $_GET['sport_type'] : '';

// Validate sport type
$valid_sports = ['Basketball', 'Volleyball', 'Badminton', 'soccer'];
if (!in_array($sport_type, $valid_sports)) {
    // Redirect to sports selection page if no valid sport type
    header("Location: viewSports.php");
    exit;
}

// Format sport name for display
$sport_name = ucfirst($sport_type);

// Establish database connection
function getDbConnection()
{
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

// Get available venues for the selected sport
$venues = [];

if (getDbConnection()) {
    // Prepare the query to find venues suitable for the selected sport
    $stmt = $conn->prepare("SELECT id, name, location, capacity, hourly_rate, description, amenities 
                            FROM venues 
                            WHERE suitable_for_sports = 1 AND sport_type = ?
                            ORDER BY hourly_rate");
    
    if ($stmt) {
        $stmt->bind_param("s", $sport_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $venues[] = $row;
        }
        
        $stmt->close();
    } else {
        $errorMsg .= "<li>Error preparing venues query: " . $conn->error . "</li>";
        $success = false;
    }
    
    $conn->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - <?php echo htmlspecialchars($sport_name); ?> Venues</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/templatemo-style.css">
    <link rel="stylesheet" href="css/fontAwesome.css">
    <style>
        .venues-container {
            padding: 60px 0;
        }
        .venue-card {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .venue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .venue-info {
            padding: 20px;
            background-color: #fff;
        }
        .venue-info h3 {
            margin-top: 0;
            color: #333;
        }
        .venue-info p {
            color: #666;
            margin-bottom: 15px;
        }
        .venue-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .venue-detail {
            display: flex;
            align-items: center;
        }
        .venue-detail i {
            margin-right: 5px;
            color: #f4bc51;
        }
        .venue-amenities {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .venue-amenities span {
            display: inline-block;
            background-color: #f8f8f8;
            padding: 5px 10px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 4px;
            font-size: 12px;
        }
        .book-button {
            display: inline-block;
            background-color: #f4bc51;
            color: #fff;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .book-button:hover {
            background-color: #e8a430;
            text-decoration: none;
            color: #fff;
        }
        .back-button {
            display: inline-block;
            background-color: #ddd;
            color: #333;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }
        .back-button:hover {
            background-color: #ccc;
            text-decoration: none;
            color: #333;
        }
        .section-heading {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-heading h2 {
            color: #333;
            font-weight: 600;
        }
        .section-heading .line-dec {
            width: 60px;
            height: 3px;
            background-color: #f4bc51;
            margin: 10px auto 20px;
        }
        .sport-banner {
            background-image: url('img/sports/<?php echo htmlspecialchars($sport_type); ?>-banner.jpg');
            background-size: cover;
            background-position: center;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: white;
        }
        .sport-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .sport-banner-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .sport-banner h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .venue-price {
            font-size: 18px;
            font-weight: 700;
            color: #f4bc51;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="sport-banner">
        <div class="sport-banner-content">
            <h1><?php echo htmlspecialchars($sport_name); ?> Venues</h1>
            <p>Select a venue to book for your <?php echo htmlspecialchars($sport_name); ?> activity</p>
        </div>
    </div>

    <div class="container venues-container">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <a href="viewSports.php" class="back-button"><i class="fa fa-arrow-left"></i> Back to Sports</a>
                </div>
            </div>
        </div>

        <div class="section-heading">
            <h2>Available <?php echo htmlspecialchars($sport_name); ?> Venues</h2>
            <div class="line-dec"></div>
            <p>Choose from our selection of venues for your <?php echo htmlspecialchars($sport_name); ?> events</p>
        </div>

        <?php if (empty($venues)): ?>
            <div class="alert alert-info">
                <p>No venues are currently available for <?php echo htmlspecialchars($sport_name); ?>. Please check back later or try a different sport.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($venues as $venue): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="venue-card">
                            <div class="venue-info">
                                <h3><?php echo htmlspecialchars($venue['name']); ?></h3>
                                <div class="venue-details">
                                    <div class="venue-detail">
                                        <i class="fa fa-map-marker"></i>
                                        <span><?php echo htmlspecialchars($venue['location']); ?></span>
                                    </div>
                                    <div class="venue-detail">
                                        <i class="fa fa-users"></i>
                                        <span>Capacity: <?php echo htmlspecialchars($venue['capacity']); ?></span>
                                    </div>
                                </div>
                                <p><?php echo htmlspecialchars($venue['description']); ?></p>
                                <div class="venue-amenities">
                                    <?php
                                    $amenities = explode(',', $venue['amenities']);
                                    foreach ($amenities as $amenity):
                                        $amenity = trim($amenity);
                                        if (!empty($amenity)):
                                    ?>
                                        <span><i class="fa fa-check"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                                <div class="venue-price">
                                    $<?php echo number_format($venue['hourly_rate'], 2); ?> per hour
                                </div>
                                <a href="book_venue.php?venue_id=<?php echo $venue['id']; ?>&sport_type=<?php echo urlencode($sport_type); ?>" class="book-button">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include "inc/footer.inc.php"; ?>

    <script src="js/vendor/jquery.min.js"></script>
    <script src="js/vendor/bootstrap.min.js"></script>
</body>
</html>