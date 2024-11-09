<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has a student role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Get the student ID
$student_id = $_SESSION['user_id'];

// Check if the assignment_id is provided in the URL
if (!isset($_GET['assignment_id'])) {
    die('Assignment ID not provided.');
}

$assignment_id = $_GET['assignment_id'];

// Fetch the assignment details to verify that the assignment exists
$assignment_query = $pdo->prepare("SELECT id, title, due_date, course_id FROM course_assignments WHERE id = ?");
$assignment_query->execute([$assignment_id]);
$assignment = $assignment_query->fetch(PDO::FETCH_ASSOC);

// If no assignment is found, display an error
if (!$assignment) {
    die('Assignment not found.');
}

// Initialize message variable
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the file was uploaded
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === 0) {
        // Define the allowed file types
        $allowed_types = ['pdf', 'doc', 'docx', 'txt'];
        $file_name = $_FILES['assignment_file']['name'];
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);

        // Check if the file type is allowed
        if (in_array(strtolower($file_type), $allowed_types)) {
            // Define the target directory
            $upload_dir = '../uploads/assignments/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
            }

            // Define the path for the uploaded file
            $target_file = $upload_dir . basename($file_name);

            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
                // Now insert into the submissions table
                try {
                    // Insert into the submissions table
                    $submission_query = $pdo->prepare("
                        INSERT INTO submissions (assignment_id, student_id, course_id, file_path, grade, submitted_on)
                        VALUES (?, ?, ?, ?, NULL, NOW())
                    ");
                    $submission_query->execute([$assignment_id, $student_id, $assignment['course_id'], $target_file]);

                    // Display a success message
                    $message = "<div class='success-message'>Your assignment has been successfully submitted!</div>";
                } catch (PDOException $e) {
                    // Handle database error
                    $message = "<div class='error-message'>There was an error submitting the assignment: " . $e->getMessage() . "</div>";
                }
            } else {
                // Error during file upload
                $message = "<div class='error-message'>There was an error uploading the file. Please try again.</div>";
            }
        } else {
            // Invalid file type
            $message = "<div class='error-message'>Only PDF, DOC, DOCX, and TXT files are allowed.</div>";
        }
    } else {
        // No file was uploaded or there was an error
        $message = "<div class='error-message'>Please select a file to upload.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Submit Assignment</title>
    <style>
/* Reset & Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4; /* Light background for contrast */
    color: #333; /* Dark text for readability */
    line-height: 1.6;
    padding: 20px;
}

main {
    max-width: 900px;
    margin: 30px auto;
    background-color: #fff; /* White background for content */
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Header */
header {
    background-color: #0044cc; /* Deep Blue */
    color: #fff;
    text-align: center;
    padding: 20px 0;
    border-radius: 8px 8px 0 0;
}

header h1 {
    font-size: 2.5rem;
    font-weight: 600;
}

/* Title */
h2 {
    font-size: 2rem;
    color: #0044cc;
    margin-bottom: 20px;
    text-align: center;
}

/* Assignment Details */
p {
    font-size: 1.1rem;
    color: #333;
    text-align: center;
    margin-bottom: 30px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
}

/* Label & Input Styles */
label {
    font-size: 1.1rem;
    color: #333;
    font-weight: 500;
}

input[type="file"] {
    padding: 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 100%;
    max-width: 400px;
    background-color: #f9f9f9;
    transition: border-color 0.3s ease;
}

input[type="file"]:focus {
    border-color: #0044cc; /* Blue focus border */
}

button {
    background-color: #ffcc00; /* Golden Button */
    color: #333;
    font-size: 1.1rem;
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    width: 50%;
    max-width: 300px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

button:hover {
    background-color: #ffaa00; /* Darker golden on hover */
    transform: scale(1.05); /* Slight zoom effect */
}

button:active {
    background-color: #ff9900;
}

/* Message Styles */
.success-message, .error-message {
    padding: 15px;
    margin: 20px 0;
    border-radius: 6px;
    font-size: 1rem;
    text-align: center;
}

.success-message {
    background-color: #4CAF50; /* Success Green */
    color: white;
}

.error-message {
    background-color: #f44336; /* Error Red */
    color: white;
}

/* Links */
a {
    font-size: 1rem;
    color: #0044cc; /* Blue Links */
    text-decoration: none;
    text-align: center;
    margin-top: 20px;
}

a:hover {
    text-decoration: underline;
}

a:active {
    color: #ffaa00; /* Golden color for active link */
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    h2 {
        font-size: 1.8rem;
    }

    form {
        width: 90%;
    }

    input[type="file"], button {
        width: 100%;
        max-width: none;
    }

    button {
        font-size: 1rem;
    }
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <h2>Submit Assignment: <?= htmlspecialchars($assignment['title']) ?></h2>
        <p>Due date: <?= htmlspecialchars($assignment['due_date']) ?></p>

        <!-- Display the message (if any) -->
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <!-- Assignment Submission Form -->
        <div class="form-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="assignment_file">Choose file to submit:</label>
                <input type="file" name="assignment_file" id="assignment_file" required><br><br>

                <button type="submit">Submit Assignment</button>
            </form>
        </div>

        <!-- After submission, provide options to continue -->
        <?php if ($message && strpos($message, 'successfully submitted') !== false): ?>
            <div class="links">
                <p><a href="view_grades.php">View Grades</a></p>
                <p><a href="student_dashboard.php">Return to Assignments List</a></p>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
