<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'instructor'")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BCH Learning</title>
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
    </style>
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
                        <p class="text-gray-300 text-sm">Admin Dashboard</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-gray-300 hover:text-secondary transition">Home</a>
                    <a href="profile.php" class="text-gray-300 hover:text-secondary transition">Profile</a>
                    <a href="../logout.php" class="bg-secondary text-primary px-4 py-2 rounded-lg hover:bg-opacity-90 transition font-semibold">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-primary rounded-lg p-6 mb-8 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-secondary mb-2">Admin Dashboard</h1>
                    <p class="text-gray-300">Manage your learning management system</p>
                </div>
                <div class="hidden md:block">
                    <img src="../images/BCH.jpg" alt="Admin Icon" class="h-16 w-16 rounded-full">
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
            <div class="stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full mr-4">
                        <i class="fas fa-users text-primary text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Users</p>
                        <h3 class="text-2xl font-bold text-primary"><?= htmlspecialchars($totalUsers) ?></h3>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full mr-4">
                        <i class="fas fa-user-graduate text-primary text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Students</p>
                        <h3 class="text-2xl font-bold text-primary"><?= htmlspecialchars($totalStudents) ?></h3>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full mr-4">
                        <i class="fas fa-chalkboard-teacher text-primary text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Instructors</p>
                        <h3 class="text-2xl font-bold text-primary"><?= htmlspecialchars($totalInstructors) ?></h3>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full mr-4">
                        <i class="fas fa-book text-primary text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Courses</p>
                        <h3 class="text-2xl font-bold text-primary"><?= htmlspecialchars($totalCourses) ?></h3>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-pink-100 rounded-full mr-4">
                        <i class="fas fa-user-plus text-primary text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Enrollments</p>
                        <h3 class="text-2xl font-bold text-primary"><?= htmlspecialchars($totalEnrollments) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="manage_users.php" class="nav-card">
                <div class="p-3 bg-blue-100 rounded-full mr-4">
                    <i class="fas fa-users-cog text-primary text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-primary">Manage Users</h3>
                    <p class="text-gray-500 text-sm">Add, edit, or remove users</p>
                </div>
            </a>

            <a href="manage_courses.php" class="nav-card">
                <div class="p-3 bg-green-100 rounded-full mr-4">
                    <i class="fas fa-book-open text-primary text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-primary">Manage Courses</h3>
                    <p class="text-gray-500 text-sm">Create and organize courses</p>
                </div>
            </a>

            <a href="manage_enrollments.php" class="nav-card">
                <div class="p-3 bg-yellow-100 rounded-full mr-4">
                    <i class="fas fa-user-plus text-primary text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-primary">Manage Enrollments</h3>
                    <p class="text-gray-500 text-sm">Handle course enrollments</p>
                </div>
            </a>

            <a href="reports.php" class="nav-card">
                <div class="p-3 bg-purple-100 rounded-full mr-4">
                    <i class="fas fa-chart-bar text-primary text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-primary">View Reports</h3>
                    <p class="text-gray-500 text-sm">Access system reports</p>
                </div>
            </a>

            <a href="feedback.php" class="nav-card">
                <div class="p-3 bg-pink-100 rounded-full mr-4">
                    <i class="fas fa-comments text-primary text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-primary">View Feedback</h3>
                    <p class="text-gray-500 text-sm">Review user feedback</p>
                </div>
            </a>

            <a href="analytics_dashboard.php" class="nav-card">
                <div class="p-3 bg-indigo-100 rounded-full mr-4">
                    <i class="fas fa-chart-line text-primary text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-primary">Analytics</h3>
                    <p class="text-gray-500 text-sm">View system analytics</p>
                </div>
            </a>
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
