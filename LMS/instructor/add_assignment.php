<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $due_date = $_POST['due_date'];

    // Insert the assignment into the database
    $insert_sql = "INSERT INTO assignments (course_id, title, due_date) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    $stmt->execute([$course_id, $title, $due_date]);

    // Set a flash message for success and redirect
    $_SESSION['flash_message'] = "Assignment added successfully.";
    header("Location: manage_assignments.php");
    exit;
}
?>
