<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle course deletion
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];

    try {
        // Fetch course info
        $course_stmt = $pdo->prepare('SELECT course_name, instructor_id FROM courses WHERE id = ?');
        $course_stmt->execute([$courseId]);
        $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
        // Fetch all enrolled students
        $students_stmt = $pdo->prepare('SELECT u.name, u.email FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.course_id = ?');
        $students_stmt->execute([$courseId]);
        $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
        // Fetch instructor
        $instructor = null;
        if ($course && $course['instructor_id']) {
            $instructor_stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
            $instructor_stmt->execute([$course['instructor_id']]);
            $instructor = $instructor_stmt->fetch(PDO::FETCH_ASSOC);
        }
        // Delete course
        $delete_sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $pdo->prepare($delete_sql);
        $stmt->execute([$courseId]);
        // Send emails
        require_once '../includes/send_mail.php';
        $catalogUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/BonnieComputerHub/LMS/pages/courses.php';
        $subject = "[Bonnie Computer Hub] Course Deleted";
        $body = "Hello,\n\nWe regret to inform you that the course '{$course['course_name']}' has been deleted from our catalog.\nYou can browse other courses here: $catalogUrl\n\nBest regards,\nBonnie Computer Hub Team";
        $allSuccess = true;
        foreach ($students as $student) {
            $mailResult = bch_send_mail($student['email'], $student['name'], $subject, $body);
            if (!$mailResult['success']) $allSuccess = false;
        }
        if ($instructor) {
            $imailResult = bch_send_mail($instructor['email'], $instructor['name'], $subject, $body);
            if (!$imailResult['success']) $allSuccess = false;
        }
        if ($allSuccess) {
            echo "Course deleted successfully.";
        } else {
            echo "Course deleted, but some notification emails failed to send.";
        }
    } catch (PDOException $e) {
        echo "Error deleting course: " . htmlspecialchars($e->getMessage());
    }
} else {
    $_SESSION['error_msg'] = "Invalid course ID.";
    header('Location: manage_courses.php');
    exit;
}
?>
