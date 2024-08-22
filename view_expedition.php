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
    header('Location: manage_expeditions.php');
    exit;
}

$expedition_id = $_GET['id'];

$sql = "SELECT e.id, e.name, e.start_date, e.end_date, e.location, e.description, ex.name as leader_name FROM Expeditions e JOIN Expeditioners ex ON e.leader_id = ex.id WHERE e.id = $expedition_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: manage_expeditions.php');
    exit;
}

$expedition = $result->fetch_assoc();

$sql_expeditioners = "SELECT ex.name, ex.email, ex.phone_number, ex.address FROM ExpeditionLists el JOIN Expeditioners ex ON el.expeditioner_id = ex.id WHERE el.expedition_id = $expedition_id";
$result_expeditioners = $conn->query($sql_expeditioners);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Expedition Details</title>
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
        <h1>Expedition Details</h1>
    </div>
    <div class="container">
        <div style="padding: 20px;">
            <a href="manage_expeditions.php" class="btn btn-success">Back to Expeditions</a>
        </div>
        <h2>Expedition Information</h2>
        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <td><?php echo $expedition['name']; ?></td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td><?php echo $expedition['start_date']; ?></td>
            </tr>
            <tr>
                <th>End Date</th>
                <td><?php echo $expedition['end_date']; ?></td>
            </tr>
            <tr>
                <th>Location</th>
                <td><?php echo $expedition['location']; ?></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?php echo $expedition['description']; ?></td>
            </tr>
            <tr>
                <th>Leader</th>
                <td><?php echo $expedition['leader_name']; ?></td>
            </tr>
        </table>
        <h2>Expeditioners</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result_expeditioners->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone_number']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>