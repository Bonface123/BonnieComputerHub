<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Fetch all courses
$courses = $pdo->prepare("SELECT id, course_name FROM courses");
$courses->execute();
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// If a course is selected, fetch students and their assignment progress
$students = [];
$assignments = [];

if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    // Fetch students enrolled in the selected course
    $students = $pdo->prepare("SELECT u.id, u.name 
                               FROM enrollments e 
                               JOIN users u ON e.user_id = u.id 
                               WHERE e.course_id = ?");
    $students->execute([$course_id]);
    $students = $students->fetchAll(PDO::FETCH_ASSOC);

    // Fetch assignments for the selected course
    $assignments = $pdo->prepare("SELECT id, title FROM assignments WHERE course_id = ?");
    $assignments->execute([$course_id]);
    $assignments = $assignments->fetchAll(PDO::FETCH_ASSOC);

    // Fetch grades for each student and assignment
    foreach ($students as &$student) {
        $student_id = $student['id'];
        foreach ($assignments as $assignment) {
            $assignment_id = $assignment['id'];

            $grade_stmt = $pdo->prepare("SELECT grade FROM grades 
                                         WHERE user_id = ? AND assignment_id = ?");
            $grade_stmt->execute([$student_id, $assignment_id]);
            $grade = $grade_stmt->fetchColumn();

            $student['grades'][$assignment_id] = $grade ?? 'Not Graded';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>View Student Progress</title>
    <style>
        
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h1>Student Progress</h1>

        <!-- Course Selection -->
        <form method="GET" action="">
            <label for="course_id">Select Course:</label>
            <select name="course_id" id="course_id" required>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['id']) ?>" <?= isset($course_id) && $course_id == $course['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View Progress</button>
        </form>

        <?php if (isset($course_id)): ?>
            <h2>Student Progress for <?= htmlspecialchars($courses[array_search($course_id, array_column($courses, 'id'))]['course_name']) ?></h2>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <?php foreach ($assignments as $assignment): ?>
                            <th><?= htmlspecialchars($assignment['title']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <?php foreach ($assignments as $assignment): ?>
                                <td><?= htmlspecialchars($student['grades'][$assignment['id']]) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
