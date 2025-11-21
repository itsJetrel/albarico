<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

// Filters
$filter_year = $_GET['year_level'] ?? '';
$filter_section = $_GET['section'] ?? '';

// Get distinct year levels and sections
$year_levels = $conn->query("SELECT DISTINCT year_level FROM students ORDER BY year_level ASC");
$sections = [];
if ($filter_year) {
    $stmt = $conn->prepare("SELECT DISTINCT section FROM students WHERE year_level = ? ORDER BY section ASC");
    $stmt->bind_param("s", $filter_year);
    $stmt->execute();
    $sections = $stmt->get_result();
}

// Get students with average grade
$query = "
    SELECT 
        s.id AS student_id,
        s.full_name,
        s.year_level,
        s.section,
        ROUND(AVG(CASE WHEN sg.grade IS NOT NULL THEN sg.grade END), 2) AS avg_grade
    FROM students s
    LEFT JOIN student_grades sg ON sg.student_id = s.id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($filter_year) {
    $query .= " AND s.year_level = ?";
    $params[] = $filter_year;
    $types .= 's';
}
if ($filter_section) {
    $query .= " AND s.section = ?";
    $params[] = $filter_section;
    $types .= 's';
}
$query .= " GROUP BY s.id ORDER BY s.year_level ASC, s.section ASC, s.full_name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grades Summary</title>
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
        .main { margin-left: 220px; padding: 20px; }
        h2 { color: #2f855a; font-size: 28px; margin-bottom: 20px; }

        .filter-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        select, button {
            padding: 6px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: center;
        }
        th {
            background: #2f855a;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f1f1f1;
        }

        .no-data {
            color: red;
            font-style: italic;
        }

        .btn-download {
            background-color: #3182ce;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            margin-left: auto;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include('../sidebar.php'); ?>

<div class="main">
    <h2>Grades Summary</h2>

    <form method="GET" class="filter-box">
        <select name="year_level" onchange="this.form.submit()">
            <option value="">-- All Year Levels --</option>
            <?php while ($row = $year_levels->fetch_assoc()): ?>
                <option value="<?= $row['year_level'] ?>" <?= $filter_year === $row['year_level'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['year_level']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <?php if ($sections): ?>
            <select name="section" onchange="this.form.submit()">
                <option value="">-- All Sections --</option>
                <?php while ($row = $sections->fetch_assoc()): ?>
                    <option value="<?= $row['section'] ?>" <?= $filter_section === $row['section'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['section']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        <?php endif; ?>

        <button class="btn-download" formaction="download_grades_csv.php" formmethod="GET">Download CSV</button>
    </form>

    <?php if ($students->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Year Level</th>
                <th>Section</th>
                <th>Average Grade</th>
            </tr>
            <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['year_level']) ?></td>
                    <td><?= htmlspecialchars($row['section']) ?></td>
                    <td><?= is_numeric($row['avg_grade']) ? $row['avg_grade'] : 'NG' ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-data">No students found for selected filter.</p>
    <?php endif; ?>
</div>
</body>
</html>
