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
$sql = "SELECT name, id 
        FROM Expeditioners 
        WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditioner = $result->fetch_assoc();
$expeditionerName = $expeditioner['name'];
$expeditionerId = $expeditioner['id'];

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Upcoming Expeditions
$sql = "SELECT e.* 
        FROM Expeditions e 
        JOIN ExpeditionLists el ON e.id = el.expedition_id 
        JOIN Expeditioners ex ON el.expeditioner_id = ex.id 
        WHERE ex.username = '".$_SESSION['username']."' AND e.start_date >= CURDATE() 
        LIMIT $start, $limit";
$result = $conn->query($sql);
$upcomingExpeditions = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcomingExpeditions[] = $row;
    }
}

// Recent Discoveries
$sql = "SELECT a.name, ex.name AS expeditioner_name, a.discovery_date 
        FROM Artifacts a 
        JOIN Expeditioners ex ON a.discovered_by = ex.id 
        ORDER BY discovery_date DESC 
        LIMIT $start, $limit";
$result = $conn->query($sql);
$recentDiscoveries = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentDiscoveries[] = $row;
    }
}

// Unread Messages
$sql = "SELECT m.content, m.send_time, e.name AS sender_name 
        FROM messages m 
        JOIN conversations c ON m.convoid = c.convoid 
        JOIN Expeditioners e ON m.senderid = e.id 
        WHERE (c.exp1 = '$expeditionerId' AND m.senderid != '$expeditionerId') 
        OR (c.exp2 = '$expeditionerId' AND m.senderid != '$expeditionerId') 
        AND m.seen = 0";
$result = $conn->query($sql);
$unreadMessages = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unreadMessages[] = $row;
    }
}

// Pagination links
$sql = "SELECT COUNT(*) as total 
        FROM Expeditions e 
        JOIN ExpeditionLists el ON e.id = el.expedition_id 
        JOIN Expeditioners ex ON el.expeditioner_id = ex.id 
        WHERE ex.username = '".$_SESSION['username']."' AND e.start_date >= CURDATE()";
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
    <title>Expeditioner Homepage</title>
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
                    <a class="nav-link active" href="#">Home</a>
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
                    <a class="nav-link" href="e_lead_exp.php">Manage Expeditions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="e_messages.php">View Messages</a>
                </li>
            </ul>
        </nav>

        <main class="col-sm-9 ml-sm-auto col-md-10 pt-3 main-content">
            <h1>Welcome, <?php echo $expeditionerName; ?></h1>
            <hr>

            <div class="row">
                <div class="col-md-6">
                    <h2>Upcoming Expeditions</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Expedition Name</th>
                                <th>Start Date</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($upcomingExpeditions) > 0) { ?>
                                <?php foreach ($upcomingExpeditions as $expedition) { ?>
                                    <tr>
                                        <td><?php echo $expedition['name']; ?></td>
                                        <td><?php echo $expedition['start_date']; ?></td>
                                        <td><?php echo $expedition['location']; ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="3">No upcoming expeditions.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h2>Recent Discoveries</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Discovery Name</th>
                                <th>Discovered By</th>
                                <th>Discovery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDiscoveries as $discovery) { ?>
                                <tr>
                                    <td><?php echo $discovery['name']; ?></td>
                                    <td><?php echo $discovery['expeditioner_name']; ?></td>
                                    <td><?php echo $discovery['discovery_date']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <br><br>

            <div class="row">
                <div class="col-md-12">
                    <h2>Unread Messages</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Message</th>
                                <th>From</th>
                                <th>Sent On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($unreadMessages) > 0) { ?>
                                <?php foreach ($unreadMessages as $message) { ?>
                                    <tr>
                                        <td><?php echo $message['content']; ?></td>
                                        <td><?php echo $message['sender_name']; ?></td>
                                        <td><?php echo $message['send_time']; ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="3">No unread messages.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

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