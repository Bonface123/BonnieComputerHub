<?php
// Student-facing course view for Bonnie Computer Hub LMS
session_start();
require_once '../includes/db_connect.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$course_id) {
    echo '<div class="p-8 text-red-600">Invalid course ID.</div>';
    exit;
}

// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 'active'");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<div class="p-8 text-red-600">Course not found or inactive.</div>';
    exit;
}

// Fetch modules
$modules = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order");
$modules->execute([$course_id]);
$modules = $modules->fetchAll(PDO::FETCH_ASSOC);

// Parse schedule
$schedule = json_decode($course['schedule'] ?? '[]', true);

$breadcrumbs = [
    "Home" => "../index.php",
    "Courses" => "courses.php",
    htmlspecialchars($course['course_name']) => ""
];
include '../includes/breadcrumbs.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body, html {
            font-family: 'Century Gothic', 'AppleGothic', sans-serif;
            font-size: 16px;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/bch-global.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['course_name']) ?> | BCH LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="../../index.html" class="text-2xl font-bold text-secondary">Bonnie Computer Hub</a>
            <a href="../pages/courses.php" class="text-white hover:text-secondary transition"><i class="fas fa-arrow-left mr-2"></i>All Courses</a>
        </div>
    </header>
    <main class="container mx-auto px-2 md:px-6 py-8">
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-10 border border-blue-100 relative overflow-hidden animate-fade-in-up">
            <div class="flex flex-col md:flex-row gap-10 items-center">
                <div class="flex-shrink-0">
                <?php if (!empty($course['banner_image'])): ?>
                    <img src="../uploads/banners/<?= htmlspecialchars($course['banner_image']) ?>" alt="Course Banner" class="h-52 w-full md:w-96 object-cover rounded-xl shadow-lg border-4 border-yellow-100 mb-4 md:mb-0 transition-transform duration-300 hover:scale-105">
                <?php elseif (!empty($course['thumbnail'])): ?>
                    <img src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>" alt="Course Thumbnail" class="h-44 w-44 rounded-xl object-cover shadow-md border-2 border-blue-100 mb-4 md:mb-0 transition-transform duration-300 hover:scale-105">
                <?php else: ?>
                    <div class="h-44 w-44 rounded-xl bg-blue-50 flex items-center justify-center text-5xl text-blue-200 mb-4 md:mb-0">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h1 class="text-4xl font-extrabold text-primary mb-4 tracking-tight flex items-center gap-3">
                        <i class="fas fa-book-open text-secondary"></i> <?= htmlspecialchars($course['course_name']) ?>
                    </h1>
                    <div class="text-lg text-gray-700 mb-4 leading-relaxed">
                        <?= $course['description'] ?>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center mb-4">
                        <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-semibold text-sm shadow-sm">
                            <i class="fas fa-coins"></i> <?= $course['price_type'] === 'free' ? 'Free' : ('KES ' . number_format($course['price'],2)) ?>
                        </span>
                        <span class="inline-flex items-center gap-1 bg-primary text-white px-3 py-1 rounded-full font-semibold text-sm shadow-sm">
                            <i class="fas fa-user-check"></i> <?= ucfirst($course['enrollment_status'] ?? 'Open') ?> Enrollment
                        </span>
                        <?php if (!empty($course['delivery_mode'])): ?>
                            <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">
                                <i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($course['delivery_mode']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($course['certification'])): ?>
                            <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                                <i class="fas fa-certificate"></i> Certificate
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($course['tags'])): ?>
                            <?php foreach (explode(',', $course['tags']) as $tag): ?>
                                <span class="bg-yellow-50 text-yellow-700 text-xs font-medium px-2 py-1 rounded-full border border-yellow-200" aria-label="Tag: <?= htmlspecialchars(trim($tag)) ?>">
                                    #<?= htmlspecialchars(trim($tag)) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($course['tools'])): ?>
                            <?php foreach (explode(',', $course['tools']) as $tool): ?>
                                <span class="bg-blue-50 text-blue-800 text-xs font-medium px-2 py-1 rounded-full border border-blue-200" aria-label="Tool: <?= htmlspecialchars(trim($tool)) ?>">
                                    <i class="fas fa-toolbox mr-1"></i><?= htmlspecialchars(trim($tool)) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($course['outcomes'])): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded mb-3 animate-fade-in-up">
                        <div class="font-semibold text-green-800 mb-1 flex items-center gap-2"><i class="fas fa-check-circle"></i> What you'll achieve:</div>
                        <div class="text-green-700 text-sm leading-relaxed"> <?= htmlspecialchars($course['outcomes']) ?> </div>
                    </div>
                    <?php endif; ?>
                    <div class="flex flex-wrap gap-2 items-center text-sm text-gray-600 mb-2">
                        <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-800 px-3 py-1 rounded-full font-medium text-xs mr-2">
                            <i class="fas fa-calendar-alt"></i> Intake:
                            <?= isset($course['intake_start']) && $course['intake_start'] ? htmlspecialchars(date('M j, Y', strtotime($course['intake_start']))) : 'TBA' ?>
                            -
                            <?= isset($course['intake_end']) && $course['intake_end'] ? htmlspecialchars(date('M j, Y', strtotime($course['intake_end']))) : 'TBA' ?>
                        </span>
                        <?php if (isset($course['enrollment_deadline']) && $course['enrollment_deadline']): ?>
                            <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 px-3 py-1 rounded-full font-medium text-xs mr-2"><i class="fas fa-hourglass-half"></i> Enroll by <?= htmlspecialchars(date('M j, Y', strtotime($course['enrollment_deadline']))) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4">
                        <h2 class="font-semibold text-primary mb-1 flex items-center gap-2"><i class="fas fa-calendar-week"></i> Weekly Schedule</h2>
                        <ul class="list-disc ml-6 text-gray-700 space-y-1">
                            <?php foreach ($schedule as $week => $topic): if ($topic): ?>
                                <li class="flex items-center gap-2"><i class="fas fa-chevron-right text-blue-400"></i> <span class="font-medium">Week <?= $week ?>:</span> <?= htmlspecialchars($topic) ?></li>
                            <?php endif; endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="my-8 border-t border-dashed border-blue-100"></div>
            <!-- Instructor and FAQ sections can be added here if data available -->
        </div>
        <!-- Modules -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-primary mb-4">Course Modules</h2>
            <?php if ($modules): ?>
                <div class="space-y-6">
                    <?php 
                    // Calculate progress (simulate: all lessons = 0% complete for now, but ready for real data)
                    $total_lessons = 0; $completed_lessons = 0;
                    foreach ($modules as $m) {
                        $c = $pdo->prepare("SELECT COUNT(*) FROM module_content WHERE module_id = ?");
                        $c->execute([$m['id']]);
                        $total_lessons += $c->fetchColumn();
                    }
                    // TODO: Replace $completed_lessons with real completion data for the logged-in student
                    $progress = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;
                    ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Overall Progress</label>
                        <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                            <div class="bg-primary h-4 rounded-full transition-all" style="width: <?= $progress ?>%"></div>
                        </div>
                        <div class="text-xs text-gray-600">You have completed <?= $completed_lessons ?> of <?= $total_lessons ?> lessons (<?= $progress ?>%)</div>
                    </div>
                    <?php foreach ($modules as $module): ?>
                        <div x-data="{ open: false }" class="border rounded-lg p-4" x-init="$el.querySelector('button').focus()">
                            <button @click="open = !open" :aria-expanded="open.toString()" class="flex items-center w-full text-left focus:outline-none focus:ring-2 focus:ring-primary rounded transition" aria-controls="module-<?= $module['id'] ?>">
                                <span class="flex-1 text-lg font-semibold text-primary">
                                    <?= htmlspecialchars($module['module_name']) ?>
                                </span>
                                <svg :class="{'rotate-90': open}" class="w-5 h-5 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                            <div x-show="open" id="module-<?= $module['id'] ?>" class="mt-2" x-transition>
                                <?php if (!empty($module['module_description'])): ?>
                                    <div class="text-gray-700 mb-2">
                                        <?= htmlspecialchars($module['module_description']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($module['learning_objectives'])): ?>
                                    <div class="ml-2 text-blue-800 text-xs mt-1"><strong>Objectives:</strong> <?= htmlspecialchars($module['learning_objectives']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($module['topics'])): ?>
                                    <div class="ml-2 text-blue-700 text-xs mt-1"><strong>Topics:</strong> <?= htmlspecialchars($module['topics']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($module['outcomes'])): ?>
                                    <div class="ml-2 text-green-700 text-xs mt-1"><strong>Outcomes:</strong> <?= htmlspecialchars($module['outcomes']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($module['resources'])): ?>
                                    <div class="ml-2 text-indigo-700 text-xs mt-1"><strong>Resources:</strong> <?= htmlspecialchars($module['resources']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($module['assignments'])): ?>
                                    <div class="ml-2 text-pink-700 text-xs mt-1"><strong>Assignment:</strong> <?= htmlspecialchars($module['assignments']) ?></div>
                                <?php endif; ?>
                                <?php
                                // Fetch module content
                                $contents = $pdo->prepare("SELECT * FROM module_content WHERE module_id = ? ORDER BY content_order");
                                $contents->execute([$module['id']]);
                                $contents = $contents->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php if ($contents): ?>
                                    <ul class="list-decimal ml-6">
                                        <?php foreach ($contents as $content): ?>
                                            <li class="mb-2 flex items-center">
                                                <span class="font-medium text-blue-700 flex-1"> <?= htmlspecialchars($content['title']) ?></span>
                                                <?php if (!empty($content['type_tag'])): ?>
                                                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700" aria-label="<?= htmlspecialchars($content['type_tag']) ?>">
                                                        <?= htmlspecialchars(ucfirst($content['type_tag'])) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($content['quiz_link'])): ?>
                                                    <a href="<?= htmlspecialchars($content['quiz_link']) ?>" class="ml-2 text-yellow-700 underline" target="_blank" rel="noopener" aria-label="Quiz">Quiz</a>
                                                <?php endif; ?>
                                                <?php if (!empty($content['resource_links'])): ?>
                                                    <a href="<?= htmlspecialchars($content['resource_links']) ?>" class="ml-2 text-green-700 underline" target="_blank" rel="noopener" aria-label="Resource">Resource</a>
                                                <?php endif; ?>
                                                <?php if (!empty($content['assignment_links'])): ?>
                                                    <a href="<?= htmlspecialchars($content['assignment_links']) ?>" class="ml-2 text-pink-700 underline" target="_blank" rel="noopener" aria-label="Assignment">Assignment</a>
                                                <?php endif; ?>
                                                <?php if ($content['content_type'] === 'video'): ?>
                                                    <a href="<?= htmlspecialchars($content['content_url']) ?>" target="_blank" class="ml-2 text-blue-700 underline">Watch Video</a>
                                                <?php elseif ($content['content_type'] === 'document'): ?>
                                                    <a href="../uploads/materials/<?= htmlspecialchars($content['content_file']) ?>" target="_blank" class="ml-2 text-blue-700 underline">Download Document</a>
                                                <?php elseif ($content['content_type'] === 'text'): ?>
                                                    <div class="ml-2 text-gray-700"> <?= $content['description'] ?> </div>
                                                <?php endif; ?>
                                                <!-- Simulated lesson progress (checkbox, disabled) -->
                                                <input type="checkbox" class="ml-2 h-4 w-4 text-primary border-gray-300 rounded" disabled aria-label="Mark lesson complete (coming soon)">
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="text-gray-500">No content added yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-gray-500">No modules added yet.</div>
            <?php endif; ?>
        </div>
        <!-- Alpine.js for collapsible modules -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <!-- Certificate Download Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
            <h2 class="text-xl font-bold text-primary mb-4">Course Certificate</h2>
            <?php
            // Fetch certificate details (status + pdf_path)
            $cert_stmt = $pdo->prepare('SELECT status, pdf_path FROM certificates WHERE user_id = ? AND course_id = ? AND status = "issued"');
            $cert_stmt->execute([$_SESSION['user_id'], $course_id]);
            $cert = $cert_stmt->fetch(PDO::FETCH_ASSOC);
            if ($cert && $cert['status'] === 'issued') {
            ?>
                <div class="flex flex-wrap gap-4 items-center">
                    <a href="../pages/certificate.php?course_id=<?= $course_id ?>&preview=1" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">Preview Certificate</a>
                    <a href="../pages/certificate.php?course_id=<?= $course_id ?>" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">Download PNG</a>
                    <?php if (!empty($cert['pdf_path'])): ?>
                        <a href="<?= htmlspecialchars($cert['pdf_path']) ?>" target="_blank" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded shadow transition">Download PDF</a>
                    <?php endif; ?>
                </div>
            <?php
            } else {
            ?>
                <div class="text-gray-600">Certificate will be available for download once you complete all course requirements.</div>
            <?php } ?>

        </div>
        
    </div>
    </main>

    <?php
    // --- Sticky Enroll Bar ---
    $showEnrollBar = ($course['enrollment_status'] ?? 'open') === 'open';
    if ($showEnrollBar): ?>
    <div class="fixed bottom-0 left-0 w-full z-50 bg-white/95 border-t border-blue-100 shadow-2xl py-4 px-4 sm:px-0 animate-fade-in-up" style="backdrop-filter: blur(4px);">
        <div class="container mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-semibold text-sm">
                    <i class="fas fa-coins"></i> <?= $course['price_type'] === 'free' ? 'Free' : ('KES ' . number_format($course['price'],2)) ?>
                </span>
                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-800 px-3 py-1 rounded-full font-medium text-xs">
                    <i class="fas fa-calendar-alt"></i> Intake: <?= isset($course['intake_start']) && $course['intake_start'] ? htmlspecialchars(date('M j, Y', strtotime($course['intake_start']))) : 'TBA' ?>
                </span>
                <?php if (isset($course['enrollment_deadline']) && $course['enrollment_deadline']): ?>
                    <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 px-3 py-1 rounded-full font-medium text-xs"><i class="fas fa-hourglass-half"></i> Enroll by <?= htmlspecialchars(date('M j, Y', strtotime($course['enrollment_deadline']))) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="#enroll-action" class="w-full sm:w-auto bg-primary text-white font-bold px-8 py-3 rounded-xl shadow hover:bg-blue-800 transition text-lg text-center animate-pulse focus:outline-none focus:ring-2 focus:ring-secondary">Enroll Now</a>
            </div>
        </div>
    </div>
    <style>
    @keyframes fade-in-up {
        0% { opacity: 0; transform: translateY(40px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.7s cubic-bezier(0.4,0,0.2,1) 0s 1; }
    </style>
    <?php endif; ?>

    <footer class="bg-primary text-white mt-12">
        <div class="container mx-auto px-4 py-6 text-center">
            &copy; <?= date('Y') ?> Bonnie Computer Hub. All rights reserved.
        </div>
    </footer>
</body>
</html>
