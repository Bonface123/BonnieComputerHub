<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',
                        secondary: '#FFD700',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="instructor_dashboard.php" class="text-xl font-bold text-secondary">Manage Resources</a>
                        <p class="text-gray-300 text-sm">Bonnie Computer Hub</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="instructor_dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Coming Soon Message -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="text-secondary mb-6">
                    <i class="fas fa-tools text-6xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-primary mb-4">Coming Soon!</h1>
                <p class="text-gray-600 mb-8">
                    We're working hard to bring you an amazing resource management system. 
                    This feature will be available soon.
                </p>
                <div class="space-y-4 text-gray-500">
                    <p class="flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Upload and manage course materials
                    </p>
                    <p class="flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Share resources with students
                    </p>
                    <p class="flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Organize content by modules
                    </p>
                </div>
                <div class="mt-8">
                    <a href="instructor_dashboard.php" 
                       class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Return to Dashboard
                    </a>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 text-center text-gray-500">
                <p class="text-sm">
                    Have suggestions for this feature? Contact the admin team.
                </p>
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