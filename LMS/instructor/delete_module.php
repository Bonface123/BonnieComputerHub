<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $module_id = $_GET['id'];
    // Allow any instructor to delete any module
    $sql = "SELECT id FROM course_modules WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$module_id]);
    if ($stmt->rowCount() > 0) {
        // Delete all module content first
        $pdo->prepare("DELETE FROM module_content WHERE module_id = ?")->execute([$module_id]);
        // Delete the module
        $pdo->prepare("DELETE FROM course_modules WHERE id = ?")->execute([$module_id]);
        $_SESSION['success_msg'] = "Module deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Module not found or access denied.";
    }
} else {
    $_SESSION['error_msg'] = "Invalid module ID.";
}
header('Location: manage_courses.php');
exit;
