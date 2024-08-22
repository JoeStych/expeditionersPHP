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

// Get the artifact id from the URL
$artifactId = $_GET['id'];

// Get the artifact information
$sql = "SELECT a.*, ex.name as expedition_name, e.name as discoverer_name 
        FROM Artifacts a 
        JOIN Expeditions ex ON a.expedition_id = ex.id 
        JOIN Expeditioners e ON a.discovered_by = e.id 
        WHERE a.id = '$artifactId'";
$result = $conn->query($sql);
$artifact = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Artifact Details</title>
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
        <a class="navbar-brand" href="#"><?php echo $_SESSION['username']; ?></a>
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
                        <a class="nav-link active" href="#">View Artifact Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_underwater.php">View Underwater Ruin Discoveries</a>
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
                <h1>Artifact Details</h1>

                <button class="btn btn-secondary" onclick="window.location.href='e_view_artifacts.php'">Back</button>

                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th>Artifact Name</th>
                            <td><?php echo $artifact['name'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Material</th>
                            <td><?php echo $artifact['material'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Weight</th>
                            <td><?php echo $artifact['weight'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Height</th>
                            <td><?php echo $artifact['height'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Width</th>
                            <td><?php echo $artifact['width'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Depth</th>
                            <td><?php echo $artifact['depth'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Discovery Date</th>
                            <td><?php echo $artifact['discovery_date'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Discovery Location</th>
                            <td><?php echo $artifact['discovery_location'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Expedition Name</th>
                            <td><?php echo $artifact['expedition_name'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Discovered By</th>
                            <td><?php echo $artifact['discoverer_name'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo $artifact['description'] ?? 'N/A'; ?></td>
                        </tr>
                    </tbody>
                </table>

            </main>
        </div>
    </div>
</body>
</html>