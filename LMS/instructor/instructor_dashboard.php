<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Fetch instructor details
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$instructor_id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch instructor's courses count
$courses_count = $pdo->prepare("
    SELECT COUNT(*) FROM courses 
    WHERE created_by = ? AND status = 'active'
");
$courses_count->execute([$instructor_id]);
$total_courses = $courses_count->fetchColumn();

// Fetch total assignments count
$assignments_count = $pdo->prepare("
    SELECT COUNT(a.id) 
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE c.created_by = ?
");
$assignments_count->execute([$instructor_id]);
$total_assignments = $assignments_count->fetchColumn();

// Fetch total submissions count
$submissions_count = $pdo->prepare("
    SELECT COUNT(s.id) 
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE c.created_by = ?
");
$submissions_count->execute([$instructor_id]);
$total_submissions = $submissions_count->fetchColumn();

// Fetch recent submissions (notifications)
$notifications_query = $pdo->prepare("
    SELECT 
        s.id,
        s.submitted_at,
        s.status,
        u.name as student_name,
        a.title as assignment_title,
        c.course_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE c.created_by = ?
    ORDER BY s.submitted_at DESC
    LIMIT 5
");
$notifications_query->execute([$instructor_id]);
$recent_submissions = $notifications_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent assignments
$recent_assignments = $pdo->prepare("
    SELECT 
        a.*,
        c.course_name,
        m.module_name,
        (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE c.created_by = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recent_assignments->execute([$instructor_id]);
$assignments = $recent_assignments->fetchAll(PDO::FETCH_ASSOC);

// Helper function to format time ago
function getTimeAgo($timestamp) {
    $difference = time() - strtotime($timestamp);
    
    if ($difference < 60) {
        return "Just now";
    } elseif ($difference < 3600) {
        $minutes = round($difference / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($difference < 86400) {
        $hours = round($difference / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($difference < 604800) {
        $days = round($difference / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return date("M j, Y", strtotime($timestamp));
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - BCH Learning</title>
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
        .stat-card {
            @apply bg-white p-6 rounded-lg shadow-md border-l-4 border-secondary hover:shadow-lg transition-shadow;
        }
        .nav-card {
            @apply flex items-center p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-primary hover:scale-105;
        }
        .notification-card {
            @apply bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-transparent hover:border-secondary;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Logo Section -->
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Learning Management System</p>
                    </div>
                </div>
                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-gray-300 hover:text-secondary transition">Home</a>
                    <a href="profile.php" class="text-gray-300 hover:text-secondary transition">Profile</a>
                    <a href="../pages/logout.php" class="bg-secondary text-primary px-4 py-2 rounded-lg hover:bg-opacity-90 transition font-semibold">Logout</a>
                </nav>
                <!-- Mobile Menu Button -->
                <button class="md:hidden text-white">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-primary mb-2">
                        Welcome back, <?= htmlspecialchars($instructor['name']) ?>!
                    </h1>
                    <p class="text-gray-600">Here's what's happening in your courses</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500"><?= date('l, F j, Y') ?></p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-primary">
                <div class="flex items-center">
                    <div class="bg-primary/10 p-3 rounded-full">
                        <i class="fas fa-book text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Active Courses</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $total_courses ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-50 p-3 rounded-full">
                        <i class="fas fa-tasks text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Total Assignments</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $total_assignments ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-50 p-3 rounded-full">
                        <i class="fas fa-file-alt text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Submissions</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $total_submissions ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Manage Courses -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-primary/10 p-4 rounded-full mb-4">
                        <i class="fas fa-book-open text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-primary mb-2">Manage Courses</h3>
                    <p class="text-gray-600 text-sm mb-4">Create and manage your courses and modules</p>
                    <a href="manage_courses.php" 
                       class="w-full bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition text-center">
                        Manage Courses
                    </a>
                </div>
            </div>

            <!-- Manage Assignments -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-green-50 p-4 rounded-full mb-4">
                        <i class="fas fa-tasks text-green-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-primary mb-2">Assignments</h3>
                    <p class="text-gray-600 text-sm mb-4">Create and manage course assignments</p>
                    <a href="manage_assignments.php" 
                       class="w-full bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition text-center">
                        Manage Assignments
                    </a>
                </div>
            </div>

            <!-- Course Resources -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-blue-50 p-4 rounded-full mb-4">
                        <i class="fas fa-file-alt text-blue-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-primary mb-2">Course Resources</h3>
                    <p class="text-gray-600 text-sm mb-4">Manage course materials and resources</p>
                    <a href="manage_resources.php" 
                       class="w-full bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition text-center">
                        Manage Resources
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activities Grid -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Recent Submissions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-primary mb-4">Recent Submissions</h2>
                <?php if (empty($recent_submissions)): ?>
                    <p class="text-gray-500 text-center py-4">No recent submissions</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_submissions as $submission): ?>
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-file-alt text-blue-500"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($submission['student_name']) ?> submitted 
                                        <span class="font-semibold"><?= htmlspecialchars($submission['assignment_title']) ?></span>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Course: <?= htmlspecialchars($submission['course_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?= getTimeAgo($submission['submitted_at']) ?>
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $submission['status'] === 'graded' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($submission['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Assignments -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-primary mb-4">Recent Assignments</h2>
                <?php if (empty($assignments)): ?>
                    <p class="text-gray-500 text-center py-4">No assignments created yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($assignments as $assignment): ?>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-medium text-gray-900">
                                            <?= htmlspecialchars($assignment['title']) ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?= htmlspecialchars($assignment['course_name']) ?> - 
                                            <?= htmlspecialchars($assignment['module_name']) ?>
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                        <?= $assignment['submission_count'] ?> submissions
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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
</body>
</html>
