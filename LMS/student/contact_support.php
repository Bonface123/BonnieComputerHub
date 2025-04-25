<?php
// student/contact_support.php: Contact support for payment issues
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    die('Unauthorized');
}
$user_id = $_SESSION['user_id'];
$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
if (!$payment_id) die('Invalid payment ID');

// Fetch payment info
$stmt = $pdo->prepare('SELECT p.*, c.course_name FROM payments p JOIN courses c ON p.course_id = c.id WHERE p.id = ? AND p.user_id = ?');
$stmt->execute([$payment_id, $user_id]);
$payment = $stmt->fetch();
if (!$payment) die('Payment not found.');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (strlen($message) < 10) {
        $error = 'Please enter a detailed message (at least 10 characters).';
    } else {
        // Send support email (reuse PHPMailer config)
        require_once '../includes/email_helper.php';
        $to = 'support@bonniecomputerhub.com'; // TODO: Set actual support email
        $subject = 'Payment Support Request - Payment ID: ' . $payment_id;
        $body = "Student ID: $user_id\nCourse: {$payment['course_name']}\nPayment ID: $payment_id\nMessage: $message";
        $sent = mail($to, $subject, $body); // Replace with PHPMailer if needed
        $success = $sent ? 'Support request sent successfully!' : 'Failed to send support request.';
    }
}
?>
<main class="container mx-auto px-4 py-10">
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-lg p-8 border border-blue-100">
        <h1 class="text-2xl font-extrabold text-primary mb-4">Contact Support</h1>
        <div class="mb-4 text-gray-700">
            <strong>Course:</strong> <?= htmlspecialchars($payment['course_name']) ?><br>
            <strong>Amount:</strong> KES <?= number_format($payment['amount'], 2) ?><br>
            <strong>Status:</strong> <?= ucfirst($payment['status']) ?><br>
            <strong>Date:</strong> <?= htmlspecialchars($payment['created_at']) ?><br>
        </div>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 rounded px-4 py-2 mb-4"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-700 rounded px-4 py-2 mb-4"> <?= htmlspecialchars($success) ?> </div>
        <?php else: ?>
        <form method="post">
            <label class="block font-semibold mb-2 text-gray-700">Describe your issue:</label>
            <textarea name="message" rows="5" class="w-full border rounded px-3 py-2 mb-4" required minlength="10"></textarea>
            <button type="submit" class="bch-btn bch-btn-primary">Send Support Request</button>
        </form>
        <?php endif; ?>
        <a href="payment_history.php" class="inline-block mt-4 text-blue-700 hover:underline">&larr; Back to Payment History</a>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?>
