<?php
include('../config/db.php');

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    echo "<p style='color:red;'>No student selected.</p>";
    exit;
}

// Get student info
$student_stmt = $conn->prepare("SELECT full_name, year_level, section FROM students WHERE id = ?");
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = $student_result->fetch_assoc();

if (!$student) {
    echo "<p style='color:red;'>Student not found.</p>";
    exit;
}

$year_level = $student['year_level'];
$section = $student['section'];

$query = "
    SELECT 
        cs.subject_code,
        cs.subject_name,
        cs.semester,
        sg.grade,
        sg.status
    FROM class_schedules cs
    LEFT JOIN student_grades sg 
        ON sg.subject_code = cs.subject_code AND sg.student_id = ?
    WHERE cs.year_level = ? AND cs.section = ?
    ORDER BY cs.semester, cs.subject_name
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $student_id, $year_level, $section);
$stmt->execute();
$result = $stmt->get_result();

$grades_by_semester = [
    '1st Semester' => [],
    '2nd Semester' => [],
    'Unknown Semester' => []
];

function normalizeSemester($sem) {
    $sem = strtolower(trim($sem));
    if (in_array($sem, ['1st', '1st semester', 'first', '1', '1st sem'])) return '1st Semester';
    if (in_array($sem, ['2nd', '2nd semester', 'second', '2', '2nd sem'])) return '2nd Semester';
    return 'Unknown Semester';
}

while ($row = $result->fetch_assoc()) {
    $normalized_sem = normalizeSemester($row['semester']);
    $grades_by_semester[$normalized_sem][] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($student['full_name']) ?> - Grades</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .main {
            margin-left: 40px;
            padding: 40px;
        }

        h2 {
            font-size: 28px;
            color: #2f855a;
            border-bottom: 3px solid #2f855a;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        h3 {
            font-size: 20px;
            margin-top: 40px;
            color: #2f855a;
            border-left: 4px solid #2f855a;
            padding-left: 10px;
            margin-bottom: 15px;
        }

        /* Center and constrain table width */
        table {
            width: 100%;         /* full width of container */
            max-width: 900px;    /* max width so it doesn't stretch too far */
            margin: 0 auto 30px; /* center horizontally */
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.05);
            border-radius: 6px;
            overflow: hidden;
            table-layout: auto;  /* let columns size dynamically */
        }
        th, td {
            padding: 16px 20px;  /* more padding for better spacing */
            text-align: left;    /* align text left for readability */
            border-bottom: 1px solid #e2e8f0;
            min-width: 120px;    /* minimum width for columns */
            word-break: break-word; /* wrap long words */
        }

        input[type="number"] {
            width: 150px;      /* wider input */
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        th {
            background-color: #f0f4f8;
            font-weight: 600;
        }

        td {
            background-color: #fff;
        }


        select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            background-color: #fff;
            min-width: 100px;
        }

        button[type="submit"] {
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #2f855a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin-left: auto;
            margin-right: auto;
            max-width: 150px;
        }

        button[type="submit"]:hover {
            background-color: #276749;
        }

        a.back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2f855a;
            text-decoration: none;
            font-weight: 500;
        }

        a.back-link:hover {
            text-decoration: underline;
        }

        p {
            font-size: 16px;
            margin-top: 20px;
            color: #666;
            text-align: center;
        }

        .btn {
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #2f855a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            display: inline-block;
            margin-bottom: 30px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #276749;
            text-decoration: none;
        }

        /* Container for form + download button */
        .form-container {
            max-width: 750px;
            margin: 0 auto;
            text-align: center;
        }

        /* Download button spacing */
        #downloadCsvBtn {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Grades for <?= htmlspecialchars($student['full_name']) ?></h2>

