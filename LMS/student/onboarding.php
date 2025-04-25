<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../pages/login.php");
    exit();
}
$course_name = isset($_GET['course']) ? htmlspecialchars($_GET['course']) : 'Your Course';
$mode = isset($_GET['mode']) ? htmlspecialchars($_GET['mode']) : '';
$intake = isset($_GET['intake']) ? htmlspecialchars($_GET['intake']) : '';
$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
require_once '../includes/db_connect.php';
$show_payment_status = false;
$payment_status = '';
$show_pay_btn = false;
if ($cid > 0) {
    // Get course price
    $stmt = $pdo->prepare("SELECT price, discount_price FROM courses WHERE id = ?");
    $stmt->execute([$cid]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_paid = $course && ($course['price'] > 0);
    if ($is_paid) {
        $show_payment_status = true;
        $user_id = $_SESSION['user_id'];
        $pay_stmt = $pdo->prepare("SELECT status FROM payments WHERE user_id = ? AND course_id = ? ORDER BY created_at DESC LIMIT 1");
        $pay_stmt->execute([$user_id, $cid]);
        $payment = $pay_stmt->fetch(PDO::FETCH_ASSOC);
        if ($payment && $payment['status'] === 'completed') {
            $payment_status = 'completed';
        } elseif ($payment && $payment['status'] === 'pending') {
            $payment_status = 'pending';
            $show_pay_btn = false;
        } else {
            $payment_status = 'not_paid';
            $show_pay_btn = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= $course_name ?> | BCH Learning</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .onboarding-card { background: #fff; border-radius: 1.25rem; box-shadow: 0 8px 24px rgba(30,64,175,0.07); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <main class="onboarding-card p-10 max-w-xl w-full text-center">
        <i class="fas fa-graduation-cap text-primary text-5xl mb-4"></i>
        <h1 class="text-2xl font-bold text-primary mb-2">Welcome to <?= $course_name ?>!</h1>
        <p class="text-gray-700 mb-6">
            <?php if ($show_payment_status): ?>
                <?php if ($payment_status === 'completed'): ?>
                    <span class="bch-bg-green-50 bch-border-l-4 bch-border-green-500 bch-text-green-700 bch-p-3 bch-rounded block mb-2"><i class="fas fa-check-circle mr-2"></i>Payment received! You are fully enrolled.</span>
                <?php elseif ($payment_status === 'pending'): ?>
                    <span class="bch-bg-yellow-50 bch-border-l-4 bch-border-yellow-500 bch-text-yellow-700 bch-p-3 bch-rounded block mb-2"><i class="fas fa-hourglass-half mr-2"></i>Your payment is pending verification. Please wait or contact support if it takes too long.</span>
                <?php elseif ($payment_status === 'not_paid'): ?>
                    <span class="bch-bg-red-50 bch-border-l-4 bch-border-red-500 bch-text-red-700 bch-p-3 bch-rounded block mb-2"><i class="fas fa-exclamation-circle mr-2"></i>No payment found for this course. Please complete your payment to access course materials.</span>
                <?php endif; ?>
                <?php if ($show_pay_btn): ?>
                    <a href="../pages/course_detail.php?id=<?= $cid ?>&pay=1" class="bch-btn bch-bg-primary bch-text-white bch-py-2 bch-px-6 bch-rounded hover:bch-bg-blue-700 transition font-bold mt-2 inline-block">Complete Payment</a>
                <?php endif; ?>
            <?php elseif ($mode === 'self-paced'): ?>
                You're now enrolled. Start learning at your own pace!
            <?php elseif ($mode === 'instructor-led'): ?>
                You're enrolled in an instructor-led course. <?php if ($intake): ?>The next intake starts on <span class="font-semibold text-primary"><?= $intake ?></span>.<?php endif; ?>
            <?php else: ?>
                You're now enrolled. Explore your new course!
            <?php endif; ?>
        </p>
        <div class="mb-6">
            <ul class="text-left text-gray-600 space-y-2">
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i>View the course curriculum and resources</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Track your progress as you learn</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Access support from instructors and peers</li>
                <li><i class="fas fa-check-circle text-green-500 mr-2"></i>Earn a certificate on completion</li>
            </ul>
        </div>
        <a href="../pages/course_player.php?course_id=<?= isset($_GET['cid']) ? intval($_GET['cid']) : '' ?>" class="bg-primary text-white font-semibold px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition text-lg inline-block">Start Course</a>
        <div class="mt-6">
            <a href="courses.php" class="text-secondary hover:underline"><i class="fas fa-arrow-left mr-1"></i>Back to Courses</a>
        </div>
    </main>
</body>
</html>
