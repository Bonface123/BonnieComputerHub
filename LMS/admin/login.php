<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: ../admin/admin_dashboard.php');
        } elseif ($user['role'] === 'instructor') {
            header('Location: ../instructor/instructor_dashboard.php');
        } elseif ($user['role'] === 'student') {
            header('Location: ../student/student_dashboard.php');
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bonnie Computer Hub</title>
   
    <style>
        /* Body & Main Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        header {
            text-align: center;
            margin-bottom: 20px;
            color: #002147;
        }
        header h1 {
            font-size: 2.5rem;
            color: #002147;
        }
        main {
            background-color: #fff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            color: #333;
        }
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        button[type="submit"] {
            background-color: #002147;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #004080;
        }
        .error-message {
            color: red;
            font-size: 0.9rem;
        }

        /* Feature Section */
        .features {
            margin-top: 30px;
            padding: 20px;
            text-align: left;
            background-color: #002147;
            color: #fff;
            border-radius: 8px;
        }
        .features h2 {
            color: #FFD700;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .features ul {
            list-style-type: none;
            padding: 0;
        }
        .features li {
            margin: 8px 0;
        }
        
        /* Footer Styles */
        footer {
            margin-top: 40px;
            text-align: center;
            padding: 10px 0;
            background-color: #333;
            color: #fff;
            width: 100%;
            position: fixed;
            bottom: 0;
        }
        footer p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        .footer-links a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }
        .footer-links a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <header>
        <h1>Bonnie Computer Hub Login</h1>
        <p>Empowering Your Digital Journey</p>
    </header>
    
    <main>
        <!-- Login Form -->
        <form action="" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
            <?php if (isset($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </form>

        <!-- Features Section -->
        <section class="features">
            <h2>Why Choose Bonnie Computer Hub?</h2>
            <ul>
                <li>💼 Professional Instructors with Real-World Experience</li>
                <li>🚀 Advanced and Practical Curriculum</li>
                <li>🌐 Industry-Leading Tools and Resources</li>
                <li>📊 Data-Driven Insights for Continuous Improvement</li>
            </ul>
        </section>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Services</a>
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
        </div>
        <p>&copy; 2024 Bonnie Computer Hub. All Rights Reserved.</p>
    </footer>
</body>
</html>
