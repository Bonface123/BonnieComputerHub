<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch module
$sql = "SELECT m.*, c.created_by FROM course_modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.created_by = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$module_id, $instructor_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    $_SESSION['error_msg'] = "Module not found or access denied.";
    header('Location: manage_courses.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_module'])) {
    $module_name = $_POST['module_name'];
    $module_description = $_POST['module_description'];
    $module_order = $_POST['module_order'];
    $stmt = $pdo->prepare("UPDATE course_modules SET module_name = ?, module_description = ?, module_order = ? WHERE id = ?");
    $stmt->execute([$module_name, $module_description, $module_order, $module_id]);
    $_SESSION['success_msg'] = "Module updated successfully.";
    header('Location: ../instructor/manage_courses.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Module - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 max-w-xl mx-auto">
            <h2 class="text-2xl font-bold text-primary mb-4">Edit Module</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Module Name</label>
                    <input type="text" name="module_name" value="<?= htmlspecialchars($module['module_name']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Module Order</label>
                    <input type="number" name="module_order" value="<?= htmlspecialchars($module['module_order']) ?>" min="1" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Module Description</label>
                    <textarea name="module_description" rows="4" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"><?= htmlspecialchars($module['module_description']) ?></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <a href="manage_courses.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">Cancel</a>
                    <button type="submit" name="edit_module" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
