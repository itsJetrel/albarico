<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include('../config/db.php');

// Fetch total number of students
$studentQuery = $conn->query("SELECT COUNT(*) as total FROM students");
$studentRow = $studentQuery->fetch_assoc();
$studentCount = $studentRow['total'];

$scheduleQuery = $conn->query("SELECT COUNT(*) as total FROM class_schedules");
$scheduleRow = $scheduleQuery->fetch_assoc();
$scheduleCount = $scheduleRow['total'];

// Count grades
$gradesResult = $conn->query("SELECT COUNT(*) AS total FROM student_grades");
$gradesRow = $gradesResult->fetch_assoc();
$gradesCount = $gradesRow['total'];


?>


<!DOCTYPE html>
<html>
<head>
    <title>SchoolSync Dashboard</title>
    <link rel="stylesheet" href="index.css">
    <style>
        

        h2 {
        font-size: 28px;
        margin-bottom: 20px;
    }

    .cards {
        display: flex;
        flex-wrap: wrap;
        gap: 50px;
        margin-top: 50px;
    }

    .card {
        flex: 1;
        min-width: 250px;
        max-width: 300px;
        background-color: white;
        border-radius: 16px;
        padding: 30px 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 5px solid #2f855a;
        position: relative;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
    }

    .card h3 {
        font-size: 20px;
        color: #2f855a;
        margin: 0;
        margin-bottom: 10px;
    }

    .card p {
        font-size: 36px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }

    .card::after {
        content: '';
        position: absolute;
        right: 20px;
        bottom: 20px;
        width: 50px;
        height: 50px;
        background-repeat: no-repeat;
        background-size: contain;
        opacity: 0.1;
    }

    .card[href*="student"]::after {
        background-image: url('https://cdn-icons-png.flaticon.com/512/1077/1077063.png');
    }

    .card[href*="schedule"]::after {
        background-image: url('https://cdn-icons-png.flaticon.com/512/2620/2620983.png');
    }

    .card[href*="grades"]::after {
        background-image: url('https://cdn-icons-png.flaticon.com/512/3135/3135768.png');
    }
        
    </style>
</head>
<body>

<div class="dashboard">
    <?php include('../sidebar.php'); ?>

    <div class="main">
        <h2>Welcome to SchoolSync Dashboard</h2>

        <div class="cards">
            <a href="../students/student_filter.php" class="card">
                <h3>Total Students</h3>
                <p><?= $studentCount ?></p>
            </a>

           <a href="../schedule/class_schedule_filter.php" class="card">
                <h3>Class Schedules</h3>
                <p><?= $scheduleCount ?></p>
            </a>


            <a href="../grades/student_grades_filter.php" class="card">
                <h3>Grades</h3>
                <p><?= $gradesCount ?></p>
            </a>
        </div>
    </div>
</div>

</body>
</html>
