<?php
// admin/refund_payment.php: Admin action to process a refund for a payment
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
if ($payment['status'] === 'refunded') {
    $_SESSION['success_msg'] = 'Payment is already refunded.';
    header('Location: manage_payments.php');
    exit();
}
if ($payment['status'] !== 'success') {
    $_SESSION['error_msg'] = 'Only successful payments can be refunded.';
    header('Location: manage_payments.php');
    exit();
}

// Mark as refunded
$pdo->prepare('UPDATE payments SET status = "refunded", updated_at = NOW() WHERE id = ?')->execute([$payment_id]);
$_SESSION['success_msg'] = 'Payment marked as refunded.';
header('Location: manage_payments.php');
exit();
