<?php
session_start();
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_blogs.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare('DELETE FROM blogs WHERE id = ?');
if ($stmt->execute([$id])) {
    $_SESSION['success_msg'] = 'Blog post deleted successfully!';
} else {
    $_SESSION['error_msg'] = 'Failed to delete blog post.';
}
header('Location: manage_blogs.php');
exit;
