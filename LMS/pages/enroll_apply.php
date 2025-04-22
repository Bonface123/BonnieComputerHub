<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// AJAX: Fetch course name for modal
if (isset($_GET['get_course_name']) && $_GET['get_course_name'] == '1' && isset($_GET['course_id'])) {
    $cid = intval($_GET['course_id']);
    try {
        $stmt = $pdo->prepare('SELECT course_name FROM courses WHERE id = ? LIMIT 1');
        $stmt->execute([$cid]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course && $course['course_name']) {
            echo json_encode(['course_name' => $course['course_name']]);
        } else {
            http_response_code(404);
            error_log("[BCH] Course name fetch failed: No course for id $cid");
            echo json_encode(['course_name' => null, 'error' => 'Course not found']);
        }
    } catch (Exception $ex) {
        http_response_code(500);
        error_log("[BCH] DB error fetching course name for id $cid: " . $ex->getMessage());
        echo json_encode(['course_name' => null, 'error' => 'Database error']);
    }
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$course_id = intval($_POST['course_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
if (!$name || !$email || !$course_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}
$stmt = $pdo->prepare('INSERT INTO course_applications (course_id, name, email, phone, message, applied_at) VALUES (?, ?, ?, ?, ?, NOW())');
try {
    $stmt->execute([$course_id, $name, $email, $phone, $message]);
    require_once '../includes/send_mail.php';
    // Fetch course name for informative emails
    $course_stmt = $pdo->prepare('SELECT course_name FROM courses WHERE id = ? LIMIT 1');
    $course_stmt->execute([$course_id]);
    $course = $course_stmt->fetch(PDO::FETCH_ASSOC);
    $course_name = $course ? $course['course_name'] : 'Unknown Course';

    // Confirmation to applicant
    $subject = "Your Application for $course_name at Bonnie Computer Hub";
    $body = "Hello $name,\n\nThank you for applying for the course: $course_name. Our team will review your application and contact you soon.\n\nIf you have any questions, feel free to reply to this email.\n\nBest regards,\nBonnie Computer Hub Team";
    $studentMail = bch_send_mail($email, $name, $subject, $body);

    // Notify admin
    $admin_stmt = $pdo->query("SELECT email, name FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    $adminMail = ["success"=>true];
    if ($admin) {
        $asubject = "[Admin Notice] New Application for $course_name";
        $abody = "A new course application has been received.\n\nApplicant Name: $name\nEmail: $email\nPhone: $phone\nCourse: $course_name\nMessage: $message\n\nPlease log in to the admin portal for more details.";
        $adminMail = bch_send_mail($admin['email'], $admin['name'], $asubject, $abody);
    }

    if ($studentMail['success'] && $adminMail['success']) {
        echo json_encode(['success' => true]);
    } else {
        // Log errors for admin
        error_log("[BCH] Application email error: Applicant: " . ($studentMail['error'] ?? 'none') . ", Admin: " . ($adminMail['error'] ?? 'none'));
        // Show user-friendly message, but confirm application is saved
        echo json_encode(['success' => false, 'message' => 'Your application was received, but there was an issue sending confirmation emails. Our team will contact you soon.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Could not submit application.']);
}
