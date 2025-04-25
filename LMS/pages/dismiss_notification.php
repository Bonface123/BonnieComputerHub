<?php
session_start();
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
$user_id = $_SESSION['user_id'];
$notification_id = intval($_POST['notification_id']);
$stmt = $pdo->prepare('UPDATE notifications SET is_dismissed = 1 WHERE id = ? AND user_id = ?');
$stmt->execute([$notification_id, $user_id]);
echo json_encode(['success' => true]);
