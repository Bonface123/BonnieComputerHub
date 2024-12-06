<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and has student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

// Get the student's ID
$student_id = $_SESSION['user_id'];

// Fetch the student's name from the database
$name_query = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$name_query->execute([$student_id]);
$user_data = $name_query->fetch(PDO::FETCH_ASSOC);
$user_name = $user_data ? $user_data['name'] : 'Student';

// Generate greeting based on time of day
date_default_timezone_set('Africa/Nairobi');
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour < 18) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

// Fetch enrolled courses count
$enrolled_query = $pdo->prepare("
    SELECT COUNT(*) FROM enrollments 
    WHERE user_id = ?
");
$enrolled_query->execute([$student_id]);
$enrolled_count = $enrolled_query->fetchColumn();

// Fetch assignments count
$assignments_query = $pdo->prepare("
    SELECT COUNT(*) FROM assignments a 
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
");
$assignments_query->execute([$student_id]);
$total_assignments = $assignments_query->fetchColumn();

// Fetch submissions count
$submissions_query = $pdo->prepare("
    SELECT COUNT(*) FROM submissions 
    WHERE student_id = ?
");
$submissions_query->execute([$student_id]);
$submissions_count = $submissions_query->fetchColumn();

// Fetch enrolled courses with details
$courses_query = $pdo->prepare("
    SELECT 
        c.id,
        c.course_name,
        c.description,
        u.name as instructor_name,
        e.enrollment_date,
        (SELECT COUNT(*) FROM course_modules WHERE course_id = c.id) as total_modules,
        (SELECT COUNT(*) FROM assignments a 
         JOIN course_modules m ON a.module_id = m.id 
         WHERE m.course_id = c.id) as total_assignments
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.created_by = u.id
    WHERE e.user_id = ?
    ORDER BY e.enrollment_date DESC
");
$courses_query->execute([$student_id]);
$enrolled_courses = $courses_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch assignments for enrolled courses
$assignments_query = $pdo->prepare("
    SELECT 
        a.id,
        a.title,
        a.description,
        a.due_date,
        a.marks,
        c.course_name,
        m.module_name
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = e.user_id
    WHERE e.user_id = ? 
    AND s.id IS NULL
    ORDER BY a.due_date ASC LIMIT 2
");
$assignments_query->execute([$student_id]);
$assignments = $assignments_query->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - BCH Learning</title>
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
                        <a href="dashboard.php" class="text-xl font-bold text-secondary">Student Dashboard</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <button class="md:hidden text-gray-300 hover:text-secondary" onclick="toggleMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Desktop navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="courses.php" class="text-gray-300 hover:text-secondary transition">My Courses</a>
                    <a href="profile.php" class="text-gray-300 hover:text-secondary transition">Profile</a>
                    <a href="../logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Logout</a>
                </nav>
            </div>

            <!-- Mobile navigation -->
            <nav id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="courses.php" class="text-gray-300 hover:text-secondary transition">My Courses</a>
                    <a href="profile.php" class="text-gray-300 hover:text-secondary transition">Profile</a>
                    <a href="../logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center">Logout</a>
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
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h1 class="text-2xl font-bold text-primary mb-2">
                <?= $greeting . ', ' . htmlspecialchars($user_name) ?>!
            </h1>
            <p class="text-gray-600">Welcome to your learning dashboard. Here's an overview of your courses and activities.</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-primary">
                <div class="flex items-center">
                    <div class="bg-primary/10 p-3 rounded-full">
                        <i class="fas fa-book text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Enrolled Courses</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $enrolled_count ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
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

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-50 p-3 rounded-full">
                        <i class="fas fa-check-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Submissions</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $submissions_count ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Your Assignments</h2>
            <?php if (empty($assignments)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <p class="text-gray-500">No assignments available yet.</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-6">
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
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    <?= $assignment['marks'] ?> marks
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                <?= htmlspecialchars($assignment['description']) ?>
                            </p>
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                                </div>
                                <a href="submit_assignment.php?id=<?= $assignment['id'] ?>" 
                                   class="bg-primary text-white px-4 py-2 rounded hover:bg-opacity-90 transition text-sm">
                                    Submit Assignment
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Access Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-primary mb-6">Quick Access</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Course Materials Card -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-primary/10 p-4 rounded-full mb-4">
                            <i class="fas fa-book-reader text-primary text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-primary mb-2">Course Materials</h3>
                        <p class="text-gray-600 text-sm mb-4">Access your learning materials and resources</p>
                        <a href="course_materials.php" 
                           class="w-full bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition text-center">
                            View Materials
                        </a>
                    </div>
                </div>

                <!-- Assignments Card -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-green-50 p-4 rounded-full mb-4">
                            <i class="fas fa-tasks text-green-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-primary mb-2">Assignments</h3>
                        <p class="text-gray-600 text-sm mb-4">View and submit your assignments</p>
                        <a href="assignments.php" 
                           class="w-full bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition text-center">
                            View Assignments
                        </a>
                    </div>
                </div>

                <!-- Progress Card -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-50 p-4 rounded-full mb-4">
                            <i class="fas fa-chart-line text-blue-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-primary mb-2">My Progress</h3>
                        <p class="text-gray-600 text-sm mb-4">Track your learning progress</p>
                        <a href="progress.php" 
                           class="w-full bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition text-center">
                            View Progress
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <h2 class="text-2xl font-bold text-primary mb-6">Your Enrolled Courses</h2>
        
        <?php if (empty($enrolled_courses)): ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500 mb-4">You haven't enrolled in any courses yet.</p>
                <a href="../pages/courses.php" 
                   class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                    Browse Available Courses
                </a>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-primary p-4">
                            <h3 class="text-xl font-bold text-secondary">
                                <?= htmlspecialchars($course['course_name']) ?>
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <span>Instructor: <?= htmlspecialchars($course['instructor_name']) ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-book mr-2"></i>
                                    <span><?= $course['total_modules'] ?> modules</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-tasks mr-2"></i>
                                    <span><?= $course['total_assignments'] ?> assignments</span>
                                </div>
                            </div>
                            
                            <a href="view_course.php?id=<?= $course['id'] ?>" 
                               class="mt-6 block text-center bg-primary text-white py-2 rounded-lg hover:bg-opacity-90 transition">
                                Continue Learning
                            </a>
                        </div>
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