<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Validate module ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid module ID.";
    exit;
}
$module_id = intval($_GET['id']);

// Fetch module info
$module_sql = "SELECT * FROM course_modules WHERE id = ?";
$module_stmt = $pdo->prepare($module_sql);
$module_stmt->execute([$module_id]);
$module = $module_stmt->fetch(PDO::FETCH_ASSOC);
if (!$module) {
    echo "<div class='alert alert-danger'>Module not found.</div>";
    include '../includes/footer.php';
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_module'])) {
    $module_name = $_POST['module_name'];
    $module_description = $_POST['module_description'];
    $module_order = $_POST['module_order'];
    $update_sql = "UPDATE course_modules SET module_name = ?, module_description = ?, module_order = ? WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$module_name, $module_description, $module_order, $module_id]);
    $_SESSION['success_msg'] = 'Module updated successfully.';
    header("Location: manage_module_content.php?module_id=$module_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Module - <?= htmlspecialchars($module['module_name']) ?></title>
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <link rel="stylesheet" href="../css/design-system.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto">
        <div class="bg-gradient-to-r from-primary via-blue-100 to-white rounded-xl shadow-lg p-6 mb-8 flex items-center gap-4 border border-blue-200">
            <div class="flex-shrink-0 bg-white rounded-full p-4 shadow">
                <i class="fas fa-cubes text-primary text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Edit Module</h1>
                <div class="text-xl md:text-2xl font-bold text-primary"> <?= htmlspecialchars($module['module_name']) ?> </div>
            </div>
        </div>
        <a href="manage_module_content.php?module_id=<?= $module_id ?>" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
            <i class="fas fa-arrow-left"></i> Go Back to Module Content
        </a>
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="bg-white rounded-xl shadow p-8 space-y-6 border border-gray-100">
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="module_name">Module Name</label>
                <input class="input input-bordered w-full" type="text" id="module_name" name="module_name" value="<?= htmlspecialchars($module['module_name']) ?>" required>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="module_description">Module Description</label>
                <textarea class="input input-bordered w-full" id="module_description" name="module_description" rows="4" required><?= htmlspecialchars($module['module_description']) ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="module_order">Module Order</label>
                <input class="input input-bordered w-32" type="number" id="module_order" name="module_order" value="<?= htmlspecialchars($module['module_order']) ?>" min="1" required>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="learning_objectives">Learning Objectives</label>
                <textarea class="input input-bordered w-full" name="learning_objectives" id="learning_objectives" rows="2" placeholder="List key objectives for this module"><?= htmlspecialchars($module['learning_objectives'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="topics">Topics</label>
                <textarea class="input input-bordered w-full" name="topics" id="topics" rows="2" placeholder="Comma-separated topics"><?= htmlspecialchars($module['topics'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="outcomes">Outcomes</label>
                <textarea class="input input-bordered w-full" name="outcomes" id="outcomes" rows="2" placeholder="Expected outcomes for this module"><?= htmlspecialchars($module['outcomes'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="resources">Resources</label>
                <textarea class="input input-bordered w-full" name="resources" id="resources" rows="2" placeholder="Links or resource references"><?= htmlspecialchars($module['resources'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="assignments">Assignments</label>
                <textarea class="input input-bordered w-full" name="assignments" id="assignments" rows="2" placeholder="Assignment details or links"><?= htmlspecialchars($module['assignments'] ?? '') ?></textarea>
            </div>
            <div class="flex items-center space-x-4 mt-8">
                <button class="btn bg-primary text-white px-6 py-2 font-semibold rounded-lg shadow-md hover:bg-primary-dark transition flex items-center gap-2" type="submit" name="update_module">
                    <i class="fas fa-save"></i> Update Module
                </button>
                <a href="manage_module_content.php?module_id=<?= $module_id ?>" class="btn btn-secondary px-6 py-2 text-gray-700 font-semibold rounded-lg shadow-md hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Module Content
                </a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
