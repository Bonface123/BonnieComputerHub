<?php 
session_start();
require_once '../includes/db_connect.php';

// Set page title for consistent navigation
$pageTitle = "Courses";

// Handle filters
$filter_category = $_GET['category'] ?? '';
$filter_skill = $_GET['skill'] ?? '';
$filter_price = $_GET['price'] ?? '';
$filter_search = trim($_GET['search'] ?? '');

$where = ["c.status = 'active'"];
$params = [];
if ($filter_category) {
    $where[] = 'c.category = ?';
    $params[] = $filter_category;
}
if ($filter_skill) {
    $where[] = 'c.skill_level = ?';
    $params[] = $filter_skill;
}
if (isset($_GET['mode']) && $_GET['mode'] !== '') {
    $where[] = 'c.mode = ?';
    $params[] = $_GET['mode'];
}
if ($filter_price === 'free') {
    $where[] = 'COALESCE(c.price,0) = 0';
} elseif ($filter_price === 'paid') {
    $where[] = 'COALESCE(c.price,0) > 0';
}
if ($filter_search) {
    $where[] = '(c.course_name LIKE ? OR c.description LIKE ?)';
    $params[] = "%$filter_search%";
    $params[] = "%$filter_search%";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT c.*, u.name as instructor_name, 
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
           (SELECT COUNT(*) FROM course_modules WHERE course_id = c.id) as total_modules,
           COALESCE(c.price, 0) as price,
           COALESCE(c.discount_price, 0) as discount_price,
           COALESCE(c.duration_weeks, 12) as duration_weeks,
           COALESCE(c.skill_level, 'Beginner') as skill_level,
           COALESCE(c.certification, 0) as certification
    FROM courses c 
    JOIN users u ON c.instructor_id = u.id 
    $where_sql
    ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$course_modules = []; // Initialize $course_modules
$course_lessons = [];

// Fetch modules and lessons for each course
foreach ($courses as $course) {
    $module_stmt = $pdo->prepare("
        SELECT id, module_name, module_order 
        FROM course_modules 
        WHERE course_id = ? 
        ORDER BY module_order ASC
    ");
    $module_stmt->execute([$course['id']]);
    $course_modules[$course['id']] = $module_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($course_modules[$course['id']] as $module) {
        $content_stmt = $pdo->prepare("SELECT id, title FROM module_content WHERE module_id = ? ORDER BY content_order ASC");
        $content_stmt->execute([$module['id']]);
        $course_lessons[$module['id']] = $content_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// --- Progress Calculation for Certificate Button ---
$progress = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    foreach ($courses as $course) {
        // Count total lessons
        $total_lessons = 0;
        $completed_lessons = 0;
        $modules_stmt = $pdo->prepare('SELECT id FROM course_modules WHERE course_id = ?');
        $modules_stmt->execute([$course['id']]);
        $module_ids = $modules_stmt->fetchAll(PDO::FETCH_COLUMN);
        if ($module_ids) {
            $placeholders = implode(',', array_fill(0, count($module_ids), '?'));
            $lessons_stmt = $pdo->prepare('SELECT id FROM module_content WHERE module_id IN (' . $placeholders . ')');
            $lessons_stmt->execute($module_ids);
            $lesson_ids = $lessons_stmt->fetchAll(PDO::FETCH_COLUMN);
            $total_lessons = count($lesson_ids);
            if ($total_lessons > 0) {
                $done_stmt = $pdo->prepare('SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND lesson_id IN (' . implode(',', array_fill(0, count($lesson_ids), '?')) . ')');
                $done_stmt->execute(array_merge([$user_id], $lesson_ids));
                $completed = $done_stmt->fetchAll(PDO::FETCH_COLUMN);
                $completed_lessons = count($completed);
            }
        }
        $percent = $total_lessons > 0 ? intval(($completed_lessons / $total_lessons) * 100) : 0;
        $progress[$course['id']] = [
            'percent' => $percent,
            'time_spent' => 0 // Placeholder, add real time if available
        ];
    }
}

// (REMOVED DUPLICATE MODULE FETCH, handled above)

// Handle MPESA payment submission
if (isset($_POST['submit_mpesa']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $course_id = intval($_POST['mpesa_course_id']);
    $amount = floatval($_POST['mpesa_amount']);
    $phone = trim($_POST['mpesa_phone']);
    $mpesa_code = strtoupper(trim($_POST['mpesa_code']));
    // Store as pending payment
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, method, status, transaction_ref, created_at) VALUES (?, ?, ?, 'mpesa', 'pending', ?, NOW())");
    $stmt->execute([$user_id, $course_id, $amount, $mpesa_code]);
    $_SESSION['success_msg'] = 'Your payment is being processed. Enrollment will be confirmed after verification.';
    header('Location: courses.php');
    exit;
}
// Handle PayPal payment submission
if (isset($_POST['submit_paypal']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $course_id = intval($_POST['paypal_course_id']);
    $amount = floatval($_POST['paypal_amount']);
    $paypal_email = trim($_POST['paypal_email']);
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, method, status, transaction_ref, created_at) VALUES (?, ?, ?, 'paypal', 'pending', ?, NOW())");
    $stmt->execute([$user_id, $course_id, $amount, $paypal_email]);
    $_SESSION['success_msg'] = 'Your PayPal payment is being processed. Enrollment will be confirmed after verification.';
    header('Location: courses.php');
    exit;
}
// Handle Card payment submission
if (isset($_POST['submit_card']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $course_id = intval($_POST['card_course_id']);
    $amount = floatval($_POST['card_amount']);
    $card_name = trim($_POST['card_name']);
    $card_number = trim($_POST['card_number']);
    $card_expiry = trim($_POST['card_expiry']);
    $card_cvc = trim($_POST['card_cvc']);
    $card_ref = $card_name . ' ' . substr($card_number, -4);
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, method, status, transaction_ref, created_at) VALUES (?, ?, ?, 'card', 'pending', ?, NOW())");
    $stmt->execute([$user_id, $course_id, $amount, $card_ref]);
    $_SESSION['success_msg'] = 'Your card payment is being processed. Enrollment will be confirmed after verification.';
    header('Location: courses.php');
    exit;
}

// Handle enrollment if user is logged in
if (isset($_POST['enroll']) && isset($_SESSION['user_id'])) {
    $course_id = $_POST['course_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already enrolled
    $check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$user_id, $course_id]);

    if ($check->rowCount() == 0) {
        try {
            $enroll = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
            $enroll->execute([$user_id, $course_id]);
            $_SESSION['success_msg'] = "Successfully enrolled in the course!";
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Error enrolling in course: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_msg'] = "You are already enrolled in this course.";
    }
    header("Location: courses.php");
    exit;
}
?>

<?php 
// Include header
include '../includes/header.php';
// Improved Breadcrumb Navigation

?>
<link rel="stylesheet" href="../../assets/css/bch-global.css">
<link rel="stylesheet" href="../assets/css/courses-progress.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/courses-progress.js"></script>
<script>
$(function() {
    $('#filter-category').on('change', function() {
        var category = $(this).val();
        var skill = $('#filter-skill').val();
        var price = $('#filter-price').val();
        var search = $('#filter-search').val();
        $('#course-grid').html('<div class="col-span-full text-center py-16"><span class="loader inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-700 rounded-full animate-spin"></span><br>Loading courses...</div>');
        $.post('fetch_courses.php', {
            category: category,
            skill: skill,
            price: price,
            search: search
        }, function(data) {
            if (data.count > 0) {
                $('#course-grid').html(data.html);
            } else {
                $('#course-grid').html('<div class="col-span-full text-center text-gray-400 text-lg py-16"><i class="fas fa-search fa-2x mb-4"></i><br>No courses found. Try adjusting your filters.</div>');
            }
        }, 'json');
    });
});
</script>
<style>
.loader { border-top-color: #1E40AF; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
</style>

<!-- Skip to main content accessibility link -->
<a href="#course-catalog" class="sr-only focus:not-sr-only absolute top-2 left-2 bg-yellow-400 text-blue-900 font-bold px-4 py-2 rounded z-50 focus:outline-none focus:ring-2 focus:ring-blue-700" tabindex="0" aria-label="Skip to main content">Skip to Content</a>
<main class="container mx-auto px-4 py-8 sm:py-10">

    <!-- Improved Breadcrumb Navigation -->
<nav class="bch-breadcrumb flex items-center gap-2 text-sm mb-2 bg-blue-50 px-4 py-2 rounded-lg" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2 mb-0">
        <li>
            <a href="../../index.php" class="text-blue-700 hover:underline font-medium focus:outline-none focus:ring-2 focus:ring-blue-700">
                <i class="fas fa-home mr-1"></i> Home
            </a>
            <span class="mx-2 text-gray-400">/</span>
        </li>
        <li>
            <span class="text-yellow-700 font-bold" aria-current="page">Courses</span>
        </li>
    </ol>
</nav>

<!-- Course Filter Section -->
    <section id="course-catalog" class="bch-container mb-2">
        <div class="bch-card bch-bg-white bch-p-6 mb-2 bch-rounded-xl bch-shadow-md course-filter-bar">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="bch-bg-green-50 bch-border-l-4 bch-border-green-500 bch-text-green-700 bch-p-4 bch-mb-6 bch-rounded">
                    <div class="bch-flex bch-items-center">
                        <i class="fas fa-check-circle bch-mr-3 bch-text-xl text-goldenrod"></i>
                        <span><?= htmlspecialchars($_SESSION['success_msg']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>
            <!-- Error Message -->
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="bch-bg-red-50 bch-border-l-4 bch-border-red-500 bch-text-red-700 bch-p-4 bch-mb-6 bch-rounded">
                    <div class="bch-flex bch-items-center">
                        <i class="fas fa-exclamation-circle bch-mr-3 bch-text-xl text-red-500"></i>
                        <span><?= htmlspecialchars($_SESSION['error_msg']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>
            <!-- Filter Bar -->
            <form method="get" class="bg-white p-4 mb-0 rounded-xl shadow flex flex-wrap gap-4 items-end" aria-label="Course Filters">
                <div>
                    <label for="filter-mode" class="block text-sm font-semibold mb-1">Mode</label>
                    <select id="filter-mode" name="mode" class="border rounded px-3 py-2 w-36" aria-label="Filter by mode">
                        <option value="">All</option>
                        <option value="self-paced" <?= (isset($_GET['mode']) && $_GET['mode']==='self-paced')?'selected':'' ?>>Self-Paced</option>
                        <option value="instructor" <?= (isset($_GET['mode']) && $_GET['mode']==='instructor')?'selected':'' ?>>Instructor-Led</option>
                    </select>
                </div>
                <div>
                    <label for="filter-category" class="block text-sm font-semibold mb-1">Category</label>
                    <?php
                        $category_stmt = $pdo->query("SELECT DISTINCT category FROM courses WHERE status = 'active' AND category IS NOT NULL AND category != '' ORDER BY category ASC");
                        $categories = $category_stmt->fetchAll(PDO::FETCH_COLUMN);
                    ?>
                    <select id="filter-category" name="category" class="border rounded px-3 py-2 w-40" aria-label="Filter by category" data-ajax-filter>
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $filter_category === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filter-skill" class="block text-sm font-semibold mb-1">Skill Level</label>
                    <select id="filter-skill" name="skill" class="border rounded px-3 py-2 w-32" aria-label="Filter by skill level">
                        <option value="">All</option>
                        <option value="Beginner" <?= $filter_skill==='Beginner'?'selected':'' ?>>Beginner</option>
                        <option value="Intermediate" <?= $filter_skill==='Intermediate'?'selected':'' ?>>Intermediate</option>
                        <option value="Advanced" <?= $filter_skill==='Advanced'?'selected':'' ?>>Advanced</option>
                    </select>
                </div>
                <div>
                    <label for="filter-price" class="block text-sm font-semibold mb-1">Price</label>
                    <select id="filter-price" name="price" class="border rounded px-3 py-2 w-28" aria-label="Filter by price">
                        <option value="">All</option>
                        <option value="free" <?= $filter_price==='free'?'selected':'' ?>>Free</option>
                        <option value="paid" <?= $filter_price==='paid'?'selected':'' ?>>Paid</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label for="filter-search" class="block text-sm font-semibold mb-1">Search</label>
                    <input id="filter-search" name="search" type="text" value="<?= htmlspecialchars($filter_search) ?>" placeholder="Search courses..." class="border rounded px-3 py-2 w-full" aria-label="Search courses">
                </div>
                <div>
                    <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded font-bold hover:bg-blue-800 transition w-full">Apply</button>
                </div>
            </form>
        </div>
    </section>


<section class="container mx-auto mb-8 py-10 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md px-4 sm:px-8">
    <div class="text-center mb-6">
        <h2 class="text-4xl font-extrabold text-blue-700 mb-3 tracking-tight">
            Explore Our Courses
        </h2>
        <p class="text-lg text-gray-700 max-w-2xl mx-auto">
            Transform your future with our expert-led, industry-relevant tech programs.
        </p>
    </div>

    <!-- Active filters (if any) -->
    <?php 
    $activeFilters = [];
    if (isset($_GET['category']) && $_GET['category'] != '') $activeFilters['Category'] = $_GET['category'];
    if (isset($_GET['skill_level']) && $_GET['skill_level'] != '') $activeFilters['Skill Level'] = $_GET['skill_level'];
    if (isset($_GET['price']) && $_GET['price'] != '') $activeFilters['Price'] = ($_GET['price'] == 'free' ? 'Free' : 'Paid');

    if (!empty($activeFilters)): 
    ?>
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-600">Active filters:</span>
        <?php foreach($activeFilters as $label => $value): ?>
            <span class="inline-flex items-center text-sm bg-blue-100 text-blue-700 rounded-full px-3 py-1">
                <?= htmlspecialchars($label) ?>: <?= htmlspecialchars(ucfirst($value)) ?>
                <a href="?<?= http_build_query(array_diff_key($_GET, [$label => ''])) ?>" class="ml-2 text-blue-500 hover:text-blue-700 font-semibold" aria-label="Remove filter">
                    &times;
                </a>
            </span>
        <?php endforeach; ?>
        <a href="courses.php" class="text-sm text-blue-600 hover:underline ml-2">Clear all</a>
    </div>
    <?php endif; ?>

    <!-- Course Grid -->
    <div id="course-grid" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    <?php if (empty($courses)): ?>
        <div class="col-span-full text-center text-gray-400 text-lg py-16">
            <i class="fas fa-search fa-2x mb-4"></i><br>
            No courses found. Try adjusting your filters.
        </div>
    <?php endif; ?>
    <?php foreach ($courses as $course): ?>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow p-5 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <!-- Header -->
                <div class="relative mb-3">
                    <?php if (!empty($course['thumbnail'])): ?>
                        <img src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>" alt="Course Thumbnail" class="h-32 w-full object-cover rounded mb-2">
                    <?php endif; ?>
                    <div class="absolute top-3 right-3 flex items-center gap-2">
                        <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1 rounded-full">
                            <?= htmlspecialchars($course['skill_level']) ?>
                        </span>
                        <?php if ($course['certification']): ?>
                            <span class="text-yellow-500" title="Certification Included" aria-label="Certification Included">
                                <i class="fas fa-certificate"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-bold text-yellow-600">
    <a href="course_detail.php?id=<?= $course['id'] ?>" class="hover:underline focus:outline-none" aria-label="View details for <?= htmlspecialchars($course['course_name']) ?>">
        <?= htmlspecialchars($course['course_name']) ?>
    </a>
</h3>
<div class="flex flex-wrap items-center gap-2 mt-1 mb-2">
    <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1 rounded-full" title="Skill Level">
        <?= htmlspecialchars($course['skill_level'] ?? 'Beginner') ?>
    </span>
    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1 rounded-full" title="Course Format">
        <?= ($course['mode'] ?? 'instructor-led') === 'self-paced' ? 'Self-Paced' : 'Instructor-Led' ?>
    </span>
    <span class="bg-yellow-400 text-blue-900 text-xs font-bold px-3 py-1 rounded-full shadow inline-block" title="Next Intake" aria-label="Next Intake">
        <?= ($course['mode'] === 'self-paced' || empty($course['next_intake_date'])) ? 'Self-paced' : htmlspecialchars(date('M j, Y', strtotime($course['next_intake_date']))) ?>
    </span>
</div>
                </div>

                <!-- Description -->
<div class="text-gray-700 text-sm mb-3">
    <?= htmlspecialchars_decode(mb_strimwidth(strip_tags($course['description'] ?? '', '<b><i><strong><em><ul><ol><li><br>'), 0, 150)) ?>...
    <a href="course_detail.php?id=<?= $course['id'] ?>" 
       class="text-blue-600 hover:underline ml-1 font-semibold focus:outline-none"
       aria-label="Read more about <?= htmlspecialchars($course['course_name'] ?? 'Course') ?>">
        Read more
    </a>
</div>
<!-- Curriculum Preview -->
<?php if (!empty($course_modules[$course['id']])): ?>
    <button class="text-xs text-blue-700 underline mb-2 focus:outline-none toggle-curriculum-btn" aria-expanded="false" aria-controls="curriculum-<?= $course['id'] ?>" onclick="toggleCurriculum(<?= $course['id'] ?>)">
        Show Curriculum
    </button>
    <div id="curriculum-<?= $course['id'] ?>" class="curriculum-container bch-hidden mb-2" style="display:none;" aria-hidden="true">
        <ul class="ml-4 list-disc text-xs">
        <?php foreach ($course_modules[$course['id']] as $midx => $module): ?>
            <li class="mb-1">
                <span class="font-semibold text-blue-900">Module <?= htmlspecialchars($module['module_order'] ?? ($midx+1)) ?>: <?= htmlspecialchars($module['module_name'] ?? 'Module') ?></span>
                <?php if (!empty($course_lessons[$module['id']])): ?>
                    <ul class="ml-4 list-decimal text-gray-600">
                        <?php foreach ($course_lessons[$module['id']] as $lesson): ?>
                            <li><?= htmlspecialchars($lesson['title'] ?? 'Lesson') ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

                <!-- Course Info -->
<div class="text-sm text-gray-600 space-y-2 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Instructor: <span class="ml-1 font-medium"><?= htmlspecialchars($course['instructor_name']) ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-book text-blue-500 mr-2"></i>
                        <?= $course['total_modules'] ?> modules
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        Duration: <span class="ml-1 font-medium"><?= $course['duration_weeks'] ?> weeks</span>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-gray-50 border rounded p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-bold">
                            <?php if ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']): ?>
                                <span class="line-through text-sm text-gray-400">Ksh <?= number_format($course['price']) ?></span>
                                <span class="text-red-600 ml-2">Ksh <?= number_format($course['discount_price']) ?></span>
                            <?php elseif ($course['price'] > 0): ?>
                                <span class="text-blue-600">Ksh <?= number_format($course['price']) ?></span>
                            <?php else: ?>
                                <span class="text-green-600 font-medium">Free</span>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                </div>

                <!-- Progress Bar or Guest Message -->
<?php if (isset($_SESSION['user_id'])): ?>
    <?php
    // Enhanced progress: percent and time spent
    $percent = isset($progress[$course['id']]['percent']) ? $progress[$course['id']]['percent'] : (isset($progress[$course['id']]) ? $progress[$course['id']] : 0);
    $time_spent = isset($progress[$course['id']]['time_spent']) ? $progress[$course['id']]['time_spent'] : 0;
    $time_display = $time_spent ? gmdate('H:i:s', $time_spent) : '00:00:00';
    ?>
    <div class="mb-4">
        <div class="flex justify-between mb-1">
            <span class="text-sm font-semibold text-blue-700">Progress</span>
            <span class="text-xs font-bold text-yellow-600">
                <?= $percent ?>%
            </span>
        </div>
        <div class="progress-bar-wrap" aria-label="Course progress bar" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" data-percent="<?= $percent ?>"<?= $percent==100?' data-complete="100"':'' ?>><?= $percent >= 15 ? $percent.'%' : '' ?></div>
        </div>
        <div class="text-xs text-gray-500 mt-1"><i class="fas fa-clock mr-1"></i> Time spent: <?= $time_display ?></div>
    </div>
<?php else: ?>
    <div class="mb-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded px-4 py-2 text-center">
        <i class="fas fa-user-circle mr-2"></i> <span>Login to track your course progress and unlock certificates.</span>
    </div>
<?php endif; ?>

<!-- Call to Action Buttons -->
<div class="flex flex-col space-y-2">
    <?php
    // === Certificate Badge on Course Card ===
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $cert_stmt = $pdo->prepare('SELECT status FROM certificates WHERE user_id = ? AND course_id = ? AND status = "issued"');
        $cert_stmt->execute([$user_id, $course['id']]);
        $cert = $cert_stmt->fetch(PDO::FETCH_ASSOC);
        if ($cert && $cert['status'] === 'issued') {
            echo '<div class="flex items-center gap-2 mb-2">';
            echo '<span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold border border-green-300"><i class="fas fa-certificate text-yellow-500 mr-1"></i> Certificate Issued</span>';
            echo '<a href="certificate.php?course_id=' . $course['id'] . '&preview=1" target="_blank" class="ml-2 px-2 py-1 rounded bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition"><i class="fas fa-eye"></i> Preview</a>';
            echo '<a href="certificate.php?course_id=' . $course['id'] . '" download class="ml-2 px-2 py-1 rounded bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition"><i class="fas fa-download"></i> Download</a>';
            echo '</div>';
        }
    }
    // === End Certificate Badge ===
    
    $is_paid = $course['price'] > 0;
    $mode = $course['mode'] ?? 'instructor';
    $can_access = false;
    $show_pay = false;
    $show_enroll = false;
    $show_start = false;
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
    ?>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php?redirect=courses.php?enroll=<?= $course['id'] ?>" class="w-full bg-blue-600 text-white text-center font-semibold py-2 rounded hover:bg-blue-700 transition">
            <i class="fas fa-lock mr-2"></i> Login to Enroll
        </a>
    <?php elseif ($show_pay): ?>
        <form method="POST" action="../payment_handler.php" class="space-y-6" autocomplete="off">
            <?php if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <input type="hidden" name="amount" value="<?= ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']) ? $course['discount_price'] : $course['price'] ?>">
            <input type="hidden" name="mpesa_pay" value="1">
            <input type="hidden" name="payment_method" value="mpesa">
            <input type="text" name="mpesa_phone" id="mpesa_phone" class="bch-form-input w-full" maxlength="13" autocomplete="off" required pattern="^0[7-9][0-9]{8}$" placeholder="e.g. 0722123456">
            <button type="submit" class="w-full bg-blue-500 text-white font-semibold py-2 rounded hover:bg-blue-600 transition-all w-full mb-1">Pay with MPESA (KES <?= number_format($course['price'],0) ?>)</button>
        </form>
    <?php elseif ($show_enroll): ?>
        <form method="POST">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <button type="submit" name="enroll" class="w-full bg-blue-600 text-white font-semibold py-2 rounded hover:bg-blue-700 transition-all w-full">Enroll Now</button>
        </form>
    <?php elseif ($can_access): ?>
        <a href="course_player.php?course_id=<?= $course['id'] ?>" class="w-full bg-primary text-white font-semibold py-2 rounded hover:bg-blue-700 transition-all w-full text-center block">Continue Course</a>
    <?php elseif ($show_wait): ?>
        <div class="w-full bg-yellow-100 text-yellow-800 font-semibold py-2 rounded text-center">
            <i class="fas fa-clock mr-2"></i> Access opens on <?= date('M j, Y', strtotime($course['intake_start'] ?? $course['start_date'] ?? '+1 week')) ?>
        </div>
    <?php endif; ?>
    <a href="course_detail.php?id=<?= $course['id'] ?>" class="w-full border border-blue-600 text-blue-600 text-center font-semibold py-2 rounded hover:bg-blue-50 transition">
        <i class="fas fa-info-circle mr-2"></i> View Details
    </a>
</div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Per-course feedback form -->

<?php
// --- Upcoming Intakes Section ---
$today = date('Y-m-d');
$upcoming_stmt = $pdo->prepare("SELECT * FROM courses WHERE status = 'active' AND next_intake_date IS NOT NULL AND next_intake_date > ? ORDER BY next_intake_date ASC LIMIT 8");
$upcoming_stmt->execute([$today]);
$upcoming_courses = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);
if ($upcoming_courses): ?>
<section class="container mx-auto mb-8 py-6 px-2 sm:px-8 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-primary flex items-center gap-2">
            <i class="fas fa-calendar-alt text-secondary"></i> Upcoming Intakes
        </h2>
        <a href="#course-catalog" class="text-blue-700 hover:underline font-medium text-sm">See All Courses</a>
    </div>
    <div class="flex gap-6 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-blue-200">
        <?php foreach ($upcoming_courses as $uc): ?>
        <div class="min-w-[270px] max-w-xs bg-white border border-blue-100 rounded-xl shadow hover:shadow-lg transition-all duration-200 p-5 flex flex-col justify-between">
            <div class="mb-3">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold"><i class="fas fa-clock mr-1"></i> <?= ($uc['mode'] === 'self-paced' || empty($uc['next_intake_date'])) ? 'Self-paced' : htmlspecialchars(date('M j, Y', strtotime($uc['next_intake_date']))) ?></span>
                    <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-semibold"><i class="fas fa-graduation-cap mr-1"></i> <?= htmlspecialchars($uc['skill_level'] ?? 'Beginner') ?></span>
                </div>
                <h3 class="text-lg font-bold text-primary mb-1 truncate" title="<?= htmlspecialchars($uc['course_name']) ?>"><?= htmlspecialchars($uc['course_name']) ?></h3>
                <p class="text-xs text-gray-600 line-clamp-2 mb-2"><?= htmlspecialchars_decode(mb_strimwidth(strip_tags($uc['description'], '<b><i><strong><em><ul><ol><li><br>'), 0, 80, '...')) ?></p>
            </div>
            <button type="button"
                class="open-enroll-modal-btn inline-block mt-auto bg-yellow-500 hover:bg-yellow-600 text-primary font-bold px-5 py-2 rounded-xl border-2 border-yellow-400 shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 text-center"
                onclick="openApplyModal(<?= $uc['id'] ?>)"
            >Apply Now</button>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

    <?php foreach ($courses as $course): ?>
      <?php
        $can_feedback = false;
        $has_feedback = false;
        $feedback_row = null;
        if (isset($_SESSION['user_id'])) {
            // Check enrollment and completion
            $user_id = $_SESSION['user_id'];
            $enroll_stmt = $pdo->prepare("SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?");
            $enroll_stmt->execute([$user_id, $course['id']]);
            $enrollment = $enroll_stmt->fetch(PDO::FETCH_ASSOC);
            if ($enrollment && $enrollment['status'] === 'completed') {
                $can_feedback = true;
                // Check if feedback exists
                $feedback_stmt = $pdo->prepare("SELECT feedback, rating FROM course_feedback WHERE user_id = ? AND course_id = ?");
                $feedback_stmt->execute([$user_id, $course['id']]);
                $feedback_row = $feedback_stmt->fetch(PDO::FETCH_ASSOC);
                $has_feedback = !!$feedback_row;
            }
        }
      ?>
      <?php if ($can_feedback): ?>
        <div id="feedback-<?= $course['id'] ?>" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
          <h4 class="text-base font-semibold mb-2 text-yellow-800">Your Feedback for this Course</h4>
          <?php if ($has_feedback): ?>
            <div class="text-green-700 mb-2"><i class="fas fa-check-circle"></i> Thank you for your feedback!</div>
            <div class="mb-1"><strong>Rating:</strong> <span class="text-yellow-500"><?php for($s=0;$s<$feedback_row['rating'];$s++) echo '★'; ?></span></div>
            <div><strong>Comment:</strong> <?= htmlspecialchars($feedback_row['feedback']) ?></div>
          <?php else: ?>
            <form method="post" action="submit_feedback.php" class="space-y-2">
              <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
              <div class="flex gap-2 mb-1 star-rating" aria-label="Rating" role="radiogroup">
                <?php for($r=1;$r<=5;$r++): ?>
                  <label class="cursor-pointer">
                    <input type="radio" name="rating" value="<?= $r ?>" required class="sr-only">
                    <span class="text-yellow-400 text-2xl" title="<?= $r ?> star<?= $r>1?'s':'' ?>"><?= str_repeat('★', $r) ?></span>
                  </label>
                <?php endfor; ?>
              </div>
              <textarea name="feedback" rows="3" maxlength="1000" placeholder="Share your experience..." required class="w-full border rounded px-3 py-2"></textarea>
              <button type="submit" class="bch-btn-primary">Submit Feedback</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
    <!-- Empty State -->
    <?php if (empty($courses)): ?>
        <div class="text-center py-12">
            <i class="fas fa-books text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500">No courses available at the moment.</p>
        </div>
    <?php endif; ?>
</section>

<!-- Schedule Section -->
 <section class="container mx-auto mb-12 py-8 px-2 sm:px-8 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md mt-16" aria-labelledby="upcoming-courses-title">
<section id="schedule" class="bg-gradient-to-br from-white to-blue-50 py-16 mt-20 rounded-2xl shadow-lg">
    <div class="max-w-7xl mx-auto px-6 sm:px-12">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-extrabold text-blue-700 mb-4 tracking-tight">
                Upcoming Course Schedule
            </h2>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto">
                Plan your learning journey with our upcoming course intakes.
            </p>
        </div>
        <div class="bg-white shadow-md rounded-xl overflow-hidden">
            <div class="bg-blue-700 text-white px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-white font-semibold mb-2 sm:mb-0">
                    Next Course Intakes - <?= date('F Y') ?>
                </h3>
                <span class="text-sm font-medium text-blue-100">Limited slots available</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-900">
                    <thead class="bg-blue-100 text-blue-800">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">Course Name</th>
                            <th class="px-6 py-3 text-left font-semibold">Start Date</th>
                            <th class="px-6 py-3 text-left font-semibold">Duration</th>
                            <th class="px-6 py-3 text-left font-semibold">Format</th>
                            <th class="px-6 py-3 text-left font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php 
                            $start_dates = ['+3 days', '+1 week', '+2 weeks', '+3 weeks'];
                            $formats = ['Online', 'Hybrid', 'In-Person', 'Self-Paced'];
                            $format_icons = [
                                'Online' => 'fa-laptop',
                                'Hybrid' => 'fa-users',
                                'In-Person' => 'fa-chalkboard-teacher',
                                'Self-Paced' => 'fa-user-clock'
                            ];
                            $i = 0;
                            foreach (array_slice($courses, 0, 4) as $course): 
                                $start_date = date('M j, Y', strtotime($start_dates[$i % count($start_dates)]));
                                $format = $formats[$i % count($formats)];
                                $icon = $format_icons[$format] ?? 'fa-calendar-alt';
                                $i++;
                        ?>
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4 font-medium text-blue-900">
                                <?= htmlspecialchars($course['course_name']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block bg-yellow-400 text-blue-900 font-semibold text-xs px-3 py-1 rounded-full">
                                    <?= $start_date ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?= $course['duration_weeks'] ?> weeks
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fas <?= $icon ?> text-blue-700"></i>
                                    <?= $format ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($course['price'] > 0): ?>
                                    <button onclick="showMpesaModal(<?= $course['id'] ?>, <?= $course['price'] ?>)" class="bg-green-600 text-white font-semibold py-2 px-4 rounded hover:bg-green-700 transition-all w-full mb-1">Pay with MPESA (KES <?= number_format($course['price'],0) ?>)</button>
                                    <button onclick="showPaypalModal(<?= $course['id'] ?>, <?= $course['price'] ?>)" class="bg-blue-500 text-white font-semibold py-2 px-4 rounded hover:bg-blue-600 transition-all w-full mb-1">Pay with PayPal</button>
                                    <button onclick="showCardModal(<?= $course['id'] ?>, <?= $course['price'] ?>)" class="bg-gray-800 text-white font-semibold py-2 px-4 rounded hover:bg-gray-900 transition-all w-full">Pay with Card</button>
                                <?php elseif (isset($_SESSION['user_id'])): ?>
                                    <a href="course_player.php?course_id=<?= $course['id'] ?>" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-700 transition-all w-full text-center block">Continue Course</a>
                                <?php else: ?>
                                    <form method="post">
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                        <button type="submit" name="enroll" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-700 transition-all w-full">Enroll Now</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 px-6 py-4 text-center">
                <a href="schedule.php" class="text-blue-700 font-semibold hover:underline inline-flex items-center">
                    View Full Schedule <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- Call to Action Section -->
<section class="mt-12 rounded-2xl shadow-lg bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md py-10">
    <div class="container mx-auto text-center px-4">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-blue-700 mb-6">
            Ready to Start Your Learning Journey?
        </h2>
        <p class="text-lg sm:text-xl text-gray-500 mb-8 max-w-3xl mx-auto">
            Join Bonnie Computer Hub today and transform your career with our industry-leading courses.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="register.php" class="inline-flex items-center justify-center px-6 py-3 text-lg font-semibold text-indigo-700 bg-yellow-400 hover:bg-yellow-500 rounded-lg shadow-lg transition duration-300">
                <i class="fas fa-user-plus mr-2" aria-hidden="true"></i> Register Now
            </a>
            <a href="#" class="inline-flex items-center justify-center px-6 py-3 text-lg font-semibold border border-white text-blue-600 hover:bg-white hover:text-indigo-700 rounded-lg shadow-lg transition duration-300">
                <i class="fas fa-file-download mr-2" aria-hidden="true"></i> Download Brochure
            </a>
        </div>
        <div class="mt-10 flex justify-center items-center flex-wrap gap-8">
            <div class="flex items-center text-blue-700">
                <i class="fas fa-shield-alt text-3xl text-yellow-300 mr-3" aria-hidden="true"></i>
                <span class="text-lg font-medium">Secure Enrollment</span>
            </div>
            <div class="flex items-center text-blue-700">
                <i class="fas fa-headset text-3xl text-yellow-300 mr-3" aria-hidden="true"></i>
                <span class="text-lg font-medium">24/7 Support</span>
            </div>
            <div class="flex items-center text-blue-700">
                <i class="fas fa-award text-3xl text-yellow-300 mr-3" aria-hidden="true"></i>
                <span class="text-lg font-medium">Industry Recognized</span>
            </div>
        </div>
    </div>
</section>


    <!-- BCH Apply Modal: loaded dynamically by apply-modal.js -->
    <script src="../../assets/js/apply-modal.js"></script>
    <?php include_once('../includes/footer.php'); ?>
    <?php if (isset($_SESSION['error_msg'])): ?>
      <div class="bg-red-100 text-red-700 rounded px-4 py-2 text-center my-4">
        <?= htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?>
      </div>
    <?php endif; ?>
    
    <!-- JavaScript for curriculum toggle -->
    <script>
        function toggleCurriculum(courseId) {
            const curriculumElement = document.getElementById(`curriculum-${courseId}`);
            const button = curriculumElement.nextElementSibling.querySelector('button');
            const isHidden = curriculumElement.classList.contains('bch-hidden');
            
            if (isHidden) {
                curriculumElement.classList.remove('bch-hidden');
                button.setAttribute('aria-expanded', 'true');
                button.innerHTML = '<i class="fas fa-chevron-up bch-mr-1"></i> Hide curriculum';
            } else {
                curriculumElement.classList.add('bch-hidden');
                button.setAttribute('aria-expanded', 'false');
                button.innerHTML = '<i class="fas fa-chevron-down bch-mr-1"></i> Show curriculum';
            }
            
            // Add a smooth animation effect
            curriculumElement.style.maxHeight = isHidden ? `${curriculumElement.scrollHeight}px` : '0';
        }
        
        // Filter functions
        function applyFilter() {
            // In a real implementation, this would collect all filter values and submit the form
            document.getElementById('filter-form').submit();
        }
        
        function clearAllFilters() {
            // Clear all checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Reset price range
            const priceRange = document.getElementById('price-range');
            if (priceRange) {
                priceRange.value = priceRange.max;
                updatePriceValue(priceRange.value);
            }
            
            // Apply the cleared filters
            window.location.href = window.location.pathname;
        }
        
        function removeFilter(type, value) {
            // Remove the specific filter
            const filterForm = document.getElementById('filter-form');
            const inputs = filterForm.elements;
            
            for (let i = 0; i < inputs.length; i++) {
                const input = inputs[i];
                
                if (input.type === 'checkbox' && input.name.includes(type) && input.value === value) {
                    input.checked = false;
                }
                
                if (type === 'price' && input.id === 'price-range') {
                    input.value = input.max;
                    updatePriceValue(input.max);
                }
                
                if (type === 'free' && input.id === 'free-courses') {
                    input.checked = false;
                }
            }
            
            // Apply the updated filters
            applyFilter();
        }
        
        function updatePriceValue(value) {
            // Update the price display with formatting
            const formattedPrice = new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: 'KES',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value).replace('KES', 'KSh');
            
            document.getElementById('price-display').textContent = formattedPrice;
        }
        
        // Add an event listener for the course cards to improve interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips if any
            const tooltips = document.querySelectorAll('[data-tooltip]');
            if (tooltips.length > 0) {
                tooltips.forEach(tooltip => {
                    // Implementation of tooltips would go here
                });
            }
            
            // Add animation effects to cards
            const courseCards = document.querySelectorAll('.course-card-transition');
            if (courseCards.length > 0) {
                courseCards.forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        this.classList.add('bch-card-hover');
                    });
                    
                    card.addEventListener('mouseleave', function() {
                        this.classList.remove('bch-card-hover');
                    });
                });
            }
        });
    </script>
    <!-- Course curriculum toggle script -->
    <script src="courses_toggle.js"></script>
<script src="../assets/js/apply-modal.js"></script>
</body>
</html>
