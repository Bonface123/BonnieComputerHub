<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch assignment details with permission check
$assignment_query = $pdo->prepare("
    SELECT 
        a.*,
        c.course_name,
        m.module_name
    FROM assignments a
    JOIN course_modules m ON a.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE a.id = ? AND a.instructor_id = ?
");
$assignment_query->execute([$assignment_id, $instructor_id]);
$assignment = $assignment_query->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    $_SESSION['error_msg'] = "Assignment not found or you don't have permission to view it.";
    header('Location: manage_assignments.php');
    exit;
}

// Fetch submissions for this assignment
$submissions_query = $pdo->prepare("
    SELECT 
        s.*,
        u.name as student_name,
        u.email as student_email,
        a.title as assignment_title,
        a.marks as total_marks
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    WHERE s.assignment_id = ?
    AND a.instructor_id = ?
    ORDER BY s.submitted_at DESC
");
$submissions_query->execute([$assignment_id, $instructor_id]);
$submissions = $submissions_query->fetchAll(PDO::FETCH_ASSOC);

// Handle grading submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    try {
        $stmt = $pdo->prepare("
            UPDATE submissions 
            SET grade = ?, feedback = ?, status = 'graded', graded_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$grade, $feedback, $submission_id]);
        $_SESSION['success_msg'] = "Submission graded successfully!";
        header("Location: view_submissions.php?id=" . $assignment_id);
        exit;
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error grading submission: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions - BCH Learning</title>
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
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="instructor_dashboard.php" class="text-xl font-bold text-secondary">View Submissions</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="manage_assignments.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Assignments
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Assignment Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h1 class="text-2xl font-bold text-primary mb-2">
                <?= htmlspecialchars($assignment['title']) ?>
            </h1>
            <div class="flex items-center text-sm text-gray-600 mb-4">
                <span class="mr-4">
                    <i class="fas fa-book mr-2"></i>
                    <?= htmlspecialchars($assignment['course_name']) ?>
                </span>
                <span>
                    <i class="fas fa-layer-group mr-2"></i>
                    <?= htmlspecialchars($assignment['module_name']) ?>
                </span>
            </div>
            <div class="flex items-center text-sm text-gray-500 space-x-4">
                <span>
                    <i class="fas fa-calendar mr-2"></i>
                    Due: <?= date('M j, Y', strtotime($assignment['due_date'])) ?>
                </span>
                <span>
                    <i class="fas fa-star mr-2"></i>
                    <?= $assignment['marks'] ?> marks
                </span>
                <span>
                    <i class="fas fa-users mr-2"></i>
                    <?= count($submissions) ?> submissions
                </span>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <?= $_SESSION['success_msg'] ?>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <?= $_SESSION['error_msg'] ?>
                <?php unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Submissions List -->
        <?php if (empty($submissions)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-inbox text-6xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Submissions Yet</h3>
                <p class="text-gray-500 mb-4">No students have submitted this assignment yet.</p>
                <div class="text-sm text-gray-500">
                    <p class="mb-2">Assignment Details:</p>
                    <ul class="space-y-1">
                        <li>
                            <i class="fas fa-calendar mr-2"></i>
                            Due: <?= date('M j, Y g:i A', strtotime($assignment['due_date'])) ?>
                        </li>
                        <li>
                            <i class="fas fa-star mr-2"></i>
                            Total Marks: <?= $assignment['marks'] ?>
                        </li>
                        <li>
                            <i class="fas fa-book mr-2"></i>
                            Module: <?= htmlspecialchars($assignment['module_name']) ?>
                        </li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($submissions as $submission): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-primary">
                                    <?= htmlspecialchars($submission['student_name']) ?>
                                </h3>
                                <p class="text-sm text-gray-600">
                                    <?= htmlspecialchars($submission['student_email']) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="<?= $submission['status'] === 'graded' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?> text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?= ucfirst($submission['status']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Submission Content -->
                        <?php if ($submission['submission_text']): ?>
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <div class="prose max-w-none">
                                    <?= $submission['submission_text'] ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Submission File -->
                        <?php if ($submission['submission_file']): ?>
                            <div class="flex items-center mb-4">
                                <i class="fas fa-paperclip text-gray-500 mr-2"></i>
                                <a href="../uploads/submissions/<?= htmlspecialchars($submission['submission_file']) ?>" 
                                   target="_blank"
                                   class="text-primary hover:text-opacity-80">
                                    View Attachment
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>
                                <i class="fas fa-clock mr-2"></i>
                                Submitted: <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?>
                            </span>
                        </div>

                        <!-- Grading Form -->
                        <div class="mt-6 pt-6 border-t">
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                <input type="hidden" name="grade_submission" value="1">
                                
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Grade (out of <?= $assignment['marks'] ?>)</label>
                                        <input type="number" name="grade" required min="0" max="<?= $assignment['marks'] ?>" 
                                               value="<?= $submission['grade'] ?? '' ?>"
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Feedback</label>
                                        <textarea name="feedback" rows="3" 
                                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" 
                                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition">
                                        <?= $submission['status'] === 'graded' ? 'Update Grade' : 'Submit Grade' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
