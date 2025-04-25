<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if assignment belongs to instructor
$stmt = $pdo->prepare("
    SELECT id 
    FROM assignments 
    WHERE id = ? AND instructor_id = ?
");
$stmt->execute([$assignment_id, $instructor_id]);

if ($stmt->rowCount() > 0) {
    try {
        // Fetch assignment and course info
        $assignment_stmt = $pdo->prepare('SELECT title, module_id FROM assignments WHERE id = ?');
        $assignment_stmt->execute([$assignment_id]);
        $assignment = $assignment_stmt->fetch(PDO::FETCH_ASSOC);
        $module_stmt = $pdo->prepare('SELECT course_id FROM course_modules WHERE id = ?');
        $module_stmt->execute([$assignment['module_id']]);
        $module = $module_stmt->fetch(PDO::FETCH_ASSOC);
        $course_id = $module['course_id'];
        $course_stmt = $pdo->prepare('SELECT course_name, instructor_id FROM courses WHERE id = ?');
        $course_stmt->execute([$course_id]);
        $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
        // Delete assignment
        $delete = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $delete->execute([$assignment_id]);
        // Send emails
        require_once '../includes/send_mail.php';
        $dashboardUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/BonnieComputerHub/LMS/pages/course_details.php?id=' . $course_id;
        $subject = "[Bonnie Computer Hub] Assignment Deleted";
        $body = "Hello,\n\nThe assignment '{$assignment['title']}' in course '{$course['course_name']}' has been deleted.\nYou can view the course here: $dashboardUrl\n\nBest regards,\nBonnie Computer Hub Team";
        // Notify students
        $students_stmt = $pdo->prepare('SELECT u.name, u.email FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.course_id = ?');
        $students_stmt->execute([$course_id]);
        $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
        $allSuccess = true;
        foreach ($students as $student) {
            $mailResult = bch_send_mail($student['email'], $student['name'], $subject, $body);
            if (!$mailResult['success']) $allSuccess = false;
        }
        // Notify instructor if not the one deleting
        if ($course['instructor_id'] && $course['instructor_id'] != $instructor_id) {
            $instructor_stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
            $instructor_stmt->execute([$course['instructor_id']]);
            $instructor = $instructor_stmt->fetch(PDO::FETCH_ASSOC);
            $imailResult = bch_send_mail($instructor['email'], $instructor['name'], $subject, $body);
            if (!$imailResult['success']) $allSuccess = false;
        }
        if ($allSuccess) {
            $_SESSION['success_msg'] = "Assignment deleted successfully";
        } else {
            $_SESSION['error_msg'] = "Assignment deleted, but some notification emails failed to send.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error deleting assignment: " . $e->getMessage();
    }
} else {
    $_SESSION['error_msg'] = "Assignment not found or you don't have permission to delete it";
}

header('Location: manage_assignments.php');
exit;

?>
