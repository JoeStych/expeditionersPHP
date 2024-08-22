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

$sql = "
    SELECT 
        e.id, 
        e.name, 
        e.leader_id,
        e.location,
        (
            SELECT COUNT(*) FROM Artifacts a WHERE a.expedition_id = e.id
        ) + (
            SELECT COUNT(*) FROM PlantLife p WHERE p.expedition_id = e.id
        ) + (
            SELECT COUNT(*) FROM AnimalLife a WHERE a.expedition_id = e.id
        ) + (
            SELECT COUNT(*) FROM UnderwaterRuins u WHERE u.expedition_id = e.id
        ) AS discoveries,
        (
            (
                SELECT COUNT(*) FROM Artifacts a WHERE a.expedition_id = e.id AND a.material != 'gold'
            ) * 20 + 
            (
                SELECT COUNT(*) FROM Artifacts a WHERE a.expedition_id = e.id AND a.material = 'gold'
            ) * 100 + 
            (
                SELECT COUNT(*) FROM PlantLife p WHERE p.expedition_id = e.id
            ) * 3 + 
            (
                SELECT COUNT(*) FROM AnimalLife a WHERE a.expedition_id = e.id
            ) * 3 + 
            (
                SELECT COUNT(*) FROM UnderwaterRuins u WHERE u.expedition_id = e.id
            ) * 2000 - 
            (
                SELECT COUNT(*) FROM Injuries i WHERE i.expedition_id = e.id AND i.severity = 'minor'
            ) * 50 - 
            (
                SELECT COUNT(*) FROM Injuries i WHERE i.expedition_id = e.id AND i.severity = 'moderate'
            ) * 700 - 
            (
                SELECT COUNT(*) FROM Injuries i WHERE i.expedition_id = e.id AND i.severity = 'severe'
            ) * 1800
        ) AS score
    FROM 
        Expeditions e
    ORDER BY 
        score DESC
";

$result = $conn->query($sql);

$limit = 10; // number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$sqlCount = "SELECT COUNT(*) as total FROM Expeditions";
$resultCount = $conn->query($sqlCount);
$total = $resultCount->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$sql .= " LIMIT $start, $limit";
$result = $conn->query($sql);

$worstSql = "
    SELECT 
        e.id, 
        e.name, 
        e.leader_id,
        e.location,
        (
            SELECT COUNT(*) FROM Artifacts a WHERE a.expedition_id = e.id
        ) + (
            SELECT COUNT(*) FROM PlantLife p WHERE p.expedition_id = e.id
        ) + (
            SELECT COUNT(*) FROM AnimalLife a WHERE a.expedition_id = e.id
        ) + (
            SELECT COUNT(*) FROM UnderwaterRuins u WHERE u.expedition_id = e.id
        ) AS discoveries,
        (
            (
                SELECT COUNT(*) FROM Artifacts a WHERE a.expedition_id = e.id AND a.material != 'gold'
            ) * 20 + 
            (
                SELECT COUNT(*) FROM Artifacts a WHERE a.expedition_id = e.id AND a.material = 'gold'
            ) * 100 + 
            (
                SELECT COUNT(*) FROM PlantLife p WHERE p.expedition_id = e.id
            ) * 3 + 
            (
                SELECT COUNT(*) FROM AnimalLife a WHERE a.expedition_id = e.id
            ) * 3 + 
            (
                SELECT COUNT(*) FROM UnderwaterRuins u WHERE u.expedition_id = e.id
            ) * 2000 - 
            (
                SELECT COUNT(*) FROM Injuries i WHERE i.expedition_id = e.id AND i.severity = 'minor'
            ) * 50 - 
            (
                SELECT COUNT(*) FROM Injuries i WHERE i.expedition_id = e.id AND i.severity = 'moderate'
            ) * 700 - 
            (
                SELECT COUNT(*) FROM Injuries i WHERE i.expedition_id = e.id AND i.severity = 'severe'
            ) * 1800
        ) AS score
    FROM 
        Expeditions e
    ORDER BY 
        score ASC
    LIMIT 10
";

$worstResult = $conn->query($worstSql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profitable Expeditions</title>
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
        <h1>Profitable Expeditions</h1>
    </div>
    <div class="container">
        <h2>Best Scoring Expeditions</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Expedition Name</th>
                    <th>Leader</th>
                    <th>Location</th>
                    <th>Discoveries</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td>
                            <?php 
                                $leaderSql = "SELECT name FROM Expeditioners WHERE id = " . $row['leader_id'];
                                $leaderResult = $conn->query($leaderSql);
                                echo $leaderResult->fetch_assoc()['name'];
                            ?>
                        </td>
                        <td><?php echo $row['location']; ?></td>
                        <td><?php echo $row['discoveries']; ?></td>
                        <td><?php echo $row['score']; ?></td>
                        <td><a href="view_expedition.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View More</a></td>
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
        <h2>Worst Scoring Expeditions</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Expedition Name</th>
                    <th>Leader</th>
                    <th>Location</th>
                    <th>Discoveries</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $worstResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td>
                            <?php 
                                $leaderSql = "SELECT name FROM Expeditioners WHERE id = " . $row['leader_id'];
                                $leaderResult = $conn->query($leaderSql);
                                echo $leaderResult->fetch_assoc()['name'];
                            ?>
                        </td>
                        <td><?php echo $row['location']; ?></td>
                        <td><?php echo $row['discoveries']; ?></td>
                        <td><?php echo $row['score']; ?></td>
                        <td><a href="view_expedition.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View More</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>