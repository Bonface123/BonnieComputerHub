<?php
// BCH LMS - Handle Feedback Submission
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['feedback'], $_POST['rating'])) {
    $user_id = $_SESSION['user_id'];
    $course_id = intval($_POST['course_id']);
    $feedback = trim($_POST['feedback']);
    $rating = intval($_POST['rating']);
    // Validate rating and feedback
    if ($feedback !== '' && $rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare('INSERT INTO course_feedback (user_id, course_id, feedback, rating, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$user_id, $course_id, $feedback, $rating]);
        // Send emails
        require_once '../includes/send_mail.php';
        // Fetch student info
        $user_stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
        $user_stmt->execute([$user_id]);
        $student = $user_stmt->fetch(PDO::FETCH_ASSOC);
        // Fetch course info
        $course_stmt = $pdo->prepare('SELECT course_name, instructor_id FROM courses WHERE id = ?');
        $course_stmt->execute([$course_id]);
        $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
        // Email student
        $courseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/BonnieComputerHub/LMS/pages/course_details.php?id=' . $course_id;
        $subject = "Feedback Received for {$course['course_name']}";
        $body = "Hello {$student['name']},\n\nThank you for submitting feedback for {$course['course_name']}!\nYou can view the course here: $courseUrl\n\nBest regards,\nBonnie Computer Hub Team";
        $studentMail = bch_send_mail($student['email'], $student['name'], $subject, $body);
        // Notify instructor
        $instructorMail = ["success"=>true];
        if ($course && $course['instructor_id']) {
            $instructor_stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
            $instructor_stmt->execute([$course['instructor_id']]);
            $instructor = $instructor_stmt->fetch(PDO::FETCH_ASSOC);
            $ifeedbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/BonnieComputerHub/LMS/instructor/manage_feedback.php?course_id=' . $course_id;
            $isubject = "New Feedback Submitted for {$course['course_name']}";
            $ibody = "Student {$student['name']} has submitted feedback for your course: {$course['course_name']}.\nView feedback: $ifeedbackUrl";
            $instructorMail = bch_send_mail($instructor['email'], $instructor['name'], $isubject, $ibody);
        }
        // Notify admin (optional, if admin email is set)
        $adminMail = ["success"=>true];
        $admin_stmt = $pdo->query("SELECT name, email FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $afeedbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/BonnieComputerHub/LMS/admin/manage_feedback.php?course_id=' . $course_id;
            $asubject = "[Admin Notice] New Feedback for {$course['course_name']}";
            $abody = "A new feedback has been submitted for course: {$course['course_name']}.\nView feedback: $afeedbackUrl";
            $adminMail = bch_send_mail($admin['email'], $admin['name'], $asubject, $abody);
        }
        if ($studentMail['success'] && $instructorMail['success'] && $adminMail['success']) {
            $_SESSION['success_msg'] = 'Thank you for your feedback!';
        } else {
            $_SESSION['error_msg'] = 'Feedback submitted, but failed to send notification email(s).';
        }
    } else {
        $_SESSION['error_msg'] = 'Please provide valid feedback and a rating between 1 and 5.';
    }
}
header('Location: courses.php');
exit;
