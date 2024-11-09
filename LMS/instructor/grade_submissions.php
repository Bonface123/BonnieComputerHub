<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has a lecturer role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['submission_id'])) {
    $submission_id = $_GET['submission_id'];

    // Fetch submission details
    $query = $pdo->prepare("
        SELECT s.id AS submission_id, a.title AS assignment_title, s.grade, s.feedback, s.status, s.submitted_on
        FROM submissions s
        JOIN course_assignments a ON a.id = s.assignment_id
        WHERE s.id = ?
    ");
    $query->execute([$submission_id]);
    $submission = $query->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update grade and feedback
        $grade = $_POST['grade'];
        $feedback = $_POST['feedback'];
        $status = $_POST['status'];

        $update_query = $pdo->prepare("
            UPDATE submissions 
            SET grade = ?, feedback = ?, status = ? 
            WHERE id = ?
        ");
        $update_query->execute([$grade, $feedback, $status, $submission_id]);

        header("Location: view_submissions.php");
        exit;
    }
} else {
    // Redirect if no submission ID is provided
    header('Location: view_submissions.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Grade Submission</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2>Grade Submission for Assignment: <?= htmlspecialchars($submission['assignment_title']) ?></h2>

        <form action="grade_submission.php?submission_id=<?= $submission_id ?>" method="POST">
            <label for="grade">Grade:</label>
            <input type="text" name="grade" value="<?= htmlspecialchars($submission['grade']) ?>" required>
            
            <label for="feedback">Feedback:</label>
            <textarea name="feedback" required><?= htmlspecialchars($submission['feedback']) ?></textarea>
            
            <label for="status">Grade Status:</label>
            <select name="status" id="status" required>
                <option value="Pending" <?= $submission['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Under Review" <?= $submission['status'] == 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                <option value="Graded" <?= $submission['status'] == 'Graded' ? 'selected' : '' ?>>Graded</option>
            </select>
            
            <button type="submit">Submit Grade</button>
        </form>

    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
