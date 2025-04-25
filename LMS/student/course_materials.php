<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch all enrolled courses
$courses_stmt = $pdo->prepare("SELECT c.*, e.status as enrollment_status FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ? AND c.status = 'active' AND c.enrollment_status = 'open'");
$courses_stmt->execute([$student_id]);
$enrolled_courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_materials = [];
foreach ($enrolled_courses as $course) {
    $course_id = $course['id'];
    $mode = $course['mode'] ?? 'instructor';
    $is_paid = $course['price'] > 0;
    $can_access = false;
    $show_pay = false;
    $show_wait = false;
    // Payment check
    if ($is_paid) {
        $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND course_id = ? AND status = 'completed'");
        $payment_stmt->execute([$student_id, $course_id]);
        $has_paid = $payment_stmt->rowCount() > 0;
        if ($has_paid) {
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
            $show_pay = true;
        }
    } else {
        // Free course
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
    }

    if ($can_access) {
        // Fetch materials for this course
        $query = $pdo->prepare("SELECT mc.*, cm.module_name, c.course_name, c.id as course_id FROM module_content mc JOIN course_modules cm ON mc.module_id = cm.id JOIN courses c ON cm.course_id = c.id WHERE c.id = ? ORDER BY cm.module_name, mc.content_order");
        $query->execute([$course_id]);
        $materials = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($materials as $material) {
            $course_name = $material['course_name'];
            $module_name = $material['module_name'];
            if (!isset($grouped_materials[$course_name])) {
                $grouped_materials[$course_name] = [
                    'course_id' => $material['course_id'],
                    'modules' => []
                ];
            }
            if (!isset($grouped_materials[$course_name]['modules'][$module_name])) {
                $grouped_materials[$course_name]['modules'][$module_name] = [];
            }
            $grouped_materials[$course_name]['modules'][$module_name][] = $material;
        }
    } else {
        // Show message for this course
        $course_name = $course['course_name'];
        $grouped_materials[$course_name] = [
            'course_id' => $course_id,
            'modules' => [],
            'access_message' => $show_pay ? 'Payment required to access materials.' : ($show_wait ? 'Access opens on ' . date('M j, Y', strtotime($course['intake_start'] ?? $course['start_date'] ?? '+1 week')) : 'Access not available.')
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',
                        secondary: '#FFD700',
                    }
                }
            }
        }
    </script>
    <style>
        .material-card {
            transition: transform 0.2s;
        }
        .material-card:hover {
            transform: translateY(-2px);
        }
        .content-preview {
            max-height: 200px;
            overflow: hidden;
            position: relative;
        }
        .content-preview::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(transparent, white);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="dashboard.php" class="text-xl font-bold text-secondary">Course Materials</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <a href="dashboard.php" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold mb-4">
        <i class="fas fa-arrow-left"></i> Go Back to Dashboard
    </a>
    <main class="container mx-auto px-4 py-8">
        <?php if (empty($grouped_materials)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-books text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Materials Available</h3>
                <p class="text-gray-500">You don't have access to any course materials yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped_materials as $course_name => $course_data): ?>
    <!-- Course Section -->
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-primary">
                <?= htmlspecialchars($course_name) ?>
            </h2>
            <?php if (!empty($course_data['access_message'])): ?>
                <div class="bg-yellow-100 text-yellow-800 font-semibold py-2 px-4 rounded mt-4">
                    <i class="fas fa-info-circle mr-2"></i><?= htmlspecialchars($course_data['access_message']) ?>
                </div>
            <?php endif; ?>
                    </div>

                    <?php foreach ($course_data['modules'] as $module_name => $module_materials): ?>
                        <!-- Module Section -->
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h3 class="text-xl font-semibold text-primary mb-4 flex items-center">
                                <i class="fas fa-folder-open mr-2"></i>
                                <?= htmlspecialchars($module_name) ?>
                                <span class="ml-2 bg-primary/10 text-primary px-2 py-0.5 rounded-full text-sm">
                                    <?= count($module_materials) ?> items
                                </span>
                            </h3>

                            <div class="space-y-4">
                                <?php foreach ($module_materials as $material): ?>
                                    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <?php
                                                $icon_class = match($material['content_type']) {
                                                    'video' => 'fab fa-youtube text-red-500',
                                                    'document' => 'fas fa-file-alt text-blue-500',
                                                    'link' => 'fas fa-link text-purple-500',
                                                    'text' => 'fas fa-align-left text-gray-500',
                                                    default => 'fas fa-file text-gray-500'
                                                };
                                                ?>
                                                <i class="<?= $icon_class ?> text-xl mr-3"></i>
                                                <div>
                                                    <h5 class="font-medium"><?= htmlspecialchars($material['title']) ?></h5>
                                                    <?php if ($material['content_type'] === 'text'): ?>
                                                        <div class="prose max-w-none mt-2">
                                                            <?= $material['description'] ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <?php if ($material['content_type'] === 'video'): ?>
                                                <a href="<?= htmlspecialchars($material['content_url']) ?>" 
                                                   target="_blank"
                                                   class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                                    <i class="fas fa-play mr-2"></i>
                                                    Watch Video
                                                </a>
                                            <?php elseif ($material['content_type'] === 'document'): ?>
                                                <a href="../uploads/materials/<?= htmlspecialchars($material['content_file']) ?>" 
                                                   target="_blank"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-file-alt text-xl"></i>
                                                </a>
                                            <?php elseif ($material['content_type'] === 'link'): ?>
                                                <a href="<?= htmlspecialchars($material['content_url']) ?>" 
                                                   target="_blank"
                                                   class="inline-flex items-center px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition">
                                                    <i class="fas fa-external-link-alt mr-2"></i>
                                                    Visit Link
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html> 