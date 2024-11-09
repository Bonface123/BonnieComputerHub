<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$user_id = $_GET['id'];

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: manage_users.php');
    exit;
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $update_sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $email, $role, $user_id]);
    
    echo "User updated successfully.";
    header('Location: manage_users.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Edit User</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
         <!-- Breadcrumb Navigation -->
         <div class="breadcrumb">
            <a href="../admin/admin_dashboard.php">Home</a> &gt; 
            <a href="edit_user.php">Edit User</a>
        </div>

        <h1>Edit User</h1>
        <form action="" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="instructor" <?= $user['role'] === 'instructor' ? 'selected' : '' ?>>Instructor</option>
            </select>

            <button type="submit" name="update_user">Update User</button>
        </form>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
