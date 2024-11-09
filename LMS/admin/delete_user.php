<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Delete the user
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $stmt->execute([$user_id]);

    header('Location: manage_users.php');
    exit;
} else {
    header('Location: manage_users.php');
    exit;
}
