<?php
include('../config/db.php');

$id = $_GET['id'] ?? null;
if (!$id) exit("Grade ID missing.");

// Get grade
$stmt = $conn->prepare("SELECT * FROM student_grades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$grade = $result->fetch_assoc();

if (!$grade) exit("Grade not found.");

// Get subjects
$subjects = $conn->query("SELECT subject_code, subject_name FROM subjects");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = $_POST['subject_code'];
    $grade_val = trim($_POST['grade']) ?: null;
    $status = strtoupper(trim($_POST['status'] ?? ''));

    // Get semester from subject
    $sem_stmt = $conn->prepare("SELECT semester FROM subjects WHERE subject_code = ?");
    $sem_stmt->bind_param("s", $subject_code);
    $sem_stmt->execute();
    $sem_result = $sem_stmt->get_result();
    $subject_data = $sem_result->fetch_assoc();
    $semester = $subject_data['semester'];

    // Determine status
    if ($grade_val !== null && $grade_val !== '') {
        $grade_float = floatval($grade_val);
        $status = ($grade_float <= 3.00) ? 'PASSED' : 'FAILED';
    } elseif (empty($status)) {
        $status = 'NG';
    }

    $update_stmt = $conn->prepare("UPDATE student_grades SET subject_code = ?, grade = ?, status = ?, semester = ? WHERE id = ?");
    $update_stmt->bind_param("sdssi", $subject_code, $grade_val, $status, $semester, $id);
    $update_stmt->execute();

    header("Location: student_grades_view.php?student_id=" . $grade['student_id']);
    exit;
}
?>

<h2>Edit Grade</h2>

<form method="POST">
    <label>Subject:</label>
    <select name="subject_code" required>
        <?php while ($subj = $subjects->fetch_assoc()): ?>
            <option value="<?= $subj['subject_code'] ?>" <?= $subj['subject_code'] === $grade['subject_code'] ? 'selected' : '' ?>>
                <?= $subj['subject_name'] ?> (<?= $subj['subject_code'] ?>)
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Grade (Leave blank for NG/INC):</label>
    <input type="number" name="grade" min="1" max="5" step="0.01" value="<?= htmlspecialchars($grade['grade']) ?>"><br><br>

    <label>Status:</label>
    <select name="status">
        <option value="">Auto Compute</option>
        <option value="PASSED" <?= $grade['status'] === 'PASSED' ? 'selected' : '' ?>>PASSED</option>
        <option value="FAILED" <?= $grade['status'] === 'FAILED' ? 'selected' : '' ?>>FAILED</option>
        <option value="INC" <?= $grade['status'] === 'INC' ? 'selected' : '' ?>>INC</option>
        <option value="NG" <?= $grade['status'] === 'NG' ? 'selected' : '' ?>>NG</option>
    </select><br><br>

    <button type="submit">Update Grade</button>
</form>
