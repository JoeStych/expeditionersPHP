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

// Get the expedition ID
$expeditionID = $_GET['id'];

// Get the expedition details
$sql = "SELECT * FROM Expeditions WHERE id = $expeditionID";
$result = $conn->query($sql);
$expedition = $result->fetch_assoc();

// Get the expeditioners who are already on the expedition
$sql = "SELECT e.name, e.id, e.phone_number, 
        (SELECT COUNT(*) FROM ExpeditionLists el WHERE el.expeditioner_id = e.id) as total_expeditions
        FROM Expeditioners e JOIN ExpeditionLists el ON e.id = el.expeditioner_id 
        WHERE el.expedition_id = $expeditionID";
$result = $conn->query($sql);
$expeditionersOnExpedition = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expeditionersOnExpedition[] = $row;
    }
}

// Get the available expeditioners
$sql = "SELECT e.name, e.id FROM Expeditioners e WHERE e.id NOT IN (
    SELECT el.expeditioner_id FROM ExpeditionLists el JOIN Expeditions ex ON el.expedition_id = ex.id
    WHERE (ex.start_date BETWEEN DATE_SUB('".$expedition['start_date']."', INTERVAL 1 WEEK) AND DATE_ADD('".$expedition['end_date']."', INTERVAL 1 WEEK))
    OR (ex.end_date BETWEEN DATE_SUB('".$expedition['start_date']."', INTERVAL 1 WEEK) AND DATE_ADD('".$expedition['end_date']."', INTERVAL 1 WEEK))
)";
$result = $conn->query($sql);
$availableExpeditioners = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $availableExpeditioners[] = $row;
    }
}

// Add expeditioners to the expedition
if (isset($_POST['expeditioners'])) {
    $expeditionersToAdd = $_POST['expeditioners'];
    foreach ($expeditionersToAdd as $expeditionerID) {
        $sql = "INSERT INTO ExpeditionLists (expedition_id, expeditioner_id) VALUES ($expeditionID, $expeditionerID)";
        $conn->query($sql);
    }
    header("Refresh:0");
}

// Remove expeditioners from the expedition
if (isset($_POST['remove_expeditioner'])) {
    $expeditionerID = $_POST['expeditioner_id'];
    $sql = "DELETE FROM ExpeditionLists WHERE expedition_id = $expeditionID AND expeditioner_id = $expeditionerID";
    $conn->query($sql);
    header("Refresh:0");
}

// Search for expeditioners
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $sql = "SELECT e.name, e.id FROM Expeditioners e WHERE e.name LIKE '%$searchTerm%' AND e.id NOT IN (
        SELECT el.expeditioner_id FROM ExpeditionLists el JOIN Expeditions ex ON el.expedition_id = ex.id
        WHERE (ex.start_date BETWEEN DATE_SUB('".$expedition['start_date']."', INTERVAL 1 WEEK) AND DATE_ADD('".$expedition['end_date']."', INTERVAL 1 WEEK))
        OR (ex.end_date BETWEEN DATE_SUB('".$expedition['start_date']."', INTERVAL 1 WEEK) AND DATE_ADD('".$expedition['end_date']."', INTERVAL 1 WEEK))
    )";
    $result = $conn->query($sql);
    $searchResults = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
} else {
    $searchResults = $availableExpeditioners;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Expedition</title>
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
                <h1>Manage Expedition</h1>

                <h2>Current Roster</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Total Expeditions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expeditionersOnExpedition as $expeditioner) { ?>
                            <tr>
                                <td><?php echo $expeditioner['name']; ?></td>
                                <td><?php echo $expeditioner['phone_number']; ?></td>
                                <td><?php echo $expeditioner['total_expeditions']; ?></td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="expeditioner_id" value="<?php echo $expeditioner['id']; ?>">
                                        <button type="submit" name="remove_expeditioner" class="btn btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <h2>Add Expeditioners</h2>
                <form action="" method="post">
                    <input type="text" name="search" placeholder="Search for expeditioners">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                <form action="" method="post">
                    <select name="expeditioners[]" multiple class="form-control">
                        <?php foreach ($searchResults as $expeditioner) { ?>
                            <option value="<?php echo $expeditioner['id']; ?>"><?php echo $expeditioner['name']; ?></option>
                        <?php } ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Add Expeditioners</button>
                </form>
            </main>
        </div>
    </div>
</body>
</html>