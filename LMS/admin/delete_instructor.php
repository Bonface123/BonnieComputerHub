<!-- admin/delete_instructor.php -->
<?php 
session_start();
include '../includes/db_connect.php'; 

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../pages/login.php");
    exit();
}

// Handle deletion of an instructor
if (isset($_GET['id'])) {
    $instructor_id = intval($_GET['id']);
    $sql = "DELETE FROM users WHERE id = ? AND role_id = 2"; // Ensure it's an instructor
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$instructor_id])) {
        header("Location: manage_users.php"); // Redirect back to manage instructors
        exit();
    } else {
        echo "<p>Error deleting instructor.</p>";
    }
} else {
    echo "<p>No instructor ID provided.</p>";
}
?>