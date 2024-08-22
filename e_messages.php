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

// Get the expeditioner's ID
$sql = "SELECT id FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerId = $result->fetch_assoc()['id'];

// Get the expeditioner's name
$sql = "SELECT name FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerName = $result->fetch_assoc()['name'];

// Get the conversations
$sql = "SELECT c.convoid, e1.name AS exp1_name, e2.name AS exp2_name FROM conversations c JOIN Expeditioners e1 ON c.exp1 = e1.id JOIN Expeditioners e2 ON c.exp2 = e2.id WHERE c.exp1 = '$expeditionerId' OR c.exp2 = '$expeditionerId'";
$result = $conn->query($sql);
$conversations = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
}

// Get the latest message for each conversation
$latestMessages = array();
foreach ($conversations as $conversation) {
    $convoid = $conversation['convoid'];
    $sql = "SELECT m.content, m.send_time, e.name AS expeditioner_name FROM messages m JOIN Expeditioners e ON m.senderid = e.id WHERE m.convoid = '$convoid' ORDER BY send_time DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $latestMessages[$convoid] = $result->fetch_assoc();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Messages</title>
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
                        <a class="nav-link" href="e_lead_exp.php">Manage Expeditions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="e_messages.php">View Messages</a>
                    </li>
                </ul>
            </nav>

            <main class="col-sm-9 ml-sm-auto col-md-10 pt-3 main-content">
                <h1>View Messages</h1>

                <button class="btn btn-primary" onclick="window.location.href='new_conversation.php'">New Conversation</button>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Communicator</th>
                            <th>Message</th>
                            <th>Time</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conversations as $conversation) { ?>
                            <?php $latestMessage = $latestMessages[$conversation['convoid']]; ?>
                            <tr>
                                <td><?php echo $conversation['exp1_name'] == $expeditionerName ? $conversation['exp2_name'] : $conversation['exp1_name']; ?></td>
                                <td><?php echo substr($latestMessage['content'], 0, 50); ?>...</td>
                                <td><?php echo $latestMessage['send_time']; ?></td>
                                <td><a href="view_message.php?id=<?php echo $conversation['convoid']; ?>" class="btn btn-primary">View</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>