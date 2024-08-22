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

// Get the expeditioner's name
$sql = "SELECT name FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerName = $result->fetch_assoc()['name'];

// Get the expeditioner's id
$sql = "SELECT id FROM Expeditioners WHERE username = '".$_SESSION['username']."'";
$result = $conn->query($sql);
$expeditionerId = $result->fetch_assoc()['id'];

// Get the expedition id from the GET parameter
$expeditionId = $_GET['expedition_id'];

// Get the expedition name
$sql = "SELECT name FROM Expeditions WHERE id = '$expeditionId'";
$result = $conn->query($sql);
$expeditionName = $result->fetch_assoc()['name'];

// Initialize variables
$discoveryType = '';
$name = '';
$description = '';
$material = '';
$weight = '';
$height = '';
$width = '';
$depth = '';
$discoveryDate = '';
$species = '';
$location = '';
$age = '';
$injuryDate = '';
$severity = '';
$success = '';
$error = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $discoveryType = $_POST['discoveryType'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $material = $_POST['material'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $width = $_POST['width'];
    $depth = $_POST['depth'];
    $discoveryDate = $_POST['discoveryDate'];
    $species = $_POST['species'];
    $location = $_POST['location'];
    $age = $_POST['age'];
    $injuryDate = $_POST['injuryDate'];
    $severity = $_POST['severity'];

    // Insert the discovery into the database
    if ($discoveryType == 'Artifact') {
        $sql = "INSERT INTO Artifacts (name, description, material, weight, height, width, depth, discovery_date, discovery_location, expedition_id, discovered_by) 
                VALUES ('$name', '$description', '$material', '$weight', '$height', '$width', '$depth', '$discoveryDate', '$location', '$expeditionId', '$expeditionerId')";
    } elseif ($discoveryType == 'PlantLife') {
        $sql = "INSERT INTO PlantLife (name, species, description, discovery_date, discovery_location, expedition_id, discovered_by) 
                VALUES ('$name', '$species', '$description', '$discoveryDate', '$location', '$expeditionId', '$expeditionerId')";
    } elseif ($discoveryType == 'AnimalLife') {
        $sql = "INSERT INTO AnimalLife (name, species, description, discovery_date, discovery_location, expedition_id, discovered_by) 
                VALUES ('$name', '$species', '$description', '$discoveryDate', '$location', '$expeditionId', '$expeditionerId')";
    } elseif ($discoveryType == 'UnderwaterRuins') {
        $sql = "INSERT INTO UnderwaterRuins (name, location, description, age, discovery_date, expedition_id, discovered_by) 
                VALUES ('$name', '$location', '$description', '$age', '$discoveryDate', '$expeditionId', '$expeditionerId')";
    } elseif ($discoveryType == 'Injury') {
        $sql = "INSERT INTO Injuries (expeditioner_id, expedition_id, injury_date, description, severity) 
                VALUES ('$expeditionerId', '$expeditionId', '$injuryDate', '$description', '$severity')";
    }

    if ($conn->query($sql) === TRUE) {
        $success = 'Discovery logged successfully!';
        $discoveryType = '';
        $name = '';
        $description = '';
        $material = '';
        $weight = '';
        $height = '';
        $width = '';
        $depth = '';
        $discoveryDate = '';
        $species = '';
        $location = '';
        $age = '';
        $injuryDate = '';
        $severity = '';
    } else {
        $error = 'Error logging discovery: ' . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Log Discoveries from a Trip</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 50px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 90px 20px 20px;
            overflow-x: hidden;
            overflow-y: auto;
            border-right: 1px solid #eee;
        }
        .sidebar .nav {
            margin-bottom: 20px;
        }
        .sidebar .nav-item {
            width: 100%;
        }
        .sidebar .nav-item + .nav-item {
            margin-left: 0;
        }
        .sidebar .nav-link {
            border-radius: 0;
        }
        .main-content {
            margin-left: 200px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="#"><?php echo $expeditionerName; ?></a>
        <button class="btn btn-danger" onclick="window.location.href='logout.php'">Logout</button>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="welcome_exp.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_edit_profile.php">Modify Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_artifacts.php">View Artifact Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_underwater.php">View Underwater Ruin Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_plants.php">View Plantlife Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_animals.php">View Animal Life Discoveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="e_view_injuries.php">View Injuries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Log Discoveries from a Trip</a>
                    </li>
                </ul>
            </nav>

            <main class="col-sm-9 ml-sm-auto col-md-10 pt-3 main-content">
                <h1>Log Discoveries from a Trip</h1>

                <h2><?php echo $expeditionName; ?></h2>

                <form method="post">
                    <div class="form-group">
                        <label for="discoveryType">Discovery Type</label>
                        <select class="form-control" id="discoveryType" name="discoveryType" onchange="updateForm()">
                            <option value="">Select a discovery type</option>
                            <option value="Artifact" <?php if ($discoveryType == 'Artifact') echo 'selected'; ?>>Artifact</option>
                            <option value="PlantLife" <?php if ($discoveryType == 'PlantLife') echo 'selected'; ?>>Plant Life</option>
                            <option value="AnimalLife" <?php if ($discoveryType == 'AnimalLife') echo 'selected'; ?>>Animal Life</option>
                            <option value="UnderwaterRuins" <?php if ($discoveryType == 'UnderwaterRuins') echo 'selected'; ?>>Underwater Ruins</option>
                            <option value="Injury" <?php if ($discoveryType == 'Injury') echo 'selected'; ?>>Injury</option>
                        </select>
                    </div>

                    <div id="artifactFields" style="display: none;">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="material">Material</label>
                            <input type="text" class="form-control" id="material" name="material" value="<?php echo $material; ?>">
                        </div>
                        <div class="form-group">
                            <label for="weight">Weight</label>
                            <input type="number" class="form-control" id="weight" name="weight" value="<?php echo $weight; ?>">
                        </div>
                        <div class="form-group">
                            <label for="height">Height</label>
                            <input type="number" class="form-control" id="height" name="height" value="<?php echo $height; ?>">
                        </div>
                        <div class="form-group">
                            <label for="width">Width</label>
                            <input type="number" class="form-control" id="width" name="width" value="<?php echo $width; ?>">
                        </div>
                        <div class="form-group">
                            <label for="depth">Depth</label>
                            <input type="number" class="form-control" id="depth" name="depth" value="<?php echo $depth; ?>">
                        </div>
                        <div class="form-group">
                            <label for="discoveryDate">Discovery Date</label>
                            <input type="date" class="form-control" id="discoveryDate" name="discoveryDate" value="<?php echo $discoveryDate; ?>">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>">
                        </div>
                    </div>

                    <div id="plantLifeFields" style="display: none;">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
                        </div>
                        <div class="form-group">
                            <label for="species">Species</label>
                            <input type="text" class="form-control" id="species" name="species" value="<?php echo $species; ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="discoveryDate">Discovery Date</label>
                            <input type="date" class="form-control" id="discoveryDate" name="discoveryDate" value="<?php echo $discoveryDate; ?>">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>">
                        </div>
                    </div>

                    <div id="animalLifeFields" style="display: none;">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
                        </div>
                        <div class="form-group">
                            <label for="species">Species</label>
                            <input type="text" class="form-control" id="species" name="species" value="<?php echo $species; ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="discoveryDate">Discovery Date</label>
                            <input type="date" class="form-control" id="discoveryDate" name="discoveryDate" value="<?php echo $discoveryDate; ?>">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>">
                        </div>
                    </div>

                    <div id="underwaterRuinsFields" style="display: none;">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" class="form-control" id="age" name="age" value="<?php echo $age; ?>">
                        </div>
                        <div class="form-group">
                            <label for="discoveryDate">Discovery Date</label>
                            <input type="date" class="form-control" id="discoveryDate" name="discoveryDate" value="<?php echo $discoveryDate; ?>">
                        </div>
                    </div>

                    <div id="injuryFields" style="display: none;">
                        <div class="form-group">
                            <label for="injuryDate">Injury Date</label>
                            <input type="date" class="form-control" id="injuryDate" name="injuryDate" value="<?php echo $injuryDate; ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="severity">Severity</label>
                            <input type="text" class="form-control" id="severity" name="severity" value="<?php echo $severity; ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Log Discovery</button>

                    <?php if ($success) { ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php } elseif ($error) { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php } ?>
                </form>

                <script>
                    function updateForm() {
                        var discoveryType = document.getElementById('discoveryType').value;
                        var artifactFields = document.getElementById('artifactFields');
                        var plantLifeFields = document.getElementById('plantLifeFields');
                        var animalLifeFields = document.getElementById('animalLifeFields');
                        var underwaterRuinsFields = document.getElementById('underwaterRuinsFields');
                        var injuryFields = document.getElementById('injuryFields');

                        artifactFields.style.display = 'none';
                        plantLifeFields.style.display = 'none';
                        animalLifeFields.style.display = 'none';
                        underwaterRuinsFields.style.display = 'none';
                        injuryFields.style.display = 'none';

                        if (discoveryType == 'Artifact') {
                            artifactFields.style.display = 'block';
                        } else if (discoveryType == 'PlantLife') {
                            plantLifeFields.style.display = 'block';
                        } else if (discoveryType == 'AnimalLife') {
                            animalLifeFields.style.display = 'block';
                        } else if (discoveryType == 'UnderwaterRuins') {
                            underwaterRuinsFields.style.display = 'block';
                        } else if (discoveryType == 'Injury') {
                            injuryFields.style.display = 'block';
                        }
                    }

                    updateForm();
                </script>
            </main>
        </div>
    </div>
</body>
</html>