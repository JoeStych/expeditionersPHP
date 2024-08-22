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
$sql = "SELECT id FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerId = $result->fetch_assoc()['id'];

// Get the conversation ID
$convoid = $_GET['id'];

// Get the name of the other person in the conversation
$sql = "SELECT IF(e1.id = '$expeditionerId', e2.name, e1.name) AS other_expeditioner_name 
        FROM conversations c 
        JOIN Expeditioners e1 ON c.exp1 = e1.id 
        JOIN Expeditioners e2 ON c.exp2 = e2.id 
        WHERE c.convoid = '$convoid'";
$result = $conn->query($sql);
$otherExpeditionerName = $result->fetch_assoc()['other_expeditioner_name'];

// Mark all messages from the other person as seen
$sql = "UPDATE messages SET seen = 1 WHERE convoid = '$convoid' AND senderid != '$expeditionerId'";
$conn->query($sql);

// Get the messages
$sql = "SELECT m.content, m.send_time, e.name AS expeditioner_name, m.seen 
        FROM messages m 
        JOIN Expeditioners e ON m.senderid = e.id 
        WHERE m.convoid = '$convoid' 
        ORDER BY send_time ASC";
$result = $conn->query($sql);
$messages = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Message</title>
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
                <h1>Conversation with <?php echo $otherExpeditionerName; ?></h1>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Expeditioner</th>
                            <th>Message</th>
                            <th>Time</th>
                            <th>Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message) { ?>
                            <tr>
                                <td><?php echo $message['expeditioner_name']; ?></td>
                                <td><?php echo $message['content']; ?></td>
                                <td><?php echo $message['send_time']; ?></td>
                                <td><?php echo $message['seen'] == 1 ? 'Yes' : 'No'; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <form action="send_message.php" method="post">
                    <input type="hidden" name="convoid" value="<?php echo $convoid; ?>">
                    <textarea name="content" class="form-control" rows="5"></textarea>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>

                <button class="btn btn-secondary" onclick="window.location.href='e_messages.php'">Return to Messages</button>
            </main>
        </div>
    </div>
</body>
</html>