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

// Fetch the assignment details
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

// If assignment not found, redirect
if (!$assignment) {
    header('Location: manage_assignments.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>View Assignment</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="breadcrumb">
            <a href="../index.php">Home</a> &gt; 
            <a href="instructor_dashboard.php">Instructor Dashboard</a> &gt; 
            <a href="manage_courses.php">Manage Courses</a> &gt; 
            <a href="manage_assignments.php?course_id=<?= htmlspecialchars($assignment['course_id']) ?>">Manage Assignments</a> &gt; 
            <span>View Assignment</span>
        </div>

        <h1>View Assignment</h1>
        <h2><?= htmlspecialchars($assignment['assignment_name']) ?></h2>
        <p><strong>Due Date:</strong> <?= htmlspecialchars($assignment['due_date']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($assignment['description']) ?></p>
        <p><strong>Max Score:</strong> <?= htmlspecialchars($assignment['max_score']) ?></p>
        <p><strong>Instructions:</strong> <?= nl2br(htmlspecialchars($assignment['instructions'])) ?></p>

        <a href="edit_assignment.php?assignment_id=<?= htmlspecialchars($assignment['id']) ?>">Edit Assignment</a>
        <form action="delete_assignment.php" method="POST" style="display:inline;">
            <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($assignment['id']) ?>">
            <button type="submit" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete Assignment</button>
        </form>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
