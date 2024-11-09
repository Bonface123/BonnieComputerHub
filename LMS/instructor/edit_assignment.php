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

// Fetch the existing assignment details
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

// If assignment not found, redirect
if (!$assignment) {
    header('Location: manage_assignments.php');
    exit;
}

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_name = $_POST['assignment_name'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'];
    $max_score = $_POST['max_score'];
    $instructions = $_POST['instructions'];

    // Update assignment
    $stmt = $pdo->prepare("UPDATE assignments SET assignment_name = ?, due_date = ?, description = ?, max_score = ?, instructions = ? WHERE id = ?");
    $stmt->execute([$assignment_name, $due_date, $description, $max_score, $instructions, $assignment_id]);

    // Redirect to avoid form resubmission
    header("Location: manage_assignments.php?course_id={$assignment['course_id']}");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Edit Assignment</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="breadcrumb">
            <a href="../index.php">Home</a> &gt; 
            <a href="instructor_dashboard.php">Instructor Dashboard</a> &gt; 
            <a href="manage_courses.php">Manage Courses</a> &gt; 
            <a href="manage_assignments.php?course_id=<?= htmlspecialchars($assignment['course_id']) ?>">Manage Assignments</a> &gt; 
            <span>Edit Assignment</span>
        </div>

        <h1>Edit Assignment</h1>
        <form action="" method="POST">
            <label for="assignment_name">Assignment Name:</label>
            <input type="text" id="assignment_name" name="assignment_name" value="<?= htmlspecialchars($assignment['assignment_name']) ?>" required>

            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($assignment['due_date']) ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($assignment['description']) ?></textarea>

            <label for="max_score">Max Score:</label>
            <input type="number" id="max_score" name="max_score" value="<?= htmlspecialchars($assignment['max_score']) ?>" required>

            <label for="instructions">Instructions:</label>
            <textarea id="instructions" name="instructions" required><?= htmlspecialchars($assignment['instructions']) ?></textarea>

            <button type="submit">Update Assignment</button>
        </form>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
