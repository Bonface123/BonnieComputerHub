<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    try {
        // Check if enrollment already exists
        $check_sql = "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$student_id, $course_id]);
        
        if ($check_stmt->fetchColumn() > 0) {
            $_SESSION['error_msg'] = "Student is already enrolled in this course.";
        } else {
            $insert_sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($insert_sql);
            $stmt->execute([$student_id, $course_id]);
            $_SESSION['success_msg'] = "Student enrolled successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    header("Location: manage_enrollments.php");
    exit;
}

// Handle unenrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unenroll_student'])) {
    $user_id = $_POST['user_id'];
    $course_id = $_POST['course_id'];

    try {
        $delete_sql = "DELETE FROM enrollments WHERE user_id = ? AND course_id = ?";
        $stmt = $pdo->prepare($delete_sql);
        $stmt->execute([$user_id, $course_id]);
        $_SESSION['success_msg'] = "Student unenrolled successfully.";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    header("Location: manage_enrollments.php");
    exit;
}

// Fetch all students
$students = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all courses
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all enrollments with detailed information
$enrollments = $pdo->query("SELECT e.user_id, e.course_id, e.enrollment_date,
                                  u.name AS student_name, u.email AS student_email,
                                  c.course_name,
                                  (SELECT COUNT(*) FROM submissions s 
                                   JOIN course_assignments ca ON s.assignment_id = ca.id 
                                   WHERE s.student_id = e.user_id AND ca.course_id = e.course_id) as submission_count
                           FROM enrollments e 
                           JOIN users u ON e.user_id = u.id 
                           JOIN courses c ON e.course_id = c.id
                           ORDER BY e.enrollment_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments - BCH Learning</title>
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
                        <a href="../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Admin Dashboard</p>
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
            <h1 class="text-2xl font-bold text-primary mb-2">Manage Enrollments</h1>
            <p class="text-gray-600">Manage student enrollments in courses</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <?= $_SESSION['success_msg'] ?>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <?= $_SESSION['error_msg'] ?>
                <?php unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Enroll Student Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-primary mb-6">Enroll Student in Course</h2>
            <form action="" method="POST" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="student_id">Select Student</label>
                        <select name="student_id" id="student_id" required 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <option value="">Select a student...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="course_id">Select Course</label>
                        <select name="course_id" id="course_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <option value="">Select a course...</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>">
                                    <?= htmlspecialchars($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="enroll_student" 
                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition duration-300">
                        <i class="fas fa-user-plus mr-2"></i>Enroll Student
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Enrollments -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">Current Enrollments</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submissions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($enrollment['student_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($enrollment['student_email']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($enrollment['course_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($enrollment['enrollment_date'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= $enrollment['submission_count'] ?> submissions
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $enrollment['user_id'] ?>">
                                        <input type="hidden" name="course_id" value="<?= $enrollment['course_id'] ?>">
                                        <button type="submit" name="unenroll_student" 
                                                onclick="return confirm('Are you sure you want to unenroll this student?')"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-user-minus mr-1"></i> Unenroll
                                        </button>
                                    </form>
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
</body>
</html>
