<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Get student's name
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get some basic stats
$stats_query = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM submissions WHERE student_id = ?) as total_submissions,
        (SELECT COUNT(*) FROM submissions WHERE student_id = ? AND status = 'graded') as graded_submissions,
        (SELECT COUNT(*) FROM enrollments WHERE user_id = ?) as courses_enrolled
");
$stats_query->execute([$student_id, $student_id, $student_id]);
$stats = $stats_query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - BCH Learning</title>
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
                        <a href="dashboard.php" class="text-xl font-bold text-secondary">My Progress</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <a href="dashboard.php" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
        <i class="fas fa-arrow-left"></i> Go Back to Dashboard
    </a>
    <main class="container mx-auto px-4 py-8">
        <!-- Motivational Header -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-3xl font-bold text-primary mb-4">
                    Keep Going, <?= htmlspecialchars($student['name']) ?>!
                </h1>
                <p class="text-gray-600 text-lg">
                    Every step forward is progress. Your journey in learning is what matters most.
                </p>
            </div>
        </div>

        <!-- Basic Stats -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-book-open text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Courses Enrolled</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['courses_enrolled'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-tasks text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Assignments Submitted</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['total_submissions'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-star text-purple-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Graded Work</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $stats['graded_submissions'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motivational Cards -->
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Learning Tips -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                    <i class="fas fa-lightbulb text-secondary mr-2"></i>
                    Tips for Success
                </h2>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span class="text-gray-600">Set specific, achievable goals for each study session</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span class="text-gray-600">Practice coding regularly, even if just for 30 minutes a day</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span class="text-gray-600">Take breaks to maintain focus and productivity</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span class="text-gray-600">Review your completed assignments to learn from feedback</span>
                    </li>
                </ul>
            </div>

            <!-- Motivational Quotes -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-primary mb-4 flex items-center">
                    <i class="fas fa-quote-left text-secondary mr-2"></i>
                    Words of Inspiration
                </h2>
                <div class="space-y-6">
                    <blockquote class="border-l-4 border-secondary pl-4 py-2">
                        <p class="text-gray-600 italic mb-2">
                            "The expert in anything was once a beginner."
                        </p>
                        <cite class="text-sm text-gray-500">- Helen Hayes</cite>
                    </blockquote>
                    <blockquote class="border-l-4 border-secondary pl-4 py-2">
                        <p class="text-gray-600 italic mb-2">
                            "Progress is not achieved by luck or accident, but by working on yourself daily."
                        </p>
                        <cite class="text-sm text-gray-500">- James Clear</cite>
                    </blockquote>
                </div>
            </div>
        </div>

        <!-- Coming Soon Section -->
        <div class="bg-white rounded-lg shadow-md p-8 mt-8">
            <div class="text-center">
                <div class="text-secondary mb-4">
                    <i class="fas fa-chart-line text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-primary mb-4">Detailed Progress Tracking Coming Soon!</h2>
                <p class="text-gray-600 mb-6">
                    We're working on bringing you detailed progress tracking, performance analytics, 
                    and personalized learning insights. Stay tuned!
                </p>
                <div class="flex justify-center space-x-4 text-sm text-gray-500">
                    <span class="flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Performance Analytics
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-trophy mr-2"></i>
                        Achievement Badges
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-road mr-2"></i>
                        Learning Path Tracking
                    </span>
                </div>
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