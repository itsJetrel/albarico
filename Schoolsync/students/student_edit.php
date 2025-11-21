<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: student_list.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: student_list.php");
    exit;
}

if (isset($_POST['update'])) {
    $student_id = $_POST['student_id'];
    $full_name = $_POST['full_name'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE students SET student_id=?, full_name=?, year_level=?, section=?, status=? WHERE id=?");
    $stmt->bind_param("sssssi", $student_id, $full_name, $year_level, $section, $status, $id);
    $stmt->execute();

    header("Location: student_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="../styles.css">
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
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            margin-left: 210px;
        }
        h2 {
            font-size: 25px;
            color: #2f855a;
            margin-bottom: 20px;
            margin-top: 20px;
        }
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        label {
            display: block;
            margin-top: 3px;
            font-weight: 600;
            color: #333;
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
        button[name="update"] {
            background-color: #2f855a;
            color: white;
        }
        button[name="update"]:hover {
            background-color: #276749;
        }
        button[type="button"] {
            background-color: #ccc;
        }
        button[type="button"]:hover {
            background-color: #999;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>

    <div class="main">
        <h2 style="text-align: center; padding-right: 190px;">Edit Student</h2>
        <div class="form-container">
            <form method="post">
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>" required>

                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>

                <label for="year_level">Year Level:</label>
                <select id="year_level" name="year_level" required>
                    <option value="1st" <?= $student['year_level'] === '1st' ? 'selected' : '' ?>>1st</option>
                    <option value="2nd" <?= $student['year_level'] === '2nd' ? 'selected' : '' ?>>2nd</option>
                    <option value="3rd" <?= $student['year_level'] === '3rd' ? 'selected' : '' ?>>3rd</option>
                    <option value="4th" <?= $student['year_level'] === '4th' ? 'selected' : '' ?>>4th</option>
                </select>

                <label for="section">Section:</label>
                <select id="section" name="section" required>
                    <option value="A" <?= strtoupper($student['section']) === 'A' ? 'selected' : '' ?>>A</option>
                    <option value="B" <?= strtoupper($student['section']) === 'B' ? 'selected' : '' ?>>B</option>
                    <option value="C" <?= strtoupper($student['section']) === 'C' ? 'selected' : '' ?>>C</option>
                    <option value="D" <?= strtoupper($student['section']) === 'D' ? 'selected' : '' ?>>D</option>
                </select>

                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Enrolled" <?= $student['status'] === 'Enrolled' ? 'selected' : '' ?>>Enrolled</option>
                    <option value="Pending" <?= $student['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Graduated" <?= $student['status'] === 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                    <option value="Dropped" <?= $student['status'] === 'Dropped' ? 'selected' : '' ?>>Dropped</option>
                </select>

                <button type="submit" name="update">Update</button>
                <button type="button" onclick="window.location.href='student_list.php'">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
