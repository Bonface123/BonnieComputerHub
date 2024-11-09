<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Check if course ID is provided and fetch the course details
if (isset($_GET['id'])) {
    $course_id = $_GET['id'];
    
    $sql = "SELECT * FROM courses WHERE id = ? AND created_by = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id, $instructor_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // If course not found or doesn't belong to the instructor, redirect
    if (!$course) {
        $_SESSION['flash_message'] = "Course not found or access denied.";
        header("Location: manage_courses.php");
        exit;
    }
} else {
    header("Location: manage_courses.php");
    exit;
}

// Handle course update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];

    $update_sql = "UPDATE courses SET course_name = ?, description = ? WHERE id = ? AND created_by = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$course_name, $description, $course_id, $instructor_id]);

    $_SESSION['flash_message'] = "Course updated successfully.";
    header("Location: manage_courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Edit Course</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="instructor_dashboard.php">Instructor Dashboard</a> &gt; 
            <a href="manage_courses.php">Manage Courses</a> &gt; 
            <span>Edit Course</span>
        </div>

        <h1>Edit Course</h1>

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Edit Course Form -->
        <form action="" method="POST">
            <label for="course_name">Course Name:</label>
            <input type="text" id="course_name" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($course['description']) ?></textarea>

            <button type="submit" name="update_course">Update Course</button>
        </form>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
