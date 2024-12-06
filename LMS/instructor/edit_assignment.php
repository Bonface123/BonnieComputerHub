<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch assignment with permission check
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        c.course_name,
        m.module_name,
        m.id as module_id
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE a.id = ? AND a.instructor_id = ?
");
$stmt->execute([$assignment_id, $instructor_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

// If assignment not found or doesn't belong to instructor
if (!$assignment) {
    $_SESSION['error_msg'] = "Assignment not found or you don't have permission to edit it.";
    header('Location: manage_assignments.php');
    exit;
}

// Fetch all modules for dropdown
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

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $instructions = $_POST['instructions'];
        $due_date = $_POST['due_date'];
        $marks = $_POST['marks'];
        $module_id = $_POST['module_id'];

        $stmt = $pdo->prepare("
            UPDATE assignments 
            SET title = ?, 
                description = ?, 
                instructions = ?, 
                due_date = ?, 
                marks = ?,
                module_id = ?
            WHERE id = ? AND instructor_id = ?
        ");
        
        $result = $stmt->execute([
            $title,
            $description,
            $instructions,
            $due_date,
            $marks,
            $module_id,
            $assignment_id,
            $instructor_id
        ]);

        if ($result) {
            $_SESSION['success_msg'] = "Assignment updated successfully!";
            header('Location: manage_assignments.php');
            exit;
        } else {
            throw new Exception("Failed to update assignment");
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error updating assignment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment - BCH Learning</title>
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
                        <a href="instructor_dashboard.php" class="text-xl font-bold text-secondary">Edit Assignment</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="manage_assignments.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Assignments
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Page Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h1 class="text-2xl font-bold text-primary mb-2">Edit Assignment</h1>
                <p class="text-gray-600">
                    <?= htmlspecialchars($assignment['course_name']) ?> - 
                    <?= htmlspecialchars($assignment['module_name']) ?>
                </p>
            </div>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <?= $_SESSION['error_msg'] ?>
                    <?php unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Module</label>
                            <select name="module_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?= $module['id'] ?>" 
                                            <?= $module['id'] == $assignment['module_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($module['course_name']) ?> - 
                                        <?= htmlspecialchars($module['module_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Title</label>
                            <input type="text" name="title" required 
                                   value="<?= htmlspecialchars($assignment['title']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea name="description" id="description" class="summernote"><?= $assignment['description'] ?></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Instructions</label>
                        <textarea name="instructions" id="instructions" class="summernote"><?= $assignment['instructions'] ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Due Date</label>
                            <input type="datetime-local" name="due_date" required 
                                   value="<?= date('Y-m-d\TH:i', strtotime($assignment['due_date'])) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Marks</label>
                            <input type="number" name="marks" required min="0" 
                                   value="<?= htmlspecialchars($assignment['marks']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="manage_assignments.php" 
                           class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                            Update Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
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
                ]
            });
        });
    </script>
</body>
</html>
