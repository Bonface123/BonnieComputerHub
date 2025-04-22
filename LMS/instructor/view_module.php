<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$instructor_id = $_SESSION['user_id'];

// Fetch module and course info, ensuring instructor owns the course
$sql = "SELECT m.*, c.course_name FROM course_modules m JOIN courses c ON m.course_id = c.id WHERE m.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$module_id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    $_SESSION['error_msg'] = "Module not found or access denied.";
    header('Location: manage_courses.php');
    exit;
}

// Fetch content for this module
$stmt = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order");
$stmt->execute([$module_id]);
$contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Module - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-primary mb-2">Module Details</h2>
            <div class="mb-2 text-gray-600 text-sm">
                <span class="font-semibold">Course:</span> <?= htmlspecialchars($module['course_name']) ?>
            </div>
            <div class="mb-2">
                <span class="inline-block bg-primary text-white text-xs px-2 py-1 rounded">Order: <?= htmlspecialchars($module['module_order']) ?></span>
            </div>
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-1">Module Name:</h3>
                <p class="text-lg"><?= htmlspecialchars($module['module_name']) ?></p>
            </div>
            <div class="mb-4">
                <h4 class="font-semibold text-gray-700 mb-1">Description:</h4>
                <p><?= nl2br(htmlspecialchars($module['module_description'])) ?></p>
            </div>
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-primary mb-2">Module Content</h4>
                <?php if ($contents): ?>
                    <div class="space-y-2">
                        <?php foreach ($contents as $content): ?>
                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                <div>
                                    <h5 class="font-medium mb-1"> <?= htmlspecialchars($content['title']) ?> </h5>
                                    <span class="text-xs bg-secondary text-primary px-2 py-1 rounded">Type: <?= ucfirst(htmlspecialchars($content['content_type'])) ?></span>
                                </div>
                                <div class="flex gap-2">
                                    <a href="view_content.php?id=<?= $content['id'] ?>" class="text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-primary px-2 py-1 rounded" aria-label="View Content"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="text-green-600 hover:text-green-800 focus:ring-2 focus:ring-primary px-2 py-1 rounded opacity-50 cursor-not-allowed" aria-label="Edit Content" tabindex="-1"><i class="fas fa-edit"></i></a>
                                    <a href="delete_content.php?id=<?= $content['id'] ?>" onclick="return confirm('Delete this content?');" class="text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-400 px-2 py-1 rounded" aria-label="Delete Content"><i class="fas fa-trash"></i></a>
                                    <?php if ($content['content_type'] === 'video'): ?>
                                        <a href="<?= htmlspecialchars($content['content_url']) ?>" target="_blank" class="text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-400 px-2 py-1 rounded" aria-label="Watch Video"><i class="fab fa-youtube text-xl"></i></a>
                                    <?php elseif ($content['content_type'] === 'document'): ?>
                                        <a href="../uploads/materials/<?= htmlspecialchars($content['content_file']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-primary px-2 py-1 rounded" aria-label="Download Document"><i class="fas fa-file-alt text-xl"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center">No content added yet.</p>
                <?php endif; ?>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <a href="manage_courses.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">Back to Courses</a>
            </div>
        </div>
    </main>
</body>
</html>
