<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch total students, courses, and enrollments
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

// Fetch enrollment statistics per course
$enrollment_stats = $pdo->query("
    SELECT c.course_name, COUNT(e.user_id) AS enrollment_count 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    GROUP BY c.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Analytics Dashboard</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="analytics_dashboard.php">Analytics</a>
        </div>


        <h1>Analytics Dashboard</h1>

        <!-- Summary Statistics -->
        <div class="stats-summary">
            <div class="stat-card">
                <h2>Total Students</h2>
                <p><?= htmlspecialchars($total_students) ?></p>
            </div>
            <div class="stat-card">
                <h2>Total Courses</h2>
                <p><?= htmlspecialchars($total_courses) ?></p>
            </div>
            <div class="stat-card">
                <h2>Total Enrollments</h2>
                <p><?= htmlspecialchars($total_enrollments) ?></p>
            </div>
        </div>

        <!-- Enrollment per Course -->
        <h2>Enrollments per Course</h2>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Enrollments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollment_stats as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['course_name']) ?></td>
                        <td><?= htmlspecialchars($stat['enrollment_count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
