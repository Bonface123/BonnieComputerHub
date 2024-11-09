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

        // Redirect user based on their role
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
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            background-color: #002147; /* BCH Blue */
            color: #FFD700; /* BCH Gold */
            text-align: center;
            padding: 20px 0;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        header p {
            font-size: 1.2rem;
            color: #FFD700;
            font-weight: 300;
        }

        /* Main Section */
        main {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
        }

        .login-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 30px;
            text-align: center;
        }

        .login-container h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #002147;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-size: 1.1rem;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #FFD700; /* Highlight input border on focus */
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #FFD700; /* BCH Gold */
            color: #002147; /* BCH Blue */
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e6c300; /* Darker Gold */
        }

        .error-message {
            color: red;
            font-size: 1.1rem;
            margin-top: 15px;
        }

        .links {
            text-align: center;
            margin-top: 10px;
        }

        .links a {
            color: #002147;
            text-decoration: none;
            font-weight: bold;
        }

        .links a:hover {
            color: #FFD700;
        }

        .features {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .features h3 {
            color: #002147;
            margin-bottom: 15px;
        }

        .features ul {
            list-style-type: none;
            font-size: 1rem;
        }

        .features ul li {
            margin: 10px 0;
            padding-left: 20px;
            position: relative;
        }

        .features ul li::before {
            content: '✔';
            position: absolute;
            left: 0;
            color: #FFD700; /* BCH Gold */
        }

        /* Footer Section */
        footer {
            background-color: #002147; /* BCH Blue */
            color: #FFD700; /* BCH Gold */
            text-align: center;
            padding: 20px 0;
            margin-top: 30px;
        }

        footer a {
            color: #FFD700;
            text-decoration: none;
            font-size: 1rem;
            margin: 0 15px;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }

            .login-container {
                width: 100%;
                padding: 20px;
            }

            .features h3 {
                font-size: 1.5rem;
            }

            .features ul li {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header>
        <h1>Bonnie Computer Hub</h1>
        <p>Empowering Your Digital Journey</p>
    </header>

    <!-- Main Content Section -->
    <main>
        <div class="login-container">
            <h2>Login to Your Account</h2>
            <form action="" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
                
                <?php if (isset($error)): ?>
                    <p class="error-message"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            </form>


    <div class="links">
        <a href="register.php">Don't have an account? Register</a><br>
        <a href="reset_password.php">Forgot your password?</a>
    </div>

            <!-- Features Section -->
            <section class="features">
                <h3>Why Choose Bonnie Computer Hub?</h3>
                <ul>
                    <li>💼 Expert Instructors with Industry Experience</li>
                    <li>🚀 Hands-On and Practical Curriculum</li>
                    <li>🌐 Access to Industry-Leading Tools</li>
                    <li>📊 Data-Driven Insights for Your Growth</li>
                </ul>
            </section>
        </div>
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
