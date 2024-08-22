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

$sql = "SELECT * FROM Expeditioners ORDER BY name LIMIT $start, $limit";
$result = $conn->query($sql);

$sql_total = "SELECT COUNT(*) as total FROM Expeditioners";
$result_total = $conn->query($sql_total);
$total = $result_total->fetch_assoc()['total'];
$pages = ceil($total / $limit);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Expeditioners</title>
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
        .back-btn {
            background-color: #f44336;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expeditioners</h1>
    </div>
    <div class="container">
        <div style="padding: 20px;">
            <a href="welcome_admin.php" class="btn btn-primary">Back to Home</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone_number']; ?></td>
                        <td><a href="view_expeditioner.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">View More</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button class="btn btn-danger" id="delete-btn">Delete Selected</button>
        <div class="pagination">
            <?php if($page > 1) { ?>
                <a href="?page=1" class="btn btn-primary">First</a>
                <a href="?page=<?php echo $page - 1; ?>" class="btn btn-primary">Prev</a> <!-- added prev page button -->
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
                <a href="?page=<?php echo $page + 1; ?>" class="btn btn-primary">Next</a> <!-- added next page button -->
                <a href="?page=<?php echo $pages; ?>" class="btn btn-primary">Last</a>
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
                    if(confirm('Are you sure you want to delete the selected expeditioners?')) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'delete_expeditioners.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('ids=' + ids.join(','));
                        xhr.onload = function() {
                            if(xhr.responseText == 'success') {
                                location.reload();
                            } else {
                                alert('Failed to delete expeditioners');
                            }
                        };
                    }
                } else {
                    alert('Please select at least one expeditioner to delete');
                }
            });
        });
    </script>
</body>
</html>