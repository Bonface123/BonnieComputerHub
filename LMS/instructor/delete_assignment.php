<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Handle assignment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];

    // Delete the assignment
    $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);

    // Redirect to manage assignments page
    header('Location: manage_assignments.php');
    exit;
}

?>
