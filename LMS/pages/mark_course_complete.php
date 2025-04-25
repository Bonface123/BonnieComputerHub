<?php
// Endpoint to mark a course as completed by a student
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit;
}

try {
    // Check if all modules in this course are completed by the student
    $modules = $pdo->prepare('SELECT id FROM course_modules WHERE course_id = ?');
    $modules->execute([$course_id]);
    $module_ids = $modules->fetchAll(PDO::FETCH_COLUMN, 0);
    if (empty($module_ids)) {
        echo json_encode(['success' => false, 'message' => 'No modules found for this course']);
        exit;
    }
    $placeholders = rtrim(str_repeat('?,', count($module_ids)), ',');
    $params = array_merge([$user_id], $module_ids);
    $completed = $pdo->prepare('SELECT COUNT(*) FROM module_completion WHERE user_id = ? AND module_id IN (' . $placeholders . ')');
    $completed->execute($params);
    $completed_count = $completed->fetchColumn();
    if ($completed_count < count($module_ids)) {
        echo json_encode(['success' => false, 'message' => 'Not all modules completed']);
        exit;
    }
    // Insert into course_completion if not already
    $stmt = $pdo->prepare('INSERT IGNORE INTO course_completion (user_id, course_id) VALUES (?, ?)');
    $stmt->execute([$user_id, $course_id]);

    // === CERTIFICATE INSERTION LOGIC ===
    // Check if certificate already exists
    $cert_check = $pdo->prepare('SELECT id FROM certificates WHERE user_id = ? AND course_id = ?');
    $cert_check->execute([$user_id, $course_id]);
    if (!$cert_check->fetchColumn()) {
        // Generate a unique certificate code
        $cert_code = strtoupper(substr(md5($user_id . '_' . $course_id . '_' . date('YmdHis')), 0, 12));
        $issued_at = date('Y-m-d H:i:s');
        $insert_cert = $pdo->prepare('INSERT INTO certificates (user_id, course_id, certificate_code, status, issued_at, pdf_path) VALUES (?, ?, ?, "issued", ?, NULL)');
        $insert_cert->execute([$user_id, $course_id, $cert_code, $issued_at]);
    }
    // === END CERTIFICATE INSERTION ===

    echo json_encode(['success' => true, 'message' => 'Course marked as completed & certificate issued']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
