<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo $username; ?>!</h1>
        <p>Logged in as: <?php echo $username; ?></p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
    <div class="button-column">
        <a href="manage_expeditioners.php" class="btn btn-primary button fade-in">Manage Expeditioners</a>
        <a href="manage_expeditions.php" class="btn btn-primary button fade-in">Manage Expeditions</a>
        <a href="manage_artifacts.php" class="btn btn-primary button fade-in">Manage Artifacts</a>
        <a href="manage_plants.php" class="btn btn-primary button fade-in">Manage Plants</a>
        <a href="manage_animals.php" class="btn btn-primary button fade-in">Manage Animals</a>
        <a href="manage_underwater_ruins.php" class="btn btn-primary button fade-in">Manage Underwater Ruins</a>
        <a href="profitable_expeditions.php" class="btn btn-primary button fade-in">View Profitable Expeditions</a>
        <a href="expedition_ratios.php" class="btn btn-primary button fade-in">View Expedition Leader Performance</a>
        <a href="add_exp.php" class="btn btn-primary button fade-in">Add New Expedition</a>
        <a href="view_valuable_artifacts.php" class="btn btn-primary button fade-in">View Valuable Artifacts</a>
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