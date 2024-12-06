<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and has student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch assignments for enrolled courses
$assignments_query = $pdo->prepare("
    SELECT 
        a.*,
        c.course_name,
        m.module_name,
        CASE 
            WHEN a.due_date < NOW() THEN 'overdue'
            WHEN s.id IS NOT NULL THEN 'submitted'
            ELSE 'pending'
        END as submission_status
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE e.user_id = ?
    ORDER BY a.due_date ASC
");
$assignments_query->execute([$student_id, $student_id]);
$assignments = $assignments_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - BCH Learning</title>
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
                        <a href="dashboard.php" class="text-xl font-bold text-secondary">My Assignments</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">Dashboard</a>
                    <a href="view_submissions.php" class="text-gray-300 hover:text-secondary transition">My Submissions</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-primary mb-2">My Assignments</h1>
            <p class="text-gray-600">View and manage your course assignments</p>
        </div>

        <?php if (empty($assignments)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-tasks text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Assignments Yet</h3>
                <p class="text-gray-500">You don't have any assignments at the moment.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-primary">
                                    <?= htmlspecialchars($assignment['title']) ?>
                                </h3>
                                <p class="text-sm text-gray-600">
                                    <?= htmlspecialchars($assignment['course_name']) ?> - 
                                    <?= htmlspecialchars($assignment['module_name']) ?>
                                </p>
                            </div>
                            <div>
                                <?php
                                $status_classes = [
                                    'overdue' => 'bg-red-100 text-red-800',
                                    'submitted' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800'
                                ];
                                $status_text = [
                                    'overdue' => 'Overdue',
                                    'submitted' => 'Submitted',
                                    'pending' => 'Pending'
                                ];
                                ?>
                                <span class="<?= $status_classes[$assignment['submission_status']] ?> text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?= $status_text[$assignment['submission_status']] ?>
                                </span>
                            </div>
                        </div>

                        <div class="text-gray-600 mb-4">
                            <?= $assignment['description'] ?>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>
                                    <i class="fas fa-calendar mr-2"></i>
                                    Due: <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?>
                                </span>
                                <span>
                                    <i class="fas fa-star mr-2"></i>
                                    <?= $assignment['marks'] ?> marks
                                </span>
                            </div>

                            <?php if ($assignment['submission_status'] === 'overdue'): ?>
                                <button disabled 
                                        class="bg-gray-300 text-gray-600 px-4 py-2 rounded cursor-not-allowed">
                                    <i class="fas fa-clock mr-2"></i>
                                    Deadline Passed
                                </button>
                            <?php elseif ($assignment['submission_status'] === 'submitted'): ?>
                                <a href="view_submission.php?id=<?= $assignment['id'] ?>" 
                                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                                    <i class="fas fa-eye mr-2"></i>
                                    View Submission
                                </a>
                            <?php else: ?>
                                <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                                   class="bg-primary text-white px-4 py-2 rounded hover:bg-opacity-90 transition">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Submit Assignment
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($assignment['submission_status'] === 'overdue'): ?>
                            <div class="mt-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                This assignment is past its due date and can no longer be submitted.
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
</body>
</html> 