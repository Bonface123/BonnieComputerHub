<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch total users and courses
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Admin Dashboard</title>
    <style>
        /* Save this as styles.css and link it to your HTML */

/* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
}

h1, h2 {
    color: #333;
    text-align: center;
}

main {
    width: 90%;
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

/* Stats container styles */
.stats-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    flex: 1;
    min-width: 180px;
    background-color: #fff;
    border: 2px solid #ffa500; /* Golden border */
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.3s;
}

.stat-card h3 {
    color: #ffa500;
    margin: 0 0 10px;
}

.stat-card p {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.stat-card:hover {
    transform: translateY(-5px);
}

/* Management links styles */
ul {
    list-style-type: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
}

ul li {
    margin: 0;
}

ul li a {
    display: block;
    padding: 12px 20px;
    background-color: #333;
    color: #ffa500;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s, color 0.3s;
}

ul li a:hover {
    background-color: #ffa500;
    color: #fff;
}

/* Header and footer styles 
*/footer {
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 10px 0;
    margin-bottom: 20px;
}

 footer a {
    color: #ffa500;
    text-decoration: none;
    font-weight: bold;
}

footer a:hover {
    color: #fff;
}


    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <h1>Admin Dashboard</h1>
        
        <h2>System Overview</h2>
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?= htmlspecialchars($totalUsers) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Students</h3>
                <p><?= htmlspecialchars($totalStudents) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Instructors</h3>
                <p><?= htmlspecialchars($totalInstructors) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Courses</h3>
                <p><?= htmlspecialchars($totalCourses) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Enrollments</h3>
                <p><?= htmlspecialchars($totalEnrollments) ?></p>
            </div>
        </div>

        <h2>Management Links</h2>
        <ul>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="manage_courses.php">Manage Courses</a></li>
            <li><a href="manage_enrollments.php">Manage Enrollments</a></li>
            <li><a href="reports.php">View Reports</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="enrollment_statistics.php">View Enrollment Statistics</a></li>
            <li><a href="analytics_dashboard.php">Analytics Dashboard</a></li>
        </ul>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
