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

// Handle material upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    $course_id = $_POST['course_id'];
    $material_description = $_POST['material_description'];
    $file = $_FILES['material_file'];

    // Check for errors in the uploaded file
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Define the upload directory
        $uploadDir = '../uploads/materials/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique file name
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the server directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Insert material details into the database
            $stmt = $pdo->prepare("INSERT INTO course_materials (course_id, description, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$course_id, $material_description, $fileName]);

            echo "Material uploaded successfully.";
        } else {
            echo "Failed to upload material.";
        }
    } else {
        echo "Error uploading file. Please try again.";
    }
}

// Handle material deletion
if (isset($_GET['delete_material'])) {
    $material_id = $_GET['delete_material'];

    // Fetch the file path of the material to delete
    $stmt = $pdo->prepare("SELECT file_path FROM course_materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($material) {
        // Delete the file from the server
        $filePath = '../uploads/materials/' . $material['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete the material record from the database
        $stmt = $pdo->prepare("DELETE FROM course_materials WHERE id = ?");
        $stmt->execute([$material_id]);

        echo "Material deleted successfully.";
    }
}

// Handle material update (editing)
if (isset($_POST['edit_material'])) {
    $material_id = $_POST['material_id'];
    $new_description = $_POST['new_description'];

    // Update the material description in the database
    $stmt = $pdo->prepare("UPDATE course_materials SET description = ? WHERE id = ?");
    $stmt->execute([$new_description, $material_id]);

    echo "Material updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Manage Courses</title>
    <style>
        /* General Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f4f7f9;
    color: #333;
    line-height: 1.6;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

main {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Header */
header {
    padding: 20px;
    background-color: #007bff;
    color: #fff;
    text-align: center;
    font-size: 1.4em;
}

/* Breadcrumb */
.breadcrumb {
    font-size: 0.9em;
    margin-bottom: 20px;
    color: #555;
}

.breadcrumb a {
    color: #007bff;
}

/* Headings */
h1, h2 {
    color: #333;
    margin-bottom: 10px;
}

h1 {
    font-size: 1.8em;
}

h2 {
    font-size: 1.4em;
    margin-top: 20px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table th, table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

table th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Form Styling */
form {
    margin-top: 20px;
}

label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
    color: #333;
}

input[type="text"],
input[type="file"],
textarea,
select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

textarea {
    height: 80px;
    resize: vertical;
}

button[type="submit"] {
    display: inline-block;
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 15px;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}

/* Success/Error Messages */
.message {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="../index.php">Home</a> &gt; 
            <a href="instructor_dashboard.php">Instructor Dashboard</a> &gt; 
            <span>Manage Courses</span>
        </div>

        <h1>Manage Courses</h1>

        <section>
            <h2>Available Courses</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= htmlspecialchars($course['id']) ?></td>
                            <td><?= htmlspecialchars($course['course_name']) ?></td>
                            <td>
                                <form action="manage_assignments.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['id']) ?>">
                                    <button type="submit">Manage Assignments</button>
                                </form>
                                <form action="view_progress.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['id']) ?>">
                                    <button type="submit">View Progress</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Section for uploading course materials -->
        <section>
            <h2>Upload New Material</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="course_id">Select Course:</label>
                <select name="course_id" id="course_id" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['id']) ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="material_description">Material Description:</label>
                <textarea name="material_description" id="material_description" required></textarea>

                <label for="material_file">Select File:</label>
                <input type="file" name="material_file" id="material_file" required>

                <button type="submit" name="upload_material">Upload Material</button>
            </form>
        </section>

        <!-- Section for displaying already uploaded materials -->
        <section>
            <h2>Uploaded Materials</h2>
            <?php foreach ($courses as $course): ?>
                <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                <?php
                // Fetch materials for this course
                $stmt = $pdo->prepare("SELECT id, description, file_path FROM course_materials WHERE course_id = ?");
                $stmt->execute([$course['id']]);
                $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if ($materials): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>File</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                                <tr>
                                    <td><?= htmlspecialchars($material['description']) ?></td>
                                    <td><a href="../uploads/materials/<?= htmlspecialchars($material['file_path']) ?>" target="_blank">Download</a></td>
                                    <td>
                                        <!-- Edit Material -->
                                        <button onclick="editMaterial(<?= $material['id'] ?>, '<?= htmlspecialchars($material['description']) ?>')">Edit</button>
                                        <!-- Delete Material -->
                                        <a href="?delete_material=<?= $material['id'] ?>" onclick="return confirm('Are you sure you want to delete this material?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No materials uploaded for this course yet.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

    </main>

    <script>
        function editMaterial(materialId, currentDescription) {
            var newDescription = prompt("Edit Material Description:", currentDescription);
            if (newDescription !== null) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                var materialIdInput = document.createElement('input');
                materialIdInput.type = 'hidden';
                materialIdInput.name = 'material_id';
                materialIdInput.value = materialId;
                form.appendChild(materialIdInput);

                var newDescriptionInput = document.createElement('input');
                newDescriptionInput.type = 'hidden';
                newDescriptionInput.name = 'new_description';
                newDescriptionInput.value = newDescription;
                form.appendChild(newDescriptionInput);

                var editMaterialInput = document.createElement('input');
                editMaterialInput.type = 'hidden';
                editMaterialInput.name = 'edit_material';
                form.appendChild(editMaterialInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
