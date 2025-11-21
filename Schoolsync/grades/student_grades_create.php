<?php
include('../config/db.php');

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    echo "<p style='color:red;'>No student selected.</p>";
    exit;
}

// Fetch student name
$student_stmt = $conn->prepare("SELECT full_name FROM students WHERE id = ?");
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();

// Fetch all subjects
$subjects = $conn->query("SELECT subject_code, subject_name FROM subjects ORDER BY subject_name")->fetch_all(MYSQLI_ASSOC);

// Fetch existing grades to filter out duplicates
$existing_stmt = $conn->prepare("SELECT subject_code FROM student_grades WHERE student_id = ?");
$existing_stmt->bind_param("i", $student_id);
$existing_stmt->execute();
$existing_result = $existing_stmt->get_result();

$existing_subjects = [];
while ($row = $existing_result->fetch_assoc()) {
    $existing_subjects[$row['subject_code']] = true;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = $_POST['subject_code'] ?? '';
    $grade = trim($_POST['grade']) ?: null;
    $status = strtoupper(trim($_POST['status'] ?? ''));

    if (isset($existing_subjects[$subject_code])) {
        $errors[] = "Grade for this subject already exists.";
    } else {
        // Get semester from subject
        $sem_stmt = $conn->prepare("SELECT semester FROM subjects WHERE subject_code = ?");
        $sem_stmt->bind_param("s", $subject_code);
        $sem_stmt->execute();
        $sem_result = $sem_stmt->get_result();
        $subject_data = $sem_result->fetch_assoc();
        $semester = $subject_data['semester'];

        // Determine status
        if ($grade !== null && $grade !== '') {
            $grade_value = floatval($grade);
            $status = ($grade_value <= 3.00) ? 'PASSED' : 'FAILED';
        } elseif (empty($status)) {
            $status = 'NG';
        }

        $insert = $conn->prepare("INSERT INTO student_grades (student_id, subject_code, semester, grade, status) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("issss", $student_id, $subject_code, $semester, $grade, $status);
        $insert->execute();

        header("Location: student_grades_view.php?student_id=$student_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Grade</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        form { max-width: 500px; }
        input, select { padding: 8px; width: 100%; margin: 10px 0; }
        button { padding: 10px 20px; }
        .error { color: red; }
        .success { color: green; }
        a { text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

<h2>Add Grade for <?= htmlspecialchars($student['full_name']) ?></h2>
<a href="student_grades_view.php?student_id=<?= $student_id ?>">⬅ Back to Grades</a>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <label for="subject_code">Subject:</label>
    <select name="subject_code" required>
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $sub): ?>
            <?php if (!isset($existing_subjects[$sub['subject_code']])): ?>
                <option value="<?= $sub['subject_code'] ?>"><?= $sub['subject_code'] ?> - <?= $sub['subject_name'] ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>

    <label for="grade">Grade (Leave blank if INC or NG):</label>
    <input type="text" name="grade" placeholder="e.g. 1.00, 2.25, 3.00">

    <label for="status">Status (INC or NG if no grade):</label>
    <input type="text" name="status" placeholder="INC / NG">

    <button type="submit">Save Grade</button>
</form>

</body>
</html>
