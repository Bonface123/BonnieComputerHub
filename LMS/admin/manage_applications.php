<?php
// Admin panel: Manage Course Applications
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once '../includes/db_connect.php';
$pageTitle = 'Manage Course Applications';
// Filtering logic
$filter_course = $_GET['course_id'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';
$where = [];
$params = [];
if ($filter_course) { $where[] = 'a.course_id = ?'; $params[] = $filter_course; }
if ($filter_status) { $where[] = 'a.status = ?'; $params[] = $filter_status; }
if ($filter_from) { $where[] = 'a.applied_at >= ?'; $params[] = $filter_from.' 00:00:00'; }
if ($filter_to) { $where[] = 'a.applied_at <= ?'; $params[] = $filter_to.' 23:59:59'; }
$where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';
$sql = "SELECT a.*, c.course_name FROM course_applications a JOIN courses c ON a.course_id = c.id $where_sql ORDER BY a.applied_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
// For course filter dropdown
$courses = $pdo->query('SELECT id, course_name FROM courses ORDER BY course_name')->fetchAll(PDO::FETCH_ASSOC);
// Handle status update
if (isset($_POST['update_status_id'], $_POST['update_status'])) {
    $id = intval($_POST['update_status_id']);
    $status = $_POST['update_status'];
    $pdo->prepare('UPDATE course_applications SET status = ? WHERE id = ?')->execute([$status, $id]);
    // Send notification if accepted
    if ($status === 'Accepted') {
        // Fetch applicant details
        $stmt = $pdo->prepare('SELECT name, email, course_id FROM course_applications WHERE id = ?');
        $stmt->execute([$id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($app && !empty($app['email'])) {
            // Fetch course name
            $course_stmt = $pdo->prepare('SELECT course_name FROM courses WHERE id = ?');
            $course_stmt->execute([$app['course_id']]);
            $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
            $to = $app['email'];
            $subject = 'Your BCH Course Application Has Been Accepted';
            $message = "Dear {$app['name']},\n\nCongratulations! Your application for the course '" . ($course['course_name'] ?? 'the selected course') . "' has been accepted.\n\nPlease log in to your BCH account for further details.\n\nBest regards,\nBonnie Computer Hub Team";
            $headers = "From: noreply@bonniecomputerhub.com\r\nReply-To: noreply@bonniecomputerhub.com";
            require_once __DIR__ . '/../includes/send_mail.php';
            $mail_result = bch_send_mail($to, $app['name'], $subject, $message);
            if ($mail_result['success']) {
                $_SESSION['success_msg'] = 'Applicant was notified by email.';
            } else {
                $_SESSION['error_msg'] = 'Status updated, but failed to send email notification.'
                    . '<br><strong>PHPMailer Error:</strong> ' . htmlspecialchars($mail_result['error'])
                    . '<br>To: ' . htmlspecialchars($to)
                    . '<br>Subject: ' . htmlspecialchars($subject);
            }
        } else {
            $_SESSION['success_msg'] = 'Status updated, but applicant email not found.';
        }
    } else {
        $_SESSION['success_msg'] = 'Application status updated.';
    }
    header('Location: manage_applications.php?'.http_build_query($_GET));
    exit;
}
// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="course_applications_'.date('Ymd_His').'.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Course','Applicant','Email','Phone','Message','Status','Applied At']);
    foreach ($applications as $app) {
        fputcsv($out, [
            $app['course_name'], $app['name'], $app['email'], $app['phone'], $app['message'], $app['status'] ?? 'New', $app['applied_at']
        ]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | BCH Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/utilities.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',
                        secondary: '#FFD700',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
<!-- Header -->
<header class="sticky top-0 z-50 bg-white shadow-md transition-all duration-300">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
            <div>
                <a href="../../index.html" class="text-xl font-bold text-primary">Bonnie Computer Hub</a>
                <p class="text-gray-500 text-sm">Admin Dashboard</p>
            </div>
        </div>
        <nav class="flex items-center space-x-6">
            <a href="admin_dashboard.php" class="text-primary hover:text-secondary font-semibold">Dashboard</a>
            <a href="manage_applications.php" class="text-primary hover:text-secondary font-semibold">Applications</a>
            <a href="manage_enrollments.php" class="text-primary hover:text-secondary font-semibold">Enrollments</a>
            <a href="manage_courses.php" class="text-primary hover:text-secondary font-semibold">Courses</a>
            <a href="manage_users.php" class="text-primary hover:text-secondary font-semibold">Users</a>
            <a href="reports.php" class="text-primary hover:text-secondary font-semibold">Reports</a>
            <a href="../index.html" class="text-primary hover:text-secondary font-semibold">Main Site</a>
            <a href="logout.php" class="text-primary hover:text-secondary font-semibold">Logout</a>
        </nav>
    </div>
</header>
<main class="container mx-auto px-4 py-8">
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="mb-6 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
            <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="mb-6 text-red-800 bg-red-100 border border-red-200 px-4 py-3 rounded">
            <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-xl shadow p-8 border border-gray-100">
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="mb-6 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
                <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="mb-6 text-red-800 bg-red-100 border border-red-200 px-4 py-3 rounded">
                <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>
        <h1 class="text-2xl md:text-3xl font-extrabold text-primary mb-8 flex items-center gap-2">
            <i class="fas fa-user-edit text-secondary"></i> <?= htmlspecialchars($pageTitle) ?>
        </h1>
        <form class="flex flex-wrap gap-4 mb-8 items-end" method="get" action="">
            <div>
                <label class="block text-xs font-semibold mb-1">Course</label>
                <select name="course_id" class="border border-blue-200 rounded px-2 py-1">
                    <option value="">All</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filter_course == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Status</label>
                <select name="status" class="border border-blue-200 rounded px-2 py-1">
                    <option value="">All</option>
                    <?php foreach (["New","Reviewed","Accepted","Rejected"] as $status): ?>
                        <option value="<?= $status ?>" <?= $filter_status == $status ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">From</label>
                <input type="date" name="from" value="<?= htmlspecialchars($filter_from) ?>" class="border border-blue-200 rounded px-2 py-1">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">To</label>
                <input type="date" name="to" value="<?= htmlspecialchars($filter_to) ?>" class="border border-blue-200 rounded px-2 py-1">
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded font-semibold">Filter</button>
            <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="bg-yellow-400 text-primary px-4 py-2 rounded font-semibold inline-flex items-center gap-2 hover:bg-yellow-500"><i class="fas fa-file-csv"></i> Export CSV</a>
        </form>
        <?php if (empty($applications)): ?>
            <div class="text-gray-500 text-center py-12">No applications yet.</div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-primary">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Course</th>
                        <th class="px-4 py-3 text-left font-semibold">Applicant</th>
                        <th class="px-4 py-3 text-left font-semibold">Email</th>
                        <th class="px-4 py-3 text-left font-semibold">Phone</th>
                        <th class="px-4 py-3 text-left font-semibold">Message</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Applied At</th>
                        <th class="px-4 py-3 text-left font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td class="px-4 py-2"><?= htmlspecialchars($app['course_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($app['name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($app['email']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($app['phone']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($app['message']) ?></td>
                        <td class="px-4 py-2">
                            <form method="post" class="inline">
                                <input type="hidden" name="update_status_id" value="<?= $app['id'] ?>">
                                <select name="update_status" class="border border-blue-200 rounded px-2 py-1 text-xs">
                                    <?php foreach (["New","Reviewed","Accepted","Rejected"] as $status): ?>
                                        <option value="<?= $status ?>" <?= ($app['status'] ?? 'New') == $status ? 'selected' : '' ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="ml-1 bg-primary text-white px-2 py-1 rounded text-xs"><i class="fas fa-save"></i></button>
                            </form>
                        </td>
                        <td class="px-4 py-2"><?= date('M d, Y H:i', strtotime($app['applied_at'])) ?></td>
                        <td class="px-4 py-2">
                            <form method="post" onsubmit="return confirm('Delete this application?')">
                                <input type="hidden" name="delete_id" value="<?= $app['id'] ?>">
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded text-xs"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
<?php
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $pdo->prepare('DELETE FROM course_applications WHERE id = ?')->execute([$del_id]);
    header('Location: manage_applications.php');
    exit;
}
