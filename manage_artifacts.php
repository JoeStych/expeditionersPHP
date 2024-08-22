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

$searchBy = isset($_GET['search_by']) ? $_GET['search_by'] : '';
$searchValue = isset($_GET['search_value']) ? $_GET['search_value'] : '';

$sql = "SELECT a.id, a.name, a.material, a.discovery_date, a.discovery_location FROM Artifacts a";

if ($searchBy && $searchValue) {
    if ($searchBy == 'name') {
        $sql .= " WHERE a.name LIKE '%$searchValue%'";
    } elseif ($searchBy == 'material') {
        $sql .= " WHERE a.material LIKE '%$searchValue%'";
    } elseif ($searchBy == 'discovery_date') {
        $sql .= " WHERE a.discovery_date = '$searchValue'";
    } elseif ($searchBy == 'discovery_location') {
        $sql .= " WHERE a.discovery_location LIKE '%$searchValue%'";
    }
}

$sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
$resultCount = $conn->query($sqlCount);
$total = $resultCount->fetch_assoc()['total'];
$pages = ceil($total / $limit);

if ($total == 0 && $searchBy == 'discovery_date') {
    $searchValue = date('Y-m-d', strtotime($searchValue));
    $startDate = date('Y-m-d', strtotime('-2 months', strtotime($searchValue)));
    $endDate = date('Y-m-d', strtotime('+2 months', strtotime($searchValue)));
    $sql .= " WHERE a.discovery_date BETWEEN '$startDate' AND '$endDate'";
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $resultCount = $conn->query($sqlCount);
    $total = $resultCount->fetch_assoc()['total'];
    $pages = ceil($total / $limit);
}

$sql .= " ORDER BY a.name LIMIT $start, $limit";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Artifacts</title>
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
            width: 200px;
            margin: 10px;
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
        <h1>Artifacts</h1>
    </div>
    <div class="container">
        <div style="padding: 20px;">
            <a href="welcome_admin.php" class="btn btn-success">Back to Home</a>
        </div>
        <form action="" method="get">
            <div class="form-group">
                <label for="search_by">Search by:</label>
                <select id="search_by" name="search_by" class="form-control">
                    <option value="name">Name</option>
                    <option value="material">Material</option>
                    <option value="discovery_date">Discovery Date</option>
                    <option value="discovery_location">Discovery Location</option>
                </select>
            </div>
            <div class="form-group" id="search_value_div">
                <label for="search_value">Search value:</label>
                <input type="text" id="search_value" name="search_value" class="form-control">
            </div>
            <div class="form-group" id="search_date_div" style="display: none;">
                <label for="search_date">Search date:</label>
                <input type="date" id="search_date" name="search_value" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <br><br> <!-- Add some padding between the search button and the table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Name</th>
                    <th>Material</th>
                    <th>Discovery Date</th>
                    <th>Discovery Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['material']; ?></td>
                        <td><?php echo $row['discovery_date']; ?></td>
                        <td><?php echo $row['discovery_location']; ?></td>
                        <td><a href="view_artifact.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View More</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button class="btn btn-danger" id="delete-btn">Delete Selected</button>
        <div class="pagination">
            <?php if($page > 1) { ?>
                <a href="?page=1&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary">First</a>
                <a href="?page=<?php echo $page - 1; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary">Prev</a>
                <?php if($page > 3) { ?>
                    <span>...</span>
                <?php } ?>
                <?php if($page > 2) { ?>
                    <a href="?page=<?php echo $page - 2; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary"><?php echo $page - 2; ?></a>
                <?php } ?>
                <a href="?page=<?php echo $page - 1; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary"><?php echo $page - 1; ?></a>
            <?php } ?>
            <a href="?page=<?php echo $page; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary active"><?php echo $page; ?></a>
            <?php if($page < $pages) { ?>
                <a href="?page=<?php echo $page + 1; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary"><?php echo $page + 1; ?></a>
                <?php if($page < $pages - 1) { ?>
                    <a href="?page=<?php echo $page + 2; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary"><?php echo $page + 2; ?></a>
                <?php } ?>
                <?php if($page < $pages - 2) { ?>
                    <span>...</span>
                <?php } ?>
                <a href="?page=<?php echo $page + 1; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary">Next</a>
                <a href="?page=<?php echo $pages; ?>&search_by=<?php echo $searchBy; ?>&search_value=<?php echo $searchValue; ?>" class="btn btn-primary">Last</a>
            <?php } ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var searchBySelect = document.getElementById('search_by');
            var searchValueInput = document.getElementById('search_value');
            var searchDateInput = document.getElementById('search_date');
            var searchValueDiv = document.getElementById('search_value_div');
            var searchDateDiv = document.getElementById('search_date_div');

            searchBySelect.addEventListener('change', function() {
                if (this.value == 'discovery_date') {
                    searchValueDiv.style.display = 'none';
                    searchDateDiv.style.display = 'block';
                } else {
                    searchValueDiv.style.display = 'block';
                    searchDateDiv.style.display = 'none';
                }
            });

            var deleteBtn = document.getElementById('delete-btn');
            deleteBtn.addEventListener('click', function() {
                var checkboxes = document.querySelectorAll('input[name="delete[]"]');
                var ids = [];
                checkboxes.forEach(function(checkbox) {
                    if(checkbox.checked) {
                        ids.push(checkbox.value);
                    }
                });
                if(ids.length > 0) {
                    if(confirm('Are you sure you want to delete the selected artifacts?')) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'delete_artifacts.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('ids=' + ids.join(','));
                        xhr.onload = function() {
                            if(xhr.responseText == 'success') {
                                location.reload();
                            } else {
                                alert('Failed to delete artifacts. Error: ' + xhr.responseText);
                            }
                        };
                        xhr.onerror = function() {
                            alert('An error occurred while deleting artifacts.');
                        };
                    }
                } else {
                    alert('Please select at least one artifact to delete');
                }
            });
        });
    </script>
</body>
</html>