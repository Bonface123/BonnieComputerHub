// pages/login_process.php
<?php
session_start();
include '../includes/db_connect.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        // Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Redirect to the dashboard based on role
        if ($user['role'] === 'admin') {
            header("Location: admin/manage_users.php");
        } elseif ($user['role'] === 'instructor') {
            header("Location: instructor/create_course.php");
        } else {
            header("Location: student/view_courses.php");
        }
        exit();
    } else {
        // Invalid credentials
        header("Location: login.php?error=Invalid email or password.");
        exit();
    }
} else {
    echo "Invalid request.";
}
?>
