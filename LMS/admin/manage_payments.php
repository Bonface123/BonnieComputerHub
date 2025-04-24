<?php
// admin/manage_payments.php: Admin Payment Management Dashboard
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

// Filters
$filter_student = $_GET['student'] ?? '';
$filter_course = $_GET['course'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_ref = $_GET['ref'] ?? '';
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';
$where = [];
$params = [];
if ($filter_student) { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $params[] = "%$filter_student%"; $params[] = "%$filter_student%"; }
if ($filter_course) { $where[] = 'c.course_name LIKE ?'; $params[] = "%$filter_course%"; }
if ($filter_status) { $where[] = 'p.status = ?'; $params[] = $filter_status; }
if ($filter_ref) { $where[] = 'p.transaction_id LIKE ?'; $params[] = "%$filter_ref%"; }
if ($filter_from) { $where[] = 'p.created_at >= ?'; $params[] = $filter_from.' 00:00:00'; }
if ($filter_to) { $where[] = 'p.created_at <= ?'; $params[] = $filter_to.' 23:59:59'; }
$where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';
$sql = "SELECT p.*, u.name AS student_name, u.email AS student_email, c.course_name FROM payments p JOIN users u ON p.user_id = u.id JOIN courses c ON p.course_id = c.id $where_sql ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

$pageTitle = 'Manage Payments';
?>
<main class="container mx-auto px-4 py-10">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg p-8 border border-blue-100">
        <h1 class="text-3xl font-extrabold text-primary mb-6">Payments Management</h1>
        <form class="mb-6 flex flex-wrap gap-4 items-end" method="get" action="">
            <div>
                <label class="block text-xs font-semibold mb-1">Student</label>
                <input type="text" name="student" value="<?= htmlspecialchars($filter_student ?? '') ?>" class="border border-blue-200 rounded px-2 py-1" placeholder="Name or Email">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Course</label>
                <input type="text" name="course" value="<?= htmlspecialchars($filter_course ?? '') ?>" class="border border-blue-200 rounded px-2 py-1" placeholder="Course Name">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Status</label>
                <select name="status" class="border border-blue-200 rounded px-2 py-1">
                    <option value="">All</option>
                    <?php foreach (["success","pending","failed","refunded"] as $status): ?>
                        <option value="<?= $status ?>" <?= ($filter_status ?? '') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">MPESA Ref</label>
                <input type="text" name="ref" value="<?= htmlspecialchars($filter_ref ?? '') ?>" class="border border-blue-200 rounded px-2 py-1" placeholder="MPESA Ref">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">From</label>
                <input type="date" name="from" value="<?= htmlspecialchars($filter_from ?? '') ?>" class="border border-blue-200 rounded px-2 py-1">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">To</label>
                <input type="date" name="to" value="<?= htmlspecialchars($filter_to ?? '') ?>" class="border border-blue-200 rounded px-2 py-1">
            </div>
            <button type="submit" class="bch-btn bch-btn-primary">Filter</button>
            <a href="manage_payments.php" class="bch-btn bch-btn-secondary">Reset</a>
        </form>
        <div class="overflow-x-auto">
        <table class="min-w-full table-auto border rounded-lg text-sm">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-3 py-2 text-left">Date</th>
                    <th class="px-3 py-2 text-left">Student</th>
                    <th class="px-3 py-2 text-left">Email</th>
                    <th class="px-3 py-2 text-left">Course</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">MPESA Ref</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr class="border-b hover:bg-blue-50">
                    <td class="px-3 py-2"><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($p['created_at']))); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($p['student_name']); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($p['student_email']); ?></td>
                    <td class="px-3 py-2"><?php echo htmlspecialchars($p['course_name']); ?></td>
                    <td class="px-3 py-2">KES <?php echo number_format($p['amount'], 2); ?></td>
                    <td class="px-3 py-2">
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
                    <td class="px-3 py-2"><?php echo htmlspecialchars($p['transaction_id'] ?? '-'); ?></td>
                    <td class="px-3 py-2 space-x-1">
                        <a href="view_payment.php?payment_id=<?php echo $p['id']; ?>" class="bch-btn bch-btn-secondary bch-btn-sm">Details</a>
                        <?php if ($p['status'] === 'pending'): ?>
                            <a href="verify_payment.php?payment_id=<?php echo $p['id']; ?>" class="bch-btn bch-btn-primary bch-btn-sm">Mark as Paid</a>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'success'): ?>
                            <a href="refund_payment.php?payment_id=<?php echo $p['id']; ?>" class="bch-btn bch-btn-accent bch-btn-sm">Refund</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>
<?php require_once '../includes/footer.php'; ?>
