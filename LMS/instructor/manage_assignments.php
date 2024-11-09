<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Fetch all courses created by admins (to be viewed by the instructor)
$courses = $pdo->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Handle assignment upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_assignment'])) {
    $course_id = $_POST['course_id'];
    $assignment_title = $_POST['assignment_title'];
    $assignment_description = $_POST['assignment_description'];
    $due_date = $_POST['due_date']; // Using datetime-local for date and time
    $marks = $_POST['marks'];
    $instructions = $_POST['instructions'];

    // Insert assignment details into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO course_assignments (course_id, title, description, due_date, marks, instructions) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$course_id, $assignment_title, $assignment_description, $due_date, $marks, $instructions]);
        echo "Assignment uploaded successfully.";
    } catch (PDOException $e) {
        echo "Error uploading assignment: " . $e->getMessage();
    }
}

// Handle assignment deletion
if (isset($_GET['delete_assignment'])) {
    $assignment_id = $_GET['delete_assignment'];

    try {
        $stmt = $pdo->prepare("DELETE FROM course_assignments WHERE id = ?");
        $stmt->execute([$assignment_id]);
        echo "Assignment deleted successfully.";
    } catch (PDOException $e) {
        echo "Error deleting assignment: " . $e->getMessage();
    }
}

// Handle assignment update (editing)
if (isset($_POST['edit_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $new_title = $_POST['new_title'];
    $new_description = $_POST['new_description'];
    $new_due_date = $_POST['new_due_date'];
    $new_marks = $_POST['new_marks'];
    $new_instructions = $_POST['new_instructions'];

    try {
        $stmt = $pdo->prepare("UPDATE course_assignments SET title = ?, description = ?, due_date = ?, marks = ?, instructions = ? 
                               WHERE id = ?");
        $stmt->execute([$new_title, $new_description, $new_due_date, $new_marks, $new_instructions, $assignment_id]);
        echo "Assignment updated successfully.";
    } catch (PDOException $e) {
        echo "Error updating assignment: " . $e->getMessage();
    }
}

// Handle grading of student submissions
if (isset($_POST['grade_submission'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];

    try {
        $stmt = $pdo->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
        $stmt->execute([$grade, $submission_id]);
        echo "Grade updated successfully.";
    } catch (PDOException $e) {
        echo "Error updating grade: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Manage Assignments</title>
       
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="breadcrumb">
            <a href="../index.php">Home</a> &gt;
            <a href="instructor_dashboard.php">Instructor Dashboard</a> &gt;
            <span>Manage Assignments</span>
        </div>

        <h1>Manage Assignments</h1>

        <!-- Assignment Upload Section -->
        <section>
            <h2>Upload New Assignment</h2>
            <form action="" method="POST">
                <label for="course_id">Select Course:</label>
                <select name="course_id" id="course_id" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="assignment_title">Assignment Title:</label>
                <input type="text" name="assignment_title" id="assignment_title" required>

                <label for="assignment_description">Assignment Description:</label>
                <textarea name="assignment_description" id="assignment_description" required></textarea>

                <label for="due_date">Due Date and Time:</label>
                <input type="datetime-local" name="due_date" id="due_date" required>

                <label for="marks">Marks:</label>
                <input type="number" name="marks" id="marks" required>

                <label for="instructions">Instructions:</label>
                <textarea name="instructions" id="instructions" required></textarea>

                <button type="submit" name="upload_assignment">Upload Assignment</button>
            </form>
        </section>

        <!-- Assignment List Section -->
        <section>
            <h2>Assignments List</h2>
            <?php foreach ($courses as $course): ?>
                <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                <?php
                $stmt = $pdo->prepare("SELECT id, title, description, due_date, marks, instructions FROM course_assignments WHERE course_id = ?");
                $stmt->execute([$course['id']]);
                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if ($assignments): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Due Date</th>
                                <th>Marks</th>
                                <th>Instructions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($assignment['title']) ?></td>
                                    <td><?= htmlspecialchars($assignment['description']) ?></td>
                                    <td><?= htmlspecialchars($assignment['due_date']) ?></td>
                                    <td><?= htmlspecialchars($assignment['marks']) ?></td>
                                    <td><?= htmlspecialchars($assignment['instructions']) ?></td>
                                    <td>
                                        <button onclick="editAssignment(<?= $assignment['id'] ?>, '<?= htmlspecialchars($assignment['title']) ?>', '<?= htmlspecialchars($assignment['description']) ?>', '<?= htmlspecialchars($assignment['due_date']) ?>', '<?= htmlspecialchars($assignment['marks']) ?>', '<?= htmlspecialchars($assignment['instructions']) ?>')">Edit</button>
                                        <a href="?delete_assignment=<?= $assignment['id'] ?>" onclick="return confirm('Are you sure you want to delete this assignment?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No assignments uploaded for this course yet.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <!-- Grading Section -->
        <section>
            <h2>Grade Submissions</h2>
            <?php foreach ($courses as $course): ?>
                <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                <?php
                $stmt = $pdo->prepare("SELECT s.id, s.student_id, s.assignment_id, s.submission_date, s.submission_file, sa.title
                                       FROM submissions s
                                       JOIN course_assignments sa ON s.assignment_id = sa.id
                                       WHERE sa.course_id = ?");
                $stmt->execute([$course['id']]);
                $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if ($submissions): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Assignment Title</th>
                                <th>Submission Date</th>
                                <th>File</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?= htmlspecialchars($submission['student_id']) ?></td>
                                    <td><?= htmlspecialchars($submission['title']) ?></td>
                                    <td><?= htmlspecialchars($submission['submission_date']) ?></td>
                                    <td><a href="../uploads/<?= htmlspecialchars($submission['submission_file']) ?>" target="_blank">View</a></td>
                                    <td>
                                        <form action="" method="POST">
                                            <input type="number" name="grade" required>
                                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                            <button type="submit" name="grade_submission">Grade</button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="view_report.php?submission_id=<?= $submission['id'] ?>">View Report</a> | 
                                        <a href="send_notification.php?submission_id=<?= $submission['id'] ?>">Send Notification</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No submissions for this course yet.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
