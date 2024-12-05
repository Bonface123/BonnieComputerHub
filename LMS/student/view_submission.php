<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];
$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch submission details with assignment info
$query = $pdo->prepare("
    SELECT 
        s.*,
        a.title as assignment_title,
        a.description as assignment_description,
        a.due_date,
        a.marks as total_marks,
        c.course_name,
        m.module_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE s.assignment_id = ? AND s.student_id = ?
");
$query->execute([$submission_id, $student_id]);
$submission = $query->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    $_SESSION['error_msg'] = "Submission not found or you don't have permission to view it.";
    header('Location: assignments.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission - BCH Learning</title>
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
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="dashboard.php" class="text-xl font-bold text-secondary">View Submission</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <button class="md:hidden text-gray-300 hover:text-secondary" onclick="toggleMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Desktop navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="assignments.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Assignments
                    </a>
                    <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">Dashboard</a>
                </nav>
            </div>

            <!-- Mobile navigation -->
            <nav id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="assignments.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Assignments
                    </a>
                    <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">Dashboard</a>
                </div>
            </nav>
        </div>

        <script>
            function toggleMenu() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            }
        </script>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Assignment Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-primary mb-2">
                <?= htmlspecialchars($submission['assignment_title']) ?>
            </h1>
            <div class="flex items-center text-sm text-gray-600 mb-4">
                <span class="mr-4">
                    <i class="fas fa-book mr-2"></i>
                    <?= htmlspecialchars($submission['course_name']) ?>
                </span>
                <span>
                    <i class="fas fa-layer-group mr-2"></i>
                    <?= htmlspecialchars($submission['module_name']) ?>
                </span>
            </div>
            <div class="prose max-w-none mb-4">
                <?= $submission['assignment_description'] ?>
            </div>
            <div class="flex items-center text-sm text-gray-500 space-x-4">
                <span>
                    <i class="fas fa-calendar mr-2"></i>
                    Due: <?= date('M j, Y g:i A', strtotime($submission['due_date'])) ?>
                </span>
                <span>
                    <i class="fas fa-star mr-2"></i>
                    <?= $submission['total_marks'] ?> marks
                </span>
            </div>
        </div>

        <!-- Submission Details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-primary mb-4">Your Submission</h2>
            
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-2"></i>
                        Submitted: <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?>
                    </div>
                    <span class="<?= $submission['status'] === 'graded' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?> text-xs font-semibold px-2.5 py-0.5 rounded-full">
                        <?= ucfirst($submission['status']) ?>
                    </span>
                </div>

                <?php if ($submission['submission_text']): ?>
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="prose max-w-none">
                            <?= $submission['submission_text'] ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($submission['submission_file']): ?>
                    <div class="flex items-center bg-blue-50 p-4 rounded-lg">
                        <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                        <a href="../uploads/submissions/<?= htmlspecialchars($submission['submission_file']) ?>" 
                           target="_blank"
                           class="text-blue-600 hover:text-blue-800">
                            View Attached File
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($submission['status'] === 'graded'): ?>
                <!-- Grading Details -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-primary mb-4">Feedback</h3>
                    <div class="bg-green-50 p-4 rounded-lg mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600">Grade:</span>
                            <span class="text-lg font-bold text-primary">
                                <?= $submission['grade'] ?> / <?= $submission['total_marks'] ?>
                            </span>
                        </div>
                        <?php if ($submission['feedback']): ?>
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Instructor Comments:</h4>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($submission['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 