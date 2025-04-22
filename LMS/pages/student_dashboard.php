<?php
session_start();
require_once '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
$user_id = $_SESSION['user_id'];

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
                        <a href="course_player.php?course_id=<?= $course['id'] ?>" class="bg-blue-600 text-white font-semibold py-2 rounded hover:bg-blue-700 transition text-center">Continue Course</a>
                        <?php if (($progress[$course['id']]['percent'] ?? $progress[$course['id']]) === 100): ?>
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
