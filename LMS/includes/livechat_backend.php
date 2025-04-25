<?php
// livechat_backend.php: Simple AJAX-based live chat backend
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false];

if ($action === 'send') {
    $msg = trim($_POST['message'] ?? '');
    $name = trim($_POST['name'] ?? 'Visitor');
    $escalate = isset($_POST['escalate']) ? intval($_POST['escalate']) : 0;
    if ($msg) {
        $stmt = $pdo->prepare('INSERT INTO livechat_messages (name, message, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$name, $msg]);
        $response['success'] = true;

        // --- Escalation logic ---
        $lower = strtolower($msg);
        $escalation_keywords = ['human', 'agent', 'help', 'support', 'real person'];
        $needs_escalation = $escalate;
        foreach ($escalation_keywords as $kw) {
            if (strpos($lower, $kw) !== false) {
                $needs_escalation = 1;
                break;
            }
        }
        if ($needs_escalation) {
            // Optionally, flag in DB for admin attention (not implemented here)
            $botReply = "I've notified a human agent. Someone will assist you as soon as possible.";
            $stmt = $pdo->prepare('INSERT INTO livechat_messages (name, message, created_at) VALUES (?, ?, NOW())');
            $stmt->execute(['Bot', $botReply]);
        } else {
            // --- Rule-based chatbot logic ---
            $botReply = null;
            // Expanded rules for BCH
            if (strpos($lower, 'hello') !== false || strpos($lower, 'hi') !== false || strpos($lower, 'good morning') !== false || strpos($lower, 'good afternoon') !== false) {
                $botReply = "Hello! How can I help you today?";
            } elseif (strpos($lower, 'price') !== false || strpos($lower, 'cost') !== false || strpos($lower, 'fee') !== false) {
                $botReply = "You can view our pricing and fees on the Courses page. Is there a specific service or class you want to know about?";
            } elseif (strpos($lower, 'contact') !== false || strpos($lower, 'email') !== false || strpos($lower, 'phone') !== false) {
                $botReply = "You can contact us at Bonniecomputerhub24@gmail.com or call +254 729 820 689.";
            } elseif (strpos($lower, 'class') !== false || strpos($lower, 'course') !== false || strpos($lower, 'lesson') !== false) {
                $botReply = "We offer a variety of classes and courses including ICT, coding, digital literacy, and more. Please visit the Classes or Courses page for details.";
            } elseif (strpos($lower, 'location') !== false || strpos($lower, 'where') !== false || strpos($lower, 'address') !== false) {
                $botReply = "We are located in Nairobi, Kenya. Check our Contact page for a map and directions.";
            } elseif (strpos($lower, 'hours') !== false || strpos($lower, 'open') !== false || strpos($lower, 'close') !== false || strpos($lower, 'time') !== false) {
                $botReply = "Our office hours are Monday to Friday, 9:00 AM to 5:00 PM.";
            } elseif (strpos($lower, 'services') !== false) {
                $botReply = "Bonnie Computer Hub offers training, tech services, computer sales, repairs, and more. Visit our Services page for the full list.";
            } elseif (strpos($lower, 'payment') !== false || strpos($lower, 'mpesa') !== false) {
                $botReply = "We accept various payment methods including Mpesa. For details, please contact us directly.";
            } elseif (strpos($lower, 'refund') !== false) {
                $botReply = "Refunds are handled on a case-by-case basis. Please contact support for assistance.";
            } elseif (strpos($lower, 'instructor') !== false || strpos($lower, 'teacher') !== false) {
                $botReply = "Our instructors are certified professionals with years of experience in the tech industry.";
            } elseif (strpos($lower, 'certificate') !== false || strpos($lower, 'certification') !== false) {
                $botReply = "Yes, we offer certificates for most of our courses upon successful completion.";
            } elseif (strpos($lower, 'blog') !== false) {
                $botReply = "Check out our Blog page for the latest updates, tips, and tech news.";
            } elseif (strpos($lower, 'about') !== false) {
                $botReply = "Bonnie Computer Hub empowers individuals and businesses through technology education and solutions. Learn more on our About page.";
            } elseif (strpos($lower, 'join') !== false || strpos($lower, 'enroll') !== false || strpos($lower, 'register') !== false) {
                $botReply = "To join a class or course, visit the Classes or Courses page and click 'Enroll' or 'Join'.";
            } elseif (strpos($lower, 'discount') !== false || strpos($lower, 'offer') !== false) {
            }
            if ($botReply) {
                $stmt = $pdo->prepare('INSERT INTO livechat_messages (message, is_user, created_at) VALUES (?, ?, NOW())');
                $stmt->execute([$botReply, 0]);
            }
        }
    }
} elseif ($action === 'fetch') {
    $lastId = intval($_GET['last_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id, message, is_user, created_at FROM livechat_messages WHERE id > ? ORDER BY id ASC');
    $stmt->execute([$lastId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}
echo json_encode($response);
