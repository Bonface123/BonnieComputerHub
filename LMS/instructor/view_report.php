<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Fetch all submissions with user (student) and assignment details
try {
    $stmt = $pdo->prepare("SELECT s.id AS submission_id, s.grade, u.email AS student_email, u.name AS student_name, sa.title AS assignment_title
                           FROM submissions s
                           JOIN users u ON s.student_id = u.id
                           JOIN course_assignments sa ON s.assignment_id = sa.id
                           ORDER BY sa.title, u.name");
    $stmt->execute();
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$submissions) {
        echo "No submissions found.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error fetching submission details: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>View Report</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h1>Submissions Report</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student Email</th>
                    <th>Assignment Title</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?= htmlspecialchars($submission['student_name']) ?></td>
                        <td><?= htmlspecialchars($submission['student_email']) ?></td>
                        <td><?= htmlspecialchars($submission['assignment_title']) ?></td>
                        <td><?= htmlspecialchars($submission['grade']) ?></td>
                        <td>
                            <a href="send_notification.php?submission_id=<?= $submission['submission_id'] ?>">Send Notification</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="instructor_dashboard.php">Back to Dashboard</a>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
