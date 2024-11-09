<!-- student/enroll_course.php -->
<?php 
session_start();
include '../includes/db_connect.php'; 

// Check if user is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    // Enroll the student in the course
    $sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$_SESSION['user_id'], $course_id])) {
        header("Location: view_courses.php?message=Successfully enrolled in the course.");
        exit();
    } else {
        header("Location: view_courses.php?error=Failed to enroll in the course.");
        exit();
    }
} else {
    header("Location: view_courses.php");
    exit();
}
?>
