<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Get submission ID from the URL
if (!isset($_GET['submission_id']) || empty($_GET['submission_id'])) {
    echo "No submission ID provided.";
    exit;
}

$submission_id = $_GET['submission_id'];

// Fetch submission and student details
try {
    $stmt = $pdo->prepare("SELECT s.id, s.student_id, s.assignment_id, u.email, u.name AS student_name, sa.title, s.grade
                           FROM submissions s
                           JOIN users u ON s.student_id = u.id
                           JOIN course_assignments sa ON s.assignment_id = sa.id
                           WHERE s.id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        echo "Submission not found.";
        exit;
    }

    // Get student's email, assignment title, and grade
    $student_email = $submission['email'];
    $assignment_title = $submission['title'];
    $grade = $submission['grade'];
    $student_name = $submission['student_name'];

} catch (PDOException $e) {
    echo "Error fetching submission details: " . $e->getMessage();
    exit;
}

// If form is submitted to send notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $subject = "Grade Notification for Assignment: " . htmlspecialchars($assignment_title);
    $message = "Dear $student_name,\n\n"
             . "Your submission for the assignment '$assignment_title' has been graded.\n"
             . "Your grade is: $grade\n\n"
             . "Thank you for your hard work!\n\n"
             . "Best regards,\n"
             . "The Instructor";

    $headers = "From: no-reply@bonniecomputerhub.com";

    // Send the email
    if (mail($student_email, $subject, $message, $headers)) {
        echo "Notification sent successfully to " . htmlspecialchars($student_email) . ".";
    } else {
        echo "Error sending notification.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Send Notification</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h1>Send Notification to Student</h1>
        <p><strong>Assignment:</strong> <?= htmlspecialchars($assignment_title) ?></p>
        <p><strong>Grade:</strong> <?= htmlspecialchars($grade) ?></p>
        <p><strong>Student Name:</strong> <?= htmlspecialchars($student_name) ?></p>
        <p><strong>Student Email:</strong> <?= htmlspecialchars($student_email) ?></p>

        <form action="" method="POST">
            <p>Do you want to send a notification to the student regarding their grade?</p>
            <button type="submit" name="send_notification">Send Notification</button>
        </form>

        <a href="instructor_dashboard.php">Back to Instructor Dashboard</a>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
