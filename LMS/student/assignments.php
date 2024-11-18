<?php
session_start();
require_once '../includes/db_connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all assignments for enrolled modules
$assignments_query = $pdo->prepare("
    SELECT 
        a.id,
        a.title,
        a.description,
        a.due_date,
        a.weight,
        m.title as module_title,
        sa.status as submission_status,
        sa.score,
        sa.submitted_at
    FROM assignments a
    JOIN modules m ON a.module_id = m.id
    JOIN module_progress mp ON m.id = mp.module_id
    LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.user_id = ?
    WHERE mp.user_id = ?
    ORDER BY a.due_date ASC
");
$assignments_query->execute([$user_id, $user_id]);
$assignments = $assignments_query->fetchAll(PDO::FETCH_ASSOC);

// Group assignments by status
$pending = [];
$submitted = [];
$graded = [];

foreach ($assignments as $assignment) {
    if (!$assignment['submission_status'] || $assignment['submission_status'] === 'pending') {
        $pending[] = $assignment;
    } elseif ($assignment['submission_status'] === 'submitted') {
        $submitted[] = $assignment;
    } else {
        $graded[] = $assignment;
    }
}
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
<body class="bg-gray-50 min-h-screen flex flex-col">
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
    <main class="container mx-auto px-4 py-8 flex-grow">
        <h1 class="text-3xl font-bold text-primary mb-8">My Assignments</h1>

        <!-- Assignment Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-50 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Pending</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= count($pending) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-50 p-3 rounded-full">
                        <i class="fas fa-paper-plane text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Submitted</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= count($submitted) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-50 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Graded</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= count($graded) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Filters -->
        <div class="flex flex-wrap gap-4 mb-8">
            <button onclick="showTab('pending')" 
                    class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-secondary hover:text-primary transition">
                Pending (<?= count($pending) ?>)
            </button>
            <button onclick="showTab('submitted')" 
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition">
                Submitted (<?= count($submitted) ?>)
            </button>
            <button onclick="showTab('graded')" 
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition">
                Graded (<?= count($graded) ?>)
            </button>
        </div>

        <!-- Pending Assignments -->
        <div id="pending-tab" class="assignment-tab">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($pending as $assignment): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-primary">
                                    <?= htmlspecialchars($assignment['title']) ?>
                                </h3>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                    Due: <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4">
                                Module: <?= htmlspecialchars($assignment['module_title']) ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">
                                    Weight: <?= $assignment['weight'] ?>%
                                </span>
                                <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                                   class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary hover:text-primary transition">
                                    Start Assignment
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Submitted Assignments -->
        <div id="submitted-tab" class="assignment-tab hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($submitted as $assignment): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-primary">
                                    <?= htmlspecialchars($assignment['title']) ?>
                                </h3>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    Submitted
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4">
                                Module: <?= htmlspecialchars($assignment['module_title']) ?>
                            </p>
                            <div class="text-sm text-gray-500">
                                Submitted on: <?= date('M d, Y', strtotime($assignment['submitted_at'])) ?>
                            </div>
                            <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                               class="mt-4 block text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                View Submission
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Graded Assignments -->
        <div id="graded-tab" class="assignment-tab hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($graded as $assignment): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-primary">
                                    <?= htmlspecialchars($assignment['title']) ?>
                                </h3>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                    Score: <?= $assignment['score'] ?>%
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4">
                                Module: <?= htmlspecialchars($assignment['module_title']) ?>
                            </p>
                            <div class="text-sm text-gray-500 mb-4">
                                Submitted on: <?= date('M d, Y', strtotime($assignment['submitted_at'])) ?>
                            </div>
                            <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                               class="block text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                View Feedback
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 mb-4">
                &copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.
            </p>
            <p class="text-secondary italic">
                "I can do all things through Christ who strengthens me." - Philippians 4:13
            </p>
        </div>
    </footer>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.assignment-tab').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Update button styles
            document.querySelectorAll('button').forEach(button => {
                button.classList.remove('bg-primary', 'text-white');
                button.classList.add('bg-gray-200');
            });
            
            // Highlight active button
            event.target.classList.remove('bg-gray-200');
            event.target.classList.add('bg-primary', 'text-white');
        }
    </script>
</body>
</html> 