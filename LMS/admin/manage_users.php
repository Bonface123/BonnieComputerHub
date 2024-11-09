<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $role = $_POST['role'];

    // Insert new user into the database
    $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    $stmt->execute([$name, $email, $password, $role]);
    
    // Set flash message and redirect
    $_SESSION['flash_message'] = "User added successfully.";
    header("Location: manage_users.php");
    exit;
}

// Fetch all users from the database
$sql = "SELECT * FROM users";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Manage Users</title>
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
    font-size: 14px;
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #333;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #ffa500;
}

/* Flash Message */
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

form input, form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 2px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

form input:focus, form select:focus {
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
            <a href="manage_users.php">Manage Users</a>
        </div>

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <h1>Manage Users</h1>

        <h2>Add New User</h2>
        <form action="" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="student">Student</option>
                <option value="instructor">Instructor</option>
            </select>

            <button type="submit" name="add_user">Add User</button>
        </form>

        <h2>Existing Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= htmlspecialchars($user['id']) ?>">Edit</a> |
                            <a href="delete_user.php?id=<?= htmlspecialchars($user['id']) ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
