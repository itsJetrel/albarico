<?php
include('../config/db.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day'];
    $time_start = $_POST['time_start'];
    $time_end = $_POST['time_end'];
    $room = $_POST['room'];

    // Update this part as needed depending on how you're identifying the schedule to update
    // For now, let's assume you're updating a specific schedule_id (can be passed via GET or POST)
    $schedule_id = $_GET['id'] ?? null;

    if ($schedule_id) {
        $stmt = $conn->prepare("UPDATE class_schedules SET day = ?, time_start = ?, time_end = ?, room = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $day, $time_start, $time_end, $room, $schedule_id);

        if ($stmt->execute()) {
            header("Location: class_schedule_filter.php");
            exit;
        } else {
            $errors[] = "Error updating schedule: " . $stmt->error;
        }
    } else {
        $errors[] = "No schedule ID provided.";
    }
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$rooms = ['Room A1', 'Room A2', 'Lab 1', 'Lab 2', 'Lab 3', 'Lab 4'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Day, Time & Room</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: 0.5rem; }
        select, input[type="time"] { width: 100%; padding: 0.5rem; }
        .btn { padding: 10px 20px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; }
        .btn:hover { background-color: #27ae60; }
    </style>
</head>
<body>
    
    
    <div class="main">
        <h2>Assign Day, Time & Room</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Day</label>
                <select name="day" required>
                    <option value="">-- Select Day --</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?= $day ?>"><?= $day ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Time Start</label>
                <input type="time" name="time_start" required>
            </div>

            <div class="form-group">
                <label>Time End</label>
                <input type="time" name="time_end" required>
            </div>

            <div class="form-group">
                <label>Room</label>
                <select name="room" required>
                    <option value="">-- Select Room --</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room ?>"><?= $room ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Save Changes</button>
        </form>
    </div>
</body>
</html>
