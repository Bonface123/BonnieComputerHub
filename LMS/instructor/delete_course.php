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
    
    // Allow any instructor to delete any course
    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        // Delete related enrollments
        $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?")->execute([$course_id]);
        // Delete related assignments (via modules)
        $module_ids_stmt = $pdo->prepare("SELECT id FROM course_modules WHERE course_id = ?");
        $module_ids_stmt->execute([$course_id]);
        $module_ids = $module_ids_stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($module_ids) {
            // Delete assignments for each module
            $in_clause = implode(',', array_fill(0, count($module_ids), '?'));
            $pdo->prepare("DELETE FROM assignments WHERE module_id IN ($in_clause)")->execute($module_ids);
            // Delete module_content for each module
            $pdo->prepare("DELETE FROM module_content WHERE module_id IN ($in_clause)")->execute($module_ids);
        }
        // Delete modules
        $pdo->prepare("DELETE FROM course_modules WHERE course_id = ?")->execute([$course_id]);
        // Now delete the course
        $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$course_id]);

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
