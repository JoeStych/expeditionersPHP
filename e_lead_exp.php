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

// Get the expeditioner's ID
$sql = "SELECT id FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerID = $result->fetch_assoc()['id'];

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Expeditions led by the user
$sql = "SELECT * FROM Expeditions WHERE leader_id = $expeditionerID AND start_date >= CURDATE() LIMIT $start, $limit";
$result = $conn->query($sql);
$expeditions = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expeditions[] = $row;
    }
}

// Pagination links
$sql = "SELECT COUNT(*) as total FROM Expeditions WHERE leader_id = $expeditionerID AND start_date >= CURDATE()";
$result = $conn->query($sql);
$total = $result->fetch_assoc()['total'];
$pages = ceil($total / $limit);
$prevPage = $page - 1;
$nextPage = $page + 1;
$firstPage = 1;
$lastPage = $pages;
$prevPrevPage = $page - 2;
$nextNextPage = $page + 2;

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Expeditions</title>
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
        .pagination {
            margin-top: 20px;
        }
        .pagination li {
            margin-right: 10px;
        }
        .pagination li a {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f0f0f0;
            color: #337ab7;
            text-decoration: none;
        }
        .pagination li a:hover {
            background-color: #337ab7;
            color: #fff;
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
                    <li class="nav-item">
                        <a class="nav-link active" href="e_lead_exp.php">Manage Expeditions</a>
                    </li>
                </ul>
            </nav>

            <main class="col-sm-9 ml-sm-auto col-md-10 pt-3 main-content">
                <h1>Manage Expeditions</h1>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expeditions as $expedition) { ?>
                            <tr>
                                <td><?php echo $expedition['name']; ?></td>
                                <td><?php echo $expedition['start_date']; ?></td>
                                <td><?php echo $expedition['end_date']; ?></td>
                                <td><?php echo $expedition['location']; ?></td>
                                <td><?php echo $expedition['description']; ?></td>
                                <td><a href="e_manage_expedition.php?id=<?php echo $expedition['id']; ?>" class="btn btn-primary">Manage</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <ul class="pagination-list">
                        <?php if ($page > 1) { ?>
                            <li><a href="?page=<?php echo $firstPage; ?>">First</a></li>
                            <li><a href="?page=<?php echo $prevPrevPage; ?>">Prev Prev</a></li>
                            <li><a href="?page=<?php echo $prevPage; ?>">Prev</a></li>
                        <?php } ?>
                        <?php if ($page < $pages) { ?>
                            <li><a href="?page=<?php echo $nextPage; ?>">Next</a></li>
                            <li><a href="?page=<?php echo $nextNextPage; ?>">Next Next</a></li>
                            <li><a href="?page=<?php echo $lastPage; ?>">Last</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </main>
        </div>
    </div>
</body>
</html>