<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $content_id = $_GET['id'];
    // Allow any instructor to delete any module content
    $sql = "SELECT id FROM module_content WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$content_id]);
    if ($stmt->rowCount() > 0) {
        // Delete the content
        $pdo->prepare("DELETE FROM module_content WHERE id = ?")->execute([$content_id]);
        $_SESSION['success_msg'] = "Content deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Content not found or access denied.";
    }
} else {
    $_SESSION['error_msg'] = "Invalid content ID.";
}
header('Location: manage_courses.php');
exit;
