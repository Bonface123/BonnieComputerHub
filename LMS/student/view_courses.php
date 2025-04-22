<!-- student/view_courses.php -->
<?php 
session_start();
include '../includes/db_connect.php'; 
include '../includes/header.php'; 

// Check if user is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../pages/login.php");
    exit();
}

// Fetch only active and open courses
$sql = "SELECT * FROM courses WHERE status = 'active' AND enrollment_status = 'open' ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll();
?>

<h2>Available Courses</h2>
<ul>
    <?php foreach ($courses as $course): ?>
        <li>
            <?php echo htmlspecialchars($course['name']); ?> 
            <a href="../student/enroll_course.php?id=<?php echo $course['id']; ?>">Enroll</a>
        </li>
    <?php endforeach; ?>
</ul>

<?php include '../includes/footer.php'; ?>
