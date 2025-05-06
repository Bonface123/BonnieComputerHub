<?php 
session_start();
include '../includes/db_connect.php'; 

// Check if user is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    // Enroll the student in the course
    $sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    ?>
    <a href="dashboard.php" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
        <i class="fas fa-arrow-left"></i> Go Back to Dashboard
    </a>
    <main class="container mx-auto px-4 py-8">
    <?php
    if ($stmt->execute([$_SESSION['user_id'], $course_id])) {
        // Fetch course info for onboarding
        $course_stmt = $pdo->prepare("SELECT course_name, mode, intake_start FROM courses WHERE id = ?");
        $course_stmt->execute([$course_id]);
        $cinfo = $course_stmt->fetch(PDO::FETCH_ASSOC);
        $cname = $cinfo['course_name'] ?? '';
        $mode = $cinfo['mode'] ?? '';
        $intake = isset($cinfo['intake_start']) ? date('M j, Y', strtotime($cinfo['intake_start'])) : '';
        // Fetch student info
        $user_stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $user_stmt->execute([$_SESSION['user_id']]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        require_once '../includes/send_mail.php';
        // Send confirmation to student
        $subject = "Enrollment Confirmation: $cname";
        $body = "Hello {$user['name']},\n\nYou have been successfully enrolled in the course: $cname.\nMode: $mode\nIntake: $intake\n\nWelcome to Bonnie Computer Hub!";
        $studentMail = bch_send_mail($user['email'], $user['name'], $subject, $body);
        // Notify admin (first admin found)
        $admin_stmt = $pdo->query("SELECT email, name FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        $adminMail = ["success"=>true];
        if ($admin) {
            $asubject = "[Admin Notice] New Enrollment: $cname";
            $abody = "Student {$user['name']} ({$user['email']}) has enrolled in $cname.";
            $adminMail = bch_send_mail($admin['email'], $admin['name'], $asubject, $abody);
        }
        if ($studentMail['success'] && $adminMail['success']) {
            $cname = urlencode($cname);
            $mode = urlencode($mode);
            $intake = urlencode($intake);
            header("Location: onboarding.php?course={$cname}&mode={$mode}&intake={$intake}&cid={$course_id}");
            exit();
        } else {
            echo '<div class="text-center text-red-600 font-bold py-12">Enrollment succeeded, but failed to send notification email(s): ' . htmlspecialchars($studentMail['error'] ?? $adminMail['error']) . '</div>';
            exit();
        }
    } else {
        header("Location: view_courses.php?error=Failed to enroll in the course.");
        exit();
    }
    ?>
    </main>
    <?php
} else {
    header("Location: view_courses.php");
    exit();
}
?>
