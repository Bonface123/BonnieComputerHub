<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch the course to be edited
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];

    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo "Course not found.";
        exit;
    }
} else {
    echo "Invalid course ID.";
    exit;
}

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $update_sql = "UPDATE courses SET course_name = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $description, $courseId]);
    echo "Course updated successfully.";
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
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="edit_course.php">Edit Course</a>
        </div>

        <h1>Edit Course</h1>

        <form action="" method="POST">
            <label for="name">Course Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($course['course_name']) ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($course['description']) ?></textarea>

            <button type="submit" name="update_course">Update Course</button>
        </form>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
