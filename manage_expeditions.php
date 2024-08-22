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

$sql = "SELECT e.id, e.name, e.start_date, e.end_date, e.location, ex.name as leader_name FROM Expeditions e JOIN Expeditioners ex ON e.leader_id = ex.id";

if ($searchBy && $searchValue) {
    if ($searchBy == 'name') {
        $sql .= " WHERE e.name LIKE '%$searchValue%'";
    } elseif ($searchBy == 'start_date') {
        $sql .= " WHERE e.start_date = '$searchValue'";
    } elseif ($searchBy == 'end_date') {
        $sql .= " WHERE e.end_date = '$searchValue'";
    } elseif ($searchBy == 'going_on_date') {
        $sql .= " WHERE '$searchValue' BETWEEN e.start_date AND e.end_date";
    } elseif ($searchBy == 'location') {
        $sql .= " WHERE e.location LIKE '%$searchValue%'";
    } elseif ($searchBy == 'leader') {
        $sql .= " WHERE ex.name LIKE '%$searchValue%'";
    }
}

$sqlCount = "SELECT COUNT(*) as total FROM ($sql) as subquery";
$resultCount = $conn->query($sqlCount);
$total = $resultCount->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$sql .= " ORDER BY e.name LIMIT $start, $limit";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Expeditions</title>
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
        <h1>Expeditions</h1>
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
                    <option value="start_date">Start Date</option>
                    <option value="end_date">End Date</option>
                    <option value="going_on_date">Going on Date</option>
                    <option value="location">Location</option>
                    <option value="leader">Leader</option>
                </select>
            </div>
            <div class="form-group" id="search_value_div">
                <label for="search_value">Search value:</label>
                <input type="text" id="search_value" name="search_value" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <br><br> <!-- Add some padding between the search button and the table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Location</th>
                    <th>Leader</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['start_date']; ?></td>
                        <td><?php echo $row['end_date']; ?></td>
                        <td><?php echo $row['location']; ?></td>
                        <td><?php echo $row['leader_name']; ?></td>
                        <td><a href="view_expedition.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View More</a></td>
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
                    if(confirm('Are you sure you want to delete the selected expeditions?')) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'delete_expeditions.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('ids=' + ids.join(','));
                        xhr.onload = function() {
                            if(xhr.responseText == 'success') {
                                location.reload();
                            } else {
                                alert('Failed to delete expeditions. Error: ' + xhr.responseText);
                            }
                        };
                        xhr.onerror = function() {
                            alert('An error occurred while deleting expeditions.');
                        };
                    }
                } else {
                    alert('Please select at least one expedition to delete');
                }
            });
        });
    </script>
</body>
</html>