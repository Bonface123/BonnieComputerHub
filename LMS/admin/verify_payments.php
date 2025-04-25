<?php
session_start();
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
// Handle verification
if (isset($_POST['verify_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $stmt = $pdo->prepare('UPDATE payments SET status = "success", completed_at = NOW() WHERE id = ?');
    $stmt->execute([$payment_id]);
    // Enroll user in course
    $payment = $pdo->prepare('SELECT user_id, course_id FROM payments WHERE id = ?');
    $payment->execute([$payment_id]);
    $row = $payment->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $enroll = $pdo->prepare('INSERT IGNORE INTO enrollments (user_id, course_id, status, enrolled_at) VALUES (?, ?, "enrolled", NOW())');
        $enroll->execute([$row['user_id'], $row['course_id']]);
    }
    $_SESSION['success_msg'] = 'Payment verified and enrollment confirmed.';
    header('Location: verify_payments.php');
    exit;
}
// Fetch pending payments
$pending = $pdo->query('SELECT p.*, u.name, u.email, c.course_name FROM payments p JOIN users u ON p.user_id = u.id JOIN courses c ON p.course_id = c.id WHERE p.status = "pending" ORDER BY p.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = "Verify Payments";
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold text-primary mb-6">Pending Payments (MPESA, PayPal, Card)</h1>
  <?php if (isset($_SESSION['success_msg'])): ?>
    <div class="mb-6 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
      <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
    </div>
  <?php endif; ?>
  <div class="bg-white rounded-xl shadow p-6">
    <?php if ($pending): ?>
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-blue-50">
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Student</th>
            <th class="px-4 py-2">Course</th>
            <th class="px-4 py-2">Amount</th>
            <th class="px-4 py-2">Method</th>
            <th class="px-4 py-2">Transaction Ref</th>
            <th class="px-4 py-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pending as $p): ?>
            <tr>
              <td class="px-4 py-2 text-gray-700"><?= htmlspecialchars($p['created_at']) ?></td>
              <td class="px-4 py-2 text-blue-700 font-semibold"><?= htmlspecialchars($p['name']) ?><br><span class="text-xs text-gray-500"><?= htmlspecialchars($p['email']) ?></span></td>
              <td class="px-4 py-2 font-bold text-primary"><?= htmlspecialchars($p['course_name']) ?></td>
              <td class="px-4 py-2 text-green-700">KES <?= number_format($p['amount'],0) ?></td>
              <td class="px-4 py-2 text-center capitalize"><?= htmlspecialchars($p['method']) ?></td>
              <td class="px-4 py-2 font-mono text-xs text-gray-800"><?= htmlspecialchars($p['transaction_ref']) ?></td>
              <td class="px-4 py-2">
                <form method="post" style="display:inline">
                  <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                  <button type="submit" name="verify_payment" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Verify & Enroll</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="text-gray-500 text-center">No pending payments.</div>
    <?php endif; ?>
  </div>
</main>
<?php include '../includes/footer.php'; ?>
