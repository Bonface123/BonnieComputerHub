<?php
// admin/view_payment.php: Admin view for a single payment (with feedback)
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit();
}
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
if (!$payment_id) die('Invalid payment ID');

// Fetch payment info with user and course
$stmt = $pdo->prepare('SELECT p.*, u.name AS student_name, u.email AS student_email, c.course_name FROM payments p JOIN users u ON p.user_id = u.id JOIN courses c ON p.course_id = c.id WHERE p.id = ?');
$stmt->execute([$payment_id]);
$payment = $stmt->fetch();
if (!$payment) die('Payment not found.');

// Fetch feedback (if any)
$rating_stmt = $pdo->prepare('SELECT rating, feedback, created_at FROM payment_ratings WHERE payment_id = ?');
$rating_stmt->execute([$payment_id]);
$rating = $rating_stmt->fetch();

$pageTitle = 'Payment Details';
?>
<main class="container mx-auto px-4 py-10">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 border border-blue-100">
        <h1 class="text-2xl font-extrabold text-primary mb-4">Payment Details</h1>
        <div class="mb-6">
            <strong>Student:</strong> <?= htmlspecialchars($payment['student_name']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($payment['student_email']) ?><br>
            <strong>Course:</strong> <?= htmlspecialchars($payment['course_name']) ?><br>
            <strong>Amount:</strong> KES <?= number_format($payment['amount'], 2) ?><br>
            <strong>Status:</strong> <?= ucfirst($payment['status']) ?><br>
            <strong>MPESA Ref:</strong> <?= htmlspecialchars($payment['transaction_id'] ?? '-') ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($payment['created_at']) ?><br>
        </div>
        <?php if ($rating): ?>
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <strong>Student Feedback:</strong><br>
                <span class="text-yellow-500 text-xl">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= $rating['rating'] ? '★' : '☆' ?>
                    <?php endfor; ?>
                </span>
                <?php if (!empty($rating['feedback'])): ?>
                    <div class="mt-2 text-gray-700 italic">"<?= htmlspecialchars($rating['feedback']) ?>"</div>
                <?php endif; ?>
                <div class="text-xs text-gray-500 mt-1">Submitted: <?= htmlspecialchars($rating['created_at']) ?></div>
            </div>
        <?php else: ?>
            <div class="mb-6 bg-gray-50 border-l-4 border-gray-300 p-4 rounded text-gray-600">
                No feedback submitted for this payment.
            </div>
        <?php endif; ?>
        <a href="manage_payments.php" class="bch-btn bch-btn-secondary">&larr; Back to Payments</a>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?>
