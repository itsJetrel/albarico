<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM class_schedules WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: class_schedule_list.php");
exit;
