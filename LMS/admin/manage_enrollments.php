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
                                   JOIN assignments a ON s.assignment_id = a.id 
                                   JOIN course_modules m ON a.module_id = m.id 
                                   WHERE s.student_id = e.user_id AND m.course_id = e.course_id) as submission_count
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
            <a href="admin_dashboard.php" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
                <i class="fas fa-arrow-left"></i> Go Back to Dashboard
            </a>
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
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">Current Enrollments</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-blue-100 shadow-sm rounded-lg">
                    <thead class="bg-blue-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Enrolled</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Submissions</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Actions</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Onboard</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-block bg-yellow-100 text-yellow-800 font-semibold px-3 py-1 rounded-full text-xs shadow"> <?= htmlspecialchars($enrollment['student_name']) ?> </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-block bg-yellow-100 text-yellow-800 font-semibold px-3 py-1 rounded-full text-xs shadow"> <?= htmlspecialchars($enrollment['course_name']) ?> </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700"> <?= date('M j, Y', strtotime($enrollment['enrollment_date'])) ?> </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $course_id = $enrollment['course_id'];
                                    $user_id = $enrollment['user_id'];
                                    $course_stmt = $pdo->prepare("SELECT price_type FROM courses WHERE id = ?");
                                    $course_stmt->execute([$course_id]);
                                    $ctype = $course_stmt->fetchColumn();
                                    if ($ctype === 'paid') {
                                        $pay_stmt = $pdo->prepare("SELECT status FROM payments WHERE user_id = ? AND course_id = ? ORDER BY created_at DESC LIMIT 1");
                                        $pay_stmt->execute([$user_id, $course_id]);
                                        $pstat = $pay_stmt->fetchColumn();
                                        if ($pstat) {
                                            echo '<span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">' . ucfirst($pstat) . '</span>';
                                        } else {
                                            echo '<span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pending</span>';
                                        }
                                    } else {
                                        echo '<span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= $enrollment['submission_count'] ?> submissions
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="send_onboarding.php" method="POST" class="inline-block mr-2">
                                        <input type="hidden" name="user_id" value="<?= $enrollment['user_id'] ?>">
                                        <input type="hidden" name="course_id" value="<?= $enrollment['course_id'] ?>">
                                        <?php
                                        $invite_sent = $pdo->prepare('SELECT calendar_invite_sent FROM enrollments WHERE user_id = ? AND course_id = ?');
                                        $invite_sent->execute([$enrollment['user_id'], $enrollment['course_id']]);
                                        $invite = $invite_sent->fetchColumn();
                                        if ($invite) {
                                            echo '<span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Sent</span>';
                                            echo '<button type="submit" class="ml-2 text-blue-600 underline">Resend</button>';
                                        } else {
                                            echo '<button type="submit" class="text-primary underline">Send Invite</button>';
                                        }
                                        ?>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="send_certificate_email.php" method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $enrollment['user_id'] ?>">
                                        <input type="hidden" name="course_id" value="<?= $enrollment['course_id'] ?>">
                                        <?php
                                        $cert_emailed = $pdo->prepare('SELECT certificate_emailed FROM certificates WHERE user_id = ? AND course_id = ?');
                                        $cert_emailed->execute([$enrollment['user_id'], $enrollment['course_id']]);
                                        $emailed = $cert_emailed->fetchColumn();
                                        if ($emailed) {
                                            echo '<span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Emailed</span>';
                                            echo '<button type="submit" class="ml-2 text-blue-600 underline">Resend</button>';
                                        } else {
                                            echo '<button type="submit" class="text-primary underline">Send Email</button>';
                                        }
                                        ?>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="" method="POST" class="inline-block mr-2">
                                        <input type="hidden" name="user_id" value="<?= $enrollment['user_id'] ?>">
                                        <input type="hidden" name="course_id" value="<?= $enrollment['course_id'] ?>">
                                        <button type="submit" name="unenroll_student" 
                                                onclick="return confirm('Are you sure you want to unenroll this student?')"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-user-minus mr-1"></i> Unenroll
                                        </button>
                                    </form>
                                    <?php
                                    // Check eligibility for certificate (not already issued)
                                    $cert_exists = $pdo->prepare('SELECT id FROM certificates WHERE user_id = ? AND course_id = ? AND status = "issued"');
                                    $cert_exists->execute([$enrollment['user_id'], $enrollment['course_id']]);
                                    if (!$cert_exists->fetch()) {
                                    ?>
                                    <form action="generate_certificate.php" method="POST" class="inline-block issue-cert-form">
                                        <input type="hidden" name="user_id" value="<?= $enrollment['user_id'] ?>">
                                        <input type="hidden" name="course_id" value="<?= $enrollment['course_id'] ?>">
                                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-secondary transition duration-300" aria-label="Issue Certificate">
                                            <i class="fas fa-certificate mr-1"></i> Issue Certificate
                                        </button>
                                    </form>
                                    <?php } ?>
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
