<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has a student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Get the student's ID
$student_id = $_SESSION['user_id'];

// Fetch the student's name from the database
$name_query = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$name_query->execute([$student_id]);
$user_data = $name_query->fetch(PDO::FETCH_ASSOC);
$user_name = $user_data ? $user_data['name'] : 'Student';

// Generate a greeting based on the time of day
date_default_timezone_set('Africa/Nairobi'); // Set to your timezone, e.g., 'America/New_York'
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour < 18) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

// Fetch enrolled courses for the student
$courses = $pdo->prepare("
    SELECT c.id, c.course_name, c.description 
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
");
$courses->execute([$student_id]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// Fetch learning materials and assignments for each course
foreach ($courses as &$course) {
    $course_id = $course['id'];

    // Fetch learning materials with all necessary columns
    $materials = $pdo->prepare("
        SELECT material_name, material_description, file_path, uploaded_at 
        FROM course_materials 
        WHERE course_id = ?
    ");
    $materials->execute([$course_id]);
    $course['materials'] = $materials->fetchAll(PDO::FETCH_ASSOC);

    // Fetch assignments
    $assignments = $pdo->prepare("SELECT id, title, due_date FROM course_assignments WHERE course_id = ?");
    $assignments->execute([$course_id]);
    $course['assignments'] = $assignments->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Student Dashboard</title>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    color: #333;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

main {
    padding: 20px;
    max-width: 1200px;
    margin: auto;
}

/* Header and Welcome Message */
.welcome-message {
    font-size: 1.5em;
    color: #004085; /* Deep blue for greeting */
    background-color: #e9ecef; /* Light background for contrast */
    padding: 15px;
    border-left: 5px solid #ffc107; /* Golden accent */
    margin-bottom: 20px;
}

/* Course Section */
.courses {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* Course Card */
.course-card {
    background-color: #ffffff; /* White background for clarity */
    border: 1px solid #004085; /* Blue border for brand alignment */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
    padding: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.course-card:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.course-card h3 {
    font-size: 1.2em;
    color: #004085; /* Blue for course titles */
    margin: 0 0 10px;
}

.course-card p {
    font-size: 0.9em;
    color: #333333;
}

/* Materials and Assignments */
.materials, .assignments {
    margin-top: 15px;
}

.materials h4, .assignments h4 {
    font-size: 1.1em;
    color: #ffc107; /* Golden for section headings */
    border-bottom: 2px solid #004085; /* Blue underline for emphasis */
    padding-bottom: 5px;
    margin-bottom: 10px;
}

.materials ul, .assignments ul {
    list-style: none;
    padding-left: 0;
}

.materials ul li, .assignments ul li {
    margin-bottom: 8px;
    padding: 8px 10px;
    border-radius: 5px;
    transition: background-color 0.2s;
}

.materials ul li a, .assignments ul li a {
    text-decoration: none;
    color: #004085;
    font-weight: bold;
}

.materials ul li a:hover, .assignments ul li a:hover {
    color: #ffc107; /* Golden hover effect for links */
}

.assignments ul li {
    background-color: #e9ecef; /* Light background for assignments */
}

/* Submit Button for Assignments */
.assignments ul li a {
    color: #ffffff;
    background-color: #004085;
    padding: 5px 10px;
    border-radius: 4px;
    margin-left: 10px;
    text-decoration: none;
    transition: background-color 0.2s;
}

.assignments ul li a:hover {
    background-color: #ffc107; /* Golden highlight on hover */
}

/* Responsive Design */
@media (max-width: 768px) {
    .courses {
        grid-template-columns: 1fr;
    }

    .welcome-message {
        font-size: 1.2em;
        padding: 10px;
    }
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="welcome-message">
            <?= $greeting . ', ' . htmlspecialchars($user_name) ?>! Welcome to your dashboard.
        </div>

        <h2>Your Enrolled Courses</h2>
        <div class="courses">
            <?php if (empty($courses)): ?>
                <p>You are not enrolled in any courses at the moment.</p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                        <p><?= htmlspecialchars($course['description']) ?></p>

                        <!-- Learning Materials Section -->
                        <div class="materials">
                            <h4>Learning Materials</h4>
                            <ul>
                                <?php if (empty($course['materials'])): ?>
                                    <li>No materials available for this course.</li>
                                <?php else: ?>
                                    <?php foreach ($course['materials'] as $material): ?>
                                        <li>
                                            <strong><?= htmlspecialchars($material['material_name']) ?></strong><br>
                                            <?= htmlspecialchars($material['material_description']) ?><br>
                                            (Uploaded on: <?= htmlspecialchars($material['uploaded_at']) ?>)<br>
                                            <a href="<?= htmlspecialchars($material['file_path']) ?>" download>
                                                Download <?= htmlspecialchars($material['material_name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Assignments Section -->
                        <div class="assignments">
                            <h4>Assignments</h4>
                            <ul>
                                <?php if (empty($course['assignments'])): ?>
                                    <li>No assignments available for this course.</li>
                                <?php else: ?>
                                    <?php foreach ($course['assignments'] as $assignment): ?>
                                        <li>
                                            <?= htmlspecialchars($assignment['title']) ?> (Due: <?= htmlspecialchars($assignment['due_date']) ?>)
                                            <a href="submit_assignment.php?assignment_id=<?= htmlspecialchars($assignment['id']) ?>">Submit</a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
