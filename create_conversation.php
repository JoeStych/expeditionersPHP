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

$expeditionerId = $_POST['expeditioner'];
$message = $_POST['message'];

// Sanitize the input
$expeditionerId = mysqli_real_escape_string($conn, $expeditionerId);
$message = mysqli_real_escape_string($conn, $message);

// Check for empty fields
if (empty($expeditionerId) || empty($message)) {
    header('Location: new_conversation.php');
    exit;
}

// Get the current user's ID
$sql = "SELECT id FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$userId = $result->fetch_assoc()['id'];

// Create a new conversation
$sql = "INSERT INTO Conversations (user1_id, user2_id) VALUES ('$userId', '$expeditionerId')";
$conn->query($sql);
$conversationId = $conn->insert_id;

// Create a new message
$sql = "INSERT INTO Messages (conversation_id, sender_id, message) VALUES ('$conversationId', '$userId', '$message')";
$conn->query($sql);

$conn->close();

header('Location: e_messages.php');
exit;
?>