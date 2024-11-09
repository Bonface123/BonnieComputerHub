<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Step 1: Handle Password Reset Request
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a secure token
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token valid for 1 hour

        // Insert token into password_resets table
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expires_at]);

        // Send reset email (for simplicity, display link directly in this example)
        $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
        echo "Password reset link: <a href='$reset_link'>$reset_link</a>";
    } else {
        $error = "Email address not found.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['token'])) {
    // Step 2: Handle New Password Submission
    $new_password = $_POST['new_password'];
    $token = $_POST['token'];

    // Find the token in the database
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset && strtotime($reset['expires_at']) > time()) {
        // Token is valid, hash new password and update user record
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['user_id']]);

        // Delete the token after successful reset
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        echo "Password successfully reset. <a href='login.php'>Login here</a>";
    } else {
        $error = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
/* General styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

/* Main container styling */
main {
    max-width: 400px;
    width: 100%;
    background-color: #ffffff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* Headings */
h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Labels and inputs */
label {
    display: block;
    font-size: 16px;
    color: #666;
    margin-bottom: 8px;
    text-align: left;
}

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    outline: none;
    transition: border-color 0.3s;
}

input[type="email"]:focus,
input[type="password"]:focus {
    border-color: #007bff;
}

/* Button styling */
button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    color: #ffffff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}

/* Error message styling */
.error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
    text-align: left;
    font-size: 14px;
    border: 1px solid #f5c6cb;
}

/* Link styling */
a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <main>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['token'])): ?>
            <!-- Form to Set New Password -->
            <h2>Enter New Password</h2>
            <form action="" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php else: ?>
            <!-- Form to Request Password Reset -->
            <h2>Request Password Reset</h2>
            <form action="" method="POST">
                <label for="email">Enter your email:</label>
                <input type="email" name="email" id="email" required>
                <button type="submit">Send Reset Link</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
