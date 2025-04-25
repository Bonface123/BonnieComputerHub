<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
$user_id = $_SESSION['user_id'];

// Fetch notifications (undismissed, newest first)
$notif_stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_dismissed = 0 ORDER BY created_at DESC LIMIT 10');
$notif_stmt->execute([$user_id]);
$notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
$unread_count = 0;
foreach ($notifications as $n) { if (!$n['is_read']) $unread_count++; }

// Fetch enrolled courses
$stmt = $pdo->prepare("SELECT c.*, e.enrolled_at, e.status as enrollment_status FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ? ORDER BY e.enrolled_at DESC");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch progress for each course (simulate for now)
$progress = [];
foreach ($courses as $course) {
    // Simulate: 100% if status is 'completed', else random progress
    $progress[$course['id']] = ($course['enrollment_status'] === 'completed') ? 100 : rand(15, 90);
}

$pageTitle = "My Dashboard - Bonnie Computer Hub";
$breadcrumbs = ["Home" => "../index.php", "Dashboard" => ""];
include '../includes/header.php';
include '../includes/breadcrumbs.php';
?>
<!-- Notification Bell and Dropdown -->
<div class="fixed top-4 right-4 z-50">
    <div class="relative">
        <button id="notif-bell" class="relative focus:outline-none" aria-label="Notifications">
            <i class="fas fa-bell text-2xl text-yellow-600"></i>
            <?php if ($unread_count > 0): ?>
                <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs px-2 py-0.5 font-bold animate-pulse">
                    <?= $unread_count ?>
                </span>
            <?php endif; ?>
        </button>
        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-blue-100 overflow-hidden">
            <div class="p-4 border-b font-bold text-bch-blue flex items-center justify-between">
                Notifications
                <button id="notif-close" class="text-gray-400 hover:text-red-500 text-lg" title="Close"><i class="fas fa-times"></i></button>
            </div>
            <div id="notif-list" class="max-h-80 overflow-y-auto">
                <?php if (empty($notifications)): ?>
                    <div class="p-4 text-gray-500 text-center">No new notifications.</div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="flex items-start gap-3 px-4 py-3 border-b hover:bg-blue-50 transition group <?= $notif['is_read'] ? '' : 'bg-yellow-50' ?>" data-notif-id="<?= $notif['id'] ?>">
                            <div class="flex-shrink-0 mt-1">
                                <?php if ($notif['type'] === 'announcement'): ?>
                                    <i class="fas fa-bullhorn text-bch-accent"></i>
                                <?php elseif ($notif['type'] === 'assignment'): ?>
                                    <i class="fas fa-tasks text-bch-blue"></i>
                                <?php elseif ($notif['type'] === 'session'): ?>
                                    <i class="fas fa-calendar-alt text-bch-gold"></i>
                                <?php else: ?>
                                    <i class="fas fa-info-circle text-gray-400"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-bch-blue mb-1">
                                    <?= htmlspecialchars($notif['title']) ?>
                                </div>
                                <div class="text-xs text-gray-700 mb-1">
                                    <?= nl2br(htmlspecialchars($notif['message'])) ?>
                                </div>
                                <?php if ($notif['link']): ?>
                                    <a href="<?= htmlspecialchars($notif['link']) ?>" class="text-xs text-blue-600 underline hover:text-blue-800" target="_blank">View Details</a>
                                <?php endif; ?>
                                <div class="text-xs text-gray-400 mt-1"><?= date('M j, Y H:i', strtotime($notif['created_at'])) ?></div>
                            </div>
                            <button class="notif-dismiss ml-2 text-gray-400 hover:text-red-500 transition" title="Dismiss" aria-label="Dismiss notification">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<main class="container mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-primary mb-8">Welcome to Your Learning Dashboard</h1>
    <?php
    // BADGES DISPLAY
    $badges = $pdo->prepare('SELECT b.* FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = ? ORDER BY ub.awarded_at');
    $badges->execute([$user_id]);
    $badges = $badges->fetchAll(PDO::FETCH_ASSOC);
    // STREAK DISPLAY & BADGE
    $streak = $pdo->prepare('SELECT current_streak, longest_streak FROM user_streaks WHERE user_id = ?');
    $streak->execute([$user_id]);
    $streak_row = $streak->fetch(PDO::FETCH_ASSOC);
    $badge_msgs = [];
    if ($streak_row) {
        // Award 7-day streak badge if achieved
        if ($streak_row['current_streak'] >= 7) {
            $badge_id = $pdo->query("SELECT id FROM badges WHERE criteria = 'streak_7'")->fetchColumn();
            $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
            if ($stmt->execute([$user_id, $badge_id])) $badge_msgs[] = "🔥 Congrats! You earned the 7-Day Streak badge!";
        }
        echo '<div class="mb-4 flex gap-6">';
        echo '<div class="bg-white border rounded-lg shadow px-6 py-3 text-center"><span class="font-semibold text-primary">Current Streak:</span> <span class="text-yellow-600 font-bold">' . intval($streak_row['current_streak']) . '</span> days</div>';
        echo '<div class="bg-white border rounded-lg shadow px-6 py-3 text-center"><span class="font-semibold text-primary">Longest Streak:</span> <span class="text-yellow-600 font-bold">' . intval($streak_row['longest_streak']) . '</span> days</div>';
        echo '</div>';
    }
    foreach ($badge_msgs as $msg) {
        echo '<div class="max-w-lg mx-auto mb-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded p-4 text-center font-semibold">' . htmlspecialchars($msg) . '</div>';
    }
    if ($badges): ?>
    <section class="mb-8">
      <h2 class="text-xl font-semibold text-primary mb-4">Your Badges</h2>
      <div class="flex flex-wrap gap-4">
        <?php foreach ($badges as $badge): ?>
          <div class="flex flex-col items-center bg-white border rounded-lg shadow px-4 py-3" style="min-width:120px;">
            <i class="fa <?= htmlspecialchars($badge['icon']) ?> text-3xl text-yellow-500 mb-2"></i>
            <div class="font-bold text-primary text-sm mb-1"><?= htmlspecialchars($badge['name']) ?></div>
            <div class="text-xs text-gray-600 text-center"><?= htmlspecialchars($badge['description']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'enrolled_success'): ?>
        <div class="mb-6 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
            Successfully enrolled! Start your learning journey below.
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'already_enrolled'): ?>
        <div class="mb-6 text-blue-800 bg-blue-100 border border-blue-200 px-4 py-3 rounded">
            You are already enrolled in this course.
        </div>
    <?php endif; ?>
    <?php if (empty($courses)): ?>
        <div class="text-center text-gray-600 py-20">
            <p class="text-xl mb-4">You have not enrolled in any courses yet.</p>
            <a href="courses.php" class="bg-yellow-500 text-white font-semibold px-6 py-3 rounded-lg shadow hover:bg-yellow-600 transition text-lg">Browse Courses</a>
        </div>
    <?php else: ?>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($courses as $course): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-bch-blue mb-2">
    <?= htmlspecialchars($course['course_name']) ?>
