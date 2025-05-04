<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$filter_category = $_POST['category'] ?? '';
$filter_skill = $_POST['skill'] ?? '';
$filter_price = $_POST['price'] ?? '';
$filter_search = trim($_POST['search'] ?? '');

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

// Render the course cards as HTML
ob_start();
foreach ($courses as $course): ?>
    <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow p-5 focus:outline-none focus:ring-2 focus:ring-yellow-400">
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
        <div class="text-gray-700 text-sm mb-3">
            <?= htmlspecialchars_decode(mb_strimwidth(strip_tags($course['description'] ?? '', '<b><i><strong><em><ul><ol><li><br>'), 0, 150)) ?>...
            <a href="course_detail.php?id=<?= $course['id'] ?>" class="text-blue-600 hover:underline ml-1 font-semibold focus:outline-none" aria-label="Read more about <?= htmlspecialchars($course['course_name'] ?? 'Course') ?>">Read more</a>
        </div>
        <div class="text-sm text-gray-600 space-y-2 mb-4">
            <div class="flex items-center">
                <i class="fas fa-user text-blue-500 mr-2"></i>
                Instructor: <span class="ml-1 font-medium"><?= htmlspecialchars($course['instructor_name']) ?></span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-users text-blue-500 mr-2"></i>
                <?= $course['enrolled_students'] ?> students enrolled
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
        <a href="course_detail.php?id=<?= $course['id'] ?>" class="w-full border border-blue-600 text-blue-600 text-center font-semibold py-2 rounded hover:bg-blue-50 transition mt-2 block">
            <i class="fas fa-info-circle mr-2"></i> View Details
        </a>
    </div>
<?php endforeach;
$html = ob_get_clean();
echo json_encode([
    'html' => $html,
    'count' => count($courses),
]);
