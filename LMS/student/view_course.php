<?php
session_start();

// --- User session and role validation must come BEFORE any output ---
if (!isset($_SESSION['user_id']) || (!isset($_SESSION['role']) && !isset($_SESSION['role_id'])) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'student') && (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3)) {
    header("Location: ../pages/login.php");
    exit();
}

include '../includes/db_connect.php';
include '../includes/header.php';

// Check if user is a student
// This validation is moved after the header include to prevent 'Cannot modify header information' error

// Validate and fetch course
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<main class="container mx-auto px-4 py-8 text-center"><h2 class="text-2xl font-bold text-red-600">Invalid Course</h2></main>';
    include '../includes/footer.php';
    exit();
}
$course_id = intval($_GET['id']);
$course_stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 'active'");
$course_stmt->execute([$course_id]);
$course = $course_stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<main class="container mx-auto px-4 py-8 text-center"><h2 class="text-2xl font-bold text-red-600">Course Not Found</h2></main>';
    include '../includes/footer.php';
    exit();
}

// Fetch modules and lessons
$modules_stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC");
$modules_stmt->execute([$course_id]);
$modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);
$lessons = [];
foreach ($modules as $mod) {
    $lessons_stmt = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order ASC");
    $lessons_stmt->execute([$mod['id']]);
    $lessons[$mod['id']] = $lessons_stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<main class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-extrabold text-primary mb-2"><?= htmlspecialchars($course['course_name']) ?></h1>
        <div class="flex flex-wrap items-center gap-4 mb-4">
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                <?= htmlspecialchars($course['category']) ?>
            </span>
            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                <?= htmlspecialchars($course['skill_level']) ?>
            </span>
            <?php if ($course['certification']): ?>
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                    <i class="fas fa-certificate"></i> Certificate
                </span>
            <?php endif; ?>
        </div>
        <p class="text-gray-700 mb-6"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-sm text-gray-500">Duration</div>
                <div class="font-bold text-lg text-blue-800"><?= $course['duration_weeks'] ?> weeks</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-sm text-gray-500">Modules</div>
                <div class="font-bold text-lg text-blue-800"><?= count($modules) ?></div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-sm text-gray-500">Price</div>
                <div class="font-bold text-lg text-blue-800">
                    <?= $course['price'] > 0 ? 'KES ' . number_format($course['price']) : 'Free' ?>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-sm text-gray-500">Format</div>
                <div class="font-bold text-lg text-blue-800"><?= htmlspecialchars($course['format'] ?? 'Online') ?></div>
            </div>
        </div>
        <h2 class="text-xl font-bold text-primary mb-3">Curriculum</h2>
        <div class="space-y-4">
            <?php foreach ($modules as $mod): ?>
                <div class="border-l-4 border-primary bg-blue-50 rounded p-4">
                    <div class="font-semibold text-blue-800 mb-1">
                        <i class="fas fa-folder-open mr-1"></i> <?= htmlspecialchars($mod['module_name']) ?>
                    </div>
                    <div class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($mod['module_description']) ?></div>
                    <ul class="list-disc pl-6 text-gray-700 text-sm">
                        <?php foreach ($lessons[$mod['id']] as $lesson): ?>
                            <li><?= htmlspecialchars($lesson['title']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="../student/courses.php" class="bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 font-semibold">Back to My Courses</a>
            <?php if ($course['price'] > 0): ?>
                <form method="POST" action="../payment_handler.php" class="space-y-6" autocomplete="off">
                    <?php if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <input type="hidden" name="amount" value="<?= ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']) ? $course['discount_price'] : $course['price'] ?>">
                    <input type="hidden" name="payment_method" value="mpesa">
                    <input type="text" name="mpesa_phone" id="mpesa_phone" class="bch-form-input w-full" maxlength="13" autocomplete="off" required pattern="^0[7-9][0-9]{8}$" placeholder="e.g. 0722123456">
                    <button type="submit" class="bg-green-600 text-white font-semibold py-2 px-6 rounded hover:bg-green-700 transition">Pay with MPESA</button>
                </form>
            <?php else: ?>
                <form method="post" action="../student/enroll_course.php" class="inline">
                    <input type="hidden" name="id" value="<?= $course['id'] ?>">
                    <button type="submit" class="bg-primary text-white font-semibold py-2 px-6 rounded hover:bg-primary-dark transition">Enroll Now</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
