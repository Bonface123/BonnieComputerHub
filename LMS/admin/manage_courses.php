<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $createdBy = $_SESSION['user_id']; // Get the ID of the user creating the course

    try {
        // Insert new course into the database
        $insert_sql = "INSERT INTO courses (course_name, description, created_by) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$name, $description, $createdBy]);
        echo "Course added successfully.";
    } catch (PDOException $e) {
        echo "Error adding course: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch all courses
$sql = "SELECT * FROM courses";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Manage Courses</title>
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

form input, form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 2px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

form textarea {
    resize: vertical;
}

form input:focus, form textarea:focus {
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

table a {
    color: #ffa500;
    text-decoration: none;
    font-weight: bold;
    padding: 5px;
    border-radius: 3px;
    transition: background-color 0.3s;
}

table a:hover {
    background-color: #ffa500;
    color: white;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>

     <!-- Breadcrumb Navigation -->
     <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="manage_enrollments.php">Manage Courses</a>
        </div>

        <h1>Manage Courses</h1>

        <h2>Add New Course</h2>
        <form action="" method="POST">
            <label for="name">Course Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <button type="submit" name="add_course">Add Course</button>
        </form>

        <h2>Existing Courses</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= htmlspecialchars($course['id']) ?></td>
                        <td><?= htmlspecialchars($course['course_name']) ?></td> <!-- Updated to use the correct column name -->
                        <td><?= htmlspecialchars($course['description']) ?></td>
                        <td>
                            <a href="edit_course.php?id=<?= htmlspecialchars($course['id']) ?>">Edit</a> |
                            <a href="delete_course.php?id=<?= htmlspecialchars($course['id']) ?>" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
