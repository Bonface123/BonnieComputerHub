<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'vendor/autoload.php'; // For PDF generation
require_once 'includes/email_helper.php';

use Mpdf\Mpdf;

// Get payment ID from query string
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
if (!$payment_id) {
    die('Invalid payment reference.');
}

// Fetch payment details (now with email)
$stmt = $pdo->prepare("SELECT p.*, c.course_name, u.name AS student_name, u.email AS student_email FROM payments p JOIN courses c ON p.course_id = c.id JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$payment) {
    die('Payment not found.');
}

// Handle PDF download
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    $mpdf = new Mpdf();
    $html = '<h2>Payment Receipt</h2>';
    $html .= '<strong>Student:</strong> ' . htmlspecialchars($payment['student_name']) . '<br>';
    $html .= '<strong>Course:</strong> ' . htmlspecialchars($payment['course_name']) . '<br>';
    $html .= '<strong>Amount:</strong> KES ' . number_format($payment['amount'], 2) . '<br>';
    $html .= '<strong>Status:</strong> ' . ucfirst($payment['status']) . '<br>';
    $html .= '<strong>MPESA Ref:</strong> ' . htmlspecialchars($payment['transaction_id']) . '<br>';
    $html .= '<strong>Date:</strong> ' . $payment['updated_at'] . '<br>';
    $mpdf->WriteHTML($html);
    $mpdf->Output('Payment_Receipt.pdf', 'D');
    exit;
}

// Send email with PDF receipt (only once per view, demo)
$email_sent = false;
if (!isset($_SESSION['receipt_emailed_' . $payment_id])) {
    $mpdf = new Mpdf();
    $html = '<h2>Payment Receipt</h2>';
    $html .= '<strong>Student:</strong> ' . htmlspecialchars($payment['student_name']) . '<br>';
    $html .= '<strong>Course:</strong> ' . htmlspecialchars($payment['course_name']) . '<br>';
    $html .= '<strong>Amount:</strong> KES ' . number_format($payment['amount'], 2) . '<br>';
    $html .= '<strong>Status:</strong> ' . ucfirst($payment['status']) . '<br>';
    $html .= '<strong>MPESA Ref:</strong> ' . htmlspecialchars($payment['transaction_id']) . '<br>';
    $html .= '<strong>Date:</strong> ' . $payment['updated_at'] . '<br>';
    $pdfPath = sys_get_temp_dir() . '/Payment_Receipt_' . $payment_id . '.pdf';
    $mpdf->WriteHTML($html);
    $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);
    $email_sent = sendPaymentReceipt(
        $payment['student_email'],
        $payment['student_name'],
        $payment['course_name'],
        number_format($payment['amount'], 2),
        ucfirst($payment['status']),
        $payment['transaction_id'],
        $payment['updated_at'],
        $pdfPath
    );
    $_SESSION['receipt_emailed_' . $payment_id] = true;
    if (file_exists($pdfPath)) unlink($pdfPath);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50">
    <div class="max-w-xl mx-auto bg-white shadow-lg rounded-lg p-8 mt-12 text-center">
        <!-- Success Animation -->
        <div class="flex justify-center mb-4">
            <svg class="w-20 h-20 text-green-500 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="#d1fae5"/>
                <path d="M8 12l2.5 2.5L16 9" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-green-700 mb-2">Payment Successful!</h1>
        <div class="mb-4 text-green-800 font-semibold text-lg">Thank you for your payment. Your enrollment is now active.</div>
        <div class="mb-4 bg-green-50 border border-green-200 rounded p-3 text-green-900">
            <span class="font-bold">Next Steps:</span> You can now access your course materials. A receipt will be sent to your email shortly.<br>
            <span class="italic text-sm">(Demo: Email sending is simulated in this version.)</span>
        </div>
        <div class="mb-2"><strong>Student:</strong> <?= htmlspecialchars($payment['student_name']) ?></div>
        <div class="mb-2"><strong>Course:</strong> <?= htmlspecialchars($payment['course_name']) ?></div>
        <div class="mb-2"><strong>Amount Paid:</strong> KES <?= number_format($payment['amount'], 2) ?></div>
        <div class="mb-2"><strong>Status:</strong> <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold"><?= ucfirst($payment['status']) ?></span></div>
        <div class="mb-2"><strong>MPESA Ref:</strong> <?= htmlspecialchars($payment['transaction_id']) ?></div>
        <div class="mb-2"><strong>Date:</strong> <?= $payment['updated_at'] ?></div>
        <a href="?payment_id=<?= $payment_id ?>&download=pdf" class="mt-4 inline-block bg-primary text-white font-bold px-6 py-2 rounded hover:bg-blue-800 transition">Download PDF Receipt</a>
        <a href="student/dashboard.php" class="mt-4 ml-2 inline-block bg-gray-200 text-primary font-bold px-6 py-2 rounded hover:bg-gray-300 transition">Go to Dashboard</a>
    </div>
    <!-- Confetti effect (simple JS) -->
    <script>
    // Basic confetti effect for celebration (optional, lightweight)
    document.addEventListener('DOMContentLoaded', function() {
        for (let i = 0; i < 80; i++) {
            let conf = document.createElement('div');
            conf.style.position = 'fixed';
            conf.style.left = Math.random() * 100 + 'vw';
            conf.style.top = '-2vh';
            conf.style.width = '8px';
            conf.style.height = '16px';
            conf.style.background = 'hsl(' + (Math.random()*360) + ',80%,60%)';
            conf.style.opacity = 0.8;
            conf.style.borderRadius = '3px';
            conf.style.zIndex = 9999;
            conf.style.transition = 'top 2.5s linear';
            document.body.appendChild(conf);
            setTimeout(() => {
                conf.style.top = (90 + Math.random()*10) + 'vh';
            }, 100);
            setTimeout(() => {
                conf.remove();
            }, 2700);
        }
    });
    </script>
</body>
</html>
