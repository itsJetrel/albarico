<?php
include('../config/db.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year_level = $_POST['year_level'];
    $subject_code = trim($_POST['subject_code']);
    $subject_name = trim($_POST['subject_name']);
    $semester = $_POST['semester'];
    $academic_year = $_POST['academic_year'];

    // Insert subject into subjects table if not exists
    $check_subject = $conn->prepare("SELECT subject_code FROM subjects WHERE subject_code = ?");
    $check_subject->bind_param("s", $subject_code);
    $check_subject->execute();
    $subject_result = $check_subject->get_result();

    if ($subject_result->num_rows === 0) {
        $insert_subject = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, semester) VALUES (?, ?, ?)");
        $insert_subject->bind_param("sss", $subject_code, $subject_name, $semester);
        $insert_subject->execute();
    }


    // Default all year levels to A–D sections
    $sections = ['A', 'B', 'C', 'D'];

    foreach ($sections as $section) {
        // Avoid duplicates
        $check_stmt = $conn->prepare("SELECT id FROM class_schedules WHERE year_level = ? AND section = ? AND subject_code = ? AND semester = ? AND academic_year = ?");
        $check_stmt->bind_param("sssss", $year_level, $section, $subject_code, $semester, $academic_year);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO class_schedules (year_level, section, subject_code, subject_name, semester, academic_year)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $year_level, $section, $subject_code, $subject_name, $semester, $academic_year);
            $stmt->execute();
        }
    }

    header("Location: class_schedule_filter.php");
    exit;
}

// Dropdown values
$year_levels = ['1st', '2nd', '3rd', '4th'];
$semesters = ['1st', '2nd'];
$academic_years = [];
$current_year = date('Y');
for ($i = 0; $i < 10; $i++) {
    $start = $current_year + $i;
    $end = $start + 1;
    $academic_years[] = "$start-$end";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Full Schedule</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: 0.5rem; }
        select, input[type="text"] { width: 100%; padding: 0.5rem; }
        .btn { padding: 10px 20px; background-color: #3498db; color: white; border: none; border-radius: 4px; }
        .btn:hover { background-color: #2980b9; }

        /* Basic Reset */
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


/* Main content area */
.main {
            margin-left: 220px; /* same width as the sidebar */
            padding: 30px;
            background-color: #f4f4f4;
            flex: 1;
            min-height: 100vh;
        }

/* Form styles */
form {
    background-color: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin-left: 220px;
}

h2 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

input[type="text"], select {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
}

input[type="text"]:focus,
select:focus {
    border-color: #3498db;
    outline: none;
}

.btn {
    padding: 10px 20px;
    background-color: #3498db;
    color: white;
    border: none;
    font-weight: bold;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn:hover {
    background-color: #2980b9;
}

/* Error box */
.error {
    background-color: #ffe6e6;
    border: 1px solid #ff4d4d;
    color: #cc0000;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>
    <div class="main">
        <h2>Create Class Schedule</h2>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Year Level</label>
                <select name="year_level" required>
                    <option value="">-- Select Year Level --</option>
                    <?php foreach ($year_levels as $yl): ?>
                        <option value="<?= $yl ?>"><?= $yl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="subject_code" required>
            </div>

            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="subject_name" required>
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester" required>
                    <option value="">-- Select Semester --</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem ?>"><?= $sem ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Academic Year</label>
                <select name="academic_year" required>
                    <option value="">-- Select Academic Year --</option>
                    <?php foreach ($academic_years as $ay): ?>
                        <option value="<?= $ay ?>"><?= $ay ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Save Schedule</button>
        </form>
    </div>
</body>
</html>
