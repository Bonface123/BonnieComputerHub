<?php
session_start();
require_once 'includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if (!$course_id) {
    echo '<div class="text-center text-red-600 font-bold py-12">Invalid course ID.</div>';
    exit;
}

// Fetch course info
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<div class="text-center text-red-600 font-bold py-12">Course not found.</div>';
    exit;
}

// Check if already enrolled
$enrolled_stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$enrolled_stmt->execute([$user_id, $course_id]);
if ($enrolled_stmt->fetch()) {
    header('Location: pages/student_dashboard.php?msg=already_enrolled');
    exit;
}

// Handle payment for paid courses
$is_paid = ($course['price'] > 0);
if ($is_paid) {
    // Payment simulation for MVP (replace with real gateway later)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
        // Payment confirmed (simulate success)
        $paid = true;
    } else {
        $paid = false;
    }
    if (!$paid) {
        // Show payment options
        include 'includes/header.php';
        echo '<main class="container mx-auto px-4 py-10">';
        echo '<div class="bg-white rounded-xl shadow-lg p-8 max-w-lg mx-auto text-center">';
        echo '<h2 class="text-2xl font-bold mb-4">Payment Required</h2>';
        echo '<p class="mb-4">This course requires payment to enroll.</p>';
        echo '<div class="mb-6 text-xl font-bold text-blue-700">Price: KES ' . number_format($course['price']) . '</div>';
        echo '<form method="POST">';
        echo '<button type="submit" name="confirm_payment" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-6 py-3 rounded-lg shadow transition text-lg w-full">Pay & Enroll</button>';
        echo '</form>';
        echo '<a href="pages/courses.php" class="block mt-6 text-blue-600 hover:underline">Back to Courses</a>';
        echo '</div></main>';
        include 'includes/footer.php';
        exit;
    }
}

// Enroll student
$enroll_stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, enrolled_at, status) VALUES (?, ?, NOW(), 'active')");
$enroll_stmt->execute([$user_id, $course_id]);

// Send enrollment confirmation and admin notification
require_once 'includes/send_mail.php';
$user_stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
$subject = "Enrollment Confirmation: {$course['course_name']}";
$body = "Hello {$user['name']},\n\nYou have been successfully enrolled in the course: {$course['course_name']}.\n\nWelcome to Bonnie Computer Hub!";
$studentMail = bch_send_mail($user['email'], $user['name'], $subject, $body);
$admin_stmt = $pdo->query("SELECT email, name FROM users WHERE role = 'admin' LIMIT 1");
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
$adminMail = ["success"=>true];
if ($admin) {
    $asubject = "[Admin Notice] New Enrollment: {$course['course_name']}";
    $abody = "Student {$user['name']} ({$user['email']}) has enrolled in {$course['course_name']}.";
    $adminMail = bch_send_mail($admin['email'], $admin['name'], $asubject, $abody);
}
if ($studentMail['success'] && $adminMail['success']) {
    header('Location: pages/student_dashboard.php?msg=enrolled_success');
    exit;
} else {
    echo '<div class="text-center text-red-600 font-bold py-12">Enrollment succeeded, but failed to send notification email(s): ' . htmlspecialchars($studentMail['error'] ?? $adminMail['error']) . '</div>';
    exit;
}
