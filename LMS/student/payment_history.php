<?php
// student/payment_history.php: Student Payment History Page
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Redirect if not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header('Location: ../pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch all payments for this student
$stmt = $pdo->prepare('SELECT p.*, c.course_name FROM payments p JOIN courses c ON p.course_id = c.id WHERE p.user_id = ? ORDER BY p.created_at DESC');
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll();

$pageTitle = 'My Payment History';
?>
<main class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8 border border-blue-100">
        <h1 class="text-3xl font-extrabold text-primary mb-6">Payment History</h1>
        <?php if (empty($payments)): ?>
            <div class="text-gray-600 text-lg">No payment records found.</div>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="min-w-full table-auto border rounded-lg">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Course</th>
                    <th class="px-4 py-2 text-left">Amount (KES)</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">MPESA Ref</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr class="border-b hover:bg-blue-50">
                    <td class="px-4 py-2"><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($p['created_at']))); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($p['course_name']); ?></td>
                    <td class="px-4 py-2"><?php echo number_format($p['amount'], 2); ?></td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?php
                            switch($p['status']) {
                                case 'success': echo 'bg-green-100 text-green-700'; break;
                                case 'pending': echo 'bg-yellow-100 text-yellow-700'; break;
                                case 'failed': echo 'bg-red-100 text-red-700'; break;
                                default: echo 'bg-gray-100 text-gray-700';
                            }
                        ?>">
                            <?php echo ucfirst($p['status']); ?>
                        </span>
                    </td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($p['transaction_id'] ?? '-'); ?></td>
                    <td class="px-4 py-2 space-x-2">
                        <?php if ($p['status'] === 'success'): ?>
                            <a href="download_receipt.php?payment_id=<?php echo $p['id']; ?>" class="bch-btn bch-btn-primary bch-btn-sm" target="_blank">Receipt</a>
                        <?php endif; ?>
                        <a href="contact_support.php?payment_id=<?php echo $p['id']; ?>" class="bch-btn bch-btn-secondary bch-btn-sm">Support</a>
                        <a href="rate_payment.php?payment_id=<?php echo $p['id']; ?>" class="bch-btn bch-btn-accent bch-btn-sm">Rate</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?>
