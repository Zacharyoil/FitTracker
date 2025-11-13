<?php
session_start();
include("../../../connection.php");

// Protect page
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch weekly objectives JSON
$stmt = $conn->prepare("SELECT weeklyObj FROM fitness WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($weeklyJson);
$stmt->fetch();
$stmt->close();

$weeklyData = json_decode($weeklyJson, true);

// Handle checkbox toggle (AJAX – no refresh)
if (isset($_POST['toggle'])) {
    $day = intval($_POST['day']);
    $objective = $_POST['objective'];

    $stmt = $conn->prepare("SELECT id FROM completed_objectives WHERE userID = ? AND day = ? AND objective = ?");
    $stmt->bind_param("iis", $userID, $day, $objective);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $delete = $conn->prepare("DELETE FROM completed_objectives WHERE userID = ? AND day = ? AND objective = ?");
        $delete->bind_param("iis", $userID, $day, $objective);
        $delete->execute();
        $delete->close();
    } else {
        $insert = $conn->prepare("INSERT INTO completed_objectives (userID, day, objective) VALUES (?,?,?)");
        $insert->bind_param("iis", $userID, $day, $objective);
        $insert->execute();
        $insert->close();
    }
    $stmt->close();
    exit(); // IMPORTANT: No redirect
}

// Fetch completed objectives
$stmt = $conn->prepare("SELECT day, objective FROM completed_objectives WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$completed = [];
while ($row = $result->fetch_assoc()) {
    $completed[$row['day']][] = $row['objective'];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Weekly Objectives</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to right, #47d4e3ff, #5ec80d);
        margin: 0;
        padding: 0;
    }

    .container {
        width: 700px;
        margin: 50px auto;
        background: rgba(255,255,255,0.9);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }

    h2 {
        text-align: center;
        font-size: 32px;
        font-weight: bold;
        background: linear-gradient(to right, #2a2a2a, #000);
        -webkit-background-clip: text;
        color: transparent;
    }

    h3 {
        margin-top: 25px;
        background: #ffffffcc;
        padding: 10px;
        border-radius: 8px;
        color: #444;
        text-align: center;
    }

    .objective {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        background: #ffffffd0;
        padding: 12px;
        border-radius: 10px;
        box-shadow: 0 0 6px rgba(0,0,0,0.1);
        transition: .2s;
    }

    .objective:hover { transform: scale(1.03); }

    input[type=checkbox] {
        margin-right: 12px;
        width: 22px;
        height: 22px;
        accent-color: #4CAF50;
        cursor: pointer;
    }

    button {
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
    }

    button:hover { background-color: #2f7a32; }

    /* Bubble burst effects */
    .burst {
        position: fixed;
        font-size: 40px;
        font-weight: bold;
        pointer-events: none;
        opacity: 0;
        z-index: 9999;
        animation-duration: 1.3s;
        animation-fill-mode: forwards;
    }

    .burst.up { color: #00a2ff; animation-name: burstUp; }
    .burst.down { color: #ff4747; animation-name: burstDown; }

    @keyframes burstUp {
        0%   { opacity: 0; transform: translateY(20px) scale(0.3); }
        20%  { opacity: 1; transform: translateY(-20px) scale(1.3); }
        60%  { opacity: 1; transform: translateY(-100px) scale(1); }
        100% { opacity: 0; transform: translateY(-180px) scale(0.8); }
    }

    @keyframes burstDown {
        0%   { opacity: 0; transform: translateY(-20px) scale(0.3); }
        20%  { opacity: 1; transform: translateY(20px) scale(1.3); }
        60%  { opacity: 1; transform: translateY(100px) scale(1); }
        100% { opacity: 0; transform: translateY(180px) scale(0.8); }
    }

    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
        border: 1px solid #ccc;
        resize: none;
    }
</style>

<script>
function spawnBurst(isAdding) {
    const count = 14;
    for (let i = 0; i < count; i++) {
        const div = document.createElement("div");
        div.classList.add("burst", isAdding ? "up" : "down");
        div.textContent = isAdding ? "+1" : "-1";

        div.style.left = (10 + Math.random() * 80) + "vw";
        div.style.top = (30 + Math.random() * 40) + "vh";
        div.style.animationDelay = (Math.random() * 0.3) + "s";

        document.body.appendChild(div);
        setTimeout(() => div.remove(), 1500);
    }
}

function toggleObjective(checkbox, day, objective) {
    spawnBurst(checkbox.checked);

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "toggle=1&day=" + day + "&objective=" + encodeURIComponent(objective)
    });
}
</script>

</head>
<body>

<form action="dashboard.php">
    <button type="submit">Go to Profile</button>
</form>

<div class="container">
    <h2>Your Weekly Objectives</h2>

    <?php if ($weeklyData): ?>
        <?php foreach ($weeklyData as $dayData): ?>
            <h3>Day <?= $dayData['day'] ?></h3>

            <?php foreach ($dayData['objectives'] as $obj): ?>
                <div class="objective">
                    <input
                        type="checkbox"
                        onchange="toggleObjective(this, <?= $dayData['day'] ?>, '<?= htmlspecialchars($obj) ?>')"
                        <?= (isset($completed[$dayData['day']]) && in_array($obj, $completed[$dayData['day']])) ? "checked" : "" ?>
                    >
                    <span><?= htmlspecialchars($obj) ?></span>
                </div>
            <?php endforeach; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <p>No objectives found.</p>
    <?php endif; ?>

    <hr><br>

    <form method="POST" action="../endpoint/gpt.php">
        <label><strong>Enter your preferences (optional):</strong></label>
        <textarea name="preferences" rows="3" placeholder="e.g. I like jogging, salad, and low-impact workouts"></textarea>
        <button type="submit">Generate / Refresh Objectives</button>
    </form>
</div>

</body>
</html>
