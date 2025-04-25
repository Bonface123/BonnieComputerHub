<?php
// Handles CRUD operations for certificates (admin)
session_start();
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

switch ($action) {
    case 'revoke':
        $stmt = $pdo->prepare('UPDATE certificates SET status = "revoked" WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
    case 'delete':
        // Remove PDF file if exists
        $pdf = $pdo->prepare('SELECT pdf_path FROM certificates WHERE id = ?');
        $pdf->execute([$id]);
        $row = $pdf->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['pdf_path'] && file_exists($row['pdf_path'])) {
            unlink($row['pdf_path']);
        }
        $stmt = $pdo->prepare('DELETE FROM certificates WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
    case 'edit':
        $grade = isset($_POST['grade']) ? floatval($_POST['grade']) : null;
        $status = $_POST['status'] ?? 'issued';
        $stmt = $pdo->prepare('UPDATE certificates SET grade = ?, status = ? WHERE id = ?');
        $stmt->execute([$grade, $status, $id]);
        echo json_encode(['success' => true]);
        break;
    case 'reissue':
        // Fetch cert info
        $stmt = $pdo->prepare('SELECT * FROM certificates WHERE id = ?');
        $stmt->execute([$id]);
        $cert = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cert) {
            echo json_encode(['success' => false, 'message' => 'Certificate not found']);
            break;
        }
        if ($cert['status'] === 'revoked') {
            echo json_encode(['success' => false, 'message' => 'Cannot re-issue a revoked certificate']);
            break;
        }
        // Regenerate certificate files
        require_once '../utils/mail_certificate.php';
        // Fetch student and course info
        $user_id = $cert['user_id'];
        $course_id = $cert['course_id'];
        $cert_id = $cert['certificate_code'];
        $user_stmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        $course_stmt = $pdo->prepare('SELECT course_name FROM courses WHERE id = ?');
        $course_stmt->execute([$course_id]);
        $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $course) {
            mail_certificate($user_id, $course_id, $user['email'], $user['name'], $course['course_name'], $cert_id);
            echo json_encode(['success' => true, 'message' => 'Certificate re-issued and emailed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User or course info missing.']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
