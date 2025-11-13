<style>
    .plus1 {
    position: absolute;
    right: -25px;
    top: -5px;
    color: #00a2ff;
    font-size: 26px;
    font-weight: bold;
    opacity: 0;
    animation: boingPlus 0.9s ease-out forwards;
    pointer-events: none;
}

@keyframes boingPlus {
    0%   { transform: scale(0.2) translateY(10px); opacity: 0; }
    20%  { transform: scale(1.6) translateY(-10px); opacity: 1; }
    40%  { transform: scale(0.9) translateY(-25px); }
    60%  { transform: scale(1.2) translateY(-35px); }
    80%  { transform: scale(1.0) translateY(-45px); opacity: 1; }
    100% { transform: scale(0.8) translateY(-60px); opacity: 0; }
}

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
        animation: fadeIn 1s ease-out;
    }

    h2 {
        text-align: center;
        color: #ffffff;
        background: linear-gradient(to right, #2a2a2a, #000);
        -webkit-background-clip: text;
        color: transparent;
        font-size: 32px;
        margin-bottom: 20px;
        font-weight: bold;
    }

    h3 {
        margin-top: 25px;
        background: #ffffffcc;
        padding: 10px;
        border-radius: 8px;
        font-size: 22px;
        color: #444;
        text-align: center;
        box-shadow: 0 0 8px rgba(0,0,0,0.15);
    }

    /* Objective row */
    .objective {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        background: #ffffffd0;
        padding: 12px;
        border-radius: 10px;
        box-shadow: 0 0 6px rgba(0,0,0,0.1);
        transition: transform .2s;
        position: relative;
        overflow: visible;
    }

    .objective:hover {
        transform: scale(1.03);
    }

    /* Custom checkbox */
    .objective input[type=checkbox] {
        margin-right: 12px;
        width: 22px;
        height: 22px;
        cursor: pointer;
        accent-color: #4CAF50;
        transform: scale(1.2);
    }

    .plus1 {
        position: absolute;
        right: -20px;
        top: 0;
        color: #4CAF50;
        font-size: 22px;
        font-weight: bold;
        opacity: 0;
        transform: translateY(10px);
        animation: pop 0.8s ease-out forwards;
    }

    @keyframes pop {
        0% { opacity: 0; transform: translateY(10px) scale(0.6); }
        40% { opacity: 1; transform: translateY(-10px) scale(1.3); }
        100% { opacity: 0; transform: translateY(-30px) scale(1); }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Buttons */
    button {
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
        margin-top: 15px;
    }

    button:hover {
        background-color: #2f7a32;
    }
</style>

<script>
function showPlusOne(element) {
    let plus = document.createElement("div");
    plus.classList.add("plus1");
    plus.innerText = "+1";
    element.parentElement.appendChild(plus);

    setTimeout(() => { plus.remove(); }, 800);
}
</script>

<?php
session_start();
include("../../../connection.php");

// Protect page
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch user's weekly objectives JSON
$stmt = $conn->prepare("SELECT weeklyObj FROM fitness WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($weeklyJson);
$stmt->fetch();
$stmt->close();

$weeklyData = json_decode($weeklyJson, true);

// Handle ticking an objective
if (isset($_POST['toggle'])) {
    $day = intval($_POST['day']);
    $objective = $_POST['objective'];

    // Check if completed
    $stmt = $conn->prepare("SELECT id FROM completed_objectives WHERE userID = ? AND day = ? AND objective = ?");
    $stmt->bind_param("iis", $userID, $day, $objective);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Remove checkmark
        $stmt2 = $conn->prepare("DELETE FROM completed_objectives WHERE userID = ? AND day = ? AND objective = ?");
        $stmt2->bind_param("iis", $userID, $day, $objective);
        $stmt2->execute();
        $stmt2->close();
    } else {
        // Add completion
        $stmt2 = $conn->prepare("INSERT INTO completed_objectives (userID, day, objective) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $userID, $day, $objective);
        $stmt2->execute();
        $stmt2->close();
    }
    $stmt->close();

    header("Location: objective.php");
    exit();
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

<form action="dashboard.php">
    <button type="submit">Go to Profile</button>
</form>

<div class="container">
    <h2>Your Weekly Objectives</h2>

    <?php if ($weeklyData): ?>
        <?php foreach ($weeklyData as $dayData): ?>
            <h3>Day <?= htmlspecialchars($dayData['day']) ?></h3>

            <form method="POST">
                <?php foreach ($dayData['objectives'] as $obj): ?>
                    <div class="objective">
                        <input type="checkbox"
                               name="objective"
                               value="<?= htmlspecialchars($obj) ?>"
                               onchange="showPlusOne(this); this.form.submit();"
                               <?= (isset($completed[$dayData['day']]) && in_array($obj, $completed[$dayData['day']])) ? 'checked' : '' ?>>

                        <span><?= htmlspecialchars($obj) ?></span>
                    </div>
                <?php endforeach; ?>

                <input type="hidden" name="toggle" value="1">
                <input type="hidden" name="day" value="<?= $dayData['day'] ?>">
            </form>
        <?php endforeach; ?>

    <?php else: ?>
        <p>No objectives found. Click the button below to generate some.</p>
    <?php endif; ?>

    <form method="POST" action="../endpoint/gpt.php">
        <button type="submit">Generate / Refresh Objectives</button>
    </form>
</div>
