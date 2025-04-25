<?php
// student/download_receipt.php: Download PDF receipt for a payment
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/email_helper.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
if (!$payment_id) die('Invalid payment ID');

// Fetch payment info
$stmt = $pdo->prepare('SELECT p.*, c.course_name, u.name AS student_name, u.email AS student_email FROM payments p JOIN courses c ON p.course_id = c.id JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.user_id = ?');
$stmt->execute([$payment_id, $user_id]);
$payment = $stmt->fetch();
if (!$payment || $payment['status'] !== 'success') die('Receipt not available for this payment.');

// Generate PDF receipt (reuse logic from payment_success.php)
require_once '../vendor/autoload.php';
use Mpdf\Mpdf;
$pdfPath = __DIR__ . '/../uploads/receipts/BCH_Receipt_' . $payment_id . '.pdf';
if (!is_dir(dirname($pdfPath))) {
    mkdir(dirname($pdfPath), 0777, true);
}
$mpdf = new Mpdf();
$html = '<h2>Payment Receipt</h2>';
$html .= '<strong>Student:</strong> ' . htmlspecialchars($payment['student_name']) . '<br>';
$html .= '<strong>Course:</strong> ' . htmlspecialchars($payment['course_name']) . '<br>';
$html .= '<strong>Amount:</strong> KES ' . number_format($payment['amount'], 2) . '<br>';
$html .= '<strong>Status:</strong> ' . ucfirst($payment['status']) . '<br>';
$html .= '<strong>MPESA Ref:</strong> ' . htmlspecialchars($payment['transaction_id']) . '<br>';
$html .= '<strong>Date:</strong> ' . htmlspecialchars($payment['updated_at']) . '<br>';
$html .= '<br>Thank you for your payment!';
$mpdf->WriteHTML($html);
$mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

// Output PDF to browser
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Payment_Receipt.pdf"');
readfile($pdfPath);
if (file_exists($pdfPath)) unlink($pdfPath);
exit;
?>
