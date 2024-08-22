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

$leader_id = $_GET['id'];

$sql = "SELECT e.name, COUNT(i.id) as injuries, COUNT(a.id) + COUNT(p.id) + COUNT(al.id) + COUNT(u.id) as discoveries
        FROM Expeditions e
        LEFT JOIN Injuries i ON e.id = i.expedition_id
        LEFT JOIN Artifacts a ON e.id = a.expedition_id
        LEFT JOIN PlantLife p ON e.id = p.expedition_id
        LEFT JOIN AnimalLife al ON e.id = al.expedition_id
        LEFT JOIN UnderwaterRuins u ON e.id = u.expedition_id
        WHERE e.leader_id = $leader_id
        GROUP BY e.name";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Expedition Leader Details</title>
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
        <h1>Expedition Leader Details</h1>
    </div>
    <div class="container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Expedition Name</th>
                    <th>Injuries</th>
                    <th>Discoveries</th>
                    <th>Ratio</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['injuries']; ?></td>
                        <td><?php echo $row['discoveries']; ?></td>
                        <td><?php echo round($row['discoveries'] / ($row['injuries'] + 1), 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="expedition_ratios.php" class="btn btn-primary">Back</a>
    </div>
</body>
</html>