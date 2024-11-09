<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    }

    if (!isset($error)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$name, $email, $hashedPassword])) {
            header('Location: login.php'); // Redirect to login after successful registration
            exit;
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Register as Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }
        header {
            background-color: #002147;
            color: #FFD700;
            padding: 20px;
            text-align: center;
        }
        main {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: bold;
        }
        input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #FFD700;
            color: #002147;
            padding: 12px;
            font-size: 1.1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #FFA500;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body>
    <header>
        <h1>Register as Student</h1>
    </header>
    <main>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="name">Full Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Register</button>
        </form>

        <!-- Additional links for login and password reset -->
        <div class="links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p>Forgot your password? <a href="reset_password.php">Reset it here</a></p>
        </div>
    </main>
    <footer>
        <p>&copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.</p>
    </footer>
</body>
</html>
