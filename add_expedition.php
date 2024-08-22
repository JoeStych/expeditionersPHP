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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $leader_id = $_POST['leader_id'];

    $sql = "INSERT INTO Expeditions (name, start_date, end_date, location, description, leader_id) VALUES ('$name', '$start_date', '$end_date', '$location', '$description', '$leader_id')";

    if ($conn->query($sql) === TRUE) {
        header('Location: welcome_admin.php');
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>