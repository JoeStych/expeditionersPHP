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

if (!isset($_GET['id'])) {
    header('Location: manage_artifacts.php');
    exit;
}

$artifact_id = $_GET['id'];

$sql = "SELECT a.id, a.name, a.description, a.material, a.weight, a.height, a.width, a.depth, a.discovery_date, a.discovery_location, e.name as expedition_name, ex.name as expeditioner_name FROM Artifacts a JOIN Expeditions e ON a.expedition_id = e.id JOIN Expeditioners ex ON a.discovered_by = ex.id WHERE a.id = $artifact_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: manage_artifacts.php');
    exit;
}

$artifact = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Artifact Details</title>
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
        <h1>Artifact Details</h1>
    </div>
    <div class="container">
        <div style="padding: 20px;">
            <a href="manage_artifacts.php" class="btn btn-success">Back to Artifacts</a>
        </div>
        <h2>Artifact Information</h2>
        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <td><?php echo $artifact['name']; ?></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?php echo $artifact['description']; ?></td>
            </tr>
            <tr>
                <th>Material</th>
                <td><?php echo $artifact['material']; ?></td>
            </tr>
            <tr>
                <th>Weight</th>
                <td><?php echo $artifact['weight']; ?></td>
            </tr>
            <tr>
                <th>Height</th>
                <td><?php echo $artifact['height']; ?></td>
            </tr>
            <tr>
                <th>Width</th>
                <td><?php echo $artifact['width']; ?></td>
            </tr>
            <tr>
                <th>Depth</th>
                <td><?php echo $artifact['depth']; ?></td>
            </tr>
            <tr>
                <th>Discovery Date</th>
                <td><?php echo $artifact['discovery_date']; ?></td>
            </tr>
            <tr>
                <th>Discovery Location</th>
                <td><?php echo $artifact['discovery_location']; ?></td>
            </tr>
            <tr>
                <th>Expedition</th>
                <td><?php echo $artifact['expedition_name']; ?></td>
            </tr>
            <tr>
                <th>Discovered By</th>
                <td><?php echo $artifact['expeditioner_name']; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>