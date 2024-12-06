<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and has student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch all submissions with assignment details
$query = $pdo->prepare("
    SELECT 
        s.*,
        a.title as assignment_title,
        a.marks as total_marks,
        m.module_name,
        c.course_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE s.student_id = ?
    ORDER BY s.submitted_at DESC
");
$query->execute([$student_id]);
$submissions = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submissions - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            width: 90%;
            height: 90%;
            margin: 2% auto;
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            cursor: pointer;
            z-index: 1001;
        }

        .file-viewer {
            width: 100%;
            height: calc(100% - 2rem);
            border: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="dashboard.php" class="text-xl font-bold text-secondary">My Submissions</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                    <a href="assignments.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-tasks mr-1"></i> Assignments
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Header with Stats -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-primary mb-2">My Submissions</h1>
                    <p class="text-gray-600">Track your assignment submissions and grades</p>
                </div>
                <div class="flex space-x-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-primary"><?= count($submissions) ?></span>
                        <span class="text-sm text-gray-500">Total Submissions</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-green-600">
                            <?= count(array_filter($submissions, fn($s) => $s['status'] === 'graded')) ?>
                        </span>
                        <span class="text-sm text-gray-500">Graded</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="success-message bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= $_SESSION['success_msg'] ?>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($submissions)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-file-alt text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Submissions Yet</h3>
                <p class="text-gray-500 mb-6">You haven't submitted any assignments yet.</p>
                <a href="assignments.php" 
                   class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90 transition">
                    <i class="fas fa-tasks mr-2"></i>
                    View Available Assignments
                </a>
            </div>
        <?php else: ?>
            <!-- Submissions Timeline -->
            <div class="relative">
                <?php foreach ($submissions as $index => $submission): 
                    $status_class = $submission['status'] === 'graded' 
                        ? 'bg-green-100 text-green-800 border-green-200' 
                        : 'bg-yellow-100 text-yellow-800 border-yellow-200';
                ?>
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border-l-4 <?= $status_class ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-primary group-hover:text-secondary transition">
                                    <?= htmlspecialchars($submission['assignment_title']) ?>
                                </h3>
                                <div class="flex items-center text-sm text-gray-600 mt-1">
                                    <span class="flex items-center">
                                        <i class="fas fa-book-open mr-2"></i>
                                        <?= htmlspecialchars($submission['course_name']) ?>
                                    </span>
                                    <span class="mx-2">•</span>
                                    <span class="flex items-center">
                                        <i class="fas fa-layer-group mr-2"></i>
                                        <?= htmlspecialchars($submission['module_name']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_class ?>">
                                    <?= ucfirst($submission['status']) ?>
                                </span>
                                <?php if ($submission['status'] === 'graded'): ?>
                                    <span class="px-3 py-1 bg-primary text-white rounded-full text-xs font-semibold">
                                        <?= $submission['grade'] ?>/<?= $submission['total_marks'] ?> marks
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($submission['submission_text']): ?>
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <div class="prose max-w-none">
                                    <?= $submission['submission_text'] ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <?php if ($submission['submission_file']): ?>
                                    <?php 
                                    $file_extension = strtolower(pathinfo($submission['submission_file'], PATHINFO_EXTENSION));
                                    $file_path = "../uploads/submissions/" . htmlspecialchars($submission['submission_file']);
                                    $file_icon = match($file_extension) {
                                        'pdf' => 'fa-file-pdf',
                                        'doc', 'docx' => 'fa-file-word',
                                        'xls', 'xlsx' => 'fa-file-excel',
                                        'ppt', 'pptx' => 'fa-file-powerpoint',
                                        'zip', 'rar' => 'fa-file-archive',
                                        default => 'fa-file'
                                    };
                                    ?>
                                    <button onclick="viewFile('<?= $file_path ?>', '<?= $file_extension ?>')" 
                                            class="flex items-center text-primary hover:text-opacity-80">
                                        <i class="fas <?= $file_icon ?> mr-2"></i>
                                        View Attachment
                                    </button>
                                <?php endif; ?>
                                <span class="text-gray-500">
                                    <i class="far fa-clock mr-2"></i>
                                    <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($submission['feedback']): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h4 class="font-medium text-primary mb-2 flex items-center">
                                    <i class="fas fa-comment-dots mr-2"></i>
                                    Instructor Feedback
                                </h4>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="prose max-w-none text-sm text-gray-700">
                                        <?= $submission['feedback'] ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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

    <div id="fileViewerModal" class="modal">
        <div class="modal-content">
            <button class="modal-close text-gray-600 hover:text-gray-800">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <iframe id="fileViewer" class="file-viewer"></iframe>
        </div>
    </div>

    <script>
        const modal = document.getElementById('fileViewerModal');
        const fileViewer = document.getElementById('fileViewer');
        const closeBtn = document.querySelector('.modal-close');

        function viewFile(filePath, fileType) {
            // For PDFs, we can show directly in iframe
            if (fileType === 'pdf') {
                fileViewer.src = filePath;
                modal.style.display = 'block';
            } 
            // For images, show directly
            else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                fileViewer.src = filePath;
                modal.style.display = 'block';
            }
            // For other file types, try to use Google Docs Viewer
            else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileType)) {
                const googleDocsUrl = 'https://docs.google.com/viewer?url=';
                const fullUrl = window.location.origin + '/' + filePath;
                fileViewer.src = googleDocsUrl + encodeURIComponent(fullUrl) + '&embedded=true';
                modal.style.display = 'block';
            }
            // For unsupported files, fall back to download
            else {
                window.open(filePath, '_blank');
            }
        }

        // Close modal when clicking the close button
        closeBtn.onclick = function() {
            modal.style.display = 'none';
            fileViewer.src = '';
        }

        // Close modal when clicking outside the content
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                fileViewer.src = '';
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                modal.style.display = 'none';
                fileViewer.src = '';
            }
        });

        // Auto-dismiss success message
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease-out';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>
