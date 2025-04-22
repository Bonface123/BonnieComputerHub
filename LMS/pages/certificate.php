<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if (!$course_id) {
    echo '<div class="text-center text-red-600 font-bold py-12">Invalid course ID.</div>';
    exit;
}

$breadcrumbs = [
    "Home" => "../index.php",
    "Courses" => "courses.php",
    htmlspecialchars($row['course_name'] ?? '') => "course_student_view.php?id=$course_id",
    "Certificate" => ""
];
include '../includes/breadcrumbs.php';

// Fetch course and enrollment info
$stmt = $pdo->prepare("SELECT c.*, e.status as enrollment_status, u.name as student_name FROM enrollments e JOIN courses c ON e.course_id = c.id JOIN users u ON e.user_id = u.id WHERE e.user_id = ? AND e.course_id = ?");
$stmt->execute([$user_id, $course_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo '<div class="text-center text-red-600 font-bold py-12">Enrollment not found.</div>';
    exit;
}
if ($row['enrollment_status'] !== 'completed') {
    echo '<div class="text-center text-yellow-700 font-bold py-12">You must complete the course to download your certificate.</div>';
    exit;
}
// === CERTIFICATE RECORD ENSURE ===
$cert_check = $pdo->prepare('SELECT id, certificate_code FROM certificates WHERE user_id = ? AND course_id = ?');
$cert_check->execute([$user_id, $course_id]);
$cert_row = $cert_check->fetch(PDO::FETCH_ASSOC);
if (!$cert_row) {
    $cert_code = strtoupper(substr(md5($user_id . '_' . $course_id . '_' . date('YmdHis')), 0, 12));
    $issued_at = date('Y-m-d H:i:s');
    $insert_cert = $pdo->prepare('INSERT INTO certificates (user_id, course_id, certificate_code, status, issued_at, pdf_path) VALUES (?, ?, ?, "issued", ?, NULL)');
    $insert_cert->execute([$user_id, $course_id, $cert_code, $issued_at]);
    $certificate_code = $cert_code;
} else {
    $certificate_code = $cert_row['certificate_code'];
}
// === END CERTIFICATE RECORD ENSURE ===

// Generate certificate image (PNG) using GD
$student_name = $row['student_name'];
$course_name = $row['course_name'];
$date = date('F j, Y');
$cert_id = strtoupper(substr(md5($user_id . $course_id . $date), 0, 8));

// Certificate design
$width = 1200;
$height = 850;
$im = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($im, 255, 255, 255);
$primary = imagecolorallocate($im, 0, 33, 71);
$accent = imagecolorallocate($im, 255, 215, 0);
$gray = imagecolorallocate($im, 100, 100, 100);

imagefilledrectangle($im, 0, 0, $width, $height, $bg);

// Border/accent
imagesetthickness($im, 10);
imagerectangle($im, 20, 20, $width-20, $height-20, $primary);

// BCH Logo (optional)
// Place your logo at (60, 60) if available

// Heading
$font = __DIR__ . '/../assets/fonts/Inter-Bold.ttf';
$font_regular = __DIR__ . '/../assets/fonts/Inter-Regular.ttf';
$font_size = 48;
$font_size_small = 28;
$font_size_name = 56;

imagettftext($im, $font_size, 0, 340, 180, $primary, $font, 'Certificate of Completion');
imagettftext($im, $font_size_small, 0, 340, 240, $gray, $font_regular, 'This certifies that');

// Student name
imagettftext($im, $font_size_name, 0, 340, 330, $accent, $font, $student_name);

// Course name
imagettftext($im, $font_size_small, 0, 340, 400, $primary, $font_regular, 'has successfully completed the course:');
imagettftext($im, $font_size_small, 0, 340, 450, $primary, $font, $course_name);

// Date and cert id
imagettftext($im, 22, 0, 340, 520, $gray, $font_regular, 'Date: ' . $date);
imagettftext($im, 22, 0, 340, 560, $gray, $font_regular, 'Certificate ID: ' . $cert_id);

// BCH signature/accreditation
imagettftext($im, 24, 0, 340, 650, $primary, $font_regular, 'Bonnie Computer Hub');

// Output as PNG for preview or download
if (isset($_GET['preview'])) {
    // Overlay a 'Preview' watermark (optional, subtle)
    $watermark = 'PREVIEW';
    $wm_color = imagecolorallocatealpha($im, 180, 180, 180, 80); // light gray, semi-transparent
    imagettftext($im, 80, 30, 400, 600, $wm_color, $font, $watermark);
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="BCH_Certificate_' . $cert_id . '_preview.png"');
    imagepng($im);
} else {
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="BCH_Certificate_' . $cert_id . '.png"');
    imagepng($im);
}
imagedestroy($im);
// === BADGE AWARDING LOGIC ===
// Award badges for course completion
$badge_msgs = [];
// Get badge ids
$badge_map = [];
$badge_rows = $pdo->query("SELECT id, name, criteria FROM badges")->fetchAll(PDO::FETCH_ASSOC);
foreach ($badge_rows as $b) $badge_map[$b['criteria']] = $b['id'];
// 1. First Course Completed
$completed_count = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND status = "completed"');
$completed_count->execute([$user_id]);
$completed = $completed_count->fetchColumn();
if ($completed == 1) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
    if ($stmt->execute([$user_id, $badge_map['complete_1']])) $badge_msgs[] = "Congratulations! You earned the 'First Course Completed' badge.";
}
// 2. 100% Completion (every certificate means 100%)
$stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
if ($stmt->execute([$user_id, $badge_map['course_100']])) $badge_msgs[] = "You earned the '100% Completion' badge!";
// 3. 3 Courses Completed
if ($completed == 3) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
    if ($stmt->execute([$user_id, $badge_map['complete_3']])) $badge_msgs[] = "Awesome! You earned the '3 Courses Completed' badge!";
}
// Show badge notification
foreach ($badge_msgs as $msg) {
    echo '<div class="max-w-lg mx-auto mt-8 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded p-4 text-center font-semibold">' . htmlspecialchars($msg) . '</div>';
}
// === END BADGE LOGIC ===

