<?php
include('../config/db.php');

$year_level = $_GET['year'] ?? null;
$section = $_GET['section'] ?? null;

if (!$year_level) {
    $result = $conn->query("SELECT DISTINCT year_level FROM students ORDER BY FIELD(year_level, '1st', '2nd', '3rd', '4th')");
} elseif ($year_level && !$section) {
    $stmt = $conn->prepare("SELECT DISTINCT section FROM students WHERE year_level = ?");
    $stmt->bind_param("s", $year_level);
    $stmt->execute();
    $sections = $stmt->get_result();
} elseif ($year_level && $section) {
    $stmt = $conn->prepare("SELECT id, full_name FROM students WHERE year_level = ? AND section = ?");
    $stmt->bind_param("ss", $year_level, $section);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grades Filter</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            display: flex;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
        }

        .sidebar {
            width: 220px;
            background-color: #2f855a;
            color: white;
            padding: 30px 20px;
            height: 100vh;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 1.7rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 10px 0;
            font-weight: 500;
            padding: 10px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #276749;
        }

        .main {
            margin-left: 220px;
            padding: 40px;
            flex: 1;
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #2f855a;
        }

        h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .card-list {
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Or center if you want them centered */
            gap: 20px;
            margin-top: 20px;
        }

        .card-list a {
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

        .card-list a:hover {
            background-color: #e6f5ec;
            border-color: #2f855a;
        }

        .student-table {
            width: 100%;
            max-width: 800px;
            min-width: 200px;
            border-collapse: collapse;
            margin-top: 20px;
            margin-left: 150px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .student-table th,
        .student-table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        .student-table th {
            background-color: #2f855a;
            color: white;
        }

        .student-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .view-link {
            color: #2f855a;
            text-decoration: none;
            font-weight: bold;
        }

        .view-link:hover {
            text-decoration: underline;
        }


    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>

    <div class="main">
        <h2>Grades</h2>

        <?php if (!isset($year_level)): ?>
            <h3>Select Year Level</h3>
            <div class="card-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="?year=<?= urlencode($row['year_level']) ?>">
                        <?= htmlspecialchars($row['year_level']) ?>
                    </a>
                <?php endwhile; ?>
            </div>

        <?php elseif ($year_level && !$section): ?>
            <h3>Select Section for <?= htmlspecialchars($year_level) ?> Year</h3>
            <div class="card-list">
                <?php while ($row = $sections->fetch_assoc()): ?>
                    <a href="?year=<?= urlencode($year_level) ?>&section=<?= urlencode($row['section']) ?>">
                        <?= htmlspecialchars($row['section']) ?>
                    </a>
                <?php endwhile; ?>
            </div>

        <?php elseif ($year_level && $section): ?>
            <h3>Students in <?= htmlspecialchars($year_level) ?> Year - Section <?= htmlspecialchars($section) ?></h3>
            <table class="student-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 350px; text-align: center;">Full Name</th>
                        <th style="width: 200px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align: center;"><?= $i++ ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td style="text-align: center;">
                                <a class="view-link" href="student_grades_view.php?student_id=<?= $row['id'] ?>">View Grades</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
