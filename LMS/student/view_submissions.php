<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has a student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch all submissions for the student
$query = $pdo->prepare("
    SELECT s.id AS submission_id, a.title AS assignment_title, s.grade, s.feedback, s.status, s.submitted_on
    FROM submissions s
    JOIN course_assignments a ON a.id = s.assignment_id
    WHERE s.student_id = ?
");
$query->execute([$student_id]);
$submissions = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Your Grades and Feedback</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2>Your Grades and Feedback</h2>
        <?php if ($submissions): ?>
            <table>
                <thead>
                    <tr>
                        <th>Assignment Title</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= htmlspecialchars($submission['assignment_title']) ?></td>
                            <td><?= htmlspecialchars($submission['grade']) ?></td>
                            <td><?= htmlspecialchars($submission['feedback']) ?></td>
                            <td class="<?= strtolower($submission['status']) ?>"><?= htmlspecialchars($submission['status']) ?></td>
                            <td><?= htmlspecialchars($submission['submitted_on']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no graded submissions.</p>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
