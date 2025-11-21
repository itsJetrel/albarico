<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

// Validate ID
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: class_schedule_list.php");
    exit;
}

// Fetch schedule
$stmt = $conn->prepare("SELECT * FROM class_schedules WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if (!$schedule) {
    header("Location: class_schedule_list.php");
    exit;
}

// Arrays for dropdowns
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$rooms = ['Room A1', 'Room A2', 'Lab 1', 'Lab 2', 'Lab 3', 'Lab 4']; // Update as needed

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day'];
    $time_start = $_POST['time_start'];
    $time_end = $_POST['time_end'];
    $room = $_POST['room'];

    $stmt = $conn->prepare("UPDATE class_schedules SET day=?, time_start=?, time_end=?, room=? WHERE id=?");
    $stmt->bind_param("ssssi", $day, $time_start, $time_end, $room, $id);
    $stmt->execute();

    header("Location: class_schedule_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Class Schedule</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 500px;
            margin: 60px auto;
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2f855a;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="time"],
        select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2f855a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background-color: #276749;
        }

        .cancel-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #2f855a;
            text-decoration: none;
        }

        .cancel-btn:hover {
             text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Class Schedule</h2>

        <form method="POST">
            <div class="form-group">
                <label>Day</label>
                <select name="day" required>
                    <option value="">-- Select Day --</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?= $day ?>" <?= $schedule['day'] === $day ? 'selected' : '' ?>><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Time Start</label>
                <input type="time" name="time_start" value="<?= htmlspecialchars($schedule['time_start']) ?>" required>
            </div>

            <div class="form-group">
                <label>Time End</label>
                <input type="time" name="time_end" value="<?= htmlspecialchars($schedule['time_end']) ?>" required>
            </div>

            <div class="form-group">
                <label>Room</label>
                <select name="room" required>
                    <option value="">-- Select Room --</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room ?>" <?= $schedule['room'] === $room ? 'selected' : '' ?>><?= $room ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Update Schedule</button>
        </form>
        <a href="class_schedule_list.php" class="btn cancel-btn">Cancel</a>
    </div>
</body>
</html>
