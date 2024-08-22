<?php
// delete_expeditions.php

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

if (isset($_POST['ids'])) {
    $ids = explode(',', $_POST['ids']);
    foreach ($ids as $id) {
        $sql = "DELETE FROM Expeditions WHERE id = $id";
        $conn->query($sql);
    }
    echo 'success';
} else {
    echo 'failed';
}

$conn->close();
?>