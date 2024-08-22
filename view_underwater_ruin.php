<?php
// view_underwater_ruin.php
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

$sql = "SELECT u.id, u.name, u.location, u.description, u.age, u.discovery_date, e.name as expedition_name, ex.name as discovered_by FROM UnderwaterRuins u JOIN Expeditions e ON u.expedition_id = e.id JOIN Expeditioners ex ON u.discovered_by = ex.id WHERE u.id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Underwater Ruin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Underwater Ruin</h1>
        <table class="table table-striped">
            <tr>
                <th>Name:</th>
                <td><?php echo $row['name']; ?></td>
            </tr>
            <tr>
                <th>Location:</th>
                <td><?php echo $row['location']; ?></td>
            </tr>
            <tr>
                <th>Description:</th>
                <td><?php echo $row['description']; ?></td>
            </tr>
            <tr>
                <th>Age:</th>
                <td><?php echo $row['age']; ?></td>
            </tr>
            <tr>
                <th>Discovery Date:</th>
                <td><?php echo $row['discovery_date']; ?></td>
            </tr>
            <tr>
                <th>Expedition Name:</th>
                <td><?php echo $row['expedition_name']; ?></td>
            </tr>
            <tr>
                <th>Discovered By:</th>
                <td><?php echo $row['discovered_by']; ?></td>
            </tr>
        </table>
        <a href="manage_underwater_ruins.php" class="btn btn-primary">Back to Manage Underwater Ruins</a>
    </div>
</body>
</html>

<?php
} else {
    echo "Underwater ruin not found";
}
?>