<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $comments = $_POST['comments'];

    // Insert feedback into the database
    $insert_sql = "INSERT INTO feedback (student_id, course_id, comments) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    $stmt->execute([$student_id, $course_id, $comments]);
    echo "Feedback submitted successfully.";
}

// Fetch all students
$students = $pdo->query("SELECT * FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all courses
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all feedback
$feedbacks = $pdo->query("SELECT f.comments, u.name AS student_name, c.course_name 
                           FROM feedback f 
                           JOIN users u ON f.student_id = u.id 
                           JOIN courses c ON f.course_id = c.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Feedback</title>
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

form select,
form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 2px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

form select:focus,
form textarea:focus {
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

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
         <!-- Breadcrumb Navigation -->
         <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="feedback.php">Feedback</a>
        </div>

        <h1>Feedback</h1>

        <h2>Submit Feedback</h2>
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

            <label for="comments">Comments:</label>
            <textarea id="comments" name="comments" required></textarea>

            <button type="submit" name="submit_feedback">Submit Feedback</button>
        </form>

        <h2>Feedback Received</h2>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course Name</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback): ?>
                    <tr>
                        <td><?= htmlspecialchars($feedback['student_name']) ?></td>
                        <td><?= htmlspecialchars($feedback['course_name']) ?></td>
                        <td><?= htmlspecialchars($feedback['comments']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
