<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expeditions";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    if ($_POST['action'] == 'pair') {
        $stmt = $conn->prepare('UPDATE Expeditioners SET username = ? WHERE id = ?');
        $stmt->bind_param('si', $_SESSION['username'], $_POST['expeditioner_id']);
        $stmt->execute();
        header('Location: welcome_exp.php');
        exit;
    } elseif ($_POST['action'] == 'register') {
        $stmt = $conn->prepare('INSERT INTO Expeditioners (name, email, phone_number, address, username) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $_POST['name'], $_POST['email'], $_POST['phone_number'], $_POST['address'], $_SESSION['username']);
        $stmt->execute();
        header('Location: welcome_exp.php');
        exit;
    }
}

$action = null;
if (isset($_POST['action']) && $_POST['action'] != '') {
    $action = $_POST['action'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Pair Account</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pair Account</h2>
        <p>It looks like you haven't paired your account with an expeditioner profile yet. Please select an option below:</p>
        <form method="post">
            <div class="form-group">
                <label for="action">Do you already have an expeditioner profile?</label>
                <select class="form-control" id="action" name="action">
                    <option value="pair">Yes, I already have an expeditioner profile</option>
                    <option value="register">No, I don't have an expeditioner profile yet</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" name="select">Continue</button>
        </form>
        <?php if ($action == 'pair') { ?>
            <form method="post">
                <input type="hidden" name="action" value="pair">
                <div class="form-group">
                    <label for="expeditioner_id">Select your expeditioner profile:</label>
                    <select class="form-control" id="expeditioner_id" name="expeditioner_id">
                        <?php
                        $stmt = $conn->prepare('SELECT * FROM Expeditioners WHERE username IS NULL');
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Pair Account</button>
            </form>
        <?php } elseif ($action == 'register') { ?>
            <form method="post">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Register</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>