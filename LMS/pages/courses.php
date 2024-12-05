<?php
session_start();
require_once '../includes/db_connect.php';

// Fetch all active courses with instructor info and enrollment count
$stmt = $pdo->query("
    SELECT c.*, u.name as instructor_name, 
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
           (SELECT COUNT(*) FROM course_modules WHERE course_id = c.id) as total_modules
    FROM courses c 
    JOIN users u ON c.created_by = u.id 
    WHERE c.status = 'active' 
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle enrollment if user is logged in
if (isset($_POST['enroll']) && isset($_SESSION['user_id'])) {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already enrolled
    $check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$user_id, $course_id]);

    if ($check->rowCount() == 0) {
        try {
            $enroll = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
            $enroll->execute([$user_id, $course_id]);
            $_SESSION['success_msg'] = "Successfully enrolled in the course!";
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Error enrolling in course: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_msg'] = "You are already enrolled in this course.";
    }
    header("Location: courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Bonnie Computer Hub LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',    // BCH Blue
                        secondary: '#FFD700',  // BCH Gold
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
                        <a href="../../index.html" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Empowering Through Technology</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-gray-300 hover:text-secondary transition">Home</a>
                    <a href="courses.php" class="text-secondary">Courses</a>
                    <a href="contact.php" class="text-gray-300 hover:text-secondary transition">Contact</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../student/dashboard.php" class="text-gray-300 hover:text-secondary transition">Dashboard</a>
                        <a href="../logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 bg-secondary text-primary rounded-lg hover:bg-white transition">Login</a>
                        <a href="register.php" class="px-4 py-2 border-2 border-secondary text-secondary rounded-lg hover:bg-secondary hover:text-primary transition">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-900 py-20 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Our Web Development Courses</h1>
                <p class="text-xl text-gray-200 mb-8">
                    Comprehensive courses designed to transform you into a professional web developer
                </p>
                <div class="flex justify-center gap-4">
                    <a href="#courses" class="px-6 py-3 bg-secondary text-primary rounded-full hover:bg-white transition duration-300">
                        View Courses
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="px-6 py-3 border-2 border-secondary text-secondary rounded-full hover:bg-secondary hover:text-primary transition duration-300">
                            Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Add this after your hero section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <?= htmlspecialchars($_SESSION['success_msg']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?= htmlspecialchars($_SESSION['error_msg']) ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
                    Please <a href="login.php" class="font-bold underline">login</a> or 
                    <a href="register.php" class="font-bold underline">register</a> to enroll in courses.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Course Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($courses as $course): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-primary p-4">
                        <h2 class="text-xl font-bold text-secondary">
                            <?= htmlspecialchars($course['course_name']) ?>
                        </h2>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            <?= substr($course['description'], 0, 150) ?>...
                        </div>

                        <div class="space-y-2 mb-6">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-user-tie mr-2"></i>
                                <span>Instructor: <?= htmlspecialchars($course['instructor_name']) ?></span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-users mr-2"></i>
                                <span><?= $course['enrolled_students'] ?> students enrolled</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-book mr-2"></i>
                                <span><?= $course['total_modules'] ?> modules</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-calendar mr-2"></i>
                                <span>Started: <?= date('M j, Y', strtotime($course['created_at'])) ?></span>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" class="mt-4">
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <button type="submit" name="enroll" 
                                        class="w-full bg-primary text-white py-3 rounded-lg hover:bg-opacity-90 transition">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Enroll Now
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="../login.php" 
                               class="block text-center bg-primary text-white py-3 rounded-lg hover:bg-opacity-90 transition">
                                <i class="fas fa-lock mr-2"></i>Login to Enroll
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($courses)): ?>
            <div class="text-center py-12">
                <i class="fas fa-books text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">No courses available at the moment.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-20">
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
