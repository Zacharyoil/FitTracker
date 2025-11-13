<?php
session_start();
include("../../connection.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title>FitTacker</title>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

  <style>
    body {
  margin: 0;
  min-height: 100vh;  /* FIXED */
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #3bbf63, #2e8b57);
  background-attachment: fixed;   /* 🟢 THIS MAKES IT EVEN CLEANER */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  overflow-x: hidden;
}


    /* Confetti emojis */
    .confetti {
      position: fixed;
      top: -20px;
      font-size: 25px;
      animation: fall linear infinite;
      opacity: 5;
    }

    @keyframes fall {
      0% { transform: translateY(-50px) rotate(0deg); }
      100% { transform: translateY(110vh) rotate(360deg); }
    }

    /* Floating glow orbs */
    .glow {
      position: fixed;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: rgba(255,255,255,0.35);
      filter: blur(7px);
      animation: float 6s infinite ease-in-out alternate;
    }

    @keyframes float {
      from { transform: translateY(0px); }
      to { transform: translateY(-25px); }
    }

    h1 {
      margin-top: 40px;
      font-size: 55px;
      background-image: linear-gradient(to right, #eaffea, #ffffff);
      -webkit-background-clip: text;
      color: transparent;
      font-weight: 700;
      text-shadow: 0 2px 5px rgba(0,0,0,0.25);
    }

    table {
      margin-top: 20px;
      border-collapse: collapse;
      width: 420px;
      background: #ffffffdd;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
      animation: fade 0.9s ease;
    }

    @keyframes fade {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }

    th, td {
      padding: 15px;
      text-align: center;
      border-bottom: 2px solid #b1e3bf;
      font-size: 18px;
      font-weight: 500;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    th {
      background: #32a852;
      color: white;
      font-size: 20px;
      font-weight: 700;
    }

    /* KEEPING your gold/silver/bronze colors */
    .gold { background-color: #ffea61 !important; font-weight: 700; }
    .silver { background-color: #d5d5d5 !important; font-weight: 700; }
    .bronze { background-color: #ffb169 !important; font-weight: 700; }

    button {
      width: 300px;
      padding: 14px;
      font-size: 20px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      background-color: #32a852;
      color: white;
      margin-top: 20px;
      transition: 0.3s;
      font-weight: 600;
      box-shadow: 0 6px 15px rgba(0,0,0,0.18);
    }

    button:hover {
      background-color: #2a9248;
      transform: scale(1.06);
      box-shadow: 0 8px 18px rgba(0,0,0,0.25);
    }

    #inputArea {
      margin-top: 25px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    input {
      padding: 12px;
      font-size: 16px;
      border-radius: 10px;
      border: none;
      width: 230px;
      background: #f2fff6;
    }

    input:focus {
      outline: none;
      border: 2px solid #32a852;
      box-shadow: 0 0 6px #9be2b2;
      transform: scale(1.03);
    }

    /* Floating fruit image */
    .side-img {
      position: fixed;
      right: 20px;
      bottom: 20px;
      width: 200px;
      animation: floatFruit 3.5s infinite ease-in-out alternate;
    }

    @keyframes floatFruit {
      0% { transform: translateY(0); }
      100% { transform: translateY(-18px); }
    }
  </style>
</head>

<body>

<form action="index.php">
  <button type="submit">Go Home</button>
</form>

<h1>LeaderBoard</h1>

<!-- CONFETTI -->
<script>
const confettiEmojis = ["✨","🌟","🍃","💚","🥝","🍏","🟢","🍀","💫","💧"];
for (let i = 0; i < 35; i++) {
  let c = document.createElement("div");
  c.classList.add("confetti");
  c.textContent = confettiEmojis[Math.floor(Math.random() * confettiEmojis.length)];
  c.style.left = Math.random() * 100 + "vw";
  c.style.animationDuration = (3 + Math.random() * 3) + "s";
  c.style.fontSize = (18 + Math.random() * 20) + "px";
  document.body.appendChild(c);
}
</script>

<!-- GLOWING DECOR ORBS -->
<div class="glow" style="left:10%; top:20%;"></div>
<div class="glow" style="left:80%; top:40%;"></div>
<div class="glow" style="left:20%; top:70%;"></div>

<table id="leaderboard">
  <thead>
    <tr><th>User</th><th>Score</th></tr>
  </thead>
  <tbody>
    <?php 
      // SCORING
      $sql = "SELECT * FROM completed_objectives";
      $result = $conn->query($sql);
      $scores = [];
      while ($row = $result->fetch_assoc()) {
          $scores[$row['userID']] = ($scores[$row['userID']] ?? 0) + 10;
      }

      //add the calories score
      $sql = "SELECT userID, healthData FROM fitness";
      $result = $conn->query($sql);
      while ($row = $result->fetch_assoc()) {
          //fetch calories from JSON
          $healthData = json_decode($row['healthData'], true);
          $calories = $healthData['active_calories_burned'] ?? 0;
          $scores[$row['userID']] = ($scores[$row['userID']] ?? 0) + intval($calories * 0.2);
      }

      // USERNAMES
      $users = [];
      $u = $conn->query("SELECT userID, username FROM fitness");
      while ($row = $u->fetch_assoc()) {
          $users[$row['userID']] = $row['username'];
      }

      arsort($scores);

      // TABLE GENERATION
      foreach ($scores as $uid => $score) {
          $name = $users[$uid] ?? "Unknown";
          echo "<tr><td>$name</td><td>$score</td></tr>";
      }
    ?>
  </tbody>
</table>



<script>


function sortByScore() {
  const tbody = document.querySelector("#leaderboard tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));

  rows.sort((a, b) =>
    parseInt(b.cells[1].textContent) - parseInt(a.cells[1].textContent)
  );

  rows.forEach(r => tbody.appendChild(r));
}

function updateRankColors() {
  const rows = Array.from(document.querySelectorAll("#leaderboard tbody tr"));

  rows.forEach(r => {
    r.classList.remove("gold","silver","bronze");
    r.cells[0].textContent = r.cells[0].textContent
      .replace("🥇 ","")
      .replace("🥈 ","")
      .replace("🥉 ","");
  });

  if (rows[0]) { rows[0].classList.add("gold"); rows[0].cells[0].textContent = "🥇 " + rows[0].cells[0].textContent; }
  if (rows[1]) { rows[1].classList.add("silver"); rows[1].cells[0].textContent = "🥈 " + rows[1].cells[0].textContent; }
  if (rows[2]) { rows[2].classList.add("bronze"); rows[2].cells[0].textContent = "🥉 " + rows[2].cells[0].textContent; }
}



sortByScore();
updateRankColors();
</script>

</body>
</html>
