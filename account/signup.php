<?php
// Start session
session_start();

// Enable error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
include("../../../connection.php");


// Handle form submission
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields except team name are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT userID FROM fitness WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }

    $stmt->close();

    // If no errors, insert into database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO fitness (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            $success = "Registration successful! Redirecting...";
            // Redirect after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FitTracker Signup</title>

<style>
/* Background gradient animation (same theme as login page) */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    height: 100vh;

    background: linear-gradient(120deg, #47d4e3, #5ec80d, #3aa8f7, #82ff72);
    background-size: 300% 300%;
    animation: bgFlow 12s infinite alternate ease-in-out;

    display: flex;
    justify-content: center;
    align-items: center;
}

@keyframes bgFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Sign-up card */
.container {
    width: 420px;
    padding: 30px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);

    border-radius: 20px;
    border: 4px solid rgba(255,255,255,0.95);

    box-shadow: 0 0 25px rgba(0,0,0,0.25);

    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    animation: bounceIn 0.9s ease-out;
    text-align: center;
}

/* Bounce animation */
@keyframes bounceIn {
    0%   { transform: translate(-50%, -50%) scale(0.4); opacity: 0; }
    60%  { transform: translate(-50%, -50%) scale(1.05); opacity: 1; }
    80%  { transform: translate(-50%, -50%) scale(0.95); }
    100% { transform: translate(-50%, -50%) scale(1); }
}

/* Title gradient text */
h2 {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 20px;

    background: linear-gradient(to right, #000, #444, #111);
    -webkit-background-clip: text;
    color: transparent;
}

/* Inputs with glow + slight slide animation */
input[type=text],
input[type=email],
input[type=password] {
    width: 90%;
    padding: 12px;
    margin: 10px auto;
    border: 2px solid #ccc;
    border-radius: 10px;
    font-size: 16px;

    display: block;
    transition: 0.3s;

    animation: slideIn 0.6s ease-out;
}

@keyframes slideIn {
    0%   { transform: translateY(15px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}

input[type=text]:focus,
input[type=email]:focus,
input[type=password]:focus {
    border-color: #47d4e3;
    box-shadow: 0 0 10px #47d4e3;
    outline: none;
}

/* Button animation */
input[type=submit] {
    padding: 12px;
    width: 90%;
    margin-top: 10px;
    background: #28a745;

    color: white;
    font-size: 18px;
    font-weight: bold;

    border: none;
    border-radius: 10px;
    cursor: pointer;

    transition: 0.25s;
}

input[type=submit]:hover {
    transform: scale(1.05);
    background: #1e7e34;
    box-shadow: 0 0 12px rgba(0,0,0,0.3);
}

/* Animated messages */
.error, .success {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    animation: fadePop 0.6s ease-out;
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

@keyframes fadePop {
    0%   { transform: translateY(-10px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}
</style>
</head>

<body>

<div class="container">
    <h2>FitTracker Signup</h2>

    <?php
    if (!empty($errors)) {
        echo '<div class="error"><ul>';
        foreach ($errors as $error) echo "<li>$error</li>";
        echo '</ul></div>';
    }

    if ($success) {
        echo "<div class='success'>$success</div>";
    }
    ?>

    <form method="POST" action="">
        <label>Full Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <input type="submit" value="Sign Up">
    </form>
</div>

</body>
</html>
