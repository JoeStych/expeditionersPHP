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

$limit = 10; // number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$sql = "SELECT ex.name, e.leader_id, COUNT(i.id) as injuries, COUNT(a.id) + COUNT(p.id) + COUNT(al.id) + COUNT(u.id) as discoveries
        FROM Expeditioners ex
        LEFT JOIN Expeditions e ON e.leader_id = ex.id
        LEFT JOIN Injuries i ON e.id = i.expedition_id
        LEFT JOIN Artifacts a ON e.id = a.expedition_id
        LEFT JOIN PlantLife p ON e.id = p.expedition_id
        LEFT JOIN AnimalLife al ON e.id = al.expedition_id
        LEFT JOIN UnderwaterRuins u ON e.id = u.expedition_id
        GROUP BY e.leader_id";

$sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
$resultCount = $conn->query($sqlCount);
$total = $resultCount->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$sql .= " ORDER BY (COUNT(a.id) + COUNT(p.id) + COUNT(al.id) + COUNT(u.id)) / (COUNT(i.id) + 1) DESC, COUNT(i.id) ASC LIMIT $start, $limit";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Expedition Leaders</title>
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
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expedition Leaders</h1>
    </div>
    <div class="container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Injuries</th>
                    <th>Discoveries</th>
                    <th>Ratio</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['injuries']; ?></td>
                        <td><?php echo $row['discoveries']; ?></td>
                        <td><?php echo round($row['discoveries'] / ($row['injuries'] + 1), 2); ?></td>
                        <td><a href="view_more_ratios.php?id=<?php echo $leader_id; ?>" class="btn btn-primary">View More</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php if($page > 1) { ?>
                <a href="?page=1" class="btn btn-primary">First</a>
                <a href="?page=<?php echo $page - 1; ?>" class="btn btn-primary">Prev</a>
                <?php if($page > 3) { ?>
                    <span>...</span>
                <?php } ?>
                <?php if($page > 2) { ?>
                    <a href="?page=<?php echo $page - 2; ?>" class="btn btn-primary"><?php echo $page - 2; ?></a>
                <?php } ?>
                <a href="?page=<?php echo $page - 1; ?>" class="btn btn-primary"><?php echo $page - 1; ?></a>
            <?php } ?>
            <a href="?page=<?php echo $page; ?>" class="btn btn-primary active"><?php echo $page; ?></a>
            <?php if($page < $pages) { ?>
                <a href="?page=<?php echo $page + 1; ?>" class="btn btn-primary"><?php echo $page + 1; ?></a>
                <?php if($page < $pages - 1) { ?>
                    <a href="?page=<?php echo $page + 2; ?>" class="btn btn-primary"><?php echo $page + 2; ?></a>
                <?php } ?>
                <?php if($page < $pages - 2) { ?>
                    <span>...</span>
                <?php } ?>
                <a href="?page=<?php echo $page + 1; ?>" class="btn btn-primary">Next</a>
                <a href="?page=<?php echo $pages; ?>" class="btn btn-primary">Last</a>
            <?php } ?>
        </div>
        <a href="welcome_admin.php" class="btn btn-primary">Back</a>
    </div>
</body>
</html>