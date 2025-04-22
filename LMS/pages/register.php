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
    require_once '../includes/send_mail.php';
    $subject = "Welcome to Bonnie Computer Hub!";
    $body = "Hello $name,\n\nThank you for registering at Bonnie Computer Hub LMS. You can now log in and start learning!\n\nBest regards,\nBonnie Computer Hub Team";
    $result = bch_send_mail($email, $name, $subject, $body);
    if ($result['success']) {
        header('Location: login.php');
        exit;
    } else {
        $error = "Registration succeeded, but failed to send welcome email: " . htmlspecialchars($result['error']);
    }
} else {
    $error = "Registration failed. Please try again.";
}
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bonnie Computer Hub LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',    // BCH Blue
                        secondary: '#FFD700',  // BCH Gold
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-primary shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-center items-center">
                <a href="../../index.php" class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-secondary">Bonnie Computer Hub</h1>
                        <p class="text-gray-300 text-sm">Empowering Through Technology</p>
                    </div>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto">
            <!-- Registration Form Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-3xl font-bold text-center text-primary mb-8">Create Your Account</h2>

                <?php if (isset($error)): ?>
                    <div class="bg-red-50 text-red-500 p-4 rounded-lg mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" id="name" name="name" required
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                   placeholder="Enter your full name">
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" required
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                   placeholder="Enter your email">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" required
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                   placeholder="Create a password">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300 transform hover:scale-105 font-medium">
                        Create Account
                    </button>
                </form>

                <!-- Additional Links -->
                <div class="mt-6 text-center space-y-4">
                    <p class="text-gray-600">
                        Already have an account? 
                        <a href="login.php" class="text-primary hover:text-secondary font-medium">
                            Login here
                        </a>
                    </p>
                    <p class="text-gray-600">
                        Forgot your password? 
                        <a href="reset_password.php" class="text-primary hover:text-secondary font-medium">
                            Reset it here
                        </a>
                    </p>
                </div>
            </div>

            <!-- Features Section -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="bg-primary/10 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-graduation-cap text-primary text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-800">Quality Education</h3>
                    <p class="text-gray-600 text-sm">Learn from industry experts</p>
                </div>
                <div class="text-center">
                    <div class="bg-primary/10 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-primary text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-800">Community Support</h3>
                    <p class="text-gray-600 text-sm">Join our learning community</p>
                </div>
                <div class="text-center">
                    <div class="bg-primary/10 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-certificate text-primary text-xl"></i>
                    </div>
                    <h3 class="font-medium text-gray-800">Certification</h3>
                    <p class="text-gray-600 text-sm">Earn recognized certificates</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 mb-4">
                &copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.
            </p>
            <p class="text-secondary italic">
                "I can do all things through Christ who strengthens me." - Philippians 4:13
            </p>
        </div>
    </footer>
</body>
</html>
