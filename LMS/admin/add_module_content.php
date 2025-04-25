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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_content'])) {
    $title = $_POST['title'];
    $content_type = $_POST['type'];
    if ($content_type === 'Other' && !empty($_POST['custom_type'])) {
        $content_type = trim($_POST['custom_type']);
    }
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $content_url = null;
    $content_file = null;
    $content_order = isset($_POST['content_order']) ? intval($_POST['content_order']) : 1;
    $type_tag = isset($_POST['type_tag']) ? $_POST['type_tag'] : null;
    $quiz_link = isset($_POST['quiz_link']) ? $_POST['quiz_link'] : null;
    $resource_links = isset($_POST['resource_links']) ? $_POST['resource_links'] : null;
    $assignment_links = isset($_POST['assignment_links']) ? $_POST['assignment_links'] : null;

    // Handle file upload for video/document
    if ((strtolower($content_type) === 'video' || strtolower($content_type) === 'document') && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = [
            'video' => ['video/mp4', 'video/webm', 'video/ogg'],
            'document' => ['application/pdf']
        ];
        $type_key = strtolower($content_type);
        $filetype = mime_content_type($_FILES['file_upload']['tmp_name']);
        if (in_array($filetype, $allowed_types[$type_key])) {
            $ext = pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION);
            $upload_dir = '../uploads/module_content/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid('content_', true) . '.' . $ext;
            $target_path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
                $content_file = $target_path;
            } else {
                $_SESSION['error_msg'] = 'File upload failed.';
                header("Location: add_module_content.php?module_id=$module_id");
                exit;
            }
        } else {
            $_SESSION['error_msg'] = 'Invalid file type.';
            header("Location: add_module_content.php?module_id=$module_id");
            exit;
        }
    } elseif (!empty($_POST['url'])) {
        $content_url = trim($_POST['url']);
    }

    $insert_sql = "INSERT INTO module_content (module_id, content_type, title, description, content_url, content_file, content_order, type_tag, quiz_link, resource_links, assignment_links) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->execute([$module_id, $content_type, $title, $description, $content_url, $content_file, $content_order, $type_tag, $quiz_link, $resource_links, $assignment_links]);
    $_SESSION['success_msg'] = 'Content item added.';
    header("Location: manage_module_content.php?module_id=$module_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Module Content - <?= htmlspecialchars($module['module_name']) ?></title>
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <link rel="stylesheet" href="../css/design-system.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-primary mb-4">Add Content to: <?= htmlspecialchars($module['module_name']) ?></h1>
    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4" enctype="multipart/form-data">
        <div>
            <label class="block font-semibold mb-1" for="title">Title</label>
            <input class="input input-bordered w-full" type="text" id="title" name="title" required>
        </div>
        <div>
            <label class="block font-semibold mb-1" for="description">Description</label>
            <textarea class="input input-bordered w-full" id="description" name="description" rows="4"></textarea>
        </div>
        <div>
            <label class="block font-semibold mb-1" for="type">Content Type</label>
            <select class="input input-bordered w-full" id="type" name="type" required onchange="toggleTypeFields(this)">
                <option value="video">Video (upload or link)</option>
                <option value="document">Document (PDF upload)</option>
                <option value="assignment">Assignment</option>
                <option value="quiz">Quiz</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1" for="type_tag">Type Tag</label>
            <select class="input input-bordered w-full" id="type_tag" name="type_tag">
                <option value="core">Core</option>
                <option value="supplemental">Supplemental</option>
                <option value="project">Project</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1" for="quiz_link">Quiz Link (if any)</label>
            <input class="input input-bordered w-full" type="url" id="quiz_link" name="quiz_link" placeholder="Paste quiz link here">
        </div>
        <div>
            <label class="block font-semibold mb-1" for="resource_links">Resource Links (comma-separated URLs)</label>
            <input class="input input-bordered w-full" type="text" id="resource_links" name="resource_links" placeholder="e.g. https://developer.mozilla.org, https://css-tricks.com">
        </div>
        <div>
            <label class="block font-semibold mb-1" for="assignment_links">Assignment Links (comma-separated URLs)</label>
            <input class="input input-bordered w-full" type="text" id="assignment_links" name="assignment_links" placeholder="e.g. https://github.com/example/project1">
        </div>
        <div id="fileUploadDiv" style="display:none; margin-top:0.5rem;">
            <label class="block font-semibold mb-1" for="file_upload">Upload File</label>
            <input type="file" id="file_upload" name="file_upload" accept="application/pdf,video/mp4,video/webm,video/ogg">
            <span class="text-sm text-gray-500">PDF for documents, video files for videos.</span>
        </div>
        <div id="urlDiv" style="display:none; margin-top:0.5rem;">
            <label class="block font-semibold mb-1" for="url">Video Link (YouTube/Vimeo)</label>
            <input class="input input-bordered w-full" type="url" id="url" name="url" placeholder="Paste a video link">
        </div>
        <script>
        function toggleTypeFields(select) {
            var fileDiv = document.getElementById('fileUploadDiv');
            var urlDiv = document.getElementById('urlDiv');
            var type = select.value;
            if (type === 'video') {
                fileDiv.style.display = 'block';
                document.getElementById('file_upload').required = false;
                urlDiv.style.display = 'block';
                document.getElementById('url').required = false;
            } else if (type === 'document') {
                fileDiv.style.display = 'block';
                document.getElementById('file_upload').required = false;
                urlDiv.style.display = 'none';
                document.getElementById('url').required = false;
            } else {
                fileDiv.style.display = 'none';
                document.getElementById('file_upload').required = false;
                urlDiv.style.display = 'none';
                document.getElementById('url').required = false;
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleTypeFields(document.getElementById('type'));
        });
        </script>
        </div>
        <div>
            <label class="block font-semibold mb-1" for="content_order">Order</label>
            <input class="input input-bordered w-32" type="number" id="content_order" name="content_order" min="1" value="1" required>
        </div>
        <button class="btn btn-success" type="submit" name="add_content">Add Content</button>
        <a href="manage_module_content.php?module_id=<?= $module_id ?>" class="btn btn-secondary ml-4">Cancel</a>
    </form>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>
