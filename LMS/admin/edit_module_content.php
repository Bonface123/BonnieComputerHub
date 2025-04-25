<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid content ID.";
    exit;
}
$content_id = intval($_GET['id']);

// Fetch content info
$content_sql = "SELECT * FROM module_content WHERE id = ?";
$content_stmt = $pdo->prepare($content_sql);
$content_stmt->execute([$content_id]);
$content = $content_stmt->fetch(PDO::FETCH_ASSOC);
if (!$content) {
    echo "Content item not found.";
    exit;
}
$module_id = $content['module_id'];

// Fetch module info
$module_sql = "SELECT * FROM course_modules WHERE id = ?";
$module_stmt = $pdo->prepare($module_sql);
$module_stmt->execute([$module_id]);
$module = $module_stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {
    $title = $_POST['title'];
    $content_type = $_POST['type'];
    $description = $_POST['description'];
    $content_order = $_POST['content_order'];
    $content_url = isset($_POST['url']) ? trim($_POST['url']) : null;
    $content_file = $content['content_file'];
    // Handle file removal
    if (isset($_POST['remove_file']) && $_POST['remove_file'] == '1' && !empty($content['content_file'])) {
        if (file_exists($content['content_file'])) {
            unlink($content['content_file']);
        }
        $content_file = null;
    }
    // Handle file upload for video/document
    if ((($content_type === 'video' || $content_type === 'document') && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK)) {
        $allowed_types = [
            'video' => ['video/mp4', 'video/webm', 'video/ogg'],
            'document' => ['application/pdf']
        ];
        $type_key = $content_type;
        $filetype = mime_content_type($_FILES['file_upload']['tmp_name']);
        if (in_array($filetype, $allowed_types[$type_key])) {
            $ext = pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION);
            $upload_dir = '../uploads/module_content/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid('content_', true) . '.' . $ext;
            $target_path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
                $content_file = $target_path;
            }
        }
    }
    // Only keep URL for video type
    if ($content_type !== 'video') {
        $content_url = null;
    }
    $update_sql = "UPDATE module_content SET title = ?, content_type = ?, description = ?, content_order = ?, content_url = ?, content_file = ? WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$title, $content_type, $description, $content_order, $content_url, $content_file, $content_id]);
    $_SESSION['success_msg'] = 'Content item updated.';
    header("Location: manage_module_content.php?module_id=$module_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Module Content - <?= htmlspecialchars($module['module_name']) ?></title>
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php include '../includes/header.php'; ?>
<main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-gradient-to-r from-primary via-blue-100 to-white rounded-xl shadow-lg p-6 mb-8 flex items-center gap-4 border border-blue-200">
            <div class="flex-shrink-0 bg-white rounded-full p-4 shadow">
                <i class="fas fa-layer-group text-primary text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Edit Content in:</h1>
                <div class="text-xl md:text-2xl font-bold text-primary"> <?= htmlspecialchars($module['module_name']) ?> </div>
            </div>
        </div>
        <a href="manage_modules.php?course_id=<?= htmlspecialchars($module['course_id']) ?>" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
            <i class="fas fa-arrow-left"></i> Go Back to Modules
        </a>
        <div class="bg-white rounded-xl shadow p-8 border border-gray-100">
            <h2 class="text-lg font-semibold text-primary mb-4 flex items-center gap-2"><i class="fas fa-pen-nib"></i> Edit Module Content</h2>
            <form method="POST" class="space-y-6" enctype="multipart/form-data" autocomplete="off">
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="title">Title</label>
                <input class="input input-bordered w-full focus:ring-2 focus:ring-primary focus:outline-none" type="text" id="title" name="title" value="<?= htmlspecialchars($content['title']) ?>" required>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="type">Type</label>
                <select class="input input-bordered w-full focus:ring-2 focus:ring-primary focus:outline-none" id="type" name="type" required>
                    <option value="video" <?= $content['content_type']==='video'?'selected':'' ?>>Video</option>
                    <option value="document" <?= $content['content_type']==='document'?'selected':'' ?>>Document</option>
                    <option value="assignment" <?= $content['content_type']==='assignment'?'selected':'' ?>>Assignment</option>
                    <option value="quiz" <?= $content['content_type']==='quiz'?'selected':'' ?>>Quiz</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="description">Description</label>
                <textarea class="input input-bordered w-full focus:ring-2 focus:ring-primary focus:outline-none" id="description" name="description" rows="6" required><?= htmlspecialchars($content['description']) ?></textarea>
            </div>
            <div id="fileUploadDiv" style="display:none; margin-top:0.5rem;">
                <label class="block font-semibold mb-2 text-gray-700" for="file_upload">Upload File</label>
                <?php if (!empty($content['content_file'])): ?>
                    <div class="flex items-center gap-2 mb-2 p-2 bg-gray-50 rounded">
                        <i class="fas fa-file-alt text-primary"></i>
                        <a href="<?= htmlspecialchars($content['content_file']) ?>" target="_blank" class="text-primary underline font-semibold"><?= htmlspecialchars(basename($content['content_file'])) ?></a>
                        <input type="checkbox" id="remove_file" name="remove_file" value="1" class="ml-2">
                        <label for="remove_file" class="text-xs text-gray-600">Remove file</label>
                    </div>
                <?php endif; ?>
                <input type="file" id="file_upload" name="file_upload" accept="application/pdf,video/mp4,video/webm,video/ogg" class="block w-full text-gray-700 border border-gray-300 rounded px-3 py-2 mt-1 focus:ring-2 focus:ring-primary focus:outline-none">
                <span class="text-xs text-gray-500">PDF for documents, video files for videos.</span>
            </div>
            <div id="urlDiv" style="display:none; margin-top:0.5rem;">
                <label class="block font-semibold mb-2 text-gray-700" for="url">Video Link (YouTube/Vimeo)</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-400"><i class="fas fa-link"></i></span>
                    <input class="input input-bordered w-full pl-10" type="url" id="url" name="url" placeholder="Paste a video link" value="<?= htmlspecialchars($content['content_url']) ?>">
                </div>
            </div>
            <script>
            function toggleTypeFields(select) {
                var fileDiv = document.getElementById('fileUploadDiv');
                var urlDiv = document.getElementById('urlDiv');
                var type = select.value;
                if (type === 'video') {
                    fileDiv.style.display = 'block';
                    urlDiv.style.display = 'block';
                } else if (type === 'document') {
                    fileDiv.style.display = 'block';
                    urlDiv.style.display = 'none';
                } else {
                    fileDiv.style.display = 'none';
                    urlDiv.style.display = 'none';
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                toggleTypeFields(document.getElementById('type'));
                document.getElementById('type').addEventListener('change', function(){toggleTypeFields(this);});
            });
            </script>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="content_order">Order</label>
                <input class="input input-bordered w-32 focus:ring-2 focus:ring-primary focus:outline-none" type="number" id="content_order" name="content_order" value="<?= htmlspecialchars($content['content_order']) ?>" min="1" required>
            </div>
            <div class="flex items-center space-x-4 mt-8">
    <button class="btn bg-primary text-white px-6 py-2 font-semibold rounded-lg shadow-md hover:bg-primary-dark transition flex items-center gap-2" type="submit" name="update_content">
        <i class="fas fa-save"></i> Update Content
    </button>
    <a href="manage_module_content.php?module_id=<?= $module_id ?>" class="btn btn-secondary px-6 py-2 text-gray-700 font-semibold rounded-lg shadow-md hover:bg-gray-200 transition flex items-center gap-2">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>
        </form>
    </div>
</div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
