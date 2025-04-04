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

// Initialize the sport types array
$sportTypes = [];

if (getDbConnection()) {
    // Get unique sport types from the venues table
    $stmt = $conn->prepare("SELECT DISTINCT sport_type FROM venues WHERE suitable_for_sports = 1");

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $sportType = $row['sport_type'];
            $imagePath = 'img/' . strtolower($sportType) . '.jpg';

            $sportTypes[] = [
                'id' => strtolower($sportType),
                'name' => ucfirst($sportType),
                'image' => $imagePath,
                'description' => 'Book a ' . ucfirst($sportType) . ' facility for your event.'
            ];
        }
        $stmt->close();
    } else {
        // Log error if query fails
        error_log("Error preparing sport types query: " . $conn->error);
    }

    $conn->close();
}

// If no sport types were found, you might want to handle this case
if (empty($sportTypes)) {
    // Display message or redirect
    echo "<div class='alert alert-info'>No sports available at the moment. Please check back later.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>HoopSpaces - Browse Sports</title>
    <?php include "inc/head.inc.php"; ?>

    <style>
        .sports-container {
            padding: 60px 0;
            min-height: calc(100vh - 90px);
        }

        .sport-card {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .sport-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
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
            background-color: #5f52b0;
            color: #ffffff;
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
    <main>
        <section class="container sports-container" aria-label="Sports Venues">
            <div class="section-heading">
                <h1>Book Your Sport Venues</h1>
                <div class="line-dec"></div>
                <p>Select a sport to view available venues and make your booking</p>
            </div>


            <div class="row">
                <?php foreach ($sportTypes as $sport): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="sport-card">
                            <div class="sport-image"
                                style="background-image: url('<?php echo htmlspecialchars($sport['image']); ?>')">
                            </div>
                            <div class="sport-info">
                                <h2><?php echo htmlspecialchars($sport['name']); ?></h2>
                                <p><?php echo htmlspecialchars($sport['description']); ?></p>
                                <a href="venues.php?sport_type=<?php echo urlencode($sport['id']); ?>"
                                    class="book-button">View Venues</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include "inc/footer.inc.php"; ?>

    <script src="js/vendor/jquery.min.js"></script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>