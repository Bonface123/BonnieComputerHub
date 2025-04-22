<?php
session_start();
require_once '../includes/db_connect.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$course_id) {
    echo '<div class="text-center text-red-600 font-bold py-12">Invalid course ID.</div>';
    exit;
}

// Fetch course info
$stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name, u.email as instructor_email, u.photo as instructor_photo FROM courses c JOIN users u ON c.created_by = u.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<div class="text-center text-red-600 font-bold py-12">Course not found.</div>';
    exit;
}

// Fetch modules and lessons
$modules_stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC");
$modules_stmt->execute([$course_id]);
$modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);
$lessons = [];
foreach ($modules as $module) {
    $content_stmt = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order ASC");
    $content_stmt->execute([$module['id']]);
    $lessons[$module['id']] = $content_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch testimonials (optional)
$testimonials = [];
$testi_stmt = $pdo->prepare("SELECT t.*, u.name as student_name FROM testimonials t JOIN users u ON t.user_id = u.id WHERE t.course_id = ?");
$testi_stmt->execute([$course_id]);
$testimonials = $testi_stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $course['course_name'] . ' - Course Details';
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
    <div class="bg-white/90 rounded-2xl shadow-2xl p-10 max-w-3xl mx-auto border border-blue-100 mt-10 mb-12">
    <div class="flex items-center gap-3 mb-4">
        <a href="courses.php" class="text-blue-700 hover:text-yellow-500 font-semibold text-sm flex items-center gap-1 focus:outline-none focus:underline"><i class="fas fa-arrow-left"></i> All Courses</a>
    </div>
        <div class="flex items-center gap-3 mb-2">
    <h1 class="text-3xl font-bold text-primary mb-0"><?= htmlspecialchars($course['course_name']) ?></h1>
    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold ml-2 <?= $course['mode']==='self-paced'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700' ?>">
        <?= $course['mode']==='self-paced'?'Self-Paced':'Instructor-Led' ?>
    </span>
</div>
<!-- Apply Now CTA Button (identical to courses.php) -->
<div class="mb-6">
    <button type="button"
        class="open-enroll-modal-btn bg-yellow-500 hover:bg-yellow-600 text-primary font-bold px-6 py-3 rounded-xl border-2 border-yellow-400 shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 text-lg"
        onclick="openApplyModal(<?= $course['id'] ?>)"
        aria-label="Apply for <?= htmlspecialchars($course['course_name']) ?>"
    >
        <i class="fas fa-paper-plane mr-2"></i> Apply Now
    </button>
</div>
<!-- The modal HTML and logic is injected by apply-modal.js -->
        <div class="flex flex-col md:flex-row md:items-center md:gap-8 mb-6">
    <?php if (!empty($course['banner_image'])): ?>
        <img src="../uploads/banners/<?= htmlspecialchars($course['banner_image']) ?>" alt="Course Banner" class="h-32 w-64 object-cover rounded mb-4 md:mb-0">
    <?php elseif (!empty($course['thumbnail'])): ?>
        <img src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>" alt="Course Thumbnail" class="h-32 w-32 object-cover rounded mb-4 md:mb-0">
    <?php endif; ?>
    <div>
        <div class="flex flex-wrap gap-2 mb-2">
            <span class="inline-block bg-gray-50 text-blue-900 px-3 py-1 rounded text-base font-bold border border-blue-100" aria-label="Price">
                Price: KES <?= ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']) ? number_format($course['discount_price']) : number_format($course['price']) ?>
            </span>
        </div>
        <p class="text-gray-700 mb-2"><span class="font-semibold">Instructor:</span> <?= htmlspecialchars($course['instructor_name']) ?></p>
        <p class="text-gray-700 mb-2"><span class="font-semibold">Skill Level:</span> <?= htmlspecialchars($course['skill_level']) ?></p>
        <p class="text-gray-700 mb-2"><span class="font-semibold">Duration:</span> <?= htmlspecialchars($course['duration_weeks']) ?> weeks</p>
        <?php if (!empty($course['delivery_mode'])): ?>
            <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-medium mr-2" aria-label="Delivery Mode">
                <?= htmlspecialchars($course['delivery_mode']) ?>
            </span>
        <?php endif; ?>
        <?php if (!empty($course['certification'])): ?>
            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-xs font-medium">Certificate on Completion</span>
        <?php endif; ?>
        <?php if (!empty($course['tags'])): ?>
            <div class="flex flex-wrap gap-2 mt-2">
                <?php foreach (explode(',', $course['tags']) as $tag): ?>
                    <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2 py-1 rounded-full" aria-label="Tag: <?= htmlspecialchars(trim($tag)) ?>">
                        #<?= htmlspecialchars(trim($tag)) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($course['tools'])): ?>
            <div class="flex flex-wrap gap-2 mt-2">
                <?php foreach (explode(',', $course['tools']) as $tool): ?>
                    <span class="bg-blue-50 text-blue-800 text-xs font-medium px-2 py-1 rounded-full border border-blue-200" aria-label="Tool: <?= htmlspecialchars(trim($tool)) ?>">
                        <i class="fas fa-toolbox mr-1"></i><?= htmlspecialchars(trim($tool)) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($course['outcomes'])): ?>
            <div class="mt-2 text-green-700 text-xs">
                <strong>Outcomes:</strong> <?= htmlspecialchars($course['outcomes']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">About this Course</h2>
            <?php
            // Render About this Course: allow safe HTML if present, else plain text
            $desc = $course['description'];
            if ($desc && preg_match('/<[^>]+>/', $desc)) {
                // Contains HTML, render as HTML (trusted input only!)
                echo '<div class="text-gray-800 leading-relaxed">' . $desc . '</div>';
            } else {
                // Plain text fallback
                echo '<p class="text-gray-800 leading-relaxed">' . nl2br(htmlspecialchars($desc)) . '</p>';
            }
            ?>
        </div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Curriculum</h2>
            <ul class="list-disc ml-6 text-gray-700">
            <?php foreach ($modules as $module): ?>
                <li class="mb-4">
                    <span class="font-semibold text-blue-900">Module <?= htmlspecialchars($module['module_order']) ?>: <?= htmlspecialchars($module['module_name']) ?></span>
                    <?php if (!empty($lessons[$module['id']])): ?>
                        <ul class="ml-4 list-decimal text-gray-600 mt-1">
                        <?php foreach ($lessons[$module['id']] as $lesson): ?>
                            <li><?= htmlspecialchars($lesson['title'] ?? 'Lesson') ?></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Course Mode & Schedule</h2>
            <p class="text-gray-700 mb-1"><span class="font-semibold">Mode:</span> 
                <?php
                // Ensure Mode always displays as 'Self-Paced' or 'Instructor-Led' (not raw value)
                $mode = strtolower($course['mode'] ?? 'instructor');
                echo $mode === 'self-paced' ? 'Self-Paced' : 'Instructor-Led';
                ?>
            </p>
            <?php
$schedule = $course['schedule'];
$decoded = null;
if (is_string($schedule) && ($tmp = json_decode($schedule, true)) && json_last_error() === JSON_ERROR_NONE) {
    $decoded = $tmp;
}
if ($decoded && is_array($decoded)) {
    // Filter out empty topics
    $nonEmpty = array_filter($decoded, function($v){return trim($v) !== '';});
    if (count($nonEmpty)) {
        echo '<div class="text-gray-700"><span class="font-semibold">Schedule:</span><ul class="list-disc ml-6">';
        $weekNum = 1;
        foreach ($decoded as $week => $desc) {
            if (trim($desc) === '') continue;
            // If key is numeric, label as Week N
            $label = is_numeric($week) ? 'Week ' . ($weekNum++) : $week;
            echo '<li><span class="font-semibold">' . htmlspecialchars($label) . ':</span> ' . htmlspecialchars($desc) . '</li>';
        }
        echo '</ul></div>';
    } else {
        echo '<div class="text-gray-700"><span class="font-semibold">Schedule:</span><ul class="list-disc ml-6"><li><span class="font-semibold">Week 1:</span> TBA</li></ul></div>';
    }
} else {
    // Fallback: Always show at least Week 1: TBA
    echo '<div class="text-gray-700"><span class="font-semibold">Schedule:</span><ul class="list-disc ml-6"><li><span class="font-semibold">Week 1:</span> TBA</li></ul></div>';
}
?>
        </div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Enrollment</h2>
            <?php
            // Payment onboarding: show payment form if ?pay=1
            if (isset($_GET['pay']) && $_GET['pay'] == 1 && isset($_SESSION['user_id'])):
                $user_id = $_SESSION['user_id'];
                // Check if user already has a pending payment for this course
                $pending_stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND course_id = ? AND status = 'pending'");
                $pending_stmt->execute([$user_id, $course['id']]);
                $pending_payment = $pending_stmt->fetch(PDO::FETCH_ASSOC);
                if ($pending_payment) {
                    echo '<div class="bch-bg-yellow-50 bch-border-l-4 bch-border-yellow-500 bch-text-yellow-700 bch-p-4 bch-mb-6 bch-rounded">You have a pending payment for this course. Please wait for verification or contact support if you have issues.</div>';
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
                    $amount = $course['discount_price'] > 0 && $course['discount_price'] < $course['price'] ? $course['discount_price'] : $course['price'];
                    $method = $_POST['payment_method'];
                    $transaction_ref = '';
                    if ($method === 'mpesa') {
                        $transaction_ref = strtoupper(trim($_POST['mpesa_code']));
                    } elseif ($method === 'paypal') {
                        $transaction_ref = trim($_POST['paypal_email']);
                    } elseif ($method === 'card') {
                        $transaction_ref = trim($_POST['card_name']) . ' ' . substr(trim($_POST['card_number']), -4);
                    }
                    $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, method, status, transaction_ref, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
                    $stmt->execute([$user_id, $course['id'], $amount, $method, $transaction_ref]);
                    // Custom confirmation and redirect to onboarding
                    echo '<div class="bch-bg-green-50 bch-border-l-4 bch-border-green-500 bch-text-green-700 bch-p-4 bch-mb-6 bch-rounded">Thank you for your payment! Your transaction is being processed. You will be redirected to your onboarding page shortly.</div>';
                    echo '<script>setTimeout(function(){ window.location.href = "../student/onboarding.php?course='.urlencode($course['course_name']).'&cid='.$course['id'].'&mode='.urlencode($course['mode']).'&intake='.urlencode($course['intake_start'] ?? $course['start_date'] ?? '').'"; }, 3000);</script>';
                } else {
            ?>
            <div class="bch-card bch-bg-white bch-p-6 bch-rounded-xl bch-shadow-md max-w-lg mx-auto">
                <h3 class="text-lg font-bold text-primary mb-4">Complete Payment</h3>
                <form method="POST" action="../LMS/payment_handler.php" class="space-y-6" autocomplete="off">
                    <!-- CSRF protection -->
                    <?php if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <input type="hidden" name="amount" value="<?= ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']) ? $course['discount_price'] : $course['price'] ?>">
                    <input type="hidden" name="mpesa_pay" value="1">
                    <div>
                        <label for="payment_method" class="block font-semibold mb-1">Payment Method</label>
                        <input type="text" value="MPESA" class="bch-form-input w-full bg-gray-100" readonly disabled>
                        <input type="hidden" name="payment_method" value="mpesa">
                    </div>
                    <div id="mpesa_fields">
                        <label class="block font-semibold mb-1" for="mpesa_code">MPESA Transaction Code</label>
                        <input type="text" name="mpesa_code" id="mpesa_code" class="bch-form-input w-full" maxlength="20" autocomplete="off" required>
                    </div>
                    <button type="submit" class="bg-blue-700 text-white px-6 py-3 rounded font-bold hover:bg-blue-800 transition w-full flex items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-blue-300" aria-live="polite">
                      <span id="bch-modal-submit-text">Apply</span>
                      <svg id="bch-modal-spinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    </button>
                        <label class="block font-semibold mb-1" for="paypal_email">PayPal Email</label>
                        <input type="email" name="paypal_email" id="paypal_email" class="bch-form-input w-full" autocomplete="off">
                    </div>
                    <div id="card_fields" class="bch-hidden">
                        <label class="block font-semibold mb-1" for="card_name">Cardholder Name</label>
                        <input type="text" name="card_name" id="card_name" class="bch-form-input w-full" autocomplete="off">
                        <label class="block font-semibold mb-1 mt-2" for="card_number">Card Number</label>
                        <input type="text" name="card_number" id="card_number" class="bch-form-input w-full" maxlength="16" autocomplete="off">
                        <label class="block font-semibold mb-1 mt-2" for="card_expiry">Expiry Date</label>
                        <input type="text" name="card_expiry" id="card_expiry" class="bch-form-input w-full" placeholder="MM/YY" maxlength="5" autocomplete="off">
                        <label class="block font-semibold mb-1 mt-2" for="card_cvc">CVC</label>
                        <input type="text" name="card_cvc" id="card_cvc" class="bch-form-input w-full" maxlength="4" autocomplete="off">
                    </div>
                    <button type="submit" class="bch-btn bch-bg-primary bch-text-white bch-py-2 bch-px-8 bch-rounded hover:bch-bg-blue-700 transition w-full font-bold" aria-label="Submit Payment">Submit Payment</button>
                </form>
                <div class="mt-4 text-center">
                    <a href="course_detail.php?id=<?= $course_id ?>" class="text-secondary hover:underline"><i class="fas fa-arrow-left mr-1"></i>Back to Course</a>
                </div>
            </div>
            <script>
            function showPaymentFields(method) {
                document.getElementById('mpesa_fields').style.display = (method === 'mpesa') ? 'block' : 'none';
                document.getElementById('paypal_fields').style.display = (method === 'paypal') ? 'block' : 'none';
                document.getElementById('card_fields').style.display = (method === 'card') ? 'block' : 'none';
            }
            </script>
            <?php 
        }
    endif;
    // --- End payment onboarding/pay form logic ---
    $is_paid = $course['price'] > 0;
    $mode = $course['mode'] ?? 'instructor';
    $can_access = false;
    $show_pay = false;
    $show_enroll = false;
    $show_wait = false;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // Check enrollment
        $enroll_stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
        $enroll_stmt->execute([$user_id, $course['id']]);
        $enrollment = $enroll_stmt->fetch(PDO::FETCH_ASSOC);
        // Check payment
        $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND course_id = ? AND status = 'completed'");
        $payment_stmt->execute([$user_id, $course['id']]);
        $has_paid = $payment_stmt->rowCount() > 0;
        // Mode logic
        if ($is_paid) {
            if ($has_paid) {
                if ($mode === 'self-paced') {
                    $can_access = true;
                } else {
                    // Instructor-led: check intake
                    $now = strtotime('now');
                    $start = strtotime($course['intake_start'] ?? $course['start_date'] ?? '+1 week');
                    if ($now >= $start) {
                        $can_access = true;
                    } else {
                        $show_wait = true;
                    }
                }
            } else {
                $show_pay = true;
            }
        } else {
            // Free course
            if ($enrollment) {
                if ($mode === 'self-paced') {
                    $can_access = true;
                } else {
                    $now = strtotime('now');
                    $start = strtotime($course['intake_start'] ?? $course['start_date'] ?? '+1 week');
                    if ($now >= $start) {
                        $can_access = true;
                    } else {
                        $show_wait = true;
                    }
                }
            } else {
                $show_enroll = true;
            }
        }
    }
    // --- End enrollment/payment status logic ---
    ?>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php?redirect=course_detail.php?id=<?= $course['id'] ?>&enroll=1" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded hover:bg-blue-700 transition">
                    <i class="fas fa-lock mr-2"></i> Login to Enroll
                </a>
            <?php elseif ($show_pay): ?>
                <a href="course_detail.php?id=<?= $course['id'] ?>&pay=1" class="bg-green-600 text-white font-semibold py-2 px-6 rounded hover:bg-green-700 transition">
                    <i class="fas fa-credit-card mr-2"></i> Pay to Enroll
                </a>
            <?php elseif ($show_enroll): ?>
                <form method="POST" autocomplete="off">
                    <!-- CSRF protection for enroll -->
                    <?php if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <button type="submit" name="enroll" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded hover:bg-yellow-600 transition" aria-label="Enroll Now">
                        <i class="fas fa-sign-in-alt mr-2"></i> Enroll Now
                    </button>
                </form>
            <?php elseif ($can_access): ?>
                <a href="course_player.php?course_id=<?= $course['id'] ?>" class="bch-btn bch-bg-primary bch-text-white bch-py-3 bch-px-8 bch-rounded-lg hover:bch-bg-yellow-400 hover:bch-text-blue-900 focus:outline-none focus:ring-2 focus:ring-bch-gold font-bold shadow-xl transition-all w-full text-lg flex items-center justify-center gap-2 mt-6" style="background: linear-gradient(90deg, #1E40AF 60%, #FFD700 100%); border: none;">
    <i class="fas fa-play-circle mr-2"></i> Start/Continue Learning
