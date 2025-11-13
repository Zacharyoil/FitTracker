<?php
session_start();
include("../../../connection.php");


// Protect page
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$username = $_SESSION['username'];

// Fetch user's points (based on completed objectives)
$stmt = $conn->prepare("SELECT COUNT(*) as completedCount FROM completed_objectives WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($completedCount);
$stmt->fetch();
$stmt->close();
$points = $completedCount * 10; // Each objective = 10 points

// Fetch calories for this user only
$stmt = $conn->prepare("SELECT healthData FROM fitness WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($healthJson);
$stmt->fetch();
$stmt->close();

$calories = 0;
if ($healthJson) {
    $data = json_decode($healthJson, true);
    $calories = intval($data['active_calories_burned'] ?? 0);
}

// Add calories-based score
$points += intval($calories * 0.2); // or whatever multiplier you choose


// Fetch calories from fitness table
$stmt = $conn->prepare("SELECT healthData FROM fitness WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($calories);
$stmt->fetch();
$stmt->close();
//fetch calories from JSON
//0 calories if healthdata is null
if ($calories == null) {
    $calories = 0;
} else {
    $caloriesData = json_decode($calories, true);
    $calories = $caloriesData['active_calories_burned'] ?? 0;
}


// Fetch weekly objectives count
$stmt = $conn->prepare("SELECT weeklyObj FROM fitness WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($weeklyJson);
$stmt->fetch();
$stmt->close();

$weeklyData = json_decode($weeklyJson, true);
$totalObjectives = 0;
if ($weeklyData) {
    foreach ($weeklyData as $day) {
        $totalObjectives += count($day['objectives']);
    }
}

$progressPercent = $totalObjectives > 0 ? round(($completedCount / $totalObjectives) * 100) : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FitTracker Dashboard</title>

<style>
    body { 
        font-family: Arial, sans-serif; 
        background: linear-gradient(to right, #47d4e3ff, #5ec80d);
        margin: 0; 
        padding: 0; 
    }

    .container { 
        max-width: 900px; 
        margin: 50px auto; 
        background: #fff; 
        padding: 30px; 
        border-radius: 10px; 
        box-shadow: 0 0 15px rgba(0,0,0,0.1); 
    }

    h1 { 
        text-align: center; 
        color: #333; 
    }

    h3 {
        text-align: center; 
        color: #666;
    }

    .stats { 
        display: flex; 
        justify-content: space-around; 
        margin-bottom: 30px; 
    }

    .card { 
        background: #4CAF50; 
        color: #fff; 
        padding: 20px; 
        border-radius: 10px; 
        text-align: center; 
        width: 25%; 
        box-shadow: 0 0 10px rgba(0,0,0,0.1); 
    }

    .card h2 { 
        margin: 0; 
        font-size: 28px; 
    }

    .card p { 
        margin: 5px 0 0; 
        font-size: 16px; 
    }

    .progress-container { 
        background-color: #ddd; 
        border-radius: 20px; 
        width: 100%; 
        height: 25px; 
        margin-bottom: 15px; 
    }

 .progress-bar { 
    height: 100%; 
    border-radius: 20px; 
    background-color: #007bff; 
    width: <?php echo $progressPercent; ?>%;
    text-align: center; 
    color: white; 
    line-height: 25px; 
    font-weight: bold; 
    animation: gr 1.4s ease-in-out forwards;
}


    @keyframes gr {
        0% {
            opacity: 0;
            transform: translateY(12px) scale(0.95);
        }
        50% {
            opacity: 1;
            transform: translateY(0px) scale(1.03);
        }
        100% {
            opacity: 1;
            transform: translateY(0px) scale(1);
        }
    }

    .links a, button { 
        display: inline-block; 
        margin-right: 10px; 
        padding: 10px 20px; 
        background: #007bff; 
        color: white; 
        border: none; 
        border-radius: 6px; 
        text-decoration: none; 
        font-weight: bold; 
        cursor: pointer; 
    }

    button:hover, .links a:hover { 
        background: #0056b3; 
    }

    .links { 
        margin-top: 20px; 
        text-align: center; 
    }
</style>

</head>
<body>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
    <h3>Your userID is <?= $userID ?></h3>

    <div class="stats">
        <div class="card">
            <h2><?= $points ?></h2>
            <p>Points</p>
        </div>
        <div class="card">
            <h2><?= $calories ?? 0 ?></h2>
            <p>Calories</p>
        </div>
        <div class="card">
            <h2><?= $totalObjectives ?></h2>
            <p>Total Objectives</p>
        </div>
    </div>

    <div class="section">
        <h2>Weekly Objectives Progress</h2>
        <div class="progress-container">
            <div class="progress-bar"><?= $progressPercent ?>%</div>
        </div>
        <p><?= $completedCount ?> out of <?= $totalObjectives ?> objectives completed.</p>
    </div>

    <div class="links">
        <a href="objective.php">View Objectives</a>
        <a href="../index.php">Main menu</a>
        <a href="../leaderboard.php">Leaderboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>
