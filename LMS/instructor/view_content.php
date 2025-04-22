<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$content_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$instructor_id = $_SESSION['user_id'];

// Fetch content, module, and course info, ensuring instructor owns the course
$sql = "SELECT mc.*, m.module_name, m.course_id, c.course_name
        FROM module_content mc
        JOIN course_modules m ON mc.module_id = m.id
        JOIN courses c ON m.course_id = c.id
        WHERE mc.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$content_id]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$content) {
    $_SESSION['error_msg'] = "Content not found or access denied.";
    header('Location: manage_courses.php');
    exit;
}

function renderContentDetails($content) {
    $html = "";
    switch ($content['content_type']) {
        case 'text':
            $html .= '<div class="prose max-w-none">' . $content['description'] . '</div>';
            break;
        case 'video':
            $html .= '<div class="my-4"><a href="' . htmlspecialchars($content['content_url']) . '" target="_blank" class="text-red-600 underline">Watch Video</a></div>';
            break;
        case 'document':
            $html .= '<div class="my-4"><a href="../uploads/materials/' . htmlspecialchars($content['content_file']) . '" target="_blank" class="text-blue-600 underline">Download Document</a></div>';
            break;
        default:
            $html .= '<span class="text-gray-500">Unknown content type.</span>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Content - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-primary mb-2">Content Details</h2>
            <div class="mb-4 text-gray-600 text-sm">
                <span class="font-semibold">Course:</span> <?= htmlspecialchars($content['course_name']) ?>
                <span class="mx-2">|</span>
                <span class="font-semibold">Module:</span> <?= htmlspecialchars($content['module_name']) ?>
            </div>
            <div class="mb-4">
                <span class="inline-block bg-primary text-white text-xs px-2 py-1 rounded">Type: <?= ucfirst(htmlspecialchars($content['content_type'])) ?></span>
            </div>
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-1">Title:</h3>
                <p class="text-lg"><?= htmlspecialchars($content['title']) ?></p>
            </div>
            <div class="mb-4">
                <h4 class="font-semibold text-gray-700 mb-1">Description/Content:</h4>
                <?= renderContentDetails($content) ?>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <a href="manage_courses.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">Back to Courses</a>
            </div>
        </div>
    </main>
</body>
</html>
