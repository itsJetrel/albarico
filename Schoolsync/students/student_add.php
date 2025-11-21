<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

if (isset($_POST['submit'])) {
    $student_id = $_POST['student_id'];
    $full_name = $_POST['full_name'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, year_level, section, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $student_id, $full_name, $year_level, $section, $status);
    $stmt->execute();

    header("Location: student_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
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
            margin-left: 240px; /* Space for sidebar */
            padding: 40px 60px;
        }

        h2 {
            font-size: 25px;
            color: #2f855a;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            margin-left: 210px;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #333;
        }

        /* Prevent input and select width collapse */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px 14px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: #fff;
            box-sizing: border-box;
            display: block;
        }


        .btn {
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

        .btn:hover {
            background-color: #276749;
        }

        .cancel-btn {
            background-color: #ccc;
            color: #333;
            margin-left: 1px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            padding: 10px 20px;
            border-radius: 6px;
        }

        .cancel-btn:hover {
            background-color: #999;
        }

    </style>
</head>
<body>

<?php include('../sidebar.php'); ?>

<div class="main">
    <h2 style="text-align: center; padding-right: 100px;">Add New Student</h2>
    <div class="form-container">
        <form method="POST">
            <label for="student_id">Student ID:</label>
            <input type="text" name="student_id" required>

            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" required>

            <label for="year_level">Year Level:</label>
            <select name="year_level">
                <option value="1st">1st</option>
                <option value="2nd">2nd</option>
                <option value="3rd">3rd</option>
                <option value="4th">4th</option>
            </select>

            <label for="section">Section:</label>
            <select name="section">
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            <label for="status">Status:</label>
            <select name="status">
                <option value="Enrolled">Enrolled</option>
                <option value="Pending">Pending</option>
                <option value="Graduated">Graduated</option>
                <option value="Dropped">Dropped</option>
            </select>

            <button class="btn" type="submit" name="submit">Add Student</button>
            <a href="student_list.php" class="btn cancel-btn">Cancel</a>

        </form>
    </div>
</div>

</body>
</html>
