<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #46e07b, #2d9e5a);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Floating Fun Shapes */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            animation: float 6s infinite ease-in-out;
            filter: blur(1px);
        }
        .bubble:nth-child(1) { width: 120px; height: 120px; top: 10%; left: 5%; animation-duration: 7s; }
        .bubble:nth-child(2) { width: 90px; height: 90px; bottom: 15%; right: 10%; animation-duration: 9s; }
        .bubble:nth-child(3) { width: 150px; height: 150px; top: 50%; right: 25%; animation-duration: 10s; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-40px); }
            100% { transform: translateY(0px); }
        }

        /* Title animation */
        p {
            color: white;
            font-size: 48px;
            font-weight: 700;
            text-shadow: 0 0 12px rgba(255,255,255,0.7);
            animation: popIn 1s ease forwards;
            margin-bottom: 40px;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.6); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Button container */
        form {
            width: 80%;
            max-width: 350px;
            margin-top: 15px;
        }

        /* Glowing fun buttons */
        button {
            width: 100%;
            padding: 16px;
            font-size: 20px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            background: #32d162;
            color: white;
            transition: 0.25s;
            box-shadow: 0 6px 15px rgba(0,0,0,0.18);
        }

        button:hover {
            transform: translateY(-4px) scale(1.05);
            background: #29ba57;
            box-shadow: 0 10px 20px rgba(0,0,0,0.25),
                        0 0 12px rgba(255,255,255,0.7);
        }

        button:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <!-- Animated floating shapes -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>

    <p>FitTracker</p>

    <form action="leaderboard.php">
        <button type="submit">🏆 Leaderboard</button>
    </form>

    <?php 
        session_start(); 
        if (isset($_SESSION['userID'])) {
            echo '<form action="account/dashboard.php">
                    <button type="submit">👤 Dashboard</button>
                  </form>';
        } else {
            echo '<form action="account/login.php">
                    <button type="submit">🔐 Login</button>
                  </form>';
        }
    ?>

</body>
</html>
