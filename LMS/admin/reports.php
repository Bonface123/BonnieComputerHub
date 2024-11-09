<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch statistics
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Reports</title>
    <style>
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

/* Breadcrumb Navigation */
.breadcrumb {
    margin-bottom: 20px;
    font-size: 0.9em;
}

.breadcrumb a {
    color: #333;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

/* Summary Statistics */
ul {
    list-style-type: none;
    padding: 0;
    font-size: 1.1em;
}

ul li {
    margin: 10px 0;
}

ul li::before {
    content: "• ";
    color: #333;
    font-weight: bold;
}

ul li {
    color: #555;
}

/* Styling for total numbers */
ul li {
    font-size: 1.2em;
    font-weight: bold;
    color: #4CAF50;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="manage_users.php">Manage Users</a>
        </div>

        <h1>Reports</h1>
        <h2>Summary Statistics</h2>
        <ul>
            <li>Total Students: <?= htmlspecialchars($totalStudents) ?></li>
            <li>Total Instructors: <?= htmlspecialchars($totalInstructors) ?></li>
            <li>Total Courses: <?= htmlspecialchars($totalCourses) ?></li>
            <li>Total Enrollments: <?= htmlspecialchars($totalEnrollments) ?></li>
        </ul>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
