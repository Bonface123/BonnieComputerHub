<?php
session_start();
require_once '../includes/db_connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$current_section = isset($_GET['section']) ? (int)$_GET['section'] : 1;

// First, verify module exists
$module_check = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$module_check->execute([$module_id]);
$module_data = $module_check->fetch(PDO::FETCH_ASSOC);

if (!$module_data) {
    $_SESSION['error'] = "Module not found.";
    header('Location: dashboard.php');
    exit;
}

// Check enrollment status
$enrollment_check = $pdo->prepare("
    SELECT * FROM module_progress 
    WHERE user_id = ? AND module_id = ?
");
$enrollment_check->execute([$user_id, $module_id]);
$enrollment = $enrollment_check->fetch(PDO::FETCH_ASSOC);

// Auto-enroll in Module 1 if not enrolled
if (!$enrollment && $module_id == 1) {
    $enroll = $pdo->prepare("
        INSERT INTO module_progress (user_id, module_id, status, completion_percentage)
        VALUES (?, ?, 'in_progress', 0)
    ");
    $enroll->execute([$user_id, $module_id]);
    
    // Refresh enrollment data
    $enrollment_check->execute([$user_id, $module_id]);
    $enrollment = $enrollment_check->fetch(PDO::FETCH_ASSOC);
} elseif (!$enrollment) {
    $_SESSION['error'] = "Please enroll in this module first.";
    header('Location: dashboard.php');
    exit;
}

// Fetch all sections for the module
$all_sections_query = $pdo->prepare("
    SELECT * FROM module_content 
    WHERE module_id = ?
    ORDER BY section_order
");
$all_sections_query->execute([$module_id]);
$sections = $all_sections_query->fetchAll(PDO::FETCH_ASSOC);

if (empty($sections)) {
    $_SESSION['error'] = "No content found for this module.";
    header('Location: dashboard.php');
    exit;
}

// Fetch current section
$current_section_data = null;
foreach ($sections as $section) {
    if ($section['section_order'] === $current_section) {
        $current_section_data = $section;
        break;
    }
}

if (!$current_section_data) {
    $_SESSION['error'] = "Section not found.";
    header('Location: dashboard.php');
    exit;
}

// Fetch student's progress for this section
$progress_query = $pdo->prepare("
    SELECT * FROM student_progress 
    WHERE user_id = ? AND section_id = ?
");
$progress_query->execute([$user_id, $current_section_data['id']]);
$progress = $progress_query->fetch(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_quiz'])) {
        // Handle quiz submission
        $score = calculateQuizScore($_POST['answers'], $current_section_data['id']);
        updateProgress($user_id, $current_section_data['id'], $score);
    } elseif (isset($_POST['submit_code'])) {
        // Handle code submission
        $result = evaluateCode($_POST['code'], $current_section_data['id']);
        updateProgress($user_id, $current_section_data['id'], $result['score']);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'complete_section') {
        $section_id = $_POST['section_id'];
        
        // Mark section as completed
        $complete_section = $pdo->prepare("
            INSERT INTO student_progress (user_id, section_id, status, score)
            VALUES (?, ?, 'completed', 100)
            ON DUPLICATE KEY UPDATE status = 'completed', score = 100
        ");
        
        if ($complete_section->execute([$user_id, $section_id])) {
            // Get module_id for this section
            $module_query = $pdo->prepare("SELECT module_id FROM module_content WHERE id = ?");
            $module_query->execute([$section_id]);
            $module_id = $module_query->fetchColumn();
            
            // Update module progress
            updateModuleProgress($pdo, $user_id, $module_id);
            
            // Redirect to remove form resubmission
            header("Location: module.php?id=$module_id&section=$current_section");
            exit;
        }
    }
}

// Add these functions after the session_start() and before the main logic

function calculateQuizScore($answers, $section_id) {
    global $pdo;
    
    $score = 0;
    $total_questions = 0;
    
    // Get correct answers from database
    $query = $pdo->prepare("SELECT id, correct_answer FROM quiz_questions WHERE section_id = ?");
    $query->execute([$section_id]);
    $questions = $query->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($questions as $question) {
        $total_questions++;
        if (isset($answers[$question['id']]) && $answers[$question['id']] === $question['correct_answer']) {
            $score++;
        }
    }
    
    return ($total_questions > 0) ? ($score / $total_questions) * 100 : 0;
}

function evaluateCode($code, $section_id) {
    global $pdo;
    
    // Get exercise details
    $query = $pdo->prepare("SELECT solution_code FROM coding_exercises WHERE section_id = ?");
    $query->execute([$section_id]);
    $exercise = $query->fetch(PDO::FETCH_ASSOC);
    
    // Basic evaluation - check if code contains required elements
    $score = 0;
    $required_elements = ['<!DOCTYPE html>', '<html>', '<head>', '<title>', '<body>'];
    
    foreach ($required_elements as $element) {
        if (stripos($code, $element) !== false) {
            $score += 20; // Each element worth 20 points
        }
    }
    
    return [
        'score' => min($score, 100),
        'feedback' => 'Code evaluation complete'
    ];
}

function updateProgress($user_id, $section_id, $score) {
    global $pdo;
    
    // Get module_id for this section
    $module_query = $pdo->prepare("SELECT module_id FROM module_content WHERE id = ?");
    $module_query->execute([$section_id]);
    $module_id = $module_query->fetchColumn();
    
    // Update or insert progress
    $query = $pdo->prepare("
        INSERT INTO student_progress (user_id, section_id, status, score)
        VALUES (?, ?, 'completed', ?)
        ON DUPLICATE KEY UPDATE status = 'completed', score = ?
    ");
    
    $query->execute([$user_id, $section_id, $score, $score]);
    
    // Update module completion percentage
    updateModuleProgress($pdo, $user_id, $module_id);
    
    return true;
}

function updateModuleProgress($pdo, $user_id, $module_id) {
    // Get total sections in module
    $total_query = $pdo->prepare("
        SELECT COUNT(*) FROM module_content WHERE module_id = ?
    ");
    $total_query->execute([$module_id]);
    $total_sections = $total_query->fetchColumn();

    // Get completed sections
    $completed_query = $pdo->prepare("
        SELECT COUNT(*) FROM student_progress sp
        JOIN module_content mc ON sp.section_id = mc.id
        WHERE sp.user_id = ? AND mc.module_id = ? AND sp.status = 'completed'
    ");
    $completed_query->execute([$user_id, $module_id]);
    $completed_sections = $completed_query->fetchColumn();

    // Calculate percentage
    $completion_percentage = ($total_sections > 0) ? 
        round(($completed_sections / $total_sections) * 100) : 0;

    // Update or insert module progress
    $update_query = $pdo->prepare("
        INSERT INTO module_progress (user_id, module_id, status, completion_percentage)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            status = CASE 
                WHEN ? >= 100 THEN 'completed'
                WHEN ? > 0 THEN 'in_progress'
                ELSE 'pending'
            END,
            completion_percentage = ?
    ");
    
    $update_query->execute([
        $user_id,
        $module_id,
        $completion_percentage >= 100 ? 'completed' : 'in_progress',
        $completion_percentage,
        $completion_percentage,
        $completion_percentage,
        $completion_percentage
    ]);

    return $completion_percentage;
}

?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module <?= $module_id ?> - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Code Mirror for code editor -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
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
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-secondary hover:text-white transition">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">
                        Progress: <?= $module_data['completion_percentage'] ?>%
                    </span>
                    <div class="w-32 bg-gray-200 rounded-full h-2.5">
                        <div class="bg-secondary h-2.5 rounded-full" 
                             style="width: <?= $module_data['completion_percentage'] ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex gap-8">
            <!-- Sidebar Navigation -->
            <div class="w-64 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-primary mb-4">Module Contents</h3>
                    <nav class="space-y-2">
                        <?php
                        $sections_query = $pdo->prepare("
                            SELECT mc.*, sp.status 
                            FROM module_content mc
                            LEFT JOIN student_progress sp ON sp.section_id = mc.id AND sp.user_id = ?
                            WHERE mc.module_id = ?
                            ORDER BY mc.section_order
                        ");
                        $sections_query->execute([$user_id, $module_id]);
                        $sections = $sections_query->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($sections as $nav_section):
                            $isActive = $nav_section['section_order'] === $current_section;
                            $isCompleted = $nav_section['status'] === 'completed';
                            $isLocked = $nav_section['section_order'] > 1 && 
                                      !isset($sections[$nav_section['section_order']-2]) || 
                                      (isset($sections[$nav_section['section_order']-2]) && 
                                       $sections[$nav_section['section_order']-2]['status'] !== 'completed');
                        ?>
                            <a href="?id=<?= $module_id ?>&section=<?= $nav_section['section_order'] ?>"
                               class="flex items-center p-2 rounded-lg <?= $isActive ? 'bg-primary text-white' : 
                                     ($isLocked ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 
                                     'text-gray-600 hover:bg-gray-100') ?>">
                                <?php if ($isCompleted): ?>
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <?php elseif ($isLocked): ?>
                                    <i class="fas fa-lock text-gray-400 mr-2"></i>
                                <?php else: ?>
                                    <i class="far fa-circle text-gray-400 mr-2"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($nav_section['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-grow">
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h1 class="text-3xl font-bold text-primary mb-6">
                        <?= htmlspecialchars($current_section_data['title']) ?>
                    </h1>

                    <?php if ($current_section_data['content_type'] === 'lesson'): ?>
                        <!-- Lesson Content -->
                        <div class="prose max-w-none">
                            <?= $current_section_data['content'] ?>
                        </div>

                    <?php elseif ($current_section_data['content_type'] === 'exercise'): ?>
                        <!-- Coding Exercise -->
                        <?php
                        $exercise_query = $pdo->prepare("SELECT * FROM coding_exercises WHERE section_id = ?");
                        $exercise_query->execute([$current_section_data['id']]);
                        $exercise = $exercise_query->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-xl font-bold mb-4">Instructions</h3>
                                <?= $exercise['instructions'] ?>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-lg font-bold mb-2">Your Code</h3>
                                    <textarea id="code-editor" class="w-full h-64"><?= $exercise['starter_code'] ?></textarea>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold mb-2">Preview</h3>
                                    <iframe id="preview" class="w-full h-64 bg-white border rounded-lg"></iframe>
                                </div>
                            </div>

                            <button onclick="runCode()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary hover:text-primary transition">
                                Run Code
                            </button>
                        </div>

                    <?php elseif ($current_section_data['content_type'] === 'quiz'): ?>
                        <!-- Quiz -->
                        <div class="space-y-8">
                            <!-- Quiz Introduction -->
                            <div class="bg-blue-50 p-6 rounded-xl">
                                <h3 class="text-xl font-bold text-primary mb-2">HTML Elements Quiz</h3>
                                <p class="text-gray-600">Test your knowledge of HTML elements. You need to score at least 70% to pass.</p>
                                <div class="mt-4 flex items-center text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span>Take your time and choose the best answer for each question.</span>
                                </div>
                            </div>

                            <form method="POST" class="space-y-8" id="quizForm">
                                <?php
                                $questions_query = $pdo->prepare("SELECT * FROM quiz_questions WHERE section_id = ?");
                                $questions_query->execute([$current_section_data['id']]);
                                $questions = $questions_query->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                                        <div class="mb-6">
                                            <h3 class="text-xl font-bold text-primary mb-4">
                                                Question <?= $index + 1 ?>: <?= htmlspecialchars($question['question']) ?>
                                            </h3>
                                            
                                            <?php $options = json_decode($question['options'], true); ?>
                                            <div class="space-y-3">
                                                <?php foreach ($options as $option): ?>
                                                    <label class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors">
                                                        <input type="radio" 
                                                               name="answers[<?= $question['id'] ?>]" 
                                                               value="<?= htmlspecialchars($option) ?>" 
                                                               class="form-radio h-5 w-5 text-primary"
                                                               required>
                                                        <span class="ml-3 text-gray-700"><?= htmlspecialchars($option) ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Submit Button -->
                                <div class="flex justify-between items-center">
                                    <button type="submit" 
                                            name="submit_quiz"
                                            class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300 flex items-center">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Submit Quiz
                                    </button>
                                    <span class="text-gray-500">
                                        <i class="fas fa-clock mr-2"></i>
                                        Take your time
                                    </span>
                                </div>
                            </form>

                            <!-- Quiz Results (show if quiz was submitted) -->
                            <?php if (isset($_POST['submit_quiz'])): ?>
                                <?php
                                $score = calculateQuizScore($_POST['answers'], $current_section_data['id']);
                                $passed = $score >= 70;
                                ?>
                                <div class="mt-8">
                                    <div class="<?= $passed ? 'bg-green-50' : 'bg-red-50' ?> p-6 rounded-xl">
                                        <div class="flex items-center mb-4">
                                            <i class="<?= $passed ? 'fas fa-check-circle text-green-500' : 'fas fa-times-circle text-red-500' ?> text-2xl mr-3"></i>
                                            <h3 class="text-xl font-bold <?= $passed ? 'text-green-700' : 'text-red-700' ?>">
                                                <?= $passed ? 'Congratulations!' : 'Keep Practicing!' ?>
                                            </h3>
                                        </div>
                                        <p class="text-gray-600 mb-4">Your score: <?= $score ?>%</p>
                                        <?php if ($passed): ?>
                                            <p class="text-green-600">You've passed this quiz! You can now proceed to the next section.</p>
                                            <a href="?id=<?= $module_id ?>&section=<?= $current_section + 1 ?>" 
                                               class="inline-block mt-4 px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                                Continue to Next Section
                                            </a>
                                        <?php else: ?>
                                            <p class="text-red-600">You need 70% to pass. Review the material and try again.</p>
                                            <button onclick="location.reload()" 
                                                    class="mt-4 px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                                Try Again
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Show Correct Answers -->
                                    <div class="mt-8 bg-white p-6 rounded-xl shadow-md">
                                        <h3 class="text-xl font-bold text-primary mb-6">Review Your Answers</h3>
                                        <?php foreach ($questions as $question): ?>
                                            <div class="mb-6 pb-6 border-b last:border-0">
                                                <p class="font-semibold mb-2"><?= htmlspecialchars($question['question']) ?></p>
                                                <p class="text-green-600 mb-2">
                                                    <i class="fas fa-check mr-2"></i>
                                                    Correct answer: <?= htmlspecialchars($question['correct_answer']) ?>
                                                </p>
                                                <p class="text-gray-600 text-sm">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <?= htmlspecialchars($question['explanation']) ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Section Completion and Navigation -->
                    <div class="mt-8 pt-8 border-t">
                            <div class="flex justify-between items-center">
                            <!-- Previous Button -->
                                <?php if ($current_section > 1): ?>
                                    <a href="?id=<?= $module_id ?>&section=<?= $current_section - 1 ?>"
                                   class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition flex items-center">
                                    <i class="fas fa-arrow-left mr-2"></i> Previous Section
                                    </a>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>

                            <!-- Complete Section Button (if not completed) -->
                                <?php
                                $completion_check = $pdo->prepare("
                                    SELECT status FROM student_progress 
                                    WHERE user_id = ? AND section_id = ?
                                ");
                                $completion_check->execute([$user_id, $current_section_data['id']]);
                                $is_completed = $completion_check->fetch(PDO::FETCH_ASSOC);
                                ?>

                                    <?php if (!$is_completed): ?>
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="action" value="complete_section">
                                            <input type="hidden" name="section_id" value="<?= $current_section_data['id'] ?>">
                                            <button type="submit" 
                                                    class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition flex items-center">
                                                <i class="fas fa-check mr-2"></i> Mark as Complete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i> Completed
                                        </span>
                                    <?php endif; ?>

                            <!-- Next Button -->
                            <?php
                            // Check if next section should be unlocked
                            $prev_section = $current_section - 1;
                            $prev_completion_check = $pdo->prepare("
                                SELECT status FROM student_progress 
                                WHERE user_id = ? AND section_id = (
                                    SELECT id FROM module_content 
                                    WHERE module_id = ? AND section_order = ?
                                )
                            ");
                            $prev_completion_check->execute([$user_id, $module_id, $prev_section]);
                            $prev_is_completed = $prev_completion_check->fetch(PDO::FETCH_ASSOC);

                            $can_proceed = $current_section === 1 || $prev_is_completed;
                            ?>

                                    <?php if ($current_section < count($sections)): ?>
                                        <?php if ($is_completed): ?>
                                            <a href="?id=<?= $module_id ?>&section=<?= $current_section + 1 ?>"
                                       class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary hover:text-primary transition flex items-center">
                                        Next Section <i class="fas fa-arrow-right ml-2"></i>
                                            </a>
                                        <?php else: ?>
                                            <button disabled 
                                                    class="bg-gray-300 text-gray-500 px-6 py-2 rounded-lg cursor-not-allowed">
                                                Complete this section first
                                            </button>
                                        <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            </div>

                        <!-- Module Completion (show only on last section) -->
                        <?php if ($current_section === count($sections)): ?>
                            <?php
                            // Check if all sections are completed
                            $completion_check = $pdo->prepare("
                                SELECT COUNT(*) as total_completed
                                FROM student_progress
                                WHERE user_id = ? AND section_id IN (
                                    SELECT id FROM module_content WHERE module_id = ?
                                ) AND status = 'completed'
                            ");
                            $completion_check->execute([$user_id, $module_id]);
                            $completion_data = $completion_check->fetch(PDO::FETCH_ASSOC);
                            $all_completed = $completion_data['total_completed'] === count($sections);
                            ?>

                            <?php if ($all_completed): ?>
                                <div class="mt-8 text-center">
                                    <div class="bg-green-50 p-6 rounded-xl mb-6">
                                        <h3 class="text-2xl font-bold text-green-800 mb-2">
                                            🎉 Congratulations!
                                        </h3>
                                        <p class="text-green-600">
                                            You've completed all sections of this module.
                                        </p>
                                    </div>
                                    <a href="dashboard.php" 
                                       class="inline-block bg-primary text-white px-8 py-3 rounded-lg hover:bg-secondary hover:text-primary transition">
                                        <i class="fas fa-graduation-cap mr-2"></i>
                                        Return to Dashboard
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize CodeMirror
        var editor = CodeMirror.fromTextArea(document.getElementById("code-editor"), {
            mode: "xml",
            theme: "monokai",
            lineNumbers: true,
            autoCloseTags: true
        });

        // Function to run code
        function runCode() {
            const code = editor.getValue();
            const preview = document.getElementById('preview').contentWindow.document;
            preview.open();
            preview.write(code);
            preview.close();
        }
    </script>
</body>
</html> 