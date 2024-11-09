<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Check if course ID is provided
if (isset($_GET['id'])) {
    $course_id = $_GET['id'];
    
    // Verify that the course belongs to the instructor
    $sql = "SELECT * FROM courses WHERE id = ? AND created_by = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id, $instructor_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        // Delete the course and its related data
        $delete_course_sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $pdo->prepare($delete_course_sql);
        $stmt->execute([$course_id]);

        // Set a flash message for successful deletion
        $_SESSION['flash_message'] = "Course deleted successfully.";
    } else {
        // Set a flash message for access denied
        $_SESSION['flash_message'] = "Course not found or access denied.";
    }
} else {
    $_SESSION['flash_message'] = "Invalid course ID.";
}

// Redirect back to manage courses page
header("Location: manage_courses.php");
exit;
?>
