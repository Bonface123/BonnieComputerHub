<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has a lecturer role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Get the lecturer ID
$lecturer_id = $_SESSION['user_id'];

// Check if the assignment_id is provided in the URL
if (!isset($_GET['assignment_id'])) {
    die('Assignment ID not provided.');
}

$assignment_id = $_GET['assignment_id'];

// Fetch the assignment details
$assignment_query = $pdo->prepare("SELECT id, title, course_id FROM course_assignments WHERE id = ?");
$assignment_query->execute([$assignment_id]);
$assignment = $assignment_query->fetch(PDO::FETCH_ASSOC);

// If no assignment is found, display an error
if (!$assignment) {
    die('Assignment not found.');
}

// Fetch all submissions for this assignment
$submissions_query = $pdo->prepare("
    SELECT s.id AS submission_id, s.student_id, s.file_path, s.submitted_on, s.grade, u.name AS student_name
    FROM submissions s
    JOIN users u ON u.id = s.student_id
    WHERE s.assignment_id = ?
");
$submissions_query->execute([$assignment_id]);
$submissions = $submissions_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>View Submissions</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2>Submissions for Assignment: <?= htmlspecialchars($assignment['title']) ?></h2>
        
        <?php if (count($submissions) > 0): ?>
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Submitted On</th>
                        <th>Grade</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= htmlspecialchars($submission['student_name']) ?></td>
                            <td><?= htmlspecialchars($submission['submitted_on']) ?></td>
                            <td>
                                <?php if ($submission['grade'] === null): ?>
                                    Not Graded
                                <?php else: ?>
                                    <?= htmlspecialchars($submission['grade']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($submission['file_path']) ?>" download>Download File</a>
                            </td>
                            <td>
                                <a href="grade_submission.php?submission_id=<?= $submission['submission_id'] ?>">Grade</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No submissions found for this assignment.</p>
        <?php endif; ?>

    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
