<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch the course to be edited
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];

    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo "Course not found.";
        exit;
    }
} else {
    echo "Invalid course ID.";
    exit;
}

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $update_sql = "UPDATE courses SET course_name = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $description, $courseId]);
    echo "Course updated successfully.";
    header("Location: manage_courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <title>Edit Course</title>
    <Style>
        /* General Body Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5; /* Light gray background for contrast */
    color: #333; /* Dark text for readability */
    margin: 0;
    padding: 0;
}

/* Header & Footer Styles */
header, footer {
    background-color: #003366; /* BCH Blue */
    color: white;
    padding: 20px;
    text-align: center;
}

/* Breadcrumb Navigation */
.breadcrumb {
    margin: 20px 0;
    font-size: 1rem;
    color: #003366; /* BCH Blue */
}

.breadcrumb a {
    color: #003366; /* BCH Blue */
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

/* Main Content Styles */
main {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Title */
h1 {
    font-size: 2rem;
    color: #003366; /* BCH Blue */
    margin-bottom: 20px;
}

/* Form Styles */
form {
    display: grid;
    gap: 15px;
    font-size: 1rem;
}

/* Label Styles */
label {
    font-weight: bold;
    color: #333;
}

/* Input & Textarea Styles */
input[type="text"], textarea {
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #003366; /* BCH Blue */
    border-radius: 5px;
    width: 100%;
    box-sizing: border-box;
}

/* Specifically for the textarea (description box) */
textarea {
    height: 200px; /* Increased height for more space */
    resize: vertical; /* Allow users to resize vertically */
}

/* Focus Effect on Inputs */
input[type="text"]:focus, textarea:focus {
    outline: none;
    border-color: #f7a700; /* BCH Gold on focus */
}

/* Button Styles */
button {
    background-color: #f7a700; /* BCH Gold */
    color: white;
    font-size: 1.1rem;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

/* Button Hover */
button:hover {
    background-color: #e68a00; /* Darker Gold on hover */
}

/* Error/Success Messages */
.message {
    margin-top: 20px;
    padding: 10px;
    border-radius: 5px;
}

.message.success {
    background-color: #f0f8f0;
    color: #4caf50;
}

.message.error {
    background-color: #ffe6e6;
    color: #f44336;
}

/* Styling the Module Cards */
.module-card {
    background-color: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #003366; /* BCH Blue */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.module-card h3 {
    color: #003366; /* BCH Blue */
    font-size: 1.6rem;
    margin-bottom: 10px;
}

.module-card p {
    color: #333;
    font-size: 1.1rem;
}

.module-card ul {
    margin: 10px 0;
    padding-left: 20px;
    list-style-type: disc;
}

.module-card a {
    background-color: #f7a700; /* BCH Gold */
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
    display: inline-block;
    margin-top: 10px;
}

.module-card a:hover {
    background-color: #e68a00; /* Darker Gold on hover */
}

/* Responsive Styles */
@media (max-width: 768px) {
    main {
        padding: 15px;
    }
}

    </Style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
         <!-- Breadcrumb Navigation -->
         <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="edit_course.php">Edit Course</a>
        </div>

        <h1>Edit Course</h1>

        <form action="" method="POST">
            <label for="name">Course Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($course['course_name']) ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($course['description']) ?></textarea>

            <button type="submit" name="update_course">Update Course</button>
        </form>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
