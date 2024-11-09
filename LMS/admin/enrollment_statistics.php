<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch course enrollment statistics
$enrollment_sql = "
    SELECT c.course_name, COUNT(e.user_id) AS enrolled_students
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    GROUP BY c.id
";
$enrollment_stats = $pdo->query($enrollment_sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Enrollment Statistics</title>
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

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    border: 1px solid #ddd;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #333;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr td[colspan="2"] {
    text-align: center;
    font-style: italic;
    color: #999;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
         <!-- Breadcrumb Navigation -->
         <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="enrollment_statistics.php">Enrollment Statistics</a>
        </div>

        <h1>Enrollment Statistics</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Enrolled Students</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollment_stats)): ?>
                    <tr>
                        <td colspan="2">No enrollment statistics available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($enrollment_stats as $stat): ?>
                        <tr>
                            <td><?= htmlspecialchars($stat['course_name']) ?></td>
                            <td><?= htmlspecialchars($stat['enrolled_students']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
