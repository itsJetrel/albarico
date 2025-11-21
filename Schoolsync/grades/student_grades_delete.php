<?php
include('../config/db.php');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No grade ID provided.";
    exit;
}

// Get student ID before deleting
$stmt = $conn->prepare("SELECT student_id FROM student_grades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$grade = $res->fetch_assoc();

if (!$grade) {
    echo "Grade not found.";
    exit;
}

$student_id = $grade['student_id'];

$delete_stmt = $conn->prepare("DELETE FROM student_grades WHERE id = ?");
$delete_stmt->bind_param("i", $id);
$delete_stmt->execute();

header("Location: student_grades_view.php?student_id=$student_id");
exit;
