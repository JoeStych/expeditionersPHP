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

$sql = "SELECT id, name FROM Expeditioners";
$result = $conn->query($sql);

$leaders = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $leaders[$row['id']] = $row['name'];
    }
}

$name = '';
$start_date = '';
$end_date = '';
$location = '';
$description = '';
$leader_id = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $leader_id = $_POST['leader_id'];

    if (empty($name) || empty($start_date) || empty($end_date) || empty($location) || empty($description) || empty($leader_id)) {
        $error = 'All fields are required.';
    } elseif ($start_date > $end_date) {
        $error = 'Start date cannot be after end date.';
    } else {
        $stmt = $conn->prepare("INSERT INTO Expeditions (name, start_date, end_date, location, description, leader_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $start_date, $end_date, $location, $description, $leader_id);

        if ($stmt->execute()) {
            $success = 'Expedition added successfully.';
            $name = '';
            $start_date = '';
            $end_date = '';
            $location = '';
            $description = '';
            $leader_id = '';
        } else {
            $error = 'Error adding expedition: ' . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Expedition</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Add Expedition</h1>
    </div>
    <div class="button-column">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
                <label for="name">Expedition Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea class="form-control" id="description" name="description" required><?php echo $description; ?></textarea>
            </div>
            <div class="form-group">
                <label for="leader_id">Leader:</label>
                <select class="form-control" id="leader_id" name="leader_id" required>
                    <option value="">Select Leader</option>
                    <?php foreach($leaders as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php if ($leader_id == $id) echo 'selected'; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Expedition</button>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
        </form>
        <a href="welcome_admin.php" class="btn btn-secondary">Cancel</a>
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