<!-- instructor/track_progress.php -->
<?php 
session_start();
include '../includes/db_connect.php'; 
include '../includes/header.php'; 

// Check if user is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../pages/login.php");
    exit();
}

// Fetch courses taught by the instructor
$sql = "SELECT id, name FROM courses WHERE instructor_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();
?>

<h2>Track Student Progress</h2>
<form action="" method="get">
    <label for="course_id">Select Course:</label>
    <select id="course_id" name="course_id" required>
        <option value="">Select a course</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">View Progress</button>
</form>

<?php 
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    
    // Fetch students enrolled in the selected course
    $sql = "SELECT users.username, assignments.file_path FROM users 
            JOIN course_enrollments ON users.id = course_enrollments.user_id 
            JOIN assignments ON course_enrollments.course_id = assignments.course_id 
            WHERE course_enrollments.course_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id]);
    $students = $stmt->fetchAll();

    echo "<h3>Student Progress for Course: " . htmlspecialchars($course_id) . "</h3>";
    echo "<ul>";
    foreach ($students as $student) {
        echo "<li>" . htmlspecialchars($student['username']) . " - Assignment: " . htmlspecialchars($student['file_path']) . "</li>";
    }
    echo "</ul>";
}
?>

<?php include '../includes/footer.php'; ?>
