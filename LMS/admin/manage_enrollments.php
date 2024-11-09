<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    $insert_sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)"; // Updated field name to 'user_id'
    $stmt = $pdo->prepare($insert_sql);
    $stmt->execute([$student_id, $course_id]);

    // Set flash message and redirect
    $_SESSION['flash_message'] = "Student enrolled successfully.";
    header("Location: manage_enrollments.php");
    exit;
}

// Handle unenrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unenroll_student'])) {
    $user_id = $_POST['user_id'];
    $course_id = $_POST['course_id'];

    $delete_sql = "DELETE FROM enrollments WHERE user_id = ? AND course_id = ?"; // Update for unenrollment
    $stmt = $pdo->prepare($delete_sql);
    $stmt->execute([$user_id, $course_id]);

    // Set flash message and redirect
    $_SESSION['flash_message'] = "Enrollment removed successfully.";
    header("Location: manage_enrollments.php");
    exit;
}

// Fetch all students
$students = $pdo->query("SELECT * FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all courses
$courses = $pdo->query("SELECT id, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC); // Updated query

// Fetch all enrollments
$enrollments = $pdo->query("SELECT e.user_id, e.course_id, u.name AS student_name, c.course_name 
                             FROM enrollments e 
                             JOIN users u ON e.user_id = u.id 
                             JOIN courses c ON e.course_id = c.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Manage Enrollments</title>
    <style>
        /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
}

h1, h2 {
    color: #333;
    text-align: center;
}

main {
    width: 90%;
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

/* Breadcrumb Navigation */
.breadcrumb {
    margin-bottom: 20px;
    font-size: 0.9em;
}

.breadcrumb a {
    color: #333;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

/* Flash Messages */
.flash-message {
    background-color: #ffa500;
    color: white;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

/* Form Styles */
form {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 30px;
}

form label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 2px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

form select:focus {
    border-color: #ffa500;
    outline: none;
}

form button {
    background-color: #ffa500;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

form button:hover {
    background-color: #ff8c00;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    border: 1px solid #ddd;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #333;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table button {
    background-color: #ffa500;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

table button:hover {
    background-color: #ff8c00;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="manage_enrollments.php">Manage Enrollments</a>
        </div>

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <h1>Manage Enrollments</h1>

        <h2>Enroll Student in Course</h2>
        <form action="" method="POST">
            <label for="student_id">Select Student:</label>
            <select id="student_id" name="student_id" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['id']) ?>"><?= htmlspecialchars($student['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="course_id">Select Course:</label>
            <select id="course_id" name="course_id" required>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="enroll_student">Enroll</button>
        </form>

        <h2>Current Enrollments</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Course Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td><?= htmlspecialchars($enrollment['user_id']) ?></td>
                        <td><?= htmlspecialchars($enrollment['student_name']) ?></td>
                        <td><?= htmlspecialchars($enrollment['course_name']) ?></td>
                        <td>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($enrollment['user_id']) ?>">
                                <input type="hidden" name="course_id" value="<?= htmlspecialchars($enrollment['course_id']) ?>">
                                <button type="submit" name="unenroll_student" onclick="return confirm('Are you sure you want to remove this enrollment?');">Unenroll</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
