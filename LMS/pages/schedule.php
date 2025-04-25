<?php
// schedule.php - Course Schedule Overview Page
session_start();
require_once '../includes/db_connect.php';

// Fetch all published courses and their schedules
$sql = "SELECT id, course_name, schedule FROM courses ORDER BY course_name ASC";
$stmt = $pdo->query($sql);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderScheduleTable($schedule) {
    $weeks = json_decode($schedule ?? '[]', true);
    if (!$weeks || !is_array($weeks)) return '<span class="text-gray-500">No schedule available.</span>';
    $out = '<table class="min-w-full text-left text-sm border mt-2 mb-4">';
    $out .= '<thead><tr><th class="px-3 py-1 border-b bg-blue-50">Week</th><th class="px-3 py-1 border-b bg-blue-50">Topic</th></tr></thead><tbody>';
    foreach ($weeks as $week => $topic) {
        if ($topic) {
            $out .= "<tr><td class='px-3 py-1 border-b'>".htmlspecialchars($week)."</td><td class='px-3 py-1 border-b'>".htmlspecialchars($topic)."</td></tr>";
        }
    }
    $out .= '</tbody></table>';
    return $out;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Schedules - Bonnie Computer Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/design-system.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include_once('../includes/header.php'); ?>
    <main class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-extrabold text-primary mb-8 text-center">Course Schedules</h1>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($courses as $course): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-blue-700 mb-2"><?= htmlspecialchars($course['course_name']) ?></h2>
                    <?= renderScheduleTable($course['schedule']) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($courses)): ?>
            <div class="text-gray-500 text-center mt-12">No courses found.</div>
        <?php endif; ?>
        <div class="mt-10 text-center">
            <a href="courses.php" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">Back to Courses</a>
        </div>
    </main>
    <?php include_once('../includes/footer.php'); ?>
</body>
</html>
