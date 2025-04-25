<?php
// Endpoint to mark a module as completed by a student
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$module_id = isset($_POST['module_id']) ? intval($_POST['module_id']) : 0;

if (!$module_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid module ID']);
    exit;
}

try {
    // Insert only if not already marked complete
    $stmt = $pdo->prepare('INSERT IGNORE INTO module_completion (user_id, module_id) VALUES (?, ?)');
    $stmt->execute([$user_id, $module_id]);
    echo json_encode(['success' => true, 'message' => 'Module marked as completed']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
