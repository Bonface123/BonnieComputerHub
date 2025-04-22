<?php
// Student endpoint to download certificate if eligible
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if (!$course_id) {
    echo 'Invalid course ID';
    exit;
}

// Check if a certificate exists and is issued
$stmt = $pdo->prepare('SELECT pdf_path FROM certificates WHERE user_id = ? AND course_id = ? AND status = "issued"');
$stmt->execute([$user_id, $course_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || empty($row['pdf_path']) || !file_exists($row['pdf_path'])) {
    echo 'Certificate not available yet.';
    exit;
}

// Serve the PDF for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="BCH_Certificate.pdf"');
readfile($row['pdf_path']);
exit;
