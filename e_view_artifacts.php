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

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Artifacts discovered by the user
$sql = "SELECT a.* FROM Artifacts a JOIN Expeditioners ex ON a.discovered_by = ex.id WHERE ex.username = '".$_SESSION['username']."' ORDER BY a.name LIMIT $start, $limit";
$result = $conn->query($sql);
$userArtifacts = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userArtifacts[] = $row;
    }
}

// All artifacts
$sql = "SELECT a.* FROM Artifacts a ORDER BY a.name LIMIT $start, $limit";
$result = $conn->query($sql);
$allArtifacts = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allArtifacts[] = $row;
    }
}

// Pagination links
$sql = "SELECT COUNT(*) as total FROM Artifacts";
$result = $conn->query($sql);
$total = $result->fetch_assoc()['total'];
$pages = ceil($total / $limit);
$prevPage = $page - 1;
$nextPage = $page + 1;
$firstPage = 1;
$lastPage = $pages;
$prevPrevPage = $page - 2;
$nextNextPage = $page + 2;
$prevOnePage = $page - 1;
$nextOnePage = $page + 1;

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Artifact Discoveries</title>
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
            text-align: center;
        }
        .pagination li {
            display: inline-block;
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
        .pagination li.active a {
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
                <h1>Artifact Discoveries</h1>

                <h2>Artifacts Discovered by You</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Artifact Name</th>
                            <th>Material</th>
                            <th>Weight</th>
                            <th>Discovery Date</th>
                            <th>Discovery Location</th>
                            <th>View More</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($userArtifacts) > 0) { ?>
                            <?php foreach ($userArtifacts as $artifact) { ?>
                                <tr>
                                    <td><?php echo $artifact['name']; ?></td>
                                    <td><?php echo $artifact['material']; ?></td>
                                    <td><?php echo $artifact['weight']; ?></td>
                                    <td><?php echo $artifact['discovery_date']; ?></td>
                                    <td><?php echo $artifact['discovery_location']; ?></td>
                                    <td><a href="e_view_artifact_detail.php?id=<?php echo $artifact['id']; ?>" class="btn btn-info">View More</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6">No discoveries found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <h2>All Artifacts</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Artifact Name</th>
                            <th>Material</th>
                            <th>Weight</th>
                            <th>Discovery Date</th>
                            <th>Discovery Location</th>
                            <th>View More</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allArtifacts as $artifact) { ?>
                            <tr>
                                <td><?php echo $artifact['name']; ?></td>
                                <td><?php echo $artifact['material']; ?></td>
                                <td><?php echo $artifact['weight']; ?></td>
                                <td><?php echo $artifact['discovery_date']; ?></td>
                                <td><?php echo $artifact['discovery_location']; ?></td>
                                <td><a href="e_view_artifact_detail.php?id=<?php echo $artifact['id']; ?>" class="btn btn-info">View More</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <ul class="pagination-list">
                        <?php if ($page > 1) { ?>
                            <li><a href="?page=<?php echo $firstPage; ?>">First</a></li>
                            <li><a href="?page=<?php echo $prevPage; ?>">Prev</a></li>
                            <?php if ($page > 3) { ?>
                                <li><a href="?page=<?php echo $prevPrevPage; ?>"><?php echo $prevPrevPage; ?></a></li>
                            <?php } ?>
                            <?php if ($page > 2) { ?>
                                <li><a href="?page=<?php echo $prevOnePage; ?>"><?php echo $prevOnePage; ?></a></li>
                            <?php } ?>
                        <?php } ?>
                        <li class="active"><a href="#"><?php echo $page; ?></a></li>
                        <?php if ($page < $pages) { ?>
                            <?php if ($page < $pages - 1) { ?>
                                <li><a href="?page=<?php echo $nextOnePage; ?>"><?php echo $nextOnePage; ?></a></li>
                            <?php } ?>
                            <?php if ($page < $pages - 2) { ?>
                                <li><a href="?page=<?php echo $nextNextPage; ?>"><?php echo $nextNextPage; ?></a></li>
                            <?php } ?>
                            <li><a href="?page=<?php echo $nextPage; ?>">Next</a></li>
                            <li><a href="?page=<?php echo $lastPage; ?>">Last</a></li>
                        <?php } ?>
                    </ul>
                </div>

            </main>
        </div>
    </div>
</body>
</html>