<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Validate module_id
if (!isset($_GET['module_id']) || !is_numeric($_GET['module_id'])) {
    echo "Invalid module ID.";
    exit;
}
$module_id = intval($_GET['module_id']);

// Fetch module info
$module_sql = "SELECT * FROM course_modules WHERE id = ?";
$module_stmt = $pdo->prepare($module_sql);
$module_stmt->execute([$module_id]);
$module = $module_stmt->fetch(PDO::FETCH_ASSOC);
if (!$module) {
    echo "Module not found.";
    exit;
}

// Fetch module content (lessons, resources, etc.)
$content_sql = "SELECT * FROM module_content WHERE module_id = ? ORDER BY id ASC";
$content_stmt = $pdo->prepare($content_sql);
$content_stmt->execute([$module_id]);
$contents = $content_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Module Content - <?= htmlspecialchars($module['module_name']) ?></title>
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <link rel="stylesheet" href="../css/design-system.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-primary mb-4">Manage Content: <?= htmlspecialchars($module['module_name']) ?></h1>
    <a href="edit_module.php?id=<?= $module_id ?>" class="btn btn-secondary mb-4">Edit Module Details</a>
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="mb-4 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
            <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="mb-4 text-red-800 bg-red-100 border border-red-200 px-4 py-3 rounded">
            <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Module Items</h2>
        <?php if (count($contents) === 0): ?>
            <p class="text-gray-500">No content items found.</p>
        <?php else: ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($contents as $item): ?>
                <li class="py-4 flex items-center justify-between">
                    <div>
                        <strong>
<?php
    // Display file link if available, else URL, else description
    if (!empty($item['content_file'])) {
        $filename = basename($item['content_file']);
        echo '<a href="' . htmlspecialchars($item['content_file']) . '" target="_blank">' . htmlspecialchars($filename) . '</a>';
    } elseif (!empty($item['content_url'])) {
        echo '<a href="' . htmlspecialchars($item['content_url']) . '" target="_blank">' . htmlspecialchars($item['content_url']) . '</a>';
    } elseif (!empty($item['description'])) {
        echo nl2br(htmlspecialchars($item['description']));
    } else {
        echo '(No content)';
    }
?>
</strong><br>
<span class="text-gray-600 text-sm"><?= htmlspecialchars($item['content_type']) ?></span>
                    </div>
                    <div>
                        <a href="edit_module_content.php?id=<?= $item['id'] ?>" class="btn btn-primary mr-2">Edit</a>
                        <a href="delete_module_content.php?id=<?= $item['id'] ?>&module_id=<?= $module_id ?>" class="btn btn-danger" onclick="return confirm('Delete this content item?')">Delete</a>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="add_module_content.php?module_id=<?= $module_id ?>" class="btn btn-success mt-6">Add New Content Item</a>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
