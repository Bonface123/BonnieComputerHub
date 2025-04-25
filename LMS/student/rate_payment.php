<?php
// student/rate_payment.php: Rate your payment experience
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

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback']);
    if ($rating < 1 || $rating > 5) {
        $error = 'Invalid rating.';
    } else {
        // Store rating in DB (add payment_rating table if needed)
        $pdo->prepare('INSERT INTO payment_ratings (payment_id, user_id, rating, feedback, created_at) VALUES (?, ?, ?, ?, NOW())')->execute([$payment_id, $user_id, $rating, $feedback]);
        $success = 'Thank you for your feedback!';
    }
}
?>
<main class="container mx-auto px-4 py-10">
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-lg p-8 border border-blue-100">
        <h1 class="text-2xl font-extrabold text-primary mb-4">Rate Your Payment</h1>
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
            <label class="block font-semibold mb-2 text-gray-700">Rate your payment experience:</label>
            <div class="flex gap-2 mb-4">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label>
                        <input type="radio" name="rating" value="<?= $i ?>" required> <span class="text-xl">★</span>
                    </label>
                <?php endfor; ?>
            </div>
            <label class="block font-semibold mb-2 text-gray-700">Feedback (optional):</label>
            <textarea name="feedback" rows="3" class="w-full border rounded px-3 py-2 mb-4"></textarea>
            <button type="submit" class="bch-btn bch-btn-accent">Submit Rating</button>
        </form>
        <?php endif; ?>
        <a href="payment_history.php" class="inline-block mt-4 text-blue-700 hover:underline">&larr; Back to Payment History</a>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?>
