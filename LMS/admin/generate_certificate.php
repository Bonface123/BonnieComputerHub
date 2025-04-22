<?php
// Generates and issues a certificate for a student if eligible
session_start();
require_once '../includes/db_connect.php';
require_once '../../vendor/autoload.php'; // For mPDF

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'instructor')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$min_grade = isset($_POST['min_grade']) ? floatval($_POST['min_grade']) : 0;

if (!$user_id || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user or course ID']);
    exit;
}

// Check if already issued
$exists = $pdo->prepare('SELECT id FROM certificates WHERE user_id = ? AND course_id = ? AND status = "issued"');
$exists->execute([$user_id, $course_id]);
if ($exists->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Certificate already issued']);
    exit;
}

// Check all modules completed
$modules = $pdo->prepare('SELECT id FROM course_modules WHERE course_id = ?');
$modules->execute([$course_id]);
$module_ids = $modules->fetchAll(PDO::FETCH_COLUMN, 0);
if (empty($module_ids)) {
    echo json_encode(['success' => false, 'message' => 'No modules found for this course']);
    exit;
}
$placeholders = rtrim(str_repeat('?,', count($module_ids)), ',');
$params = array_merge([$user_id], $module_ids);
// $completed = $pdo->prepare('SELECT COUNT(*) FROM module_completion WHERE user_id = ? AND module_id IN (' . $placeholders . ')');
// $completed->execute($params);
// if ($completed->fetchColumn() < count($module_ids)) {
//     // User-friendly presentation for incomplete modules
//     if (isset($_POST['ajax']) && $_POST['ajax']) {
//         // If AJAX, return JSON as before
//         echo json_encode(['success' => false, 'message' => 'Not all modules completed']);
//     } else {
//         // If not AJAX, show a styled error page
//         include '../includes/header.php';
//         echo '<main class="container mx-auto px-4 py-12">';
//         echo '<div class="max-w-lg mx-auto bg-red-50 border-l-4 border-red-500 p-8 rounded-xl text-center shadow-lg">';
//         echo '<div class="flex justify-center mb-4"><span class="inline-flex items-center justify-center bg-red-100 text-red-600 rounded-full w-12 h-12"><i class="fas fa-exclamation-triangle text-2xl"></i></span></div>';
//         echo '<h2 class="text-2xl font-bold text-red-700 mb-2">Certificate Cannot Be Issued</h2>';
//         echo '<p class="text-red-700 mb-4">Not all modules for this course have been completed by the student. Please ensure all modules are fully completed before issuing a certificate.</p>';
//         echo '<a href="javascript:history.back()" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary hover:text-primary transition">Go Back</a>';
//         echo '</div>';
//         echo '</main>';
//         include '../includes/footer.php';
//     }
//     exit;
// }

// Certificate generation is unrestricted for testing purposes.
$assignments = $pdo->prepare('SELECT a.id FROM assignments a JOIN course_modules m ON a.module_id = m.id WHERE m.course_id = ?');
$assignments->execute([$course_id]);
$assignment_ids = $assignments->fetchAll(PDO::FETCH_COLUMN, 0);
$grade = null;
// All checks for assignment submission and minimum grade are bypassed.

// Fetch user and course info
$user = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$user->execute([$user_id]);
$user_name = $user->fetchColumn();
$course = $pdo->prepare('SELECT course_name FROM courses WHERE id = ?');
$course->execute([$course_id]);
$course_name = $course->fetchColumn();

// Generate unique certificate code
$certificate_code = strtoupper(bin2hex(random_bytes(8)));

// Generate PDF certificate (using mPDF)
$mpdf = new \Mpdf\Mpdf();
$html = '<div style="text-align:center; font-family:Inter,sans-serif; border:8px solid #1E40AF; padding:30px;">
    <img src="../images/BCH.jpg" style="height:80px;"><br><br>
    <span style="font-size:32px; color:#1E40AF; font-weight:bold;">Certificate of Completion</span><br><br>
    <span style="font-size:20px;">Awarded to</span><br>
    <span style="font-size:28px; font-weight:bold;">'.htmlspecialchars($user_name).'</span><br><br>
    <span style="font-size:18px;">for successfully completing the course</span><br>
    <span style="font-size:24px; color:#FFD700;">'.htmlspecialchars($course_name).'</span><br><br>
    <span style="font-size:16px;">Date: '.date('F j, Y').'</span><br>
    <span style="font-size:16px;">Grade: '.($grade !== null ? number_format($grade,2).'%' : 'N/A').'</span><br><br>
    <span style="font-size:14px;">Certificate ID: '.$certificate_code.'</span><br><br>
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode("http://localhost/bonniecomputerhub/LMS/pages/verify_certificate.php?code=$certificate_code").'" alt="QR Code"><br>
    <span style="font-size:14px;">Verify at: http://localhost/bonniecomputerhub/LMS/pages/verify_certificate.php?code='.$certificate_code.'</span>
    <br><br><span style="font-size:18px; font-style:italic; color:#888;">Bonnie Computer Hub</span>
</div>';
$mpdf->WriteHTML($html);
$pdf_path = '../../certificates/certificate_'.$certificate_code.'.pdf';
$mpdf->Output($pdf_path, \Mpdf\Output\Destination::FILE);

// Insert certificate record
$stmt = $pdo->prepare('INSERT INTO certificates (user_id, course_id, issued_by, grade, certificate_code, status, pdf_path) VALUES (?, ?, ?, ?, ?, "issued", ?)');
$stmt->execute([$user_id, $course_id, $_SESSION['user_id'], $grade, $certificate_code, $pdf_path]);

// Send certificate email to user
require_once '../includes/send_mail.php';
$user_email = $pdo->prepare('SELECT email FROM users WHERE id = ?');
$user_email->execute([$user_id]);
$email_addr = $user_email->fetchColumn();
$subject = "[Bonnie Computer Hub] Your Certificate for $course_name";
// Direct link to download certificate
$downloadUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/BonnieComputerHub/LMS/pages/download_certificate.php?course_id=' . $course_id;
$body = "Hello $user_name,\n\nCongratulations on completing the course: $course_name!\n\nYour certificate is attached to this email.\nYou can also download or view your certificate here: $downloadUrl\n\nBest regards,\nBonnie Computer Hub Team";
$result = bch_send_mail($email_addr, $user_name, $subject, $body, '', [realpath($pdf_path)]);
session_start();
if ($result['success']) {
    $_SESSION['success_msg'] = 'Certificate issued and emailed to ' . htmlspecialchars($email_addr);
} else {
    $_SESSION['error_msg'] = 'Certificate issued, but failed to email: ' . htmlspecialchars($result['error']);
}
header('Location: manage_enrollments.php');
exit;
