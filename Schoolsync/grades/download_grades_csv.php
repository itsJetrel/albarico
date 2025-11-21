<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include('../config/db.php');

// Get filters
$filter_year = $_GET['year_level'] ?? '';
$filter_section = $_GET['section'] ?? '';

// Build query
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
$result = $stmt->get_result();

// Set headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="grades_summary.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, ['Student ID', 'Full Name', 'Year Level', 'Section', 'Average Grade']);

// Write data
while ($row = $result->fetch_assoc()) {
    $avg = is_numeric($row['avg_grade']) ? $row['avg_grade'] : 'NG';
    fputcsv($output, [
        $row['student_id'],
        $row['full_name'],
        $row['year_level'],
        $row['section'],
        $avg
    ]);
}

fclose($output);
exit;
?>
