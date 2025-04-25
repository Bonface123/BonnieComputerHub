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
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'self-paced';
    $next_intake_date = ($mode === 'instructor-led' && !empty($_POST['next_intake_date'])) ? $_POST['next_intake_date'] : null;

    $sql = "INSERT INTO courses (name, description, instructor_id, mode, next_intake_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$course_name, $course_description, $_SESSION['user_id'], $mode, $next_intake_date])) {
        echo "<p>Course created successfully!</p>";
    } else {
        echo "<p>Failed to create course. Please try again.</p>";
    }
}
?>

<h2>Create Course</h2>
<form action="" method="post">
    <label for="mode">Course Mode:</label>
    <select name="mode" id="mode" onchange="toggleIntakeField()" required>
        <option value="instructor-led">Instructor-led</option>
        <option value="self-paced" selected>Self-paced</option>
    </select>
    <div id="intake-date-field" style="display:none;">
        <label for="next_intake_date">Next Intake Date:</label>
        <input type="date" id="next_intake_date" name="next_intake_date" disabled>
        <span style="font-size:12px;color:#888;">Leave blank for self-paced courses.</span>
    </div>
    <script>
    function toggleIntakeField() {
        var mode = document.getElementById('mode').value;
        var intakeField = document.getElementById('intake-date-field');
        var intakeInput = document.getElementById('next_intake_date');
        if (mode === 'instructor-led') {
            intakeField.style.display = 'block';
            intakeInput.disabled = false;
            intakeInput.required = true;
        } else {
            intakeField.style.display = 'none';
            intakeInput.disabled = true;
            intakeInput.required = false;
            intakeInput.value = '';
        }
    }
    document.addEventListener('DOMContentLoaded', toggleIntakeField);
    </script>
    <label for="course_name">Course Name:</label>
    <input type="text" id="course_name" name="course_name" required>
    
    <label for="course_description">Course Description:</label>
    <textarea id="course_description" name="course_description" required></textarea>
    
    <button type="submit" name="create_course">Create Course</button>
</form>

<?php include '../includes/footer.php'; ?>