</h2>
<div class="flex flex-wrap items-center gap-2 mb-2">
    <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1 rounded-full" title="Skill Level">
        <?= htmlspecialchars($course['skill_level'] ?? 'Beginner') ?>
    </span>
    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1 rounded-full" title="Course Format">
        <?= ($course['mode'] ?? 'instructor-led') === 'self-paced' ? 'Self-Paced' : 'Instructor-Led' ?>
    </span>
    <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-3 py-1 rounded-full" title="Next Intake">
        <?php if (($course['mode'] ?? 'instructor-led') === 'self-paced'): ?>
            Start Anytime
        <?php elseif (!empty($course['next_intake_date'])): ?>
            <?= htmlspecialchars(date('M j, Y', strtotime($course['next_intake_date']))) ?>
        <?php else: ?>
            TBA
        <?php endif; ?>
    </span>
</div>
                        <p class="text-gray-700 mb-3">Enrolled: <?= date('M j, Y', strtotime($course['enrolled_at'])) ?></p>
                        <div class="mb-4">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-blue-700">Progress</span>
                                <span class="text-sm font-medium text-blue-700"><?= $progress[$course['id']]['percent'] ?? $progress[$course['id']] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-yellow-400 h-3 rounded-full" style="width: <?= $progress[$course['id']]['percent'] ?? $progress[$course['id']] ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-col gap-2">
    <?php if (($course['mode'] ?? 'instructor-led') === 'self-paced'): ?>
        <a href="course_player.php?course_id=<?= $course['id'] ?>" class="bg-blue-600 text-white font-semibold py-2 rounded hover:bg-blue-700 transition text-center">Continue Course</a>
        <div class="text-xs text-gray-500 text-center">Self-Paced: Learn at your own speed.</div>
        <?php
        // Optionally, fetch and display recommended next lesson/module here
        ?>
    <?php else: ?>
        <a href="schedule.php?course_id=<?= $course['id'] ?>" class="bg-yellow-600 text-white font-semibold py-2 rounded hover:bg-yellow-700 transition text-center">View Schedule</a>
        <?php
        // Simulate next session date/time for demonstration
        $nextSession = !empty($course['next_intake_date']) ? $course['next_intake_date'] : null;
        $today = date('Y-m-d');
        if ($nextSession && $nextSession === $today): ?>
            <a href="course_player.php?course_id=<?= $course['id'] ?>" class="bg-green-600 text-white font-semibold py-2 rounded hover:bg-green-700 transition text-center">Join Next Session (Live)</a>
            <div class="text-xs text-green-700 text-center">Your next live session is today!</div>
        <?php elseif ($nextSession): ?>
            <div class="text-xs text-blue-700 text-center">Next live session: <?= htmlspecialchars(date('M j, Y', strtotime($nextSession))) ?></div>
        <?php else: ?>
            <div class="text-xs text-gray-500 text-center">Next session: TBA</div>
        <?php endif; ?>
    <?php endif; ?>
