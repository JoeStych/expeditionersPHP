<?php
session_start();

// Check if the user is logged in
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

// Get the expeditioner's name
$sql = "SELECT name FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerName = $result->fetch_assoc()['name'];

// Get the underwater ruin id
$underwaterRuinId = $_GET['id'];

// Get the underwater ruin details
$sql = "SELECT u.*, ex.name as expedition_name, e.name as discoverer_name
        FROM UnderwaterRuins u
        JOIN Expeditions ex ON u.expedition_id = ex.id
        JOIN Expeditioners e ON u.discovered_by = e.id
        WHERE u.id = $underwaterRuinId";

$result = $conn->query($sql);
$underwaterRuin = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Underwater Ruin Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 50px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 90px 20px 20px;
            overflow-x: hidden;
            overflow-y: auto;
            border-right: 1px solid #eee;
        }
        .sidebar .nav {
            margin-bottom: 20px;
        }
        .sidebar .nav-item {
            width: 100%;
        }
        .sidebar .nav-item + .nav-item {
            margin-left: 0;
        }
        .sidebar .nav-link {
            border-radius: 0;
        }
        .main-content {
            margin-left: 200px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="#"><?php echo $expeditionerName; ?></a>
        <button class="btn btn-danger" onclick="window.location.href='logout.php'">Logout</button>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="welcome_exp.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_edit_profile.php">Modify Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_artifacts.php">View Artifact Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">View Underwater Ruin Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_plants.php">View Plantlife Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_animals.php">View Animal Life Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_injuries.php">View Injuries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_log_data.php">Log Discoveries from a Trip</a>
                    </li>
                </ul>
            </nav>

            <main class="col-sm-9 ml-sm-auto col-md-10 pt-3 main-content">
                <h1>Underwater Ruin Details</h1>

                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th>Underwater Ruin Name</th>
                            <td><?php echo $underwaterRuin['name']; ?></td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td><?php echo $underwaterRuin['location']; ?></td>
                        </tr>
                        <tr>
                            <th>Age</th>
                            <td><?php echo $underwaterRuin['age']; ?></td>
                        </tr>
                        <tr>
                            <th>Discovery Date</th>
                            <td><?php echo $underwaterRuin['discovery_date']; ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo $underwaterRuin['description']; ?></td>
                        </tr>
                        <tr>
                            <th>Expedition Name</th>
                            <td><?php echo $underwaterRuin['expedition_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Discoverer Name</th>
                            <td><?php echo $underwaterRuin['discoverer_name']; ?></td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn btn-info" onclick="window.location.href='e_view_underwater.php'">Back</button>

            </main>
        </div>
    </div>
</body>
</html>