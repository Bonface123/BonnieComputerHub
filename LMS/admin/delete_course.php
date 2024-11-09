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
        $delete_sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $pdo->prepare($delete_sql);
        $stmt->execute([$courseId]);
        echo "Course deleted successfully.";
    } catch (PDOException $e) {
        echo "Error deleting course: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Invalid course ID.";
    exit;
}

// Redirect back to the manage courses page
header("Location: manage_courses.php");
exit;
?>
