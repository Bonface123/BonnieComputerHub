<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Fetch all assignments for this instructor
$query = $pdo->prepare("
    SELECT 
        a.*,
        c.course_name,
        m.module_name,
        (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE a.instructor_id = ?
    ORDER BY a.created_at DESC
");
$query->execute([$instructor_id]);
$assignments = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch modules for dropdown
$modules_query = $pdo->prepare("
    SELECT 
        m.id, 
        m.module_name, 
        c.course_name
    FROM course_modules m
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.course_name, m.module_name
");
$modules_query->execute();
$modules = $modules_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
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
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="instructor_dashboard.php" class="text-xl font-bold text-secondary">Manage Assignments</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
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
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-primary mb-2">Manage Assignments</h1>
                    <p class="text-gray-600">Create and manage your course assignments</p>
                </div>
                <button onclick="openModal()" 
                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                    <i class="fas fa-plus mr-2"></i>Add Assignment
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <?= $_SESSION['success_msg'] ?>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <?= $_SESSION['error_msg'] ?>
                <?php unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Assignment List -->
        <?php if (empty($assignments)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-tasks text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Assignments Yet</h3>
                <p class="text-gray-500 mb-6">Click the "Add Assignment" button to create your first assignment.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-primary mb-2">
                                    <?= htmlspecialchars($assignment['title']) ?>
                                </h3>
                                <div class="flex items-center text-sm text-gray-600 mb-4">
                                    <span class="mr-4">
                                        <i class="fas fa-book mr-2"></i>
                                        <?= htmlspecialchars($assignment['course_name']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-layer-group mr-2"></i>
                                        <?= htmlspecialchars($assignment['module_name']) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-4">
                                    <?= $assignment['description'] ?>
                                </p>
                                <div class="flex items-center text-sm text-gray-500 space-x-4">
                                    <span>
                                        <i class="fas fa-calendar mr-2"></i>
                                        Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-star mr-2"></i>
                                        <?= $assignment['marks'] ?> marks
                                    </span>
                                    <span>
                                        <i class="fas fa-file-alt mr-2"></i>
                                        <?= $assignment['submission_count'] ?> submissions
                                    </span>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="view_submissions.php?id=<?= $assignment['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-users text-xl"></i>
                                </a>
                                <a href="edit_assignment.php?id=<?= $assignment['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit text-xl"></i>
                                </a>
                                <button onclick="deleteAssignment(<?= $assignment['id'] ?>)"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Add Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg max-w-3xl mx-auto mt-10 p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-primary">Add New Assignment</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="assignmentForm" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Module</label>
                        <select name="module_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">Select Module</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?= $module['id'] ?>">
                                    <?= htmlspecialchars($module['course_name']) ?> - 
                                    <?= htmlspecialchars($module['module_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Title</label>
                        <input type="text" name="title" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea name="description" id="description" class="summernote"></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Instructions</label>
                    <textarea name="instructions" id="instructions" class="summernote"></textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Due Date</label>
                        <input type="datetime-local" name="due_date" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Marks</label>
                        <input type="number" name="marks" required min="0" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeModal()"
                            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                        Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Summernote
        $(document).ready(function() {
            $('.summernote').summernote({
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
                        // Update the hidden textarea value
                        $(this).val(contents);
                    }
                }
            });
        });

        // Modal functions
        function openModal() {
            document.getElementById('assignmentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('assignmentModal').classList.add('hidden');
        }

        // Handle assignment deletion
        function deleteAssignment(id) {
            if (confirm('Are you sure you want to delete this assignment?')) {
                window.location.href = `delete_assignment.php?id=${id}`;
            }
        }

        // Form submission
        document.getElementById('assignmentForm').onsubmit = function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Get Summernote content and update hidden textareas
            const description = $('#description').summernote('code');
            const instructions = $('#instructions').summernote('code');
            
            // Remove required attribute from hidden textareas
            $('#description, #instructions').removeAttr('required');
            
            // Update FormData with Summernote content
            formData.set('description', description);
            formData.set('instructions', instructions);
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            submitBtn.disabled = true;

            fetch('add_assignment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error creating assignment');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating assignment. Please try again.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        };
    </script>
</body>
</html>
