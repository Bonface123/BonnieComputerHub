<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['module_id', 'title', 'description', 'instructions', 'due_date', 'marks'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        $module_id = $_POST['module_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $instructions = $_POST['instructions'];
        $due_date = $_POST['due_date'];
        $marks = $_POST['marks'];

        // Validate module exists
        $check_module = $pdo->prepare("SELECT id FROM course_modules WHERE id = ?");
        $check_module->execute([$module_id]);
        if (!$check_module->fetch()) {
            throw new Exception("Invalid module selected");
        }

        // Insert assignment with instructor_id
        $stmt = $pdo->prepare("
            INSERT INTO assignments 
            (instructor_id, module_id, title, description, instructions, due_date, marks) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $instructor_id,
            $module_id,
            $title,
            $description,
            $instructions,
            $due_date,
            $marks
        ]);

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Assignment created successfully'
            ]);
        } else {
            throw new Exception("Failed to create assignment");
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false, 
    'message' => 'Invalid request method'
]);
exit;
?>
