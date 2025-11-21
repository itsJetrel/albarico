<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include('../config/db.php');

$year = $_GET['year'] ?? null;
$section = $_GET['section'] ?? null;

if (!$year) {
    // Get all year levels
    $result = $conn->query("SELECT DISTINCT year_level FROM students ORDER BY FIELD(year_level, '1st', '2nd', '3rd', '4th')");
} elseif ($year && !$section) {
    // Get sections under the selected year level
    $stmt = $conn->prepare("SELECT DISTINCT section FROM students WHERE year_level = ?");
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $sections = $stmt->get_result();
} else {
    // Get students in the selected year and section
    $stmt = $conn->prepare("SELECT * FROM students WHERE year_level = ? AND section = ?");
    $stmt->bind_param("ss", $year, $section);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Filter</title>
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

        h2, h3 {
            margin-bottom: 20px;
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


    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>

    <div class="main">

    <?php if (!$year): ?>
        <h3>Select Year Level</h3>
        <div class="card-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <a class="card" href="?year=<?= urlencode($row['year_level']) ?>">
                    <?= htmlspecialchars($row['year_level']) ?>
                </a>
            <?php endwhile; ?>
        </div>

    <?php elseif ($year && !$section): ?>
        <h3>Select Section under <?= htmlspecialchars($year) ?> Year</h3>
        <div class="card-container">
            <?php while ($row = $sections->fetch_assoc()): ?>
                <a class="card" href="?year=<?= urlencode($year) ?>&section=<?= urlencode($row['section']) ?>">
                    <?= htmlspecialchars($row['section']) ?>
                </a>
            <?php endwhile; ?>
        </div> 

    <?php elseif ($year && $section): ?>
        <h3>Students in <?= htmlspecialchars($year) ?> Year - Section <?= htmlspecialchars($section) ?></h3>
        <table>
            <thead style="width: 350px; text-align: center;">
                <tr>
                    <th style="width: 100px; text-align: center;">Student ID</th>
                    <th style="width: 350px; text-align: center;">Full Name</th>
                    <th style="width: 200px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td style="text-align: center;"><?= htmlspecialchars($row['student_id']) ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($row['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
