<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Get the assignment ID from the request
$assignment_id = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;

// Fetch assignment name and student progress for the selected assignment
$query = $pdo->prepare("
    SELECT a.title AS assignment_title, u.id AS student_id, u.name AS student_name, 
           s.submitted_on, s.submission_status 
    FROM assignments a 
    JOIN submissions s ON a.id = s.assignment_id 
    JOIN users u ON s.student_id = u.id 
    WHERE a.id = ?
");
$query->execute([$assignment_id]);
$students = $query->fetchAll(PDO::FETCH_ASSOC);

// Get assignment title for display
$assignment_title = $students ? $students[0]['assignment_title'] : 'Assignment Not Found';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Student Progress for <?= htmlspecialchars($assignment_title) ?></title>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fc;
    color: #333;
    margin: 0;
    padding: 0;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Breadcrumb Navigation */
.breadcrumb {
    background-color: #f1f1f1;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #007bff;
    font-size: 14px;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

/* Main Content */
main {
    width: 80%;
    margin: 0 auto;
    padding: 20px;
}

h1 {
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #007bff;
    color: white;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Empty Table Message */
td[colspan="4"] {
    text-align: center;
    font-style: italic;
    color: #999;
}

/* Footer Styles */
footer {
    background-color: #333;
    color: white;
    padding: 10px;
    text-align: center;
    position: fixed;
    width: 100%;
    bottom: 0;
}

/* Custom Button Styles (if needed for further functionality) */
button {
    padding: 10px 15px;
    font-size: 14px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="breadcrumb">
            <a href="../index.php">Home</a> &gt; 
            <a href="instructor_dashboard.php">Instructor Dashboard</a> &gt; 
            <span>Student Progress for <?= htmlspecialchars($assignment_title) ?></span>
        </div>

        <h1>Student Progress for Assignment: <?= htmlspecialchars($assignment_title) ?></h1>

        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Submission Date</th>
                    <th>Submission Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                            <td><?= htmlspecialchars($student['submitted_on']) ?></td>
                            <td><?= htmlspecialchars($student['submission_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No submissions found for this assignment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
