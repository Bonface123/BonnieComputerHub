<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$content_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch content, module, and course info, ensuring instructor owns the course
$sql = "SELECT mc.*, m.module_name, m.id AS module_id, c.course_name FROM module_content mc JOIN course_modules m ON mc.module_id = m.id JOIN courses c ON m.course_id = c.id WHERE mc.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$content_id]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$content) {
    $_SESSION['error_msg'] = "Content not found or access denied.";
    header('Location: manage_courses.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_content'])) {
    $title = $_POST['content_title'];
    $description = $_POST['content_description'];
    $content_type = $_POST['content_type'];
    $content_order = $_POST['content_order'];
    $content_url = null;
    $content_file = $content['content_file'];

    switch($content_type) {
        case 'video':
            $content_url = $_POST['video_url'];
            break;
        case 'document':
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/materials/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['document_file']['name']);
                $filePath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['document_file']['tmp_name'], $filePath)) {
                    $content_file = $fileName;
                }
            }
            break;
        case 'text':
            $description = $_POST['text_content'];
            break;
    }

    $stmt = $pdo->prepare("UPDATE module_content SET content_type=?, title=?, description=?, content_url=?, content_file=?, content_order=? WHERE id=?");
    $stmt->execute([
        $content_type,
        $title,
        $description,
        $content_url,
        $content_file,
        $content_order,
        $content_id
    ]);
    $_SESSION['success_msg'] = "Content updated successfully.";
    header('Location: view_module.php?id=' . $content['module_id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-primary mb-2">Edit Content</h2>
            <div class="mb-2 text-gray-600 text-sm">
                <span class="font-semibold">Course:</span> <?= htmlspecialchars($content['course_name']) ?>
                <span class="mx-2">|</span>
                <span class="font-semibold">Module:</span> <?= htmlspecialchars($content['module_name']) ?>
            </div>
            <?php if (isset($_SESSION['error_msg'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
        <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
    </div>
<?php endif; ?>
<form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
    <div>
        <label class="block text-gray-700 font-medium mb-2">Content Type</label>
        <select name="content_type" id="contentType" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" onchange="showRelevantFields()">
            <option value="text" <?= $content['content_type']==='text'?'selected':'' ?>>Text</option>
            <option value="video" <?= $content['content_type']==='video'?'selected':'' ?>>YouTube Video</option>
            <option value="document" <?= $content['content_type']==='document'?'selected':'' ?>>Document</option>
        </select>
    </div>
    <div>
        <label class="block text-gray-700 font-medium mb-2">Title</label>
        <input type="text" name="content_title" value="<?= htmlspecialchars($content['title']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
    </div>
    <div>
        <label class="block text-gray-700 font-medium mb-2">Order</label>
        <input type="number" name="content_order" value="<?= htmlspecialchars($content['content_order']) ?>" min="1" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
    </div>
    <div id="textContentField" class="<?= $content['content_type']==='text'?'':'hidden' ?>">
        <label class="block text-gray-700 font-medium mb-2">Content</label>
        <textarea name="text_content" rows="5" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"><?= htmlspecialchars_decode($content['description']) ?></textarea>
    </div>
    <div id="videoContentField" class="<?= $content['content_type']==='video'?'':'hidden' ?>">
        <label class="block text-gray-700 font-medium mb-2">YouTube Video URL</label>
        <input type="url" name="video_url" value="<?= htmlspecialchars($content['content_url']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
    </div>
    <div id="documentContentField" class="<?= $content['content_type']==='document'?'':'hidden' ?>">
        <label class="block text-gray-700 font-medium mb-2">Upload Document (leave blank to keep current)</label>
        <input type="file" name="document_file" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" accept=".pdf,.doc,.docx,.ppt,.pptx">
        <?php if (!empty($content['content_file'])): ?>
            <div class="mt-2"><a href="../uploads/materials/<?= htmlspecialchars($content['content_file']) ?>" target="_blank" class="text-blue-600 underline">Current Document</a></div>
        <?php endif; ?>
    </div>
    <div class="flex justify-end gap-2 mt-6">
        <button type="submit" name="edit_content" class="bg-secondary text-primary px-6 py-2 rounded-lg hover:bg-opacity-90 transition border border-primary">Update</button>
        <a href="view_module.php?id=<?= $content['module_id'] ?>" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">Cancel</a>
        <button type="submit" name="edit_content" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">Save Changes</button>
    </div>
</form>
<script>
function showRelevantFields() {
    var type = document.getElementById('contentType').value;
    document.getElementById('textContentField').classList.add('hidden');
    document.getElementById('videoContentField').classList.add('hidden');
    document.getElementById('documentContentField').classList.add('hidden');
    if (type === 'text') {
        document.getElementById('textContentField').classList.remove('hidden');
    } else if (type === 'video') {
        document.getElementById('videoContentField').classList.remove('hidden');
    } else if (type === 'document') {
        document.getElementById('documentContentField').classList.remove('hidden');
    }
}
document.addEventListener('DOMContentLoaded', showRelevantFields);
</script>
        </div>
    </main>
</body>
</html>
