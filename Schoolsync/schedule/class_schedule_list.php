<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

// Sorting logic
$sort = $_GET['sort'] ?? 'day';
$order_by = ($sort === 'year')
    ? "year_level, section, semester, day, time_start"
    : "day, time_start, room";

$result = $conn->query("SELECT * FROM class_schedules ORDER BY $order_by");

$conflicts = [];
$schedules = [];

while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

for ($i = 0; $i < count($schedules); $i++) {
    for ($j = $i + 1; $j < count($schedules); $j++) {
        $a = $schedules[$i];
        $b = $schedules[$j];
        if (
            $a['room'] === $b['room'] &&
            $a['day'] === $b['day'] &&
            $a['semester'] === $b['semester'] &&
            $a['academic_year'] === $b['academic_year'] &&
            $a['time_start'] < $b['time_end'] &&
            $a['time_end'] > $b['time_start']
        ) {
            $conflicts[$a['id']] = true;
            $conflicts[$b['id']] = true;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Class Schedules</title>
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
            margin-left: 220px;
            padding: 30px;
            background-color: #f4f4f4;
            min-height: 100vh;
        }

        .main h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .sort-btn {
            float: right;
            padding: 8px 16px;
            background-color: #2f855a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 17px;
        }

        .sort-btn:hover {
            background-color: #276749;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #e2e8f0;
        }

        .conflict {
            background-color: #ffe6e6;
        }

        .actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    color: white;
    transition: background-color 0.3s ease;
}

.btn-action.edit {
    background-color: #2f855a; /* blue */
}

.btn-action.edit:hover {
    background-color: #276749;
}

.btn-action.delete {
    background-color: #e53e3e; /* red */
}

.btn-action.delete:hover {
    background-color: #9b2c2c;
}


        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #2f855a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .btn:hover {
            background-color:#276749;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>

    <div class="main">

    <a class="btn" href="class_schedule_create_full.php">+ Add New Subjects and Schedule</a>
        
        <h2>
            All Class Schedules
            <a href="?sort=<?= $sort === 'year' ? 'day' : 'year' ?>" class="sort-btn">
                Sort by <?= $sort === 'year' ? 'Day & Time' : 'Year & Section' ?>
            </a>
        </h2>

        <table>
            <tr>
                <th>Subject</th>
                <th>Day</th>
                <th>Time</th>
                <th>Room</th>
                <th>Year</th>
                <th>Section</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($schedules as $row): ?>
            <tr class="<?= isset($conflicts[$row['id']]) ? 'conflict' : '' ?>">
                <td><?= htmlspecialchars($row['subject_code']) ?> - <?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= $row['day'] ?></td>
                <td><?= $row['time_start'] ?> - <?= $row['time_end'] ?></td>
                <td><?= $row['room'] ?></td>
                <td><?= $row['year_level'] ?></td>
                <td><?= $row['section'] ?></td>
                <td><?= $row['semester'] ?></td>
                <td class="actions">
                    <a href="class_schedule_edit.php?id=<?= $row['id'] ?>" class="btn-action edit">✏️</a>
                    <a href="class_schedule_delete.php?id=<?= $row['id'] ?>" class="btn-action delete" onclick="return confirm('Delete this schedule?')">🗑</a>
                </td>

            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
