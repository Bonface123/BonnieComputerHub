<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['module_id']) || !is_numeric($_GET['module_id'])) {
    echo "Invalid request.";
    exit;
}
$content_id = intval($_GET['id']);
$module_id = intval($_GET['module_id']);

// Fetch content info for confirmation
$content_sql = "SELECT * FROM module_content WHERE id = ?";
$content_stmt = $pdo->prepare($content_sql);
$content_stmt->execute([$content_id]);
$content = $content_stmt->fetch(PDO::FETCH_ASSOC);
if (!$content) {
    echo "Content item not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_confirm'])) {
    $delete_sql = "DELETE FROM module_content WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_sql);
    if ($delete_stmt->execute([$content_id])) {
        $_SESSION['success_msg'] = "Module content deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Failed to delete module content.";
    }
    header('Location: manage_module_content.php?module_id=' . $module_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Module Content - <?= htmlspecialchars($content['title']) ?></title>
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <link rel="stylesheet" href="../css/design-system.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-primary mb-4">Delete Content Item</h1>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p>Are you sure you want to delete <strong><?= htmlspecialchars($content['title']) ?></strong>?</p>
        <form method="POST" class="mt-6 flex space-x-4">
            <button class="btn btn-danger" type="submit" name="delete_confirm">Yes, Delete</button>
            <a href="manage_module_content.php?module_id=<?= $module_id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
