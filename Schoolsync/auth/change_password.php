<?php
session_start();
require_once '../config/db.php'; // mysqli connection $conn

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($current_password, $user['password'])) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hashed, $user_id);
            if ($update->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Failed to update password.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
                width: 220px;
                background-color: #2c3e50;
                color: white;
                padding: 20px;
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
    
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 30px;
            color: #ecf0f1;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 10px 0;
        }

        .main {
            margin-left: 450px;
            margin-top: 20px;
            padding: 60px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2f855a;
        }

        label {
            font-weight: bold;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #2f855a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #276749;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            background-color: #fed7d7;
            color: #c53030;
        }

        .success {
            background-color: #c6f6d5;
            color: #2f855a;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>

    <div class="main">
        <div class="form-container">
            <h2 style="margin-bottom: 15px;">Change Password</h2>

            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>

                <label>New Password:</label>
                <input type="password" name="new_password" required>

                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
