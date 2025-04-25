<?php
session_start();
require_once '../includes/db_connect.php';

// Get instructor ID from query string
$instructor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch instructor details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'instructor'");
$stmt->execute([$instructor_id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    include '../includes/header.php';
    echo '<section class="flex flex-col items-center justify-center min-h-[60vh] bg-gradient-to-br from-bch-blue-light to-bch-blue-dark py-20 animate-fadeIn">
        <div class="bg-white rounded-2xl shadow-2xl border border-bch-blue-light max-w-md w-full p-10 text-center">
            <div class="flex flex-col items-center mb-6">
                <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-bch-gold/20 text-bch-gold text-5xl mb-4"><i class="fas fa-user-slash"></i></span>
                <h1 class="text-3xl font-extrabold text-bch-blue mb-2">Instructor Not Found</h1>
                <p class="text-bch-gray-700 mb-4">Sorry, the instructor you are looking for does not exist or may have been removed.</p>';
                if (!empty($_SERVER['HTTP_REFERER'])) {
                    echo '<a href="javascript:history.back()" class="inline-block bg-bch-gold text-bch-blue font-semibold px-6 py-2 rounded-lg shadow hover:bg-bch-blue hover:text-bch-gold transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-bch-gold">Go Back</a>';
                } else {
                    echo '<a href="courses.php" class="inline-block bg-bch-gold text-bch-blue font-semibold px-6 py-2 rounded-lg shadow opacity-70 cursor-not-allowed">Go Back</a>';
                }
    echo '       </div>
        </div>
    </section>';

    include '../includes/footer.php';
    exit;
}

// Fetch courses taught by this instructor
$courses_stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
$courses_stmt->execute([$instructor_id]);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
$breadcrumbs = [
    ['label' => 'Home', 'url' => getBaseUrl() . 'index.html'],
    ['label' => 'Instructors', 'url' => 'instructors.php'],
    ['label' => htmlspecialchars($instructor['name']), 'url' => null]
];
?>

<section class="relative bg-gradient-to-br from-bch-blue-light to-bch-blue-dark py-16 mb-8 animate-fadeIn">
    <div class="container mx-auto px-4 max-w-3xl flex flex-col md:flex-row items-center gap-10">
        <div class="flex-shrink-0">
            <?php if (!empty($instructor['photo'])): ?>
                <img src="../uploads/profiles/<?= htmlspecialchars($instructor['photo']) ?>" alt="<?= htmlspecialchars($instructor['name']) ?>" class="h-44 w-44 object-cover rounded-full border-4 border-bch-gold shadow-lg bg-white">
            <?php else: ?>
                <div class="h-44 w-44 flex items-center justify-center bg-bch-gray-200 rounded-full border-4 border-bch-gold text-bch-blue text-4xl shadow-lg">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-1 text-center md:text-left">
            <h1 class="text-4xl font-extrabold text-bch-gold mb-2 tracking-tight drop-shadow"> <?= htmlspecialchars($instructor['name']) ?> </h1>
            <div class="text-xl text-bch-gray-100 mb-3 font-semibold">Instructor Profile</div>
            <?php if (!empty($instructor['bio'])): ?>
                <div class="prose max-w-none text-bch-gray-100 mb-4"> <?= $instructor['bio'] ?> </div>
            <?php endif; ?>
            <div class="flex flex-wrap gap-3 items-center justify-center md:justify-start mt-2 text-bch-gray-100">
                <span class="inline-flex items-center gap-2"><i class="fas fa-envelope text-bch-gold"></i> <?= htmlspecialchars($instructor['email']) ?></span>
                <!-- Placeholder social links -->
                <a href="#" class="hover:text-bch-gold transition" aria-label="LinkedIn"><i class="fab fa-linkedin text-xl"></i></a>
                <a href="#" class="hover:text-bch-gold transition" aria-label="Twitter"><i class="fab fa-twitter text-xl"></i></a>
                <a href="#" class="hover:text-bch-gold transition" aria-label="Facebook"><i class="fab fa-facebook text-xl"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="container mx-auto px-4 max-w-5xl animate-slideInUp">
    <div class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-bch-blue-light p-10">
        <h2 class="text-2xl md:text-3xl font-bold text-bch-blue mb-8 flex items-center gap-3"><i class="fas fa-chalkboard-teacher text-bch-gold"></i> Courses Taught</h2>
        <?php if ($courses): ?>
            <div class="grid gap-8 md:grid-cols-2">
                <?php foreach ($courses as $course): ?>
                    <div class="group bg-gradient-to-br from-bch-gray-100 to-bch-blue-50 rounded-xl shadow-lg p-6 border border-bch-blue-light flex flex-col transition-transform transform hover:-translate-y-1 hover:shadow-2xl focus-within:ring-2 focus-within:ring-bch-gold animate-fadeIn">
                        <div class="flex items-center gap-4 mb-3">
                            <?php if (!empty($course['thumbnail'])): ?>
                                <img src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>" alt="Course Thumbnail" class="h-16 w-16 object-cover rounded border-2 border-bch-gold shadow">
                            <?php else: ?>
                                <div class="h-16 w-16 flex items-center justify-center bg-bch-gray-200 rounded border-2 border-bch-gold text-bch-blue">No Image</div>
                            <?php endif; ?>
                            <div>
                                <a href="course-details.php?id=<?= $course['id'] ?>" class="text-xl font-bold text-bch-blue group-hover:text-bch-gold transition underline underline-offset-4 decoration-bch-gold"> <?= htmlspecialchars($course['course_name']) ?> </a>
                                <div class="text-sm text-bch-gray-700 mt-1">
                                    <?= $course['price_type'] === 'free' ? '<span class=\'font-semibold text-green-600\'>Free</span>' : ('<span class=\'font-semibold text-bch-gold\'>Paid: KES ' . number_format($course['price'],2) . '</span>') ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-bch-gray-800 line-clamp-3 mb-2"> <?= strip_tags($course['description']) ?> </div>
                        <a href="course-details.php?id=<?= $course['id'] ?>" class="mt-auto inline-block bg-bch-blue text-bch-gold px-4 py-2 rounded-lg font-semibold shadow hover:bg-bch-gold hover:text-bch-blue transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-bch-gold">View Course</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-bch-gray-600 italic">No courses found for this instructor.</div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
