<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

$year_level = $_GET['year_level'] ?? '';
$section = $_GET['section'] ?? '';

// Fetch year levels
$year_result = $conn->query("SELECT DISTINCT year_level FROM class_schedules ORDER BY year_level ASC");

// Fetch sections for selected year level
$section_result = null;
if ($year_level) {
    $stmt = $conn->prepare("SELECT DISTINCT section FROM class_schedules WHERE year_level = ? ORDER BY section ASC");
    $stmt->bind_param("s", $year_level);
    $stmt->execute();
    $section_result = $stmt->get_result();
}

// Fetch schedules for selected year + section
$schedule_1st = $schedule_2nd = null;
if ($year_level && $section) {
    $stmt = $conn->prepare("SELECT * FROM class_schedules WHERE year_level = ? AND section = ? ORDER BY day, time_start");
    $stmt->bind_param("ss", $year_level, $section);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedule_1st = [];
    $schedule_2nd = [];
    while ($row = $result->fetch_assoc()) {
        if (strtolower($row['semester']) === '1st') {
            $schedule_1st[] = $row;
        } elseif (strtolower($row['semester']) === '2nd') {
            $schedule_2nd[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Class Schedule Filter</title>
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
            margin-left: 250px;
            margin-top: 20px;
            padding: 60px;
        }
        h2 {
            font-size: 28px;
            color: #2f855a;
            margin-bottom: 20px;
            border-bottom: 3px solid #2f855a;
            padding-bottom: 10px;
            font-weight: 700;
        }

         label {
            font-size: 16px;
            margin-top: 10px;
            display: block;
            font-weight: 500;
        }

        select {
            width: 300px;
            padding: 10px 12px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            transition: all 0.2s ease-in-out;
        }

        select:hover,
        select:focus {
            border-color: #2f855a;
            outline: none;
            box-shadow: 0 0 4px rgba(47, 133, 90, 0.3);
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        a.link {
            text-decoration: none;
            color: #2f855a;
            font-weight: bold;
        }

        a.link:hover {
            text-decoration: underline;
        }

        table {
            width: 950px;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #2f855a;
            color: white;
        }

        .card-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Use center if you want the cards centered */
            gap: 15px;
            margin-top: 20px;
            text-align: center;
        }


        .card {
           width: 300px;
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: #2f855a;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: 0.2s ease;
            margin-left: 200px;
        }

        .card:hover {
            background-color: #e6f5ec;
            border-color: #2f855a;
        }
        select, button { padding: 6px 10px; margin: 5px 0; }
       
    </style>
</head>
<body>
<?php include('../sidebar.php'); ?>

<div class="main">
    <h2>Class Schedules</h2>

    <form method="GET" action="class_schedule_filter.php">
        <label for="year_level">Year Level:</label><br>
        <select name="year_level" id="year_level" onchange="this.form.submit()">
            <option value="">-- Select Year Level --</option>
            <?php while ($row = $year_result->fetch_assoc()): ?>
                <option value="<?= $row['year_level'] ?>" <?= $year_level === $row['year_level'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['year_level']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <?php if ($section_result): ?>
            <label for="section">Section:</label><br>
            <select name="section" id="section" onchange="this.form.submit()">
                <option value="">-- Select Section --</option>
                <?php while ($row = $section_result->fetch_assoc()): ?>
                    <option value="<?= $row['section'] ?>" <?= $section === $row['section'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['section']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        <?php endif; ?>
    </form>

    <?php if ($year_level && $section): ?>
        <?php if (!empty($schedule_1st)): ?>
            <h3 style="margin-top: 20px; ">1st Semester Schedule - <?= htmlspecialchars($year_level) ?> <?= htmlspecialchars($section) ?></h3>
            <table>
                <tr>
                    <th style="width: 350px; text-align: center;">Subject</th>
                    <th style="width: 150px; text-align: center;">Day</th>
                    <th style="width: 250px; text-align: center;">Time</th>
                    <th style="width: 150px; text-align: center;">Room</th>
                </tr>
                <?php foreach ($schedule_1st as $row): ?>
                <tr>
                    <td style="width: 350px; text-align: center;"><?= htmlspecialchars($row['subject_code']) ?> - <?= htmlspecialchars($row['subject_name']) ?></td>
                    <td style="width: 150px; text-align: center;"><?= $row['day'] ?></td>
                    <td style="width: 250px; text-align: center;"><?= $row['time_start'] ?> - <?= $row['time_end'] ?></td>
                    <td style="width: 150px; text-align: center;"><?= $row['room'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (!empty($schedule_2nd)): ?>
            <h3 style="margin-top: 20px; ">2nd Semester Schedule - <?= htmlspecialchars($year_level) ?> <?= htmlspecialchars($section) ?></h3>
            <table>
                <tr>
                    <th style="width: 350px; text-align: center;">Subject</th>
                    <th style="width: 150px; text-align: center;">Day</th>
                    <th style="width: 250px; text-align: center;">Time</th>
                    <th style="width: 150px; text-align: center;">Room</th>
                </tr>
                <?php foreach ($schedule_2nd as $row): ?>
                <tr>
                    <td style="width: 350px; text-align: center;"><?= htmlspecialchars($row['subject_code']) ?> - <?= htmlspecialchars($row['subject_name']) ?></td>
                    <td style="width: 150px; text-align: center;"><?= $row['day'] ?></td>
                    <td style="width: 250px; text-align: center;"><?= $row['time_start'] ?> - <?= $row['time_end'] ?></td>
                    <td style="width: 150px; text-align: center;"><?= $row['room'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (empty($schedule_1st) && empty($schedule_2nd)): ?>
            <p>No class schedules found for this section.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
