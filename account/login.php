<?php
// Start session
session_start();

// Enable error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include("../../../connection.php");

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = "Both email and password are required.";
    }

    if (empty($errors)) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT userID, username, password FROM fitness WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($userID, $username, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start session
                $_SESSION['userID'] = $userID;
                $_SESSION['username'] = $username;
                $success = "Login successful! Redirecting...";

                // Redirect after 2 seconds
                header("refresh:2;url=dashboard.php");
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Email not registered.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>FitTracker Login</title>

    <style>
        /* Background gradient animation */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(120deg, #47d4e3ff, #5ec80d, #3aa8f7, #82ff72);
            background-size: 300% 300%;
            animation: bgFlow 12s infinite alternate ease-in-out;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        @keyframes bgFlow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Login card with bounce-in animation */
    .container {
    width: 420px;
    padding: 30px;

    /* White outline so it stands out more */
    border: 4px solid rgba(255,255,255,0.95);

    border-radius: 20px;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(10px);

    box-shadow: 0 0 25px rgba(0,0,0,0.25);

    /* Bounce animation */
    animation: bounceIn 0.9s ease-out;

    /* PERFECT CENTERING */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    text-align: center;
}


    /* PERFECT CENTERING */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    text-align: center;
}


        @keyframes bounceIn {
            0%   { transform: scale(0.4); opacity: 0; }
            60%  { transform: scale(1.05); opacity: 1; }
            80%  { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        h2 {
            background: linear-gradient(to right, #1a1a1a, #333, #000);
            -webkit-background-clip: text;
            color: transparent;
            font-size: 34px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        /* Inputs with glow on focus */
        input[type=email], input[type=password] {
            width: 90%;
            padding: 12px;
            margin: 5px 0 20px;
            border: 2px solid #ccc;
            border-radius: 8px;
            transition: 0.3s;
            font-size: 16px;
        }

        input[type=email]:focus,
        input[type=password]:focus {
            border-color: #47d4e3;
            box-shadow: 0 0 10px #47d4e3;
            outline: none;
        }

        /* Submit button with pulse animation */
        input[type=submit] {
            padding: 12px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 17px;
            font-weight: bold;
            width: 100%;
            transition: 0.2s;
        }

        input[type=submit]:hover {
            background: #0056b3;
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(0,0,0,0.25);
        }

        /* Animated error + success messages */
        .error, .success {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            animation: msgPop 0.5s ease-out;
        }

        .error { 
            background: #ffdddd; 
            border-left: 6px solid #ff3b3b; 
            color: #c40000; 
        }

        .success { 
            background: #ddffdd; 
            border-left: 6px solid #3ac43a; 
            color: #0f8f0f; 
        }

        @keyframes msgPop {
            0% { transform: translateY(-10px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Home button */
        button {
            padding: 10px 20px;
            background: #4CAF50;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
            font-weight: bold;
            transition: 0.25s;
        }

        button:hover {
            transform: scale(1.1);
            background: #2f7a32;
            box-shadow: 0 0 12px rgba(0,0,0,0.3);
        }
    </style>
</head>

<body>

<form action="../index.php">
    <button type="submit">Go Home</button>
</form>

<div class="container">
    <h2>FitTracker Login</h2>

    <?php
    if (!empty($errors)) {
        echo '<div class="error"><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul></div>';
    }

    if ($success) {
        echo "<div class='success'>$success</div>";
    }
    ?>

    <form method="POST" action="">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="signup.php">Sign up here</a>.</p>
</div>

</body>
</html>
