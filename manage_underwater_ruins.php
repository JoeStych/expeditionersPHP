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

$sql = "SELECT u.id, u.name, u.location, u.description, u.age, u.discovery_date, e.name as expedition_name, ex.name as discovered_by FROM UnderwaterRuins u JOIN Expeditions e ON u.expedition_id = e.id JOIN Expeditioners ex ON u.discovered_by = ex.id";

if ($searchBy && $searchValue) {
    if ($searchBy == 'name') {
        $sql .= " WHERE u.name LIKE '%$searchValue%'";
    } elseif ($searchBy == 'location') {
        $sql .= " WHERE u.location LIKE '%$searchValue%'";
    } elseif ($searchBy == 'description') {
        $sql .= " WHERE u.description LIKE '%$searchValue%'";
    } elseif ($searchBy == 'age') {
        $sql .= " WHERE u.age = '$searchValue'";
    } elseif ($searchBy == 'discovery_date') {
        $sql .= " WHERE u.discovery_date = '$searchValue'";
    } elseif ($searchBy == 'expedition_name') {
        $sql .= " WHERE e.name LIKE '%$searchValue%'";
    } elseif ($searchBy == 'discovered_by') {
        $sql .= " WHERE ex.name LIKE '%$searchValue%'";
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
    $sql .= " WHERE u.discovery_date BETWEEN '$startDate' AND '$endDate'";
    $sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
    $resultCount = $conn->query($sqlCount);
    $total = $resultCount->fetch_assoc()['total'];
    $pages = ceil($total / $limit);
}

$sql .= " ORDER BY u.name LIMIT $start, $limit";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Underwater Ruins</title>
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
        <h1>Underwater Ruins</h1>
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
                    <option value="location">Location</option>
                    <option value="description">Description</option>
                    <option value="age">Age</option>
                    <option value="discovery_date">Discovery Date</option>
                    <option value="expedition_name">Expedition Name</option>
                    <option value="discovered_by">Discovered By</option>
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
                    <th>Location</th>
                    <th>Description</th>
                    <th>Age</th>
                    <th>Discovery Date</th>
                    <th>Expedition Name</th>
                    <th>Discovered By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['location']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><?php echo $row['age']; ?></td>
                        <td><?php echo $row['discovery_date']; ?></td>
                        <td><?php echo $row['expedition_name']; ?></td>
                        <td><?php echo $row['discovered_by']; ?></td>
                        <td><a href="view_underwater_ruin.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View More</a></td>
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
                    if(confirm('Are you sure you want to delete the selected underwater ruins?')) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'delete_underwater_ruins.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('ids=' + ids.join(','));
                        xhr.onload = function() {
                            if(xhr.responseText == 'success') {
                                location.reload();
                            } else {
                                alert('Failed to delete underwater ruins. Error: ' + xhr.responseText);
                            }
                        };
                        xhr.onerror = function() {
                            alert('An error occurred while deleting underwater ruins.');
                        };
                    }
                } else {
                    alert('Please select at least one underwater ruin to delete');
                }
            });
        });
    </script>
</body>
</html>