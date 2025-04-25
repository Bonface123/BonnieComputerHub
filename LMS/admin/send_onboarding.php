<?php
require_once '../includes/db_connect.php';
require_once '../includes/send_mail.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['course_id'])) {
    $user_id = (int)$_POST['user_id'];
    $course_id = (int)$_POST['course_id'];
    // Fetch user and course info
    $user = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
    $user->execute([$user_id]);
    $user = $user->fetch(PDO::FETCH_ASSOC);
    $course = $pdo->prepare('SELECT course_name FROM courses WHERE id = ?');
    $course->execute([$course_id]);
    $course = $course->fetch(PDO::FETCH_ASSOC);
    // Send real onboarding/calendar invite email
    $subject = "[Bonnie Computer Hub] Course Onboarding: " . $course['course_name'];
    $body = "Hello {$user['name']},\n\nYou have been enrolled in the course: {$course['course_name']}.\n\nPlease check your dashboard for onboarding details and calendar events.\n\nBest regards,\nBonnie Computer Hub Team";
    $result = bch_send_mail($user['email'], $user['name'], $subject, $body);
    if ($result['success']) {
        $pdo->prepare('UPDATE enrollments SET calendar_invite_sent = 1 WHERE user_id = ? AND course_id = ?')->execute([$user_id, $course_id]);
        $_SESSION['success_msg'] = 'Calendar invite sent to ' . htmlspecialchars($user['email']);
    } else {
        $_SESSION['error_msg'] = 'Failed to send invite: ' . htmlspecialchars($result['error']);
    }
}
header('Location: manage_enrollments.php');
exit;
