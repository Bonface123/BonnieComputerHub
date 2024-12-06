<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Fetch course details
$course = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$course->execute([$course_id]);
$course_details = $course->fetch(PDO::FETCH_ASSOC);

if (!$course_details) {
    $_SESSION['error_msg'] = "Course not found.";
    header('Location: manage_courses.php');
    exit;
}

// Handle module addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    $module_name = $_POST['module_name'];
    $module_description = $_POST['module_description'];
    $module_order = $_POST['module_order'];

    try {
        $stmt = $pdo->prepare("INSERT INTO course_modules (course_id, module_name, module_description, module_order) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$course_id, $module_name, $module_description, $module_order]);
        $_SESSION['success_msg'] = "Module added successfully.";
        header("Location: manage_modules.php?course_id=" . $course_id);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding module: " . $e->getMessage();
    }
}

// Fetch existing modules for this course
$modules = $pdo->prepare("
    SELECT * FROM course_modules 
    WHERE course_id = ? 
    ORDER BY module_order ASC
");
$modules->execute([$course_id]);
$modules = $modules->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules - <?= htmlspecialchars($course_details['course_name']) ?></title>
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
                        <p class="text-gray-300 text-sm">Course Modules</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="manage_courses.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Courses
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2">
                Manage Modules: <?= htmlspecialchars($course_details['course_name']) ?>
            </h1>
            <p class="text-gray-600"><?= $course_details['description'] ?></p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <?= $_SESSION['success_msg'] ?>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <?= $_SESSION['error_msg'] ?>
                <?php unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Add Module Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-primary mb-6">Add New Module</h2>
            <form action="" method="POST" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="module_name">Module Name</label>
                        <input type="text" name="module_name" id="module_name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="Enter module name">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="module_order">Module Order</label>
                        <input type="number" name="module_order" id="module_order" required min="1"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="Enter module order">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2" for="module_description">Module Description</label>
                        <div id="module_description"></div>
                        <input type="hidden" name="module_description" id="module_description_hidden">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="add_module" 
                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition duration-300">
                        Add Module
                    </button>
                </div>
            </form>
        </div>

        <!-- Modules List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">Course Modules</h2>
            <?php if ($modules): ?>
                <div class="space-y-4">
                    <?php foreach ($modules as $module): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-primary">
                                        <?= htmlspecialchars($module['module_name']) ?>
                                    </h3>
                                    <p class="text-gray-600 mt-1">
                                        <?= $module['module_description'] ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Order: <?= $module['module_order'] ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="edit_module.php?id=<?= $module['id'] ?>" 
                                       class="text-primary hover:text-opacity-80">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_module_content.php?module_id=<?= $module['id'] ?>" 
                                       class="text-green-600 hover:text-green-800">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <a href="delete_module.php?id=<?= $module['id'] ?>" 
                                       onclick="return confirm('Are you sure you want to delete this module?')"
                                       class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 py-4">No modules added yet.</p>
            <?php endif; ?>
        </div>
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
        $(document).ready(function() {
            $('#module_description').summernote({
                placeholder: 'Write module description here...',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onChange: function(contents) {
                        $('#module_description_hidden').val(contents);
                    }
                }
            });
        });
    </script>
</body>
</html> 