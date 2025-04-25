<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all feedback with user and course details
$feedback = $pdo->query("
    SELECT f.*, 
           u.name as student_name, 
           u.email as student_email,
           c.course_name,
           (SELECT COUNT(*) FROM enrollments WHERE user_id = f.student_id) as total_enrollments,
           (SELECT COUNT(*) FROM submissions s 
            JOIN assignments a ON s.assignment_id = a.id 
            WHERE s.student_id = f.student_id) as total_submissions
    FROM feedback f
    JOIN users u ON f.student_id = u.id
    JOIN courses c ON f.course_id = c.id
    ORDER BY f.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get feedback statistics
$stats = [
    'total_feedback' => count($feedback),
    'courses_with_feedback' => $pdo->query("SELECT COUNT(DISTINCT course_id) FROM feedback")->fetchColumn(),
    'students_giving_feedback' => $pdo->query("SELECT COUNT(DISTINCT student_id) FROM feedback")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback - BCH Learning</title>
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
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Student Feedback</p>
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
            <h1 class="text-2xl font-bold text-primary mb-2">Student Feedback</h1>
            <p class="text-gray-600">Review and manage student feedback for courses</p>
        </div>

        <!-- Feedback Statistics -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full mr-4">
                        <i class="fas fa-comments text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Feedback</p>
                        <h3 class="text-2xl font-bold text-blue-500"><?= $stats['total_feedback'] ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full mr-4">
                        <i class="fas fa-book text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Courses with Feedback</p>
                        <h3 class="text-2xl font-bold text-green-500"><?= $stats['courses_with_feedback'] ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full mr-4">
                        <i class="fas fa-users text-purple-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Students Giving Feedback</p>
                        <h3 class="text-2xl font-bold text-purple-500"><?= $stats['students_giving_feedback'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">All Feedback</h2>
            <?php if ($feedback): ?>
                <div class="space-y-6">
                    <?php foreach ($feedback as $item): ?>
                        <div class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-4">
                                        <img class="h-10 w-10 rounded-full mr-4" 
                                             src="<?= isset($item['profile_image']) ? htmlspecialchars($item['profile_image']) : '../images/default-avatar.png' ?>" 
                                             alt="Student avatar">
                                        <div>
                                            <h3 class="text-lg font-medium text-primary">
                                                <?= htmlspecialchars($item['student_name']) ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                <?= htmlspecialchars($item['student_email']) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-4 mb-4">
                                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($item['comments'])) ?></p>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                                        <span>
                                            <i class="fas fa-book mr-1"></i>
                                            <?= htmlspecialchars($item['course_name']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-graduation-cap mr-1"></i>
                                            <?= $item['total_enrollments'] ?> courses enrolled
                                        </span>
                                        <span>
                                            <i class="fas fa-tasks mr-1"></i>
                                            <?= $item['total_submissions'] ?> submissions
                                        </span>
                                        <span>
                                            <i class="fas fa-clock mr-1"></i>
                                            <?= date('M j, Y g:i A', strtotime($item['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <button class="text-gray-400 hover:text-gray-600" 
                                            onclick="markAsReviewed(<?= $item['id'] ?>)">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-comments text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No feedback received yet</p>
                </div>
            <?php endif; ?>
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
        function markAsReviewed(feedbackId) {
            if (confirm('Mark this feedback as reviewed?')) {
                // You can implement an AJAX call here to update the feedback status
                console.log('Marking feedback ' + feedbackId + ' as reviewed');
            }
        }
    </script>
</body>
</html>
