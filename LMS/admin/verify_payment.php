<?php
// admin/verify_payment.php: Mark payment as paid (admin action)
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
if (!$payment_id) {
    $_SESSION['error_msg'] = 'Invalid payment ID.';
    header('Location: manage_payments.php');
    exit();
}

// Fetch payment info
$stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ?');
$stmt->execute([$payment_id]);
$payment = $stmt->fetch();
if (!$payment) {
    $_SESSION['error_msg'] = 'Payment not found.';
    header('Location: manage_payments.php');
    exit();
}
if ($payment['status'] === 'success') {
    $_SESSION['success_msg'] = 'Payment is already marked as paid.';
    header('Location: manage_payments.php');
    exit();
}

// Mark as paid
$pdo->prepare('UPDATE payments SET status = "success", updated_at = NOW() WHERE id = ?')->execute([$payment_id]);

// Fetch user and course info
$user_id = $payment['user_id'];
$course_id = $payment['course_id'];

// Check if already enrolled
$enroll_stmt = $pdo->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?');
$enroll_stmt->execute([$user_id, $course_id]);
$alreadyEnrolled = $enroll_stmt->fetch();

if (!$alreadyEnrolled) {
    // Enroll the student
    $pdo->prepare('INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)')->execute([$user_id, $course_id]);
}

// Fetch course info
$course_stmt = $pdo->prepare('SELECT course_name, mode, next_intake_date FROM courses WHERE id = ?');
$course_stmt->execute([$course_id]);
$cinfo = $course_stmt->fetch(PDO::FETCH_ASSOC);
$cname = $cinfo['course_name'] ?? '';
$mode = $cinfo['mode'] ?? '';
$intake = isset($cinfo['next_intake_date']) && $cinfo['next_intake_date'] ? date('M j, Y', strtotime($cinfo['next_intake_date'])) : '';
// Fetch student info
$user_stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
require_once '../includes/send_mail.php';
// Send confirmation to student
$subject = "Enrollment Confirmation: $cname";
$body = "Hello {$user['name']},\n\nYou have been successfully enrolled in the course: $cname.\nMode: $mode\nIntake: $intake\n\nWelcome to Bonnie Computer Hub!";
$studentMail = bch_send_mail($user['email'], $user['name'], $subject, $body);
// Notify admin (first admin found)
$admin_stmt = $pdo->query("SELECT email, name FROM users WHERE role = 'admin' LIMIT 1");
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
$adminMail = ["success"=>true];
if ($admin) {
    $asubject = "[Admin Notice] New Enrollment: $cname";
    $abody = "Student {$user['name']} ({$user['email']}) has enrolled in $cname.";
    $adminMail = bch_send_mail($admin['email'], $admin['name'], $asubject, $abody);
}
if ($studentMail['success'] && $adminMail['success']) {
    $_SESSION['success_msg'] = 'Payment marked as paid. Student enrolled and notified.';
    header('Location: manage_payments.php');
    exit();
} else {
    $_SESSION['error_msg'] = 'Enrollment succeeded, but failed to send notification email(s): ' . htmlspecialchars($studentMail['error'] ?? $adminMail['error']);
    header('Location: manage_payments.php');
    exit();
}
