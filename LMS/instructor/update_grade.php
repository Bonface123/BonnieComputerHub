<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $assignment_id = $_POST['assignment_id'];
    $grade = $_POST['grade'];

    // Check if the grade already exists for this student and assignment
    $check_stmt = $pdo->prepare("SELECT grade FROM grades WHERE user_id = ? AND assignment_id = ?");
    $check_stmt->execute([$student_id, $assignment_id]);

    if ($check_stmt->fetch()) {
        // Update the grade if it exists
        $update_stmt = $pdo->prepare("UPDATE grades SET grade = ? WHERE user_id = ? AND assignment_id = ?");
        $update_stmt->execute([$grade, $student_id, $assignment_id]);
    } else {
        // Insert a new grade if it doesn't exist
        $insert_stmt = $pdo->prepare("INSERT INTO grades (user_id, assignment_id, grade) VALUES (?, ?, ?)");
        $insert_stmt->execute([$student_id, $assignment_id, $grade]);
    }

    // Fetch student and assignment info
    $student_stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $student_stmt->execute([$student_id]);
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
    $assignment_stmt = $pdo->prepare("SELECT title FROM assignments WHERE id = ?");
    $assignment_stmt->execute([$assignment_id]);
    $assignment = $assignment_stmt->fetch(PDO::FETCH_ASSOC);
    require_once '../includes/send_mail.php';
    $subject = "Assignment Graded: {$assignment['title']}";
    $body = "Hello {$student['name']},\n\nYour assignment '{$assignment['title']}' has been graded.\nGrade: $grade\n\nCheck your dashboard for details.";
    $mailResult = bch_send_mail($student['email'], $student['name'], $subject, $body);
    if ($mailResult['success']) {
        $_SESSION['flash_message'] = "Grade updated successfully.";
    } else {
        $_SESSION['flash_message'] = "Grade updated, but failed to send email notification: " . htmlspecialchars($mailResult['error']);
    }
    header("Location: view_progress.php?course_id=" . $_POST['course_id']);
    exit;
}
?>
