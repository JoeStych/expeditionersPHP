<?php
session_start();

// Check if the user is logged in
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

// Get the conversation ID and message content from the form data
$convoid = $_POST['convoid'];
$content = $_POST['content'];

// Get the expeditioner's ID
$sql = "SELECT id FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerId = $result->fetch_assoc()['id'];

// Insert the message into the database
$sql = "INSERT INTO messages (senderid, content, convoid) VALUES ('$expeditionerId', '$content', '$convoid')";
if ($conn->query($sql) === TRUE) {
    header('Location: view_message.php?id=' . $convoid);
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>