</a>                </a>
            <?php elseif ($show_wait): ?>
                <div class="bg-yellow-100 text-yellow-800 font-semibold py-2 px-6 rounded">
                    <i class="fas fa-clock mr-2"></i> Access opens on <?php
        if (!empty($course['next_intake_date'])) {
            echo htmlspecialchars(date('M j, Y', strtotime($course['next_intake_date'])));
        } else {
            echo 'TBA';
        }
    ?>
</div>                </div>
            <?php endif; ?>
        </div>
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Price</h2>
            <?php if (!empty($course['discount_price']) && $course['discount_price'] < $course['price']): ?>
                <span class="text-lg font-bold text-green-700 mr-2">KES <?= number_format($course['discount_price']) ?></span>
                <span class="line-through text-gray-400">KES <?= number_format($course['price']) ?></span>
            <?php else: ?>
                <span class="text-lg font-bold text-bch-blue">KES <?= number_format($course['price']) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($testimonials)): ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">What Our Students Say</h2>
                <ul class="space-y-3">
                    <?php foreach ($testimonials as $t): ?>
                        <li class="bg-blue-50 rounded p-3"><span class="font-semibold text-bch-blue"><?= htmlspecialchars($t['student_name']) ?>:</span> <?= htmlspecialchars($t['testimonial']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include '../includes/footer.php'; ?>



<!-- Application Modal Markup (identical to courses.php) -->
<!-- BCH Apply Modal: enhanced and handled by apply-modal.js -->
<div id="bch-apply-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" tabindex="-1" aria-modal="true" role="dialog">
    <div class="bch-modal-card bg-white rounded-2xl shadow-2xl max-w-lg w-full p-0 relative animate-fadeIn font-inter">
        <div class="bch-modal-header flex items-center gap-3 px-8 py-5 rounded-t-2xl" style="background: linear-gradient(90deg, #1E40AF 80%, #FFD700 100%);">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-primary text-2xl">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight mb-0 flex-1">Apply for Course</h2>
            <button id="bch-close-modal" class="ml-auto text-white hover:text-yellow-300 text-3xl focus:outline-none focus:ring-2 focus:ring-yellow-400" aria-label="Close">&times;</button>
        </div>
        <form id="bch-apply-form" class="space-y-4 px-8 py-6" novalidate autocomplete="off">
            <input type="hidden" name="course_id" id="bch-modal-course-id" />
            <div class="mb-3">
                <label class="block font-semibold mb-1 text-primary" for="bch-modal-course-name">Course Name</label>
                <div class="flex items-center gap-2">
                    <i class="fas fa-book-open text-blue-600 text-lg"></i>
                    <input type="text" id="bch-modal-course-name" name="course_name" class="w-full border border-blue-200 rounded px-3 py-2 bg-gray-100 text-blue-700 font-semibold focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition" readonly tabindex="-1" aria-readonly="true" />
                </div>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-primary" for="bch-modal-name">Full Name</label>
                <input type="text" id="bch-modal-name" name="name" class="w-full border rounded px-3 py-2" required />
                <span id="bch-modal-name-error" class="text-xs text-red-600 mt-1 block"></span>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-primary" for="bch-modal-email">Email</label>
                <input type="email" id="bch-modal-email" name="email" class="w-full border rounded px-3 py-2" required />
                <span id="bch-modal-email-error" class="text-xs text-red-600 mt-1 block"></span>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-primary" for="bch-modal-phone">Phone</label>
                <input type="text" id="bch-modal-phone" name="phone" class="w-full border rounded px-3 py-2" />
                <span id="bch-modal-phone-error" class="text-xs text-red-600 mt-1 block"></span>
            </div>
            <div>
                <label class="block font-semibold mb-1 text-primary" for="bch-modal-message">Message (optional)</label>
                <textarea id="bch-modal-message" name="message" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div id="bch-apply-feedback" class="mt-4 text-center text-sm"></div>
            <button type="submit" id="bch-modal-submit" class="bg-blue-700 text-white px-6 py-3 rounded font-bold hover:bg-blue-800 transition w-full flex items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-blue-300" aria-live="polite">
              <span id="bch-modal-submit-text">Apply</span>
              <svg id="bch-modal-spinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
            </button>
        </form>
    </div>
</div>
<script src="../../assets/js/apply-modal.js"></script>