// Feedback form logic
if (!isset($_GET['preview'])) {
    $feedback_stmt = $pdo->prepare('SELECT id FROM course_feedback WHERE user_id = ? AND course_id = ?');
    $feedback_stmt->execute([$user_id, $course_id]);
    $has_feedback = $feedback_stmt->fetchColumn();
    if (!$has_feedback) {
        echo '<div class="max-w-lg mx-auto mt-10 bg-white border rounded shadow p-8 text-center">';
        echo '<h2 class="text-xl font-bold text-primary mb-4">We Value Your Feedback!</h2>';
        echo '<form method="post" action="">';
        echo '<label class="block text-gray-700 font-semibold mb-2">Rate this course:</label>';
        echo '<div class="flex justify-center gap-2 mb-4">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<label><input type="radio" name="rating" value="' . $i . '" required> <span class="text-yellow-500 text-lg">&#9733;</span></label>';
        }
        echo '</div>';
        echo '<textarea name="comments" placeholder="Comments (optional)" class="w-full border rounded px-3 py-2 mb-4"></textarea>';
        echo '<button type="submit" name="submit_feedback" class="bg-primary text-white px-6 py-2 rounded">Submit Feedback</button>';
        echo '</form>';
        echo '</div>';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments'] ?? '');
    $stmt = $pdo->prepare('INSERT INTO course_feedback (user_id, course_id, rating, comments) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $course_id, $rating, $comments]);
    echo '<div class="max-w-lg mx-auto mt-10 bg-green-100 text-green-800 border border-green-200 rounded p-6 text-center font-semibold">Thank you for your feedback!</div>';
}
exit;
