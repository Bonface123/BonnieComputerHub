<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch enrolled courses for the current student
$stmt = $pdo->prepare("
    SELECT c.*, u.name as instructor_name,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
           e.enrollment_date,
           (SELECT COUNT(*) FROM module_content WHERE module_id IN 
                (SELECT id FROM course_modules WHERE course_id = c.id)) as total_content,
           (SELECT COUNT(*) FROM assignments WHERE module_id IN 
                (SELECT id FROM course_modules WHERE course_id = c.id)) as total_assignments
    FROM courses c 
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.created_by = u.id
    WHERE e.user_id = ?
    ORDER BY e.enrollment_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - BCH Learning</title>
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
                        <a href="../../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Empowering Through Technology</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="../../index.php" class="text-gray-300 hover:text-secondary transition">Home</a>
                    <a href="courses.php" class="text-secondary">Courses</a>
                    <a href="contact.php" class="text-gray-300 hover:text-secondary transition">Contact</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">Dashboard</a>
                        <a href="../logout.php" class="bg-secondary text-primary px-4 py-2 rounded-lg hover:bg-opacity-90">Logout</a>
                    <?php else: ?>
                        <a href="../login.php" class="text-gray-300 hover:text-secondary transition">Login</a>
                        <a href="../register.php" class="bg-secondary text-primary px-4 py-2 rounded-lg hover:bg-opacity-90">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <a href="dashboard.php" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
        <i class="fas fa-arrow-left"></i> Go Back to Dashboard
    </a>
    <main class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-primary mb-4">My Enrolled Courses</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Track your progress and continue learning in your enrolled courses.
            </p>
        </div>

        <?php if (empty($enrolled_courses)): ?>
            <div class="text-center py-12">
                <i class="fas fa-books text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 mb-4">You haven't enrolled in any courses yet.</p>
                <a href="../pages/courses.php" 
                   class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-opacity-90 transition">
                    Browse Available Courses
                </a>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="bg-primary p-4">
                            <h2 class="text-xl font-bold text-secondary">
                                <?= htmlspecialchars($course['course_name']) ?>
                            </h2>
                        </div>

                        <div class="p-6">
                            <!-- Course Progress -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Course Progress</span>
                                    <span>50%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-secondary h-2.5 rounded-full" style="width: 50%"></div>
                                </div>
                            </div>

                            <!-- Course Stats -->
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <span>Instructor: <?= htmlspecialchars($course['instructor_name']) ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-book mr-2"></i>
                                    <span><?= $course['total_content'] ?> learning materials</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-tasks mr-2"></i>
                                    <span><?= $course['total_assignments'] ?> assignments</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span>Enrolled: <?= date('M j, Y', strtotime($course['enrollment_date'])) ?></span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="view_course.php?id=<?= $course['id'] ?>" 
                               class="block text-center bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition">
                                <i class="fas fa-play-circle mr-2"></i>Continue Learning
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
