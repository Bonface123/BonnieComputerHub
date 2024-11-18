<?php
session_start();
require_once '../includes/db_connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch assignment details
$assignment_query = $pdo->prepare("
    SELECT a.*, m.title as module_title 
    FROM assignments a
    JOIN modules m ON a.module_id = m.id
    WHERE a.id = ?
");
$assignment_query->execute([$assignment_id]);
$assignment = $assignment_query->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    $_SESSION['error'] = "Assignment not found.";
    header('Location: dashboard.php');
    exit;
}

// Check if assignment is already submitted
$submission_query = $pdo->prepare("
    SELECT * FROM student_assignments 
    WHERE user_id = ? AND assignment_id = ?
");
$submission_query->execute([$user_id, $assignment_id]);
$existing_submission = $submission_query->fetch(PDO::FETCH_ASSOC);

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_text = $_POST['submission_text'];
    $file = isset($_FILES['assignment_file']) ? $_FILES['assignment_file'] : null;

    // Handle file upload
    $file_path = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/assignments/';
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('assignment_') . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            $error = "Failed to upload file.";
        }
    }

    if (!isset($error)) {
        if ($existing_submission) {
            // Update existing submission
            $update = $pdo->prepare("
                UPDATE student_assignments 
                SET submission_text = ?,
                    file_path = COALESCE(?, file_path),
                    status = 'submitted',
                    submitted_at = NOW()
                WHERE user_id = ? AND assignment_id = ?
            ");
            $update->execute([$submission_text, $file_path, $user_id, $assignment_id]);
        } else {
            // Create new submission
            $insert = $pdo->prepare("
                INSERT INTO student_assignments 
                (user_id, assignment_id, submission_text, file_path, status, submitted_at)
                VALUES (?, ?, ?, ?, 'submitted', NOW())
            ");
            $insert->execute([$user_id, $assignment_id, $submission_text, $file_path]);
        }

        // Update module progress
        updateModuleProgress($pdo, $user_id, $assignment['module_id']);

        $success = "Assignment submitted successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/2kyop6caxkyq6jfssj4dvckadnr8lw2jfg1fclpkrc19kbig/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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

        tinymce.init({
            selector: '#submission_text',
            height: 400,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
        });
    </script>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-secondary hover:text-white transition">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Assignment Details -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h1 class="text-3xl font-bold text-primary mb-4">
                    <?= htmlspecialchars($assignment['title']) ?>
                </h1>
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">
                        <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                    </p>
                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                        <span>
                            <i class="fas fa-book mr-2"></i>
                            <?= htmlspecialchars($assignment['module_title']) ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar mr-2"></i>
                            Due: <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                        </span>
                        <span>
                            <i class="fas fa-weight-hanging mr-2"></i>
                            Weight: <?= $assignment['weight'] ?>%
                        </span>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Submission Form -->
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Assignment Instructions -->
                    <div class="bg-gray-50 p-6 rounded-xl mb-6">
                        <h3 class="text-xl font-bold text-primary mb-4">Assignment Instructions</h3>
                        <div class="prose max-w-none">
                            <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                        </div>
                    </div>

                    <!-- Editor Section -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="mb-6">
                            <label for="submission_text" class="block text-gray-700 font-medium mb-2">
                                Your Submission
                            </label>
                            <textarea id="submission_text" name="submission_text" 
                                      class="w-full"><?= $existing_submission ? htmlspecialchars($existing_submission['submission_text']) : '' ?></textarea>
                        </div>

                        <!-- File Upload Section -->
                        <div class="border-t pt-6">
                            <label class="block text-gray-700 font-medium mb-2">
                                Attachments
                            </label>
                            <div class="flex items-center space-x-4">
                                <input type="file" id="assignment_file" name="assignment_file"
                                       class="hidden" accept=".pdf,.doc,.docx,.zip">
                                <label for="assignment_file" 
                                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer transition flex items-center">
                                    <i class="fas fa-paperclip mr-2"></i>
                                    Add Files
                                </label>
                                <?php if ($existing_submission && $existing_submission['file_path']): ?>
                                    <div class="flex items-center bg-blue-50 px-4 py-2 rounded-lg">
                                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                        <span class="text-sm text-gray-600">
                                            <?= basename($existing_submission['file_path']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Supported formats: PDF, DOC, DOCX, ZIP (Max size: 10MB)
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" 
                                    class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300 flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i>
                                <?= $existing_submission ? 'Update Submission' : 'Submit Assignment' ?>
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($existing_submission): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                        <h3 class="text-xl font-bold text-primary mb-4">Submission Status</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-600 mb-2">Status</p>
                                <span class="px-3 py-1 rounded-full text-sm inline-flex items-center
                                    <?= $existing_submission['status'] === 'graded' ? 'bg-green-100 text-green-800' : 
                                        ($existing_submission['status'] === 'submitted' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-gray-100 text-gray-800') ?>">
                                    <i class="fas fa-circle text-xs mr-2"></i>
                                    <?= ucfirst($existing_submission['status']) ?>
                                </span>
                            </div>
                            <div>
                                <p class="text-gray-600 mb-2">Submitted on</p>
                                <p class="text-gray-800">
                                    <?= date('M d, Y h:i A', strtotime($existing_submission['submitted_at'])) ?>
                                </p>
                            </div>
                            <?php if ($existing_submission['status'] === 'graded'): ?>
                                <div>
                                    <p class="text-gray-600 mb-2">Score</p>
                                    <p class="text-2xl font-bold text-primary">
                                        <?= $existing_submission['score'] ?>%
                                    </p>
                                </div>
                                <?php if ($existing_submission['feedback']): ?>
                                    <div class="col-span-2">
                                        <p class="text-gray-600 mb-2">Instructor Feedback</p>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <?= nl2br(htmlspecialchars($existing_submission['feedback'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 mb-4">
                &copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.
            </p>
            <p class="text-secondary italic">
                "I can do all things through Christ who strengthens me." - Philippians 4:13
            </p>
        </div>
    </footer>
</body>

</html>