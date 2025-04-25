<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}
$blogId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($blogId <= 0) {
    header('Location: manage_blogs.php');
    exit;
}
// Fetch blog info
$stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
$stmt->execute([$blogId]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) {
    header('Location: manage_blogs.php');
    exit;
}
// Handle approve/reject/delete comment
if (isset($_POST['approve_comment']) && is_numeric($_POST['approve_comment'])) {
    $approveStmt = $pdo->prepare('UPDATE blog_comments SET approved = 1 WHERE id = ?');
    $approveStmt->execute([$_POST['approve_comment']]);
    $_SESSION['success_msg'] = 'Comment approved.';
    header('Location: admin_blog_comments.php?id='.$blogId);
    exit;
}
if (isset($_POST['delete_comment']) && is_numeric($_POST['delete_comment'])) {
    $delStmt = $pdo->prepare('DELETE FROM blog_comments WHERE id = ?');
    $delStmt->execute([$_POST['delete_comment']]);
    $_SESSION['success_msg'] = 'Comment deleted.';
    header('Location: admin_blog_comments.php?id='.$blogId);
    exit;
}
// Fetch all comments (threaded)
$sql = "SELECT c.*, u.name, u.email, u.role, u.avatar FROM blog_comments c LEFT JOIN users u ON c.author_id = u.id WHERE c.blog_id = ? ORDER BY c.created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$blogId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$threaded = [];
foreach ($comments as $c) {
    $parent = $c['parent_id'] ?? null;
    $threaded[$parent][] = $c;
}
function render_comments_admin($parent_id, $threaded, $level = 0) {
    if (!isset($threaded[$parent_id])) return;
    foreach ($threaded[$parent_id] as $c) {
        $isAdmin = isset($c['role']) && $c['role'] === 'admin';
        $avatarUrl = isset($c['avatar']) && $c['avatar'] ? '../assets/images/avatars/' . htmlspecialchars($c['avatar']) : '../assets/images/default-avatar.png';
        $status = $c['approved'] ? '<span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded">Approved</span>' : '<span class="ml-2 px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded">Pending</span>';
        echo '<div class="flex gap-3 mb-4 ml-'.($level*32).'">';
        echo '<img src="'.$avatarUrl.'" alt="avatar" class="w-8 h-8 rounded-full border">';
        echo '<div class="flex-1">';
        echo '<div class="flex items-center gap-2">';
        echo '<span class="font-semibold">'.htmlspecialchars($c['name'] ?? 'Guest').'</span>';
        if ($isAdmin) echo '<span class="ml-1 px-2 py-0.5 bg-yellow-500 text-white text-xs rounded">Admin</span>';
        echo '<span class="ml-2 text-xs text-gray-400">'.date('F j, Y H:i', strtotime($c['created_at'])).'</span>';
        echo $status;
        echo '</div>';
        echo '<div class="text-gray-800">'.nl2br(htmlspecialchars($c['content'])).'</div>';
        if (!$c['approved']) {
            echo '<form method="POST" class="inline"><input type="hidden" name="approve_comment" value="'.$c['id'].'"><button type="submit" class="text-xs text-green-700 hover:text-green-900 ml-2">Approve</button></form>';
        }
        echo '<form method="POST" class="inline"><input type="hidden" name="delete_comment" value="'.$c['id'].'"><button type="submit" class="text-xs text-red-600 hover:text-red-900 ml-2">Delete</button></form>';
        echo '</div></div>';
        render_comments_admin($c['id'], $threaded, $level+1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments | <?= htmlspecialchars($blog['title']) ?> | Bonnie Computer Hub Admin</title>
    <link href="../assets/css/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/design-system.css" rel="stylesheet">
    <link href="../assets/css/components.css" rel="stylesheet">
    <link href="../assets/css/utilities.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap">
</head>
<body class="bg-gray-50 font-inter min-h-screen flex flex-col">
<?php include '../includes/header.php'; ?>
<main class="flex-1 px-4 py-8 max-w-3xl mx-auto w-full">
    <h1 class="text-2xl font-bold text-primary mb-4">Comments for: <?= htmlspecialchars($blog['title']) ?></h1>
<?php
// Count approved/pending
$stats = $pdo->prepare("SELECT SUM(approved=1) as approved, SUM(approved=0) as pending FROM blog_comments WHERE blog_id = ?");
$stats->execute([$blogId]);
$counts = $stats->fetch(PDO::FETCH_ASSOC);
?>
<div class="flex gap-4 mb-4">
    <span class="px-3 py-1 bg-green-100 text-green-700 rounded">Approved: <?= $counts['approved'] ?? 0 ?></span>
    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded">Pending: <?= $counts['pending'] ?? 0 ?></span>
</div>
<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
    </div>
<?php endif; ?>
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <?php render_comments_admin(null, $threaded); ?>
        <?php if (empty($comments)): ?>
            <div class="text-gray-400 italic">No comments for this blog yet.</div>
        <?php endif; ?>
    </div>
    <div class="mt-6">
        <a href="manage_blogs.php" class="text-primary hover:underline">&larr; Back to Blog Management</a>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
