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

// Fetch course info
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<div class="text-center text-red-600 font-bold py-12">Course not found.</div>';
    exit;
}

$breadcrumbs = [
    "Home" => "../index.php",
    "Courses" => "courses.php",
    htmlspecialchars($course['course_name']) => "course_student_view.php?id=$course_id",
    "Player" => ""
];
include '../includes/breadcrumbs.php';

// Fetch modules and lessons (include all rich fields)
$modules_stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC");
$modules_stmt->execute([$course_id]);
$modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);
$lessons = [];
foreach ($modules as $module) {
    $content_stmt = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order ASC");
    $content_stmt->execute([$module['id']]);
    $lessons[$module['id']] = $content_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle lesson completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id'])) {
    $lesson_id = intval($_POST['lesson_id']);
    // Mark lesson as complete (idempotent)
    $stmt = $pdo->prepare("INSERT IGNORE INTO lesson_progress (user_id, lesson_id, completed_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $lesson_id]);

    // After marking as complete, check if all lessons are now complete
    // Fetch all lesson IDs for this course
    $all_lesson_ids = [];
    foreach ($lessons as $lesson_list) {
        foreach ($lesson_list as $lesson) {
            $all_lesson_ids[] = $lesson['id'];
        }
    }
    // Fetch completed lessons for this user
    $progress_stmt = $pdo->prepare("SELECT lesson_id FROM lesson_progress WHERE user_id = ?");
    $progress_stmt->execute([$user_id]);
    $completed_lessons = array_column($progress_stmt->fetchAll(PDO::FETCH_ASSOC), 'lesson_id');
    // If all lessons are completed, update enrollment status
    if (!array_diff($all_lesson_ids, $completed_lessons)) {
        // Only send certificate if just completed (status was not already 'completed')
        $status_stmt = $pdo->prepare("SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?");
        $status_stmt->execute([$user_id, $course_id]);
        $status = $status_stmt->fetchColumn();
        if ($status !== 'completed') {
            $update_stmt = $pdo->prepare("UPDATE enrollments SET status = 'completed', completed_at = NOW() WHERE user_id = ? AND course_id = ?");
            $update_stmt->execute([$user_id, $course_id]);
            // Fetch student info for email
            $info_stmt = $pdo->prepare("SELECT u.email, u.name, c.course_name FROM users u, courses c WHERE u.id = ? AND c.id = ?");
            $info_stmt->execute([$user_id, $course_id]);
            $info = $info_stmt->fetch(PDO::FETCH_ASSOC);
            if ($info && !empty($info['email'])) {
                require_once __DIR__ . '/../utils/mail_certificate.php';
                $cert_id = strtoupper(substr(md5($user_id . $course_id . date('F j, Y')), 0, 8));
                mail_certificate($user_id, $course_id, $info['email'], $info['name'], $info['course_name'], $cert_id);
            }
        }
    }
    header("Location: course_player.php?course_id=$course_id&lesson_id=$lesson_id");
    exit;
}

// Determine selected lesson
$selected_lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : null;
if (!$selected_lesson_id && count($modules)) {
    // Default to first lesson of first module
    $first_module = $modules[0];
    if (!empty($lessons[$first_module['id']])) {
        $selected_lesson_id = $lessons[$first_module['id']][0]['id'];
    }
}

// Fetch selected lesson
$selected_lesson = null;
foreach ($lessons as $module_id => $lesson_list) {
    foreach ($lesson_list as $lesson) {
        if ($lesson['id'] == $selected_lesson_id) {
            $selected_lesson = $lesson;
            break 2;
        }
    }
}

// Fetch completed lessons for progress
$progress_stmt = $pdo->prepare("SELECT lesson_id FROM lesson_progress WHERE user_id = ?");
$progress_stmt->execute([$user_id]);
$completed_lessons = array_column($progress_stmt->fetchAll(PDO::FETCH_ASSOC), 'lesson_id');
$total_lessons = 0;
foreach ($lessons as $lesson_list) $total_lessons += count($lesson_list);
$completed_count = count(array_intersect($completed_lessons, array_merge(...array_map(function($l){return array_column($l, 'id');}, $lessons))));
$progress_percent = $total_lessons ? intval(($completed_count / $total_lessons) * 100) : 0;

$pageTitle = $course['course_name'] . ' - Course Player';
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar: Modules & Lessons -->
        <aside class="w-full md:w-1/4 bg-white rounded-xl shadow p-4 mb-8 md:mb-0 md:sticky md:top-8 focus-within:ring-2 focus-within:ring-yellow-400" aria-label="Course Outline">
    <h2 class="text-lg font-bold text-bch-blue mb-4">Course Outline</h2>
    <?php if (empty($modules)): ?>
        <div class="text-gray-400 text-center py-8">No modules or lessons available for this course.</div>
    <?php else: ?>
        <ul class="space-y-3" role="listbox" tabindex="0">
            <?php foreach ($modules as $module): ?>
                <li>
                    <div class="font-semibold text-blue-700 mb-1"><?= htmlspecialchars($module['module_name']) ?></div>
                    <ul class="ml-3 space-y-1">
                        <?php foreach ($lessons[$module['id']] as $lesson): ?>
                            <li>
                                <a href="course_player.php?course_id=<?= $course_id ?>&lesson_id=<?= $lesson['id'] ?>"
                                   class="block px-2 py-1 rounded transition focus:outline-none focus:ring-2 focus:ring-primary <?= ($lesson['id'] == $selected_lesson_id) ? 'font-bold text-yellow-600 bg-yellow-50' : 'text-gray-700' ?> hover:bg-blue-50"
                                   role="option" aria-selected="<?= ($lesson['id'] == $selected_lesson_id) ? 'true' : 'false' ?>"
                                   tabindex="0">
                                    <?= htmlspecialchars($lesson['title']) ?>
                                    <?php if (!empty($lesson['type_tag'])): ?>
                                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700" aria-label="<?= htmlspecialchars($lesson['type_tag']) ?>">
                                            <?= htmlspecialchars(ucfirst($lesson['type_tag'])) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (in_array($lesson['id'], $completed_lessons)): ?>
                                        <span class="ml-2 text-green-600" title="Completed"><i class="fas fa-check-circle"></i></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <div class="mt-8">
        <a href="student_dashboard.php" class="text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-primary rounded px-2 py-1"><i class="fas fa-arrow-left mr-2"></i>Back to Dashboard</a>
    </div>
</aside>

        <!-- Main Content: Lesson -->
        <section class="flex-1 bg-white rounded-xl shadow p-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-primary mb-2"><?= htmlspecialchars($course['course_name']) ?></h1>
                <div class="flex items-center gap-3">
                    <span class="font-semibold text-blue-700">Progress:</span>
                    <span class="text-lg font-bold text-yellow-600"><?= $progress_percent ?>%</span>
                </div>
            </div>
            <div class="mb-8">
                <div class="w-full bg-gray-200 rounded-full h-3" role="progressbar" aria-valuenow="<?= $progress_percent ?>" aria-valuemin="0" aria-valuemax="100" tabindex="0">
                    <div class="bg-yellow-400 h-3 rounded-full transition-all duration-300" style="width: <?= $progress_percent ?>%"></div>
                </div>
            </div>
            <?php if ($selected_lesson): ?>
    <div class="mb-8">
        <h2 class="text-xl font-bold text-bch-blue mb-2"><?= htmlspecialchars($selected_lesson['title']) ?></h2>
        <div class="flex flex-wrap gap-2 mb-4">
            <?php if (!empty($selected_lesson['type_tag'])): ?>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700" aria-label="<?= htmlspecialchars($selected_lesson['type_tag']) ?>">
                    <?= htmlspecialchars(ucfirst($selected_lesson['type_tag'])) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($selected_lesson['quiz_link'])): ?>
                <a href="<?= htmlspecialchars($selected_lesson['quiz_link']) ?>" class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 underline" target="_blank" aria-label="Quiz">Quiz</a>
            <?php endif; ?>
            <?php if (!empty($selected_lesson['resource_links'])): ?>
                <a href="<?= htmlspecialchars($selected_lesson['resource_links']) ?>" class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 underline" target="_blank" aria-label="Resource">Resource</a>
            <?php endif; ?>
            <?php if (!empty($selected_lesson['assignment_links'])): ?>
                <a href="<?= htmlspecialchars($selected_lesson['assignment_links']) ?>" class="px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-700 underline" target="_blank" aria-label="Assignment">Assignment</a>
            <?php endif; ?>
        </div>
        <div class="text-gray-800 leading-relaxed mb-4">
            <?php
            $hasContent = false;
            // Always show description if present
            if (!empty($selected_lesson['description'])) {
                echo nl2br(htmlspecialchars($selected_lesson['description']));
                $hasContent = true;
            }
            // Show file if present (video or document)
            if (!empty($selected_lesson['content_file'])) {
                $file = $selected_lesson['content_file'];
                $type = strtolower($selected_lesson['content_type']);
                if ($type === 'video') {
                    echo '<div class="my-4"><video controls class="w-full max-w-lg"><source src="' . htmlspecialchars($file) . '" type="video/mp4">Your browser does not support the video tag.</video></div>';
                } elseif ($type === 'document') {
                    echo '<div class="my-4"><a href="../uploads/materials/' . htmlspecialchars($file) . '" target="_blank" class="inline-flex items-center bg-blue-100 text-blue-700 px-4 py-2 rounded hover:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-primary"><i class="fas fa-file-pdf mr-2"></i>Download Document</a></div>';
                }
                $hasContent = true;
            }
            // Show URL if present (video, document, or link)
            if (!empty($selected_lesson['content_url'])) {
                $type = strtolower($selected_lesson['content_type']);
                $url = $selected_lesson['content_url'];
                if ($type === 'video') {
                    // Embed YouTube/Vimeo if possible, else show link
                    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                        $embed = preg_replace('/watch\?v=([\w-]+)/', 'embed/$1', str_replace('youtu.be/', 'youtube.com/embed/', $url));
                        echo '<div class="my-4"><iframe width="560" height="315" src="' . htmlspecialchars($embed) . '" frameborder="0" allowfullscreen class="w-full max-w-lg"></iframe></div>';
                    } elseif (strpos($url, 'vimeo.com') !== false) {
                        $vimeo_id = preg_replace('/[^0-9]/', '', $url);
                        echo '<div class="my-4"><iframe src="https://player.vimeo.com/video/' . htmlspecialchars($vimeo_id) . '" width="640" height="360" frameborder="0" allowfullscreen class="w-full max-w-lg"></iframe></div>';
                    } else {
                        echo '<div class="my-4"><a href="' . htmlspecialchars($url) . '" target="_blank" class="inline-flex items-center bg-blue-100 text-blue-700 px-4 py-2 rounded hover:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-primary"><i class="fas fa-external-link-alt mr-2"></i>Watch Video</a></div>';
                    }
                } elseif ($type === 'document') {
                    echo '<div class="my-4"><a href="' . htmlspecialchars($url) . '" target="_blank" class="inline-flex items-center bg-blue-100 text-blue-700 px-4 py-2 rounded hover:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-primary"><i class="fas fa-file-pdf mr-2"></i>View Document</a></div>';
                } else {
                    echo '<div class="my-4"><a href="' . htmlspecialchars($url) . '" target="_blank" class="inline-flex items-center bg-blue-100 text-blue-700 px-4 py-2 rounded hover:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-primary"><i class="fas fa-link mr-2"></i>Open Resource</a></div>';
                }
                $hasContent = true;
            }
            if (!empty($selected_lesson['content_type']) && $selected_lesson['content_type'] === 'text' && !empty($selected_lesson['content_text'])) {
                echo '<div class="my-4 p-4 bg-gray-50 border rounded">' . nl2br(htmlspecialchars($selected_lesson['content_text'])) . '</div>';
                $hasContent = true;
            }
            if (!$hasContent) {
                echo '<em>No content available for this lesson.</em>';
            }
            ?>
        </div>
        <?php if (!in_array($selected_lesson['id'], $completed_lessons)): ?>
            <form method="POST">
                <input type="hidden" name="lesson_id" value="<?= $selected_lesson['id'] ?>">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded shadow mt-4 focus:outline-none focus:ring-2 focus:ring-green-400">Mark as Complete</button>
            </form>
        <?php else: ?>
            <div class="text-green-700 font-semibold mt-4"><i class="fas fa-check-circle mr-2"></i>Lesson Completed</div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="text-gray-400 text-center py-12">No lesson selected or available.</div>
<?php endif; ?>
        </section>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
