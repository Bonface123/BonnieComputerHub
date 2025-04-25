<?php
session_start();
require_once '../includes/db_connect.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name, u.bio as instructor_bio, u.photo as instructor_photo FROM courses c JOIN users u ON c.created_by = u.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<div class='text-red-500 font-semibold'>Course not found.</div>";
    include '../includes/footer.php';
    exit;
}

$modules_stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC");
$modules_stmt->execute([$course_id]);
$modules = $modules_stmt->fetchAll(PDO::FETCH_ASSOC);

$lessons = [];
foreach ($modules as $module) {
    $lessons_stmt = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order ASC");
    $lessons_stmt->execute([$module['id']]);
    $lessons[$module['id']] = $lessons_stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';

$breadcrumbs = [
    ['label' => 'Home', 'url' => getBaseUrl() . 'index.html'],
    ['label' => 'Courses', 'url' => 'courses.php'],
    ['label' => htmlspecialchars($course['course_name']), 'url' => null]
];
?>

<div class="container mx-auto px-4 py-12 max-w-5xl">
    <div class="bg-white shadow-2xl rounded-2xl border border-gray-200 overflow-hidden">

        <div class="p-8 space-y-12">

            <!-- Overview -->
            <div class="flex flex-col md:flex-row md:space-x-10">
                <!-- Main Content -->
                <div class="flex-1">
                    <div class="mb-6 flex flex-col sm:flex-row gap-6">
                        <div>
                            <?php if (!empty($course['thumbnail'])): ?>
                                <img src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>"
                                    alt="Course Thumbnail"
                                    class="h-52 w-52 object-cover rounded-2xl border shadow-md">
                            <?php else: ?>
                                <div class="h-52 w-52 flex items-center justify-center bg-gray-100 rounded-2xl border text-gray-400 text-lg">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h1 class="text-4xl font-bold text-blue-800 mb-4"><?= htmlspecialchars($course['course_name']) ?></h1>
                            <div class="flex flex-wrap items-center gap-4 mb-3">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-semibold text-xs flex items-center"><i class="fas fa-calendar-alt mr-1"></i> Next Intake: <?= ($course['mode'] === 'self-paced' || empty($course['next_intake_date'])) ? 'Self-paced' : htmlspecialchars(date('M j, Y', strtotime($course['next_intake_date']))) ?></span>
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold text-xs flex items-center"><i class="fas fa-graduation-cap mr-1"></i> <?= htmlspecialchars($course['skill_level'] ?? 'Beginner') ?></span>
                            </div>
                            <p class="text-gray-700 leading-relaxed mb-4"><?= nl2br(htmlspecialchars_decode(strip_tags($course['description'], '<b><i><strong><em><ul><ol><li><br>'))) ?></p>
                            <!-- Mobile CTA Apply Button -->
                            <button type="button" onclick="openApplyModal(<?= $course_id ?>)" class="block md:hidden w-full bg-yellow-500 text-primary font-bold py-3 rounded-full shadow hover:bg-yellow-400 transition focus:outline-none focus:ring-4 focus:ring-yellow-300 text-lg mb-4">Apply for this Course</button>
<div id="bch-apply-feedback" class="mt-4 text-center text-sm"></div>
<script>
function submitCourseApplication(formData, callback) {
    fetch('enroll_apply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => callback(data))
    .catch(() => callback({success: false, message: 'Submission failed. Try again.'}));
}
// Modal logic is handled in apply-modal.js, but ensure feedback div is available for error/success.
</script>
                            <a href="javascript:history.back()" class="text-blue-600 mt-3 inline-block hover:underline">← Back</a>
                        </div>
                    </div>
                </div>
                <!-- Sticky Sidebar (Desktop) -->
                <aside class="hidden md:block w-80">
                    <div class="sticky top-24 bg-white border border-blue-100 rounded-2xl shadow-lg p-6 flex flex-col gap-4">
                        <div class="text-2xl font-bold text-blue-700 mb-2">Ksh
                            <?php if ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']): ?>
                                <span class="line-through text-red-400 text-lg mr-2"><?= number_format($course['price']) ?></span>
                                <span class="text-green-600"><?= number_format($course['discount_price']) ?></span>
                            <?php elseif ($course['price'] > 0): ?>
                                <span><?= number_format($course['price']) ?></span>
                            <?php else: ?>
                                <span class="text-green-600">Free</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-calendar-alt text-yellow-500"></i>
                            <span>Next Intake: <?= ($course['mode'] === 'self-paced' || empty($course['next_intake_date'])) ? 'Self-paced' : htmlspecialchars(date('M j, Y', strtotime($course['next_intake_date']))) ?></span>
                        </div>
                        <button type="button" onclick="openApplyModal(<?= $course_id ?>)" class="w-full bg-yellow-500 text-primary font-bold py-3 rounded-full shadow hover:bg-yellow-400 transition focus:outline-none focus:ring-4 focus:ring-yellow-300 text-lg">Apply for this Course</button>
                    </div>
                </aside>
            </div>


            <!-- Curriculum -->
            <div>
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">📚 Curriculum</h2>
                <?php foreach ($modules as $index => $module): ?>
                    <div x-data="{ open: false }" class="mb-4 border rounded-lg shadow-sm">
                        <button @click="open = !open"
                                class="w-full px-4 py-3 text-left text-lg font-semibold bg-gray-50 hover:bg-gray-100 transition-all flex justify-between items-center">
                            <?= htmlspecialchars($module['module_name']) ?>
                            <span x-show="!open">+</span>
                            <span x-show="open">−</span>
                        </button>
                        <div x-show="open" class="px-5 pb-4 pt-2 text-gray-700">
                            <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($module['module_description']) ?></p>
                            <ul class="list-disc pl-5 space-y-1">
                                <?php foreach ($lessons[$module['id']] as $lesson): ?>
                                    <li><?= htmlspecialchars($lesson['title']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Instructor -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">👨‍🏫 Instructor</h2>
                <div class="flex space-x-5 items-start">
                    <?php if ($course['instructor_photo']): ?>
                        <img src="../uploads/instructor_photos/<?= htmlspecialchars($course['instructor_photo']) ?>"
                             alt="<?= htmlspecialchars($course['instructor_name']) ?>"
                             class="w-20 h-20 rounded-full border shadow">
                    <?php endif; ?>
                    <div>
                        <a href="instructor-profile.php?id=<?= $course['created_by'] ?>"
                           class="text-lg font-bold text-blue-600 hover:underline">
                            <?= htmlspecialchars($course['instructor_name']) ?>
                        </a>
                        <p class="text-gray-600 mt-1 text-sm"><?= nl2br(htmlspecialchars($course['instructor_bio'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Requirements -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">✅ Requirements & Outcomes</h2>
                <ul class="list-disc pl-6 text-gray-700 space-y-1">
                    <li>Basic knowledge of web development</li>
                    <li>Computer with internet access</li>
                    <li>Willingness to learn</li>
                </ul>
            </div>

            <!-- Pricing -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">💰 Pricing</h2>
                <p class="text-lg text-gray-700">
                    <?php if ($course['discount_price'] > 0 && $course['discount_price'] < $course['price']): ?>
                        <span class="line-through text-red-400">Ksh <?= number_format($course['price']) ?></span>
                        <span class="text-green-600 font-bold ml-2">Ksh <?= number_format($course['discount_price']) ?></span>
                    <?php elseif ($course['price'] > 0): ?>
                        <span class="text-blue-700 font-bold">Ksh <?= number_format($course['price']) ?></span>
                    <?php else: ?>
                        <span class="text-green-600 font-bold">Free</span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Media -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">🎬 Media</h2>
                <div class="flex flex-wrap gap-4">
                    <img src="../uploads/media/sample-image.jpg" class="w-64 h-40 object-cover rounded shadow" alt="Sample">
                    <video controls class="w-64 h-40 rounded shadow">
                        <source src="../uploads/media/sample-video.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>

            <!-- CTA -->
            <div class="border-t pt-6">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="enroll.php" method="POST">
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                            Enroll Now
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                        Login to Enroll
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js for collapse functionality -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="../../assets/js/apply-modal.js"></script>
<script>
// Ensure the modal is present if user lands directly on this page
if (!document.getElementById('bch-apply-modal')) {
    var script = document.createElement('script');
    script.src = '../../assets/js/apply-modal.js';
    document.body.appendChild(script);
}
</script>

<?php include '../includes/footer.php'; ?>

                    </a>
                
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js for collapse functionality -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="../../assets/js/apply-modal.js"></script>
<script>
// Ensure the modal is present if user lands directly on this page
if (!document.getElementById('bch-apply-modal')) {
    var script = document.createElement('script');
    script.src = '../../assets/js/apply-modal.js';
    document.body.appendChild(script);
}
</script>

<?php include '../includes/footer.php'; ?>
