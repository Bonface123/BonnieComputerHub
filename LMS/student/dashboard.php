<?php
session_start();
require_once '../includes/db_connect.php';

// Helper function to format time ago
function getTimeAgo($timestamp) {
    $difference = time() - $timestamp;
    
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
    } elseif ($difference < 2592000) {
        $weeks = round($difference / 604800);
        return $weeks . " week" . ($weeks > 1 ? "s" : "") . " ago";
    } elseif ($difference < 31536000) {
        $months = round($difference / 2592000);
        return $months . " month" . ($months > 1 ? "s" : "") . " ago";
    } else {
        return date("M d, Y", $timestamp);
    }
}

// Check if the user is logged in and has a student role
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

// Generate a greeting based on the time of day
date_default_timezone_set('Africa/Nairobi');
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour < 18) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

// Fetch enrolled courses for the student
$courses = $pdo->prepare("
    SELECT c.id, c.course_name, c.description 
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
");
$courses->execute([$student_id]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// Fetch learning materials and assignments for each course
foreach ($courses as &$course) {
    $course_id = $course['id'];

    // Fetch learning materials
    $materials = $pdo->prepare("
        SELECT material_name, material_description, file_path, uploaded_at 
        FROM course_materials 
        WHERE course_id = ?
    ");
    $materials->execute([$course_id]);
    $course['materials'] = $materials->fetchAll(PDO::FETCH_ASSOC);

    // Fetch assignments
    $assignments = $pdo->prepare("SELECT id, title, due_date FROM course_assignments WHERE course_id = ?");
    $assignments->execute([$course_id]);
    $course['assignments'] = $assignments->fetchAll(PDO::FETCH_ASSOC);
}
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
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="courses.php" class="text-gray-300 hover:text-secondary transition">My Courses</a>
                    <a href="assignments.php" class="text-gray-300 hover:text-secondary transition">Assignments</a>
                    <a href="profile.php" class="text-gray-300 hover:text-secondary transition">Profile</a>
                    <a href="../logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Welcome Message -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h1 class="text-2xl font-bold text-primary mb-2">
                <?= $greeting . ', ' . htmlspecialchars($user_name) ?>!
            </h1>
            <p class="text-gray-600">Welcome to your learning dashboard. Here's an overview of your courses and activities.</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php
            // Get enrolled courses count
            $enrolled_query = $pdo->prepare("
                SELECT COUNT(*) FROM module_progress 
                WHERE user_id = ?
            ");
            $enrolled_query->execute([$student_id]);
            $enrolled_count = $enrolled_query->fetchColumn();

            // Get completed assignments count
            $completed_assignments_query = $pdo->prepare("
                SELECT COUNT(*) FROM student_assignments 
                WHERE user_id = ? AND status = 'graded'
            ");
            $completed_assignments_query->execute([$student_id]);
            $completed_assignments = $completed_assignments_query->fetchColumn();

            // Get pending assignments count
            $pending_assignments_query = $pdo->prepare("
                SELECT COUNT(a.id) 
                FROM assignments a
                JOIN module_content mc ON a.module_id = mc.module_id
                JOIN module_progress mp ON mc.module_id = mp.module_id
                LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.user_id = ?
                WHERE mp.user_id = ? 
                AND (sa.status IS NULL OR sa.status = 'pending')
            ");
            $pending_assignments_query->execute([$student_id, $student_id]);
            $pending_assignments = $pending_assignments_query->fetchColumn();

            // Get achievements (completed modules)
            $achievements_query = $pdo->prepare("
                SELECT COUNT(*) FROM module_progress 
                WHERE user_id = ? AND status = 'completed'
            ");
            $achievements_query->execute([$student_id]);
            $achievements = $achievements_query->fetchColumn();
            ?>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-primary">
                <div class="flex items-center">
                    <div class="bg-primary/10 p-3 rounded-full">
                        <i class="fas fa-book text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Enrolled Modules</h4>
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
                        <h4 class="text-gray-500 text-sm">Completed Assignments</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $completed_assignments ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-50 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Pending Tasks</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $pending_assignments ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-50 p-3 rounded-full">
                        <i class="fas fa-trophy text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-gray-500 text-sm">Achievements</h4>
                        <p class="text-2xl font-bold text-gray-800"><?= $achievements ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access and Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Quick Access Tools -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-primary mb-4">Quick Access</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-book-reader text-primary mr-3"></i>
                        <span>My Courses</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-tasks text-primary mr-3"></i>
                        <span>Assignments</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-calendar text-primary mr-3"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-user text-primary mr-3"></i>
                        <span>Profile</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-primary mb-4">Recent Activity</h3>
                <div class="space-y-4">
                    <?php
                    // Get recent activities
                    $activities_query = $pdo->prepare("
                        (SELECT 
                            'assignment' as type,
                            'Submitted Assignment' as action,
                            a.title as item_name,
                            sa.submitted_at as activity_date
                        FROM student_assignments sa
                        JOIN assignments a ON sa.assignment_id = a.id
                        WHERE sa.user_id = ? AND sa.status = 'submitted')
                        UNION
                        (SELECT 
                            'module' as type,
                            'Completed Module' as action,
                            m.title as item_name,
                            mp.completed_at as activity_date
                        FROM module_progress mp
                        JOIN modules m ON mp.module_id = m.id
                        WHERE mp.user_id = ? AND mp.status = 'completed')
                        ORDER BY activity_date DESC
                        LIMIT 5
                    ");
                    $activities_query->execute([$student_id, $student_id]);
                    $activities = $activities_query->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($activities as $activity):
                        $icon_class = $activity['type'] === 'assignment' ? 'fa-file-alt text-blue-500' : 'fa-check text-green-500';
                        $bg_class = $activity['type'] === 'assignment' ? 'bg-blue-50' : 'bg-green-50';
                        $time_ago = getTimeAgo(strtotime($activity['activity_date']));
                    ?>
                        <div class="flex items-start">
                            <div class="<?= $bg_class ?> p-2 rounded-full">
                                <i class="fas <?= $icon_class ?>"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium"><?= $activity['action'] ?>: <?= htmlspecialchars($activity['item_name']) ?></p>
                                <p class="text-xs text-gray-500"><?= $time_ago ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-primary mb-4">Upcoming Deadlines</h3>
                <div class="space-y-4">
                    <?php
                    // Get upcoming deadlines
                    $deadlines_query = $pdo->prepare("
                        SELECT 
                            a.id,
                            a.title,
                            a.due_date,
                            DATEDIFF(a.due_date, CURRENT_DATE) as days_left
                        FROM assignments a
                        JOIN module_content mc ON a.module_id = mc.module_id
                        JOIN module_progress mp ON mc.module_id = mp.module_id
                        LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.user_id = ?
                        WHERE mp.user_id = ?
                        AND (sa.status IS NULL OR sa.status = 'pending')
                        AND a.due_date >= CURRENT_DATE
                        ORDER BY a.due_date ASC
                        LIMIT 3
                    ");
                    $deadlines_query->execute([$student_id, $student_id]);
                    $deadlines = $deadlines_query->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($deadlines as $deadline):
                        if ($deadline['days_left'] <= 2) {
                            $bg_class = 'bg-red-50';
                            $icon_class = 'text-red-500';
                            $icon = 'fa-exclamation-circle';
                        } elseif ($deadline['days_left'] <= 5) {
                            $bg_class = 'bg-yellow-50';
                            $icon_class = 'text-yellow-500';
                            $icon = 'fa-clock';
                        } else {
                            $bg_class = 'bg-blue-50';
                            $icon_class = 'text-blue-500';
                            $icon = 'fa-calendar';
                        }
                    ?>
                        <div class="flex items-center justify-between p-3 <?= $bg_class ?> rounded-lg">
                            <div class="flex items-center">
                                <i class="fas <?= $icon ?> <?= $icon_class ?> mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium"><?= htmlspecialchars($deadline['title']) ?></p>
                                    <p class="text-xs text-gray-500">
                                        Due in <?= $deadline['days_left'] ?> days
                                    </p>
                                </div>
                            </div>
                            <a href="submit_assignment.php?id=<?= $deadline['id'] ?>" 
                               class="text-xs text-primary hover:text-secondary">View</a>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($deadlines)): ?>
                        <p class="text-gray-500 italic text-center">No upcoming deadlines</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Enrolled Modules -->
        <h2 class="text-2xl font-bold text-primary mb-6">Your Enrolled Modules</h2>

        <?php
        // Fetch enrolled modules for the current student
        $modules_query = $pdo->prepare("
            SELECT 
                m.id,
                m.title,
                m.description,
                mp.status,
                mp.completion_percentage
            FROM modules m
            INNER JOIN module_progress mp ON m.id = mp.module_id
            WHERE mp.user_id = ?
            ORDER BY m.order_number ASC
        ");
        $modules_query->execute([$student_id]);
        $modules = $modules_query->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($modules)): ?>
            <div class="bg-blue-50 text-blue-600 p-4 rounded-lg">
                <p>You are not enrolled in any modules at the moment.</p>
                <a href="../pages/courses.php" class="inline-block mt-2 text-primary hover:text-secondary">
                    Browse Available Modules →
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($modules as $module): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-primary p-6">
                            <h3 class="text-xl font-bold text-secondary mb-2">
                                <?= htmlspecialchars($module['title']) ?>
                            </h3>
                            <div class="flex justify-between items-center text-white text-sm">
                                <span class="px-3 py-1 bg-white/10 rounded-full">
                                    <?= ucfirst($module['status']) ?>
                                </span>
                                <span><?= $module['completion_percentage'] ?>% Complete</span>
                            </div>
                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="w-full bg-white/10 rounded-full h-2">
                                    <div class="bg-secondary h-2 rounded-full" 
                                         style="width: <?= $module['completion_percentage'] ?>%"></div>
                                </div>
                            </div>
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