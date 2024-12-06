<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $createdBy = $_SESSION['user_id'];

    try {
        $insert_sql = "INSERT INTO courses (course_name, description, created_by) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$name, $description, $createdBy]);
        $_SESSION['success_msg'] = "Course added successfully.";
        header("Location: manage_courses.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding course: " . $e->getMessage();
    }
}

// Fetch all courses with creator information
$sql = "SELECT c.*, u.name as creator_name 
        FROM courses c 
        LEFT JOIN users u ON c.created_by = u.id 
        ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <p class="text-gray-300 text-sm">Admin Dashboard</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="admin_dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2">Manage Courses</h1>
            <p class="text-gray-600">Create and manage courses in the learning management system</p>
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

        <!-- Create Course Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-primary mb-6">Create New Course</h2>
            <form action="" method="POST" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2" for="name">Course Name</label>
                        <input type="text" name="name" id="name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="Enter course name">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2" for="description">Course Description</label>
                        <div id="description"></div>
                        <input type="hidden" name="description" id="description-hidden">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="add_course" 
                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition duration-300">
                        Create Course
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">Existing Courses</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($course['course_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-h-20 overflow-y-auto">
                                        <?= $course['description'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($course['creator_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($course['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="manage_modules.php?course_id=<?= $course['id'] ?>" 
                                       class="text-green-600 hover:text-green-800 mr-3">
                                        <i class="fas fa-folder-open"></i> Modules
                                    </a>
                                    <a href="edit_course.php?id=<?= $course['id'] ?>" 
                                       class="text-primary hover:text-opacity-80 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_course.php?id=<?= $course['id'] ?>" 
                                       onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')"
                                       class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Bonnie Computer Hub. All rights reserved.</p>
                <div class="mt-2">
                    <a href="#" class="text-secondary hover:text-opacity-80 mx-2">Privacy Policy</a>
                    <a href="#" class="text-secondary hover:text-opacity-80 mx-2">Terms of Service</a>
                    <a href="#" class="text-secondary hover:text-opacity-80 mx-2">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        $(document).ready(function() {
            // Initialize Summernote
            $('#description').summernote({
                placeholder: 'Write course description here...',
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
                        $('#description-hidden').val(contents);
                    }
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                var description = $('#description').summernote('code');
                if (description.trim() === '' || description.trim() === '<p><br></p>') {
                    alert('Please provide a course description');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
