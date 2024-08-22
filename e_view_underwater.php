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
$userPage = isset($_GET['userPage']) ? $_GET['userPage'] : 1;
$allPage = isset($_GET['allPage']) ? $_GET['allPage'] : 1;
$userStart = ($userPage - 1) * $limit;
$allStart = ($allPage - 1) * $limit;

// Underwater Ruins discovered by the user
$sql = "SELECT u.* FROM UnderwaterRuins u JOIN Expeditioners ex ON u.discovered_by = ex.id WHERE ex.username = '".$_SESSION['username']."' ORDER BY u.name LIMIT $userStart, $limit";
$result = $conn->query($sql);
$userUnderwaterRuins = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userUnderwaterRuins[] = $row;
    }
}

// All Underwater Ruins
$sql = "SELECT u.* FROM UnderwaterRuins u ORDER BY u.name LIMIT $allStart, $limit";
$result = $conn->query($sql);
$allUnderwaterRuins = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allUnderwaterRuins[] = $row;
    }
}

// Pagination links for user discoveries
$sql = "SELECT COUNT(*) as total FROM UnderwaterRuins u JOIN Expeditioners ex ON u.discovered_by = ex.id WHERE ex.username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$userTotal = $result->fetch_assoc()['total'];
$userPages = ceil($userTotal / $limit);
$userPrevPage = $userPage - 1;
$userNextPage = $userPage + 1;
$userFirstPage = 1;
$userLastPage = $userPages;
$userPrevPrevPage = $userPage - 2;
$userNextNextPage = $userPage + 2;
$userPrevOnePage = $userPage - 1;
$userNextOnePage = $userPage + 1;

// Pagination links for all discoveries
$sql = "SELECT COUNT(*) as total FROM UnderwaterRuins";
$result = $conn->query($sql);
$allTotal = $result->fetch_assoc()['total'];
$allPages = ceil($allTotal / $limit);
$allPrevPage = $allPage - 1;
$allNextPage = $allPage + 1;
$allFirstPage = 1;
$allLastPage = $allPages;
$allPrevPrevPage = $allPage - 2;
$allNextNextPage = $allPage + 2;
$allPrevOnePage = $allPage - 1;
$allNextOnePage = $allPage + 1;

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Underwater Ruin Discoveries</title>
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
                <h1>Underwater Ruin Discoveries</h1>

                <h2>Underwater Ruins Discovered by You</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Underwater Ruin Name</th>
                            <th>Location</th>
                            <th>Age</th>
                            <th>Discovery Date</th>
                            <th>View More</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($userUnderwaterRuins) > 0) { ?>
                            <?php foreach ($userUnderwaterRuins as $underwaterRuin) { ?>
                                <tr>
                                    <td><?php echo $underwaterRuin['name']; ?></td>
                                    <td><?php echo $underwaterRuin['location']; ?></td>
                                    <td><?php echo $underwaterRuin['age']; ?></td>
                                    <td><?php echo $underwaterRuin['discovery_date']; ?></td>
                                    <td><a href="e_view_underwater_ruin_details.php?id=<?php echo $underwaterRuin['id']; ?>" class="btn btn-info">View More</a></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5">No discoveries found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <ul class="pagination-list">
                        <?php if ($userPage > 1) { ?>
                            <li><a href="?userPage=<?php echo $userFirstPage; ?>">First</a></li>
                            <li><a href="?userPage=<?php echo $userPrevPage; ?>">Prev</a></li>
                            <?php if ($userPage > 3) { ?>
                                <li><a href="?userPage=<?php echo $userPrevPrevPage; ?>"><?php echo $userPrevPrevPage; ?></a></li>
                            <?php } ?>
                            <?php if ($userPage > 2) { ?>
                                <li><a href="?userPage=<?php echo $userPrevOnePage; ?>"><?php echo $userPrevOnePage; ?></a></li>
                            <?php } ?>
                        <?php } ?>
                        <li class="active"><a href="#"><?php echo $userPage; ?></a></li>
                        <?php if ($userPage < $userPages) { ?>
                            <?php if ($userPage < $userPages - 1) { ?>
                                <li><a href="?userPage=<?php echo $userNextOnePage; ?>"><?php echo $userNextOnePage; ?></a></li>
                            <?php } ?>
                            <?php if ($userPage < $userPages - 2) { ?>
                                <li><a href="?userPage=<?php echo $userNextNextPage; ?>"><?php echo $userNextNextPage; ?></a></li>
                            <?php } ?>
                            <li><a href="?userPage=<?php echo $userNextPage; ?>">Next</a></li>
                            <li><a href="?userPage=<?php echo $userLastPage; ?>">Last</a></li>
                        <?php } ?>
                    </ul>
                </div>

                <h2>All Underwater Ruins</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Underwater Ruin Name</th>
                            <th>Location</th>
                            <th>Age</th>
                            <th>Discovery Date</th>
                            <th>View More</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUnderwaterRuins as $underwaterRuin) { ?>
                            <tr>
                                <td><?php echo $underwaterRuin['name']; ?></td>
                                <td><?php echo $underwaterRuin['location']; ?></td>
                                <td><?php echo $underwaterRuin['age']; ?></td>
                                <td><?php echo $underwaterRuin['discovery_date']; ?></td>
                                <td><a href="e_view_underwater_ruin_details.php?id=<?php echo $underwaterRuin['id']; ?>" class="btn btn-info">View More</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <ul class="pagination-list">
                        <?php if ($allPage > 1) { ?>
                            <li><a href="?allPage=<?php echo $allFirstPage; ?>">First</a></li>
                            <li><a href="?allPage=<?php echo $allPrevPage; ?>">Prev</a></li>
                            <?php if ($allPage > 3) { ?>
                                <li><a href="?allPage=<?php echo $allPrevPrevPage; ?>"><?php echo $allPrevPrevPage; ?></a></li>
                            <?php } ?>
                            <?php if ($allPage > 2) { ?>
                                <li><a href="?allPage=<?php echo $allPrevOnePage; ?>"><?php echo $allPrevOnePage; ?></a></li>
                            <?php } ?>
                        <?php } ?>
                        <li class="active"><a href="#"><?php echo $allPage; ?></a></li>
                        <?php if ($allPage < $allPages) { ?>
                            <?php if ($allPage < $allPages - 1) { ?>
                                <li><a href="?allPage=<?php echo $allNextOnePage; ?>"><?php echo $allNextOnePage; ?></a></li>
                            <?php } ?>
                            <?php if ($allPage < $allPages - 2) { ?>
                                <li><a href="?allPage=<?php echo $allNextNextPage; ?>"><?php echo $allNextNextPage; ?></a></li>
                            <?php } ?>
                            <li><a href="?allPage=<?php echo $allNextPage; ?>">Next</a></li>
                            <li><a href="?allPage=<?php echo $allLastPage; ?>">Last</a></li>
                        <?php } ?>
                    </ul>
                </div>

            </main>
        </div>
    </div>
</body>
</html>