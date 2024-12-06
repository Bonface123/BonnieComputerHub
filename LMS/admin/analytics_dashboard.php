<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch overall statistics
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$total_assignments = $pdo->query("SELECT COUNT(*) FROM course_assignments")->fetchColumn();
$total_submissions = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();

// Fetch enrollment statistics per course
$enrollment_stats = $pdo->query("
    SELECT c.course_name, COUNT(e.user_id) AS enrollment_count,
           (SELECT COUNT(*) FROM submissions s 
            JOIN course_assignments ca ON s.assignment_id = ca.id 
            WHERE ca.course_id = c.id) as submission_count
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    GROUP BY c.id
    ORDER BY enrollment_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent activities
$recent_activities = $pdo->query("
    SELECT 'enrollment' as type, u.name as user_name, c.course_name, e.enrollment_date as activity_date
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrollment_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Calculate completion rates
$completion_stats = $pdo->query("
    SELECT c.course_name,
           COUNT(DISTINCT e.user_id) as total_students,
           COUNT(DISTINCT CASE WHEN mp.status = 'completed' THEN e.user_id END) as completed_students
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN module_progress mp ON e.user_id = mp.user_id
    GROUP BY c.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <p class="text-gray-300 text-sm">Analytics Dashboard</p>
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
            <h1 class="text-2xl font-bold text-primary mb-2">Analytics Dashboard</h1>
            <p class="text-gray-600">Comprehensive system analytics and statistics</p>
        </div>

        <!-- Key Metrics -->
        <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full mr-4">
                        <i class="fas fa-user-graduate text-blue-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Students</p>
                        <h3 class="text-2xl font-bold text-blue-500"><?= number_format($total_students) ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full mr-4">
                        <i class="fas fa-book text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Courses</p>
                        <h3 class="text-2xl font-bold text-green-500"><?= number_format($total_courses) ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full mr-4">
                        <i class="fas fa-user-plus text-yellow-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Enrollments</p>
                        <h3 class="text-2xl font-bold text-yellow-500"><?= number_format($total_enrollments) ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full mr-4">
                        <i class="fas fa-tasks text-purple-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Assignments</p>
                        <h3 class="text-2xl font-bold text-purple-500"><?= number_format($total_assignments) ?></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-pink-500">
                <div class="flex items-center">
                    <div class="p-3 bg-pink-100 rounded-full mr-4">
                        <i class="fas fa-file-alt text-pink-500 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Submissions</p>
                        <h3 class="text-2xl font-bold text-pink-500"><?= number_format($total_submissions) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <!-- Enrollment Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-primary mb-4">Course Enrollments</h2>
                <canvas id="enrollmentChart"></canvas>
            </div>

            <!-- Completion Rates Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-primary mb-4">Course Completion Rates</h2>
                <canvas id="completionChart"></canvas>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-primary mb-4">Recent Activities</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($activity['user_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        Enrolled in <?= htmlspecialchars($activity['course_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($activity['activity_date'])) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

    <script>
        // Prepare data for charts
        const enrollmentData = {
            labels: <?= json_encode(array_column($enrollment_stats, 'course_name')) ?>,
            datasets: [{
                label: 'Enrollments',
                data: <?= json_encode(array_column($enrollment_stats, 'enrollment_count')) ?>,
                backgroundColor: 'rgba(0, 33, 71, 0.2)',
                borderColor: 'rgba(0, 33, 71, 1)',
                borderWidth: 1
            }]
        };

        const completionData = {
            labels: <?= json_encode(array_column($completion_stats, 'course_name')) ?>,
            datasets: [{
                label: 'Completion Rate (%)',
                data: <?= json_encode(array_map(function($stat) {
                    return $stat['total_students'] > 0 
                        ? round(($stat['completed_students'] / $stat['total_students']) * 100, 1)
                        : 0;
                }, $completion_stats)) ?>,
                backgroundColor: 'rgba(255, 215, 0, 0.2)',
                borderColor: 'rgba(255, 215, 0, 1)',
                borderWidth: 1
            }]
        };

        // Create charts
        new Chart(document.getElementById('enrollmentChart'), {
            type: 'bar',
            data: enrollmentData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(document.getElementById('completionChart'), {
            type: 'line',
            data: completionData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
</body>
</html>
