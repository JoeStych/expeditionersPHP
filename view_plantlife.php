<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expeditions";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];

$sql = "SELECT * FROM PlantLife WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $species = $row['species'];
    $description = $row['description'];
    $discovery_date = $row['discovery_date'];
    $discovery_location = $row['discovery_location'];
    $expedition_id = $row['expedition_id'];
    $discovered_by = $row['discovered_by'];

    // Get expedition information
    $sql = "SELECT * FROM Expeditions WHERE id = $expedition_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $expedition_name = $row['name'];
        $expedition_description = $row['description'];
    }

    // Get discoverer information
    $sql = "SELECT * FROM Expeditioners WHERE id = $discovered_by";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $discoverer_name = $row['name'];
        $discoverer_email = $row['email'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Plant Life</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Plant Life Details</h1>
    </div>
    <div class="container">
        <div style="padding: 20px;">
            <a href="manage_plants.php" class="btn btn-success">Back to Plant Life</a>
        </div>
        <h2>Plant Information</h2>
        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <td><?php echo $name; ?></td>
            </tr>
            <tr>
                <th>Species</th>
                <td><?php echo $species; ?></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?php echo $description; ?></td>
            </tr>
            <tr>
                <th>Discovery Date</th>
                <td><?php echo $discovery_date; ?></td>
            </tr>
            <tr>
                <th>Discovery Location</th>
                <td><?php echo $discovery_location; ?></td>
            </tr>
            <tr>
                <th>Expedition</th>
                <td><?php echo $expedition_name; ?></td>
            </tr>
            <tr>
                <th>Expedition Description</th>
                <td><?php echo $expedition_description; ?></td>
            </tr>
            <tr>
                <th>Discovered By</th>
                <td><?php echo $discoverer_name; ?></td>
            </tr>
            <tr>
                <th>Discoverer Email</th>
                <td><?php echo $discoverer_email; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>