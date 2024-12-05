<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Fetch all courses
$courses = $pdo->query("SELECT id, course_name, description FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Handle module addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    $course_id = $_POST['course_id'];
    $module_name = $_POST['module_name'];
    $module_description = $_POST['module_description'];
    $module_order = $_POST['module_order'];

    try {
        $stmt = $pdo->prepare("INSERT INTO course_modules (course_id, module_name, module_description, module_order) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$course_id, $module_name, $module_description, $module_order]);
        $_SESSION['success_msg'] = "Module added successfully.";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding module: " . $e->getMessage();
    }
    header("Location: manage_courses.php");
    exit;
}

// Handle content addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_content'])) {
    $module_id = $_POST['module_id'];
    $content_type = $_POST['content_type'];
    $title = $_POST['content_title'];
    $description = $_POST['content_description'];
    $content_order = $_POST['content_order'];

    try {
        $content_url = null;
        $content_file = null;

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
                $description = $_POST['text_content']; // Rich text content from Summernote
                break;
        }

        $stmt = $pdo->prepare("INSERT INTO module_content (
            module_id, content_type, title, description, content_url, content_file, content_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $module_id,
            $content_type,
            $title,
            $description,
            $content_url,
            $content_file,
            $content_order
        ]);

        $_SESSION['success_msg'] = "Content added successfully.";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding content: " . $e->getMessage();
    }
    header("Location: manage_courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',
                        secondary: '#FFD700',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Course Management</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="instructor_dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Course List -->
        <?php foreach ($courses as $course): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary"><?= htmlspecialchars($course['course_name']) ?></h2>
                    <button onclick="toggleModuleForm(<?= $course['id'] ?>)" 
                            class="bg-secondary text-primary px-4 py-2 rounded-lg hover:bg-opacity-90 transition">
                        <i class="fas fa-plus mr-2"></i>Add Module
                    </button>
                </div>

                <!-- Add Module Form -->
                <div id="moduleForm<?= $course['id'] ?>" class="hidden bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-primary mb-4">Add New Module</h3>
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Module Name</label>
                                <input type="text" name="module_name" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Module Order</label>
                                <input type="number" name="module_order" required min="1"
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Module Description</label>
                            <textarea name="module_description" required rows="4"
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="add_module" 
                                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                                Add Module
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Modules List -->
                <?php
                $modules = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order");
                $modules->execute([$course['id']]);
                $modules = $modules->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if ($modules): ?>
                    <div class="space-y-4">
                        <?php foreach ($modules as $module): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-primary">
                                        <?= htmlspecialchars($module['module_name']) ?>
                                    </h3>
                                    <button onclick="toggleContentForm(<?= $module['id'] ?>)"
                                            class="text-primary hover:text-opacity-80">
                                        <i class="fas fa-plus-circle"></i> Add Content
                                    </button>
                                </div>

                                <!-- Add Content Form -->
                                <div id="contentForm<?= $module['id'] ?>" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                                    <h4 class="font-medium text-primary mb-4">Add Module Content</h4>
                                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                        
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-2">Content Type</label>
                                                <select name="content_type" onchange="toggleContentTypeFields(this)" required
                                                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                                                    <option value="">Select type...</option>
                                                    <option value="text">Text Content</option>
                                                    <option value="video">YouTube Video</option>
                                                    <option value="document">Document</option>
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-2">Content Order</label>
                                                <input type="number" name="content_order" required min="1"
                                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2">Content Title</label>
                                            <input type="text" name="content_title" required
                                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                                        </div>

                                        <!-- Text Content Field -->
                                        <div class="text-field hidden">
                                            <label class="block text-gray-700 font-medium mb-2">Content</label>
                                            <div class="summernote"></div>
                                            <input type="hidden" name="text_content" class="text-content-hidden">
                                        </div>

                                        <!-- YouTube Video URL field -->
                                        <div class="video-field hidden">
                                            <label class="block text-gray-700 font-medium mb-2">YouTube Video URL</label>
                                            <input type="url" name="video_url" 
                                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                                                   placeholder="https://www.youtube.com/watch?v=...">
                                        </div>

                                        <!-- Document upload field -->
                                        <div class="document-field hidden">
                                            <label class="block text-gray-700 font-medium mb-2">Upload Document</label>
                                            <input type="file" name="document_file" 
                                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                                                   accept=".pdf,.doc,.docx,.ppt,.pptx">
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" name="add_content"
                                                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                                                Add Content
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Module Content List -->
                                <?php
                                $contents = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order");
                                $contents->execute([$module['id']]);
                                $contents = $contents->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <?php if ($contents): ?>
                                    <div class="space-y-2">
                                        <?php foreach ($contents as $content): ?>
                                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                                <div>
                                                    <h5 class="font-medium"><?= htmlspecialchars($content['title']) ?></h5>
                                                    <?php if ($content['content_type'] === 'text'): ?>
                                                        <div class="prose max-w-none mt-2">
                                                            <?= $content['description'] ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <?php if ($content['content_type'] === 'video'): ?>
                                                        <a href="<?= htmlspecialchars($content['content_url']) ?>" 
                                                           target="_blank"
                                                           class="text-red-600 hover:text-red-800">
                                                            <i class="fab fa-youtube text-xl"></i>
                                                        </a>
                                                    <?php elseif ($content['content_type'] === 'document'): ?>
                                                        <a href="../uploads/materials/<?= htmlspecialchars($content['content_file']) ?>" 
                                                           target="_blank"
                                                           class="text-blue-600 hover:text-blue-800">
                                                            <i class="fas fa-file-alt text-xl"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-center">No content added yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center">No modules added yet.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Bonnie Computer Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleModuleForm(courseId) {
            const form = document.getElementById(`moduleForm${courseId}`);
            form.classList.toggle('hidden');
        }

        function toggleContentForm(moduleId) {
            const form = document.getElementById(`contentForm${moduleId}`);
            form.classList.toggle('hidden');
        }

        function toggleContentTypeFields(select) {
            const form = select.closest('form');
            const textField = form.querySelector('.text-field');
            const videoField = form.querySelector('.video-field');
            const documentField = form.querySelector('.document-field');
            
            // Hide all fields first
            textField.classList.add('hidden');
            videoField.classList.add('hidden');
            documentField.classList.add('hidden');
            
            // Show selected field
            switch(select.value) {
                case 'text':
                    textField.classList.remove('hidden');
                    break;
                case 'video':
                    videoField.classList.remove('hidden');
                    break;
                case 'document':
                    documentField.classList.remove('hidden');
                    break;
            }
        }

        // Initialize Summernote editors
        $(document).ready(function() {
            $('.summernote').each(function() {
                $(this).summernote({
                    placeholder: 'Write your content here...',
                    height: 200,
                    callbacks: {
                        onChange: function(contents) {
                            $(this).closest('.text-field').find('.text-content-hidden').val(contents);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
