<!-- sidebar.php -->
<style>
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

.sidebar h2 {
    margin-top: 0;
    font-size: 1.5rem;
    text-align: center;
    margin-bottom: 1.5rem;
    color: #ecf0f1;
}
</style>

<div class="sidebar">
    <h2>SchoolSync</h2>
    <nav class="nav-links">
        <a href="../dashboard/index.php">🏠 Dashboard</a>
        <a href="../students/student_list.php">👨‍🎓 Students</a>
        <a href="../schedule/class_schedule_list.php">📅 Class Schedules</a>
        <a href="../grades/grades_summary.php">📝 Grades</a>
        <a href="../auth/change_password.php">🔑 Change Password</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</div>
