<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include('../config/db.php');

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Before deleting student, check for foreign key dependencies and delete related data if needed
    // or handle this properly with ON DELETE CASCADE in DB
    // For now, assume cascade or no related data

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
}

// Fetch students
$result = $conn->query("SELECT * FROM students ORDER BY id ASC");
?>

<!-- Keep your existing PHP logic above this -->
<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
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

        .main {
            margin-left: 220px;
            padding: 40px;
            background-color: #f4f4f4;
            flex-grow: 1;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .button-row {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            background-color: #2f855a;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #276749;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #e2e8f0;
        }

        .conflict {
            background-color: #ffe6e6;
        }

        
        tr:nth-child(even) {
            background-color: #fafafa;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .action-buttons {
  display: flex;
  gap: 8px;
  align-items: center;
}

.action-buttons a,
.action-buttons button {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 4px;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s ease;
}

.action-buttons a {
  background-color: #3182ce; /* blue */
}

.action-buttons a:hover {
  background-color: #2c5282;
}

.action-buttons button {
  background-color: #e53e3e; /* red */
  color: white;
}

.action-buttons button:hover {
  background-color: #9b2c2c;
}

    </style>
    <script>
        function confirmDelete(form) {
            if (confirm("Are you sure you want to delete this student?")) {
                form.submit();
            }
            return false;
        }
    </script>
</head>
<body>
<div class="dashboard">
    <?php include('../sidebar.php'); ?>

    <div class="main">
        <h2>Student List</h2>

        <div class="button-row">
            <a class="btn" href="student_add.php">+ Add New Student</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Year Level</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['year_level']) ?></td>
                    <td><?= htmlspecialchars($row['section']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td class="action-buttons">
                        <a href="student_edit.php?id=<?= $row['id'] ?>">✏️</a>
                        <form method="POST" onsubmit="return confirmDelete(this);">
                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                            <button type="submit">🗑</button>
                        </form>
                    </td>

                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
