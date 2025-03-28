<?php
session_start();

// Initialize variables
$errorMsg = "";
$success = true;

// Establish database connection
function getDbConnection()
{
    global $errorMsg, $success;

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

    return $conn;
}

// Connect to database
$conn = getDbConnection();
$sportTypes = [];

if ($conn) {
    // Get unique sport types from the venues table
    $stmt = $conn->prepare("SELECT DISTINCT sport_type FROM venues WHERE suitable_for_sports = 1");
    
    if (!$stmt) {
        // If query fails, use default sport types
        $sportTypes = [
            ['id' => 'basketball', 'name' => 'Basketball', 'image' => 'img/sports/basketball.jpg', 'description' => 'Book a basketball court for your team practice or friendly match.'],
            ['id' => 'volleyball', 'name' => 'Volleyball', 'image' => 'img/sports/volleyball.jpg', 'description' => 'Reserve a volleyball court for your group or team.'],
            ['id' => 'badminton', 'name' => 'Badminton', 'image' => 'img/sports/badminton.jpg', 'description' => 'Book a badminton court for singles or doubles games.'],
            ['id' => 'soccer', 'name' => 'Soccer', 'image' => 'img/sports/soccer.jpg', 'description' => 'Reserve a soccer field for your team practice or match.']
        ];
    } else {
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sportType = $row['sport_type'];
                $imagePath = 'img/sports/' . $sportType . '.jpg';
                
                $sportTypes[] = [
                    'id' => $sportType,
                    'name' => ucfirst($sportType),
                    'image' => $imagePath,
                    'description' => 'Book a ' . ucfirst($sportType) . ' facility for your event.'
                ];
            }
            $stmt->close();
        } else {
            // If no sport types found in venues, use default sport types
            $sportTypes = [
                ['id' => 'basketball', 'name' => 'Basketball', 'image' => 'img/sports/basketball.jpg', 'description' => 'Book a basketball court for your team practice or friendly match.'],
                ['id' => 'volleyball', 'name' => 'Volleyball', 'image' => 'img/sports/volleyball.jpg', 'description' => 'Reserve a volleyball court for your group or team.'],
                ['id' => 'badminton', 'name' => 'Badminton', 'image' => 'img/sports/badminton.jpg', 'description' => 'Book a badminton court for singles or doubles games.'],
                ['id' => 'soccer', 'name' => 'Soccer', 'image' => 'img/sports/soccer.jpg', 'description' => 'Reserve a soccer field for your team practice or match.']
            ];
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GatherSpot - Browse Sports</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/templatemo-style.css">
    <link rel="stylesheet" href="css/fontAwesome.css">
    <style>
        .sports-container {
            padding: 60px 0;
        }
        .sport-card {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .sport-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .sport-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        .sport-info {
            padding: 20px;
            background-color: #fff;
        }
        .sport-info h3 {
            margin-top: 0;
            color: #333;
        }
        .sport-info p {
            color: #666;
            margin-bottom: 20px;
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
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container sports-container">
        <div class="section-heading">
            <h2>Book Your Sport Venues</h2>
            <div class="line-dec"></div>
            <p>Select a sport to view available venues and make your booking</p>
        </div>

        <div class="row">
            <?php foreach ($sportTypes as $sport): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="sport-card">
                        <div class="sport-image" style="background-image: url('<?php echo htmlspecialchars($sport['image']); ?>')"></div>
                        <div class="sport-info">
                            <h3><?php echo htmlspecialchars($sport['name']); ?></h3>
                            <p><?php echo htmlspecialchars($sport['description']); ?></p>
                            <a href="venues.php?sport_type=<?php echo urlencode($sport['id']); ?>" class="book-button">View Venues</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include "inc/footer.inc.php"; ?>

    <script src="js/vendor/jquery.min.js"></script>
    <script src="js/vendor/bootstrap.min.js"></script>
</body>
</html>