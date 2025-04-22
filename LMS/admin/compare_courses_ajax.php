<?php
require_once '../includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_GET['ids'])) {
    echo json_encode([]);
    exit;
}
$ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));
if (count($ids) < 2) {
    echo json_encode([]);
    exit;
}
$in  = str_repeat('?,', count($ids) - 1) . '?';
$sql = "SELECT c.*, u.name as instructor_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.id WHERE c.id IN ($in)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($courses);
