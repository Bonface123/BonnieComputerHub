<?php
session_start();
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_blogs.php');
    exit;
}

$id = intval($_GET['id']);
$error = '';

// Fetch the blog post
$stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
$stmt->execute([$id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) {
    $_SESSION['error_msg'] = 'Blog post not found.';
    header('Location: manage_blogs.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $imageFileName = $blog['image'] ?? null;
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imgDir = '../../assets/images/blogs/';
        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $imageFileName = uniqid('blog_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $imgDir . $imageFileName);
        }
    }
    if ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } else {
        $update = $pdo->prepare('UPDATE blogs SET title = ?, content = ?, image = ? WHERE id = ?');
        if ($update->execute([$title, $content, $imageFileName, $id])) {
            $_SESSION['success_msg'] = 'Blog post updated successfully!';
            header('Location: manage_blogs.php');
            exit;
        } else {
            $error = 'Failed to update blog post.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog | Admin - Bonnie Computer Hub</title>
    <link href="../../assets/css/tailwind.min.css" rel="stylesheet">
    <link href="../../assets/css/design-system.css" rel="stylesheet">
    <link href="../../assets/css/components.css" rel="stylesheet">
    <link href="../../assets/css/utilities.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap">
    <script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50 font-inter min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>
    <main class="flex-1 px-4 py-8 max-w-2xl mx-auto w-full">
        <h1 class="text-2xl font-bold text-primary mb-6">Edit Blog Post</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-8 space-y-6 border border-gray-100">
            <div>
                <label for="title" class="block text-gray-700 font-medium mb-2">Title</label>
                <input type="text" name="title" id="title" required value="<?= htmlspecialchars($blog['title']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Enter blog title">
            </div>
            <div>
                <label for="content" class="block text-gray-700 font-medium mb-2">Content</label>
                <textarea name="content" id="content" rows="10" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent summernote" placeholder="Write your blog content..."><?= htmlspecialchars($blog['content']) ?></textarea>
            </div>
            <div>
                <label for="image" class="block text-gray-700 font-medium mb-2">Blog Image (optional)</label>
                <input type="file" name="image" id="image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-600 file:text-white hover:file:bg-yellow-700 transition" />
                <?php if (!empty($blog['image'])): ?>
                <div class="mt-2">
                    <img src="../../assets/images/blogs/<?= htmlspecialchars($blog['image']) ?>" alt="Current Blog Image" class="h-32 rounded shadow border object-cover">
                </div>
                <?php endif; ?>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-2 rounded-lg font-bold shadow flex items-center gap-2 transition-all duration-200 focus:ring-2 focus:ring-yellow-400 focus:outline-none">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
        <!-- Summernote for Rich Text Editing -->
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#content').summernote({
                    placeholder: 'Write your blog content...',
                    tabsize: 2,
                    height: 300,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            });
        </script>
        <div class="mt-6">
            <a href="manage_blogs.php" class="text-primary hover:underline">&larr; Back to Blog Management</a>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