</div>                        <?php if (($progress[$course['id']]['percent'] ?? $progress[$course['id']]) === 100): ?>
                            <?php
                            // Fetch certificate details (status + pdf_path)
                            $cert_stmt = $pdo->prepare('SELECT status, pdf_path FROM certificates WHERE user_id = ? AND course_id = ? AND status = "issued"');
                            $cert_stmt->execute([$user_id, $course['id']]);
                            $cert = $cert_stmt->fetch(PDO::FETCH_ASSOC);
                            if ($cert && $cert['status'] === 'issued'):
                            ?>
                            <div class="flex flex-wrap gap-3 justify-center mt-2">
                                <a href="certificate.php?course_id=<?= $course['id'] ?>&preview=1" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">Preview Certificate</a>
                                <a href="certificate.php?course_id=<?= $course['id'] ?>" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">Download PNG</a>
                                <?php if (!empty($cert['pdf_path'])): ?>
                                    <a href="<?= htmlspecialchars($cert['pdf_path']) ?>" target="_blank" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded shadow transition">Download PDF</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php
                            // Feedback prompt
                            $feedback_stmt = $pdo->prepare('SELECT id FROM course_feedback WHERE user_id = ? AND course_id = ?');
                            $feedback_stmt->execute([$user_id, $course['id']]);
                            $has_feedback = $feedback_stmt->fetchColumn();
                            if (!$has_feedback): ?>
                                <div class="mt-2 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded px-4 py-2 text-center">
                                    <span>Help us improve! <a href="certificate.php?course_id=<?= $course['id'] ?>#feedback" class="underline text-yellow-700">Leave course feedback</a></span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

<!-- Notification Bell JS -->
<script>
// Toggle dropdown
const bell = document.getElementById('notif-bell');
const dropdown = document.getElementById('notif-dropdown');
const closeBtn = document.getElementById('notif-close');
bell && bell.addEventListener('click', () => {
    dropdown.classList.toggle('hidden');
});
closeBtn && closeBtn.addEventListener('click', () => {
    dropdown.classList.add('hidden');
});
// Dismiss notification
const notifList = document.getElementById('notif-list');
notifList && notifList.addEventListener('click', function(e) {
    if (e.target.closest('.notif-dismiss')) {
        const notifDiv = e.target.closest('[data-notif-id]');
        const notifId = notifDiv.getAttribute('data-notif-id');
        fetch('dismiss_notification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'notification_id=' + encodeURIComponent(notifId)
        }).then(res => res.json()).then(data => {
            if (data.success) {
                notifDiv.remove();
                // Update badge
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    let count = parseInt(badge.textContent) - 1;
                    if (count > 0) badge.textContent = count;
                    else badge.remove();
                }
                // If no notifications left
                if (!notifList.querySelector('[data-notif-id]')) {
                    notifList.innerHTML = '<div class="p-4 text-gray-500 text-center">No new notifications.</div>';
                }
            }
        });
    } else if (e.target.closest('[data-notif-id]')) {
        // Mark as read on click
        const notifDiv = e.target.closest('[data-notif-id]');
        if (!notifDiv.classList.contains('bg-yellow-50')) return;
        const notifId = notifDiv.getAttribute('data-notif-id');
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'notification_id=' + encodeURIComponent(notifId)
        }).then(res => res.json()).then(data => {
            if (data.success) {
                notifDiv.classList.remove('bg-yellow-50');
                notifDiv.classList.add('bg-white');
                // Update badge
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    let count = parseInt(badge.textContent) - 1;
                    if (count > 0) badge.textContent = count;
                    else badge.remove();
                }
            }
        });
    }
});
// Hide dropdown on outside click
window.addEventListener('click', function(e) {
    if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
