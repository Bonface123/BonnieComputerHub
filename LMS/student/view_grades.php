<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has a student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch all submissions for the student
$query = $pdo->prepare("
    SELECT s.id AS submission_id, a.title AS assignment_title, s.grade, s.feedback, s.status, s.submitted_on
    FROM submissions s
    JOIN course_assignments a ON a.id = s.assignment_id
    WHERE s.student_id = ?
");
$query->execute([$student_id]);
$submissions = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Your Grades and Feedback</title>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    color: #333;
}

h2 {
    color: #002F6C; /* BCH Blue */
    font-size: 24px;
    margin-bottom: 20px;
}

main {
    padding: 20px;
    background-color: white;
    max-width: 1200px;
    margin: 0 auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #002F6C; /* BCH Blue */
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Status Styling (using BCH colors) */
.pending {
    color: yellow;
    font-weight: bold;
}

.under-review {
    color: #FF9800; /* Amber color for under review */
    font-weight: bold;
}

.graded {
    color: green;
    font-weight: bold;
}

/* Button Styles */
button {
    background-color: #FFD700; /* BCH Gold */
    border: none;
    padding: 10px 15px;
    color: white;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #F39C12; /* Darker shade of gold for hover */
}

button:active {
    background-color: #E67E22; /* Even darker shade on click */
}

/* Link Styles */
a {
    text-decoration: none;
    color: #002F6C;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
    color: #FFD700; /* BCH Gold */
}

/* Empty State Styling */
p {
    font-size: 18px;
    color: #888;
}

/* Footer and Header Adjustments */
header, footer {
    background-color: #002F6C; /* BCH Blue */
    color: white;
    text-align: center;
    padding: 15px 0;
}

footer {
    margin-top: 20px;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2>Your Grades and Feedback</h2>
        <?php if ($submissions): ?>
            <table>
                <thead>
                    <tr>
                        <th>Assignment Title</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= htmlspecialchars($submission['assignment_title']) ?></td>
                            <td>
                                <?php 
                                    if (empty($submission['grade'])) {
                                        echo "<span class='no-grade'>Grade not assigned yet</span>";
                                    } else {
                                        echo htmlspecialchars($submission['grade']);
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    if (empty($submission['feedback'])) {
                                        echo "<span class='no-feedback'>No feedback provided yet</span>";
                                    } else {
                                        echo htmlspecialchars($submission['feedback']);
                                    }
                                ?>
                            </td>
                            <td class="<?= strtolower($submission['status']) ?>"><?= htmlspecialchars($submission['status']) ?></td>
                            <td><?= htmlspecialchars($submission['submitted_on']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no submissions to view.</p>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
