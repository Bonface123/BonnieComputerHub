<!-- instructor/create_course.php -->
<?php 
session_start();
include '../includes/db_connect.php'; 
include '../includes/header.php'; 

// Check if user is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../pages/login.php");
    exit();
}

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_course'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];

    $sql = "INSERT INTO courses (name, description, instructor_id) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$course_name, $course_description, $_SESSION['user_id']])) {
        echo "<p>Course created successfully!</p>";
    } else {
        echo "<p>Failed to create course. Please try again.</p>";
    }
}
?>

<h2>Create Course</h2>
<form action="" method="post">
    <label for="course_name">Course Name:</label>
    <input type="text" id="course_name" name="course_name" required>
    
    <label for="course_description">Course Description:</label>
    <textarea id="course_description" name="course_description" required></textarea>
    
    <button type="submit" name="create_course">Create Course</button>
</form>

<?php include '../includes/footer.php'; ?>
