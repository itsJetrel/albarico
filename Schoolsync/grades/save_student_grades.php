<?php
include('../config/db.php'); // Make sure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $grades = $_POST['grades'] ?? [];

    if (!$student_id || empty($grades)) {
        echo "Invalid submission.";
        exit;
    }

    foreach ($grades as $subject_code => $data) {
        $grade_raw = trim($data['grade'] ?? '');
        $status = trim($data['status'] ?? 'NG');
        $semester = trim($data['semester'] ?? '');
        $academic_year = trim($data['academic_year'] ?? '');

        $grade = ($grade_raw === '') ? null : floatval($grade_raw);

        // Ensure subject_code exists in subjects table to satisfy FK constraint
        $subject_check = $conn->prepare("SELECT subject_code FROM subjects WHERE subject_code = ?");
        $subject_check->bind_param("s", $subject_code);
        $subject_check->execute();
        $subject_result = $subject_check->get_result();

        if ($subject_result->num_rows === 0) {
            echo "<script>alert('Subject code $subject_code does not exist in the subjects table.'); window.history.back();</script>";
            exit;
        }

        $subject_check->close();

        // Check if grade record already exists
        $check_stmt = $conn->prepare("SELECT id FROM student_grades WHERE student_id = ? AND subject_code = ?");
        $check_stmt->bind_param("is", $student_id, $subject_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing
            if ($grade === null) {
                $update_stmt = $conn->prepare("UPDATE student_grades SET grade = NULL, status = ?, semester = ?, academic_year = ? WHERE student_id = ? AND subject_code = ?");
                $update_stmt->bind_param("sssis", $status, $semester, $academic_year, $student_id, $subject_code);
            } else {
                $update_stmt = $conn->prepare("UPDATE student_grades SET grade = ?, status = ?, semester = ?, academic_year = ? WHERE student_id = ? AND subject_code = ?");
                $update_stmt->bind_param("dsssis", $grade, $status, $semester, $academic_year, $student_id, $subject_code);
            }
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new
            if ($grade === null) {
                $insert_stmt = $conn->prepare("INSERT INTO student_grades (student_id, subject_code, grade, status, semester, academic_year) VALUES (?, ?, NULL, ?, ?, ?)");
                $insert_stmt->bind_param("issss", $student_id, $subject_code, $status, $semester, $academic_year);
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO student_grades (student_id, subject_code, grade, status, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("isdsss", $student_id, $subject_code, $grade, $status, $semester, $academic_year);
            }
            if (!$insert_stmt->execute()) {
                echo "<script>alert('Error saving grade for $subject_code: " . $conn->error . "'); window.history.back();</script>";
                exit;
            }
            $insert_stmt->close();
        }

        $check_stmt->close();
    }

    echo "<script>alert('Grades successfully saved!'); window.location.href = 'student_grades_view.php?student_id=" . htmlspecialchars($student_id, ENT_QUOTES) . "';</script>";
    exit;
} else {
    echo "Invalid request.";
}
?>
