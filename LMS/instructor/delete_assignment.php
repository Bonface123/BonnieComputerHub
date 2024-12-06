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
        $delete = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $delete->execute([$assignment_id]);
        $_SESSION['success_msg'] = "Assignment deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error deleting assignment: " . $e->getMessage();
    }
} else {
    $_SESSION['error_msg'] = "Assignment not found or you don't have permission to delete it";
}

header('Location: manage_assignments.php');
exit;

?>
