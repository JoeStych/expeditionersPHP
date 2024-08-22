
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

// Paging system
$limit = 10;
$page = 1;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}
$start = ($page - 1) * $limit;

$sql = "SELECT *, weight + height + width + depth AS value FROM Artifacts ORDER BY value DESC LIMIT $start, $limit";
$result = $conn->query($sql);

// Get total number of pages
$sql_total = "SELECT COUNT(*) AS total FROM Artifacts";
$result_total = $conn->query($sql_total);
$total = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Valuable Artifacts</title>
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
        .fade-in {
            opacity: 0;
            transition: opacity 1s;
        }
        .fade-in.show {
            opacity: 1;
        }
        .button-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .button {
            width: 300px;
            margin: 10px;
            white-space: normal;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Valuable Artifacts</h1>
    </div>
    <div class="button-column">
        <a href="welcome_admin.php" class="btn btn-primary button fade-in">Back to Homepage</a>
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Material</th>
                <th>Weight</th>
                <th>Height</th>
                <th>Width</th>
                <th>Depth</th>
                <th>Discovery Date</th>
                <th>Discovery Location</th>
                <th>Value</th>
            </tr>
            <?php if ($result->num_rows > 0) { ?>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["name"]; ?></td>
                        <td><?php echo $row["description"]; ?></td>
                        <td><?php echo $row["material"]; ?></td>
                        <td><?php echo $row["weight"]; ?></td>
                        <td><?php echo $row["height"]; ?></td>
                        <td><?php echo $row["width"]; ?></td>
                        <td><?php echo $row["depth"]; ?></td>
                        <td><?php echo $row["discovery_date"]; ?></td>
                        <td><?php echo $row["discovery_location"]; ?></td>
                        <td><?php echo $row["value"]; ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="10">No artifacts found</td>
                </tr>
            <?php } ?>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php if ($page > 1) { ?>
                    <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                <?php } ?>
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) { ?>
                    <li class="page-item <?php if ($i == $page) { echo 'active'; } ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php } ?>
                <?php if ($page < $total_pages) { ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a></li>
                <?php } ?>
            </ul>
        </nav>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var fadeIns = document.querySelectorAll('.fade-in');
            fadeIns.forEach(function(fadeIn) {
                fadeIn.classList.add('show');
            });
        });
    </script>
</body>
</html>