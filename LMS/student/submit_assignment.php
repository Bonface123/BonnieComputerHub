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

// Fetch assignment details with course and module info
$assignment_query = $pdo->prepare("
    SELECT 
        a.*,
        cm.module_name,
        c.course_name
    FROM assignments a
    JOIN course_modules cm ON a.module_id = cm.id
    JOIN courses c ON cm.course_id = c.id
    WHERE a.id = ?
");
$assignment_query->execute([$assignment_id]);
$assignment = $assignment_query->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    $_SESSION['error_msg'] = "Assignment not found.";
    header('Location: dashboard.php');
    exit;
}

// Check if assignment is already submitted
$submission_query = $pdo->prepare("
    SELECT * FROM submissions 
    WHERE student_id = ? AND assignment_id = ?
");
$submission_query->execute([$user_id, $assignment_id]);
$existing_submission = $submission_query->fetch(PDO::FETCH_ASSOC);

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_text = $_POST['submission_text'] ?? '';
    $file = isset($_FILES['submission_file']) ? $_FILES['submission_file'] : null;

    try {
        // Handle file upload
        $file_path = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/submissions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('submission_') . '.' . $file_extension;
            $file_path = $file_name;

            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $file_name)) {
                throw new Exception("Failed to upload file.");
            }
        }

        require_once '../includes/send_mail.php';
        if ($existing_submission) {
            // Update existing submission
            $stmt = $pdo->prepare("
                UPDATE submissions 
                SET submission_text = ?,
                    submission_file = COALESCE(?, submission_file),
                    submitted_at = CURRENT_TIMESTAMP
                WHERE student_id = ? AND assignment_id = ?
            ");
            $stmt->execute([$submission_text, $file_path, $user_id, $assignment_id]);
        } else {
            // Create new submission
            $stmt = $pdo->prepare("
                INSERT INTO submissions 
                (assignment_id, student_id, submission_file, submission_text, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$assignment_id, $user_id, $file_path, $submission_text]);
        }
        // Fetch student and instructor info
        $user_stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $student = $user_stmt->fetch(PDO::FETCH_ASSOC);
        $instructor_stmt = $pdo->prepare("SELECT u.name, u.email FROM users u JOIN assignments a ON a.instructor_id = u.id WHERE a.id = ?");
        $instructor_stmt->execute([$assignment_id]);
        $instructor = $instructor_stmt->fetch(PDO::FETCH_ASSOC);
        // Email student
        $subject = "Assignment Submission Confirmation: {$assignment['title']}";
        $body = "Hello {$student['name']},\n\nYour assignment '{$assignment['title']}' for {$assignment['course_name']} has been submitted successfully.\n\nThank you!";
        $studentMail = bch_send_mail($student['email'], $student['name'], $subject, $body);
        // Email instructor
        $instructorMail = ["success"=>true];
        if ($instructor) {
            $isubject = "[Instructor Notice] New Assignment Submission: {$assignment['title']}";
            $ibody = "Student {$student['name']} ({$student['email']}) has submitted '{$assignment['title']}' for {$assignment['course_name']}.";
            $instructorMail = bch_send_mail($instructor['email'], $instructor['name'], $isubject, $ibody);
        }
        if ($studentMail['success'] && $instructorMail['success']) {
            $_SESSION['success_msg'] = "Assignment submitted successfully!";
            header("Location: view_submissions.php");
            exit;
        } else {
            $_SESSION['error_msg'] = "Submission succeeded, but failed to send notification email(s): " . htmlspecialchars($studentMail['error'] ?? $instructorMail['error']);
        }

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error submitting assignment: " . $e->getMessage();
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Summernote JS -->
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

        $(document).ready(function() {
            $('#submission_text').summernote({
                placeholder: 'Write your submission here...',
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onChange: function(contents) {
                        $('#submission_text_hidden').val(contents);
                    }
                }
            });

            // If there's existing content, set it in Summernote
            <?php if ($existing_submission && $existing_submission['submission_text']): ?>
                $('#submission_text').summernote('code', <?= json_encode($existing_submission['submission_text']) ?>);
            <?php endif; ?>
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
                            <?= htmlspecialchars($assignment['module_name']) ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar mr-2"></i>
                            Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                        </span>
                        <span>
                            <i class="fas fa-star mr-2"></i>
                            Marks: <?= $assignment['marks'] ?>
                        </span>
                    </div>
                </div>

                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6">
                        <?= $_SESSION['success_msg'] ?>
                        <?php unset($_SESSION['success_msg']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                        <?= $_SESSION['error_msg'] ?>
                        <?php unset($_SESSION['error_msg']); ?>
                    </div>
                <?php endif; ?>

                <!-- Submission Form -->
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Assignment Instructions -->
                    <div class="bg-gray-50 p-6 rounded-xl mb-6">
                        <h3 class="text-xl font-bold text-primary mb-4">Assignment Instructions</h3>
                        <div class="prose max-w-none">
                            <?= $assignment['instructions'] ?>
                        </div>
                    </div>

                    <!-- Editor Section -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="mb-6">
                            <label for="submission_text" class="block text-gray-700 font-medium mb-2">
                                Your Submission
                            </label>
                            <textarea id="submission_text" name="submission_text"></textarea>
                            <input type="hidden" id="submission_text_hidden" name="submission_text">
                        </div>

                        <!-- File Upload Section -->
                        <div class="border-t pt-6">
                            <label class="block text-gray-700 font-medium mb-2">
                                Attachments
                            </label>
                            <div class="flex items-center space-x-4">
                                <input type="file" id="submission_file" name="submission_file"
                                       class="hidden" accept=".pdf,.doc,.docx,.zip">
                                <label for="submission_file" 
                                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer transition flex items-center">
                                    <i class="fas fa-paperclip mr-2"></i>
                                    Add Files
                                </label>
                                <?php if ($existing_submission && $existing_submission['submission_file']): ?>
                                    <div class="flex items-center bg-blue-50 px-4 py-2 rounded-lg">
                                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                        <span class="text-sm text-gray-600">
                                            <?= basename($existing_submission['submission_file']) ?>
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
                                    class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-opacity-90 transition flex items-center">
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