<div class="form-container">
    <!-- Download CSV button -->
    <button id="downloadCsvBtn" class="btn" type="button">Download Grades CSV</button>

    <form method="POST" action="save_student_grades.php" id="gradesForm">
        <input type="hidden" name="student_id" value="<?= $student_id ?>">
        <?php
        $hasSubjects = false;
        foreach (['1st Semester', '2nd Semester'] as $semester) {
            $subjects = $grades_by_semester[$semester];
            if (!empty($subjects)) {
                $hasSubjects = true;
                echo "<h3>" . $semester . "</h3>";
                echo "<table>";
                echo "<tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Grade</th>
                        <th>Status</th>
                      </tr>";
                foreach ($subjects as $subject) {
                    $subjectCode = htmlspecialchars($subject['subject_code']);
                    $subjectName = htmlspecialchars($subject['subject_name']);
                    $grade = htmlspecialchars($subject['grade'] ?? '');
                    $status = htmlspecialchars($subject['status'] ?? '');

                    echo "<tr>";
                    echo "<td>$subjectCode</td>";
                    echo "<td>$subjectName</td>";
                    echo "<td>
                            <input type='number' step='0.01' name='grades[$subjectCode][grade]' value='$grade' required>
                            <input type='hidden' name='grades[$subjectCode][subject_name]' value='$subjectName'>
                          </td>";
                    echo "<td>
                            <select name='grades[$subjectCode][status]' required>
                                <option value='Passed' " . ($status === 'Passed' ? 'selected' : '') . ">Passed</option>
                                <option value='Failed' " . ($status === 'Failed' ? 'selected' : '') . ">Failed</option>
                                <option value='INC' " . ($status === 'INC' ? 'selected' : '') . ">INC</option>
                                <option value='NG' " . ($status === 'NG' ? 'selected' : '') . ">NG</option>
                            </select>
                          </td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        if (!$hasSubjects) {
            echo "<p>No subjects found for this student.</p>";
        }
        ?>
        <?php if ($hasSubjects): ?>
            <button type="submit">Save Grades</button>
        <?php endif; ?>
    </form>
    

    <a class="btn" href="../dashboard/index.php">← Back to Dashboard</a><br><br>
    <?php
        // You need to get the student's year_level and section from $student (you already have these in your code)

        $year = urlencode($student['year_level']);
        $section = urlencode($student['section']);
        ?>

        

</div>

<script>
    // CSV Download logic
    document.getElementById('downloadCsvBtn').addEventListener('click', function () {
        const form = document.getElementById('gradesForm');
        const rows = [];
        const headers = ['Subject Code', 'Subject Name', 'Grade', 'Status'];
        rows.push(headers.join(','));

        // Loop through each semester's table
        document.querySelectorAll('table').forEach(table => {
            // Skip header row (th)
            const trs = table.querySelectorAll('tbody tr, tr:not(:first-child)');
            trs.forEach(tr => {
                const cols = tr.querySelectorAll('td');
                if (cols.length === 4) {
                    const subjectCode = cols[0].innerText.trim();
                    const subjectName = cols[1].innerText.trim();
                    const gradeInput = cols[2].querySelector('input[type="number"]');
                    const grade = gradeInput ? gradeInput.value.trim() : '';
                    const statusSelect = cols[3].querySelector('select');
                    const status = statusSelect ? statusSelect.value.trim() : '';

                    // Escape commas for CSV
                    const escapeCsv = str => `"${str.replace(/"/g, '""')}"`;

                    rows.push([
                        escapeCsv(subjectCode),
                        escapeCsv(subjectName),
                        escapeCsv(grade),
                        escapeCsv(status)
                    ].join(','));
                }
            });
        });

        // Create CSV file
        const csvContent = rows.join('\n');
        const blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8;'});
        const url = URL.createObjectURL(blob);

        // Create temporary download link
        const a = document.createElement('a');
        a.href = url;
        a.download = 'grades_<?= htmlspecialchars($student['full_name'], ENT_QUOTES) ?>.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
</script>

</body>
</html>
