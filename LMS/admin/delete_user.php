<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    // Get user avatar filename
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && !empty($user['avatar']) && $user['avatar'] !== 'default-avatar.png') {
        $avatarPath = __DIR__ . '/../assets/images/avatars/' . $user['avatar'];
        if (file_exists($avatarPath)) {
            @unlink($avatarPath);
        }
    }
    // Delete the user
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    if ($stmt->execute([$user_id])) {
        $_SESSION['success_msg'] = "User deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Failed to delete user.";
    }
    header('Location: manage_users.php');
    exit;
} else {
    header('Location: manage_users.php');
    exit;
}
