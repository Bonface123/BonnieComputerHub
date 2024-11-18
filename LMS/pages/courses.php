<?php
session_start();
include('../includes/db_connect.php');

// Function to check module eligibility
function canEnrollInModule($pdo, $user_id, $module_number) {
    if ($module_number == 1) {
        return true; // Anyone can enroll in Module 1
    }

    // Check if previous module is completed
    $prev_module = $module_number - 1;
    $stmt = $pdo->prepare("
        SELECT status 
        FROM module_progress 
        WHERE user_id = ? AND module_id = ? 
        AND status = 'completed'
    ");
    $stmt->execute([$user_id, $prev_module]);
    return $stmt->rowCount() > 0;
}

// Function to get module progress
function getModuleProgress($pdo, $user_id, $module_number) {
    $stmt = $pdo->prepare("
        SELECT status, completion_percentage 
        FROM module_progress 
        WHERE user_id = ? AND module_id = ?
    ");
    $stmt->execute([$user_id, $module_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle enrollment
if (isset($_POST['enroll']) && isset($_SESSION['user_id'])) {
    $module_id = $_POST['module_id'];
    $user_id = $_SESSION['user_id'];

    if (canEnrollInModule($pdo, $user_id, $module_id)) {
        // Check if already enrolled
        $check_stmt = $pdo->prepare("
            SELECT id FROM module_progress 
            WHERE user_id = ? AND module_id = ?
        ");
        $check_stmt->execute([$user_id, $module_id]);

        if ($check_stmt->rowCount() == 0) {
            // Create new enrollment
            $enroll_stmt = $pdo->prepare("
                INSERT INTO module_progress (user_id, module_id, status) 
                VALUES (?, ?, 'in_progress')
            ");
            $enroll_stmt->execute([$user_id, $module_id]);
            $success_message = "Successfully enrolled in Module $module_id!";
        } else {
            $error_message = "You are already enrolled in this module.";
        }
    } else {
        $error_message = "You must complete the previous module first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Bonnie Computer Hub LMS</title>
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
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="../../index.html" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Empowering Through Technology</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="../index.php" class="text-gray-300 hover:text-secondary transition">Home</a>
                    <a href="courses.php" class="text-secondary">Courses</a>
                    <a href="contact.php" class="text-gray-300 hover:text-secondary transition">Contact</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../student/dashboard.php" class="text-gray-300 hover:text-secondary transition">Dashboard</a>
                        <a href="../logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 bg-secondary text-primary rounded-lg hover:bg-white transition">Login</a>
                        <a href="register.php" class="px-4 py-2 border-2 border-secondary text-secondary rounded-lg hover:bg-secondary hover:text-primary transition">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-900 py-20 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Our Web Development Courses</h1>
                <p class="text-xl text-gray-200 mb-8">
                    Comprehensive courses designed to transform you into a professional web developer
                </p>
                <div class="flex justify-center gap-4">
                    <a href="#courses" class="px-6 py-3 bg-secondary text-primary rounded-full hover:bg-white transition duration-300">
                        View Courses
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="px-6 py-3 border-2 border-secondary text-secondary rounded-full hover:bg-secondary hover:text-primary transition duration-300">
                            Get Started
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Add this after your hero section -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
                    Please <a href="login.php" class="font-bold underline">login</a> or 
                    <a href="register.php" class="font-bold underline">register</a> to enroll in courses.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-20">
        <div class="container mx-auto px-4">
            <!-- Course Modules -->
            <section id="courses" class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-center text-primary mb-12">Web Development Program Structure</h2>
                <p class="text-gray-600 text-center mb-12 max-w-3xl mx-auto">
                    Our comprehensive web development program is structured into three modules, each lasting 8 weeks. 
                    The complete program spans 6 months, providing you with a solid foundation in web development.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Module 1 -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                        <div class="bg-primary p-6">
                            <h3 class="text-2xl font-bold text-secondary mb-2">Module 1</h3>
                            <p class="text-gray-200">Introduction to Web Development</p>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php 
                                $progress = getModuleProgress($pdo, $_SESSION['user_id'], 1);
                                if ($progress): 
                                ?>
                                    <div class="mt-4">
                                        <div class="bg-white/10 rounded-full h-2 mb-2">
                                            <div class="bg-secondary h-2 rounded-full" 
                                                 style="width: <?= $progress['completion_percentage'] ?>%"></div>
                                        </div>
                                        <p class="text-sm text-white">
                                            <?= $progress['status'] === 'completed' ? 'Completed' : 'In Progress' ?> - 
                                            <?= $progress['completion_percentage'] ?>%
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">HTML Basics (Week 1-2)</h4>
                                        <p class="text-gray-600 text-sm">Structure, elements, and semantic markup</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">Advanced HTML (Week 3-4)</h4>
                                        <p class="text-gray-600 text-sm">Forms, multimedia, and best practices</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">CSS Basics (Week 5-6)</h4>
                                        <p class="text-gray-600 text-sm">Styling, layouts, and responsive design</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">Advanced CSS (Week 7-8)</h4>
                                        <p class="text-gray-600 text-sm">Animations, flexbox, and grid systems</p>
                                    </div>
                                </li>
                            </ul>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if (!$progress): ?>
                                    <form method="POST" class="mt-6">
                                        <input type="hidden" name="module_id" value="1">
                                        <button type="submit" name="enroll" 
                                                class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                                            Enroll Now
                                        </button>
                                    </form>
                                <?php elseif ($progress['status'] === 'completed'): ?>
                                    <div class="mt-6 text-center">
                                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full">
                                            ✓ Module Completed
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-6">
                                        <a href="../student/module.php?id=1" 
                                           class="block text-center bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                                            Continue Learning
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="register.php" 
                                   class="mt-6 block text-center bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                                    Register to Enroll
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Module 2 -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                        <div class="bg-primary p-6">
                            <h3 class="text-2xl font-bold text-secondary mb-2">Module 2</h3>
                            <p class="text-gray-200">JavaScript Essentials</p>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">JavaScript Basics (Week 1-2)</h4>
                                        <p class="text-gray-600 text-sm">Syntax, variables, and control structures</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">DOM Manipulation (Week 3-4)</h4>
                                        <p class="text-gray-600 text-sm">Events, selectors, and dynamic content</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">Event Handling (Week 5-6)</h4>
                                        <p class="text-gray-600 text-sm">User interactions and form validation</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">Advanced JavaScript (Week 7-8)</h4>
                                        <p class="text-gray-600 text-sm">ES6+, async programming, and APIs</p>
                                    </div>
                                </li>
                            </ul>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php 
                                $canEnroll = canEnrollInModule($pdo, $_SESSION['user_id'], 2);
                                $progress = getModuleProgress($pdo, $_SESSION['user_id'], 2);
                                ?>
                                
                                <?php if (!$canEnroll): ?>
                                    <div class="mt-6 text-center">
                                        <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full">
                                            Complete Module 1 first
                                        </span>
                                    </div>
                                <?php elseif (!$progress): ?>
                                    <form method="POST" class="mt-6">
                                        <input type="hidden" name="module_id" value="2">
                                        <button type="submit" name="enroll" 
                                                class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                                            Enroll Now
                                        </button>
                                    </form>
                                    <?php elseif ($progress['status'] === 'completed'): ?>
                                    <div class="mt-6 text-center">
                                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full">
                                            ✓ Module Completed
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-6">
                                        <a href="../student/module.php?id=2" 
                                           class="block text-center bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                                            Continue Learning
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="register.php" 
                                   class="mt-6 block text-center bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                                    Register to Enroll
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Module 3 -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                        <div class="bg-primary p-6">
                            <h3 class="text-2xl font-bold text-secondary mb-2">Module 3</h3>
                            <p class="text-gray-200">Backend Development</p>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">PHP Basics (Week 1-2)</h4>
                                        <p class="text-gray-600 text-sm">Syntax, functions, and OOP basics</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">Advanced PHP (Week 3-4)</h4>
                                        <p class="text-gray-600 text-sm">Sessions, cookies, and security</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">MySQL Basics (Week 5-6)</h4>
                                        <p class="text-gray-600 text-sm">Database design and SQL queries</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <div>
                                        <h4 class="font-semibold">Full Stack Integration (Week 7-8)</h4>
                                        <p class="text-gray-600 text-sm">Building complete web applications</p>
                                    </div>
                                </li>
                            </ul>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php 
                                $canEnroll = canEnrollInModule($pdo, $_SESSION['user_id'], 3);
                                $progress = getModuleProgress($pdo, $_SESSION['user_id'], 3);
                                ?>
                                
                                <?php if (!$canEnroll): ?>
                                    <div class="mt-6 text-center">
                                        <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full">
                                            Complete Module 1 first
                                        </span>
                                    </div>
                                <?php elseif (!$progress): ?>
                                    <form method="POST" class="mt-6">
                                        <input type="hidden" name="module_id" value="3">
                                        <button type="submit" name="enroll" 
                                                class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                                            Enroll Now
                                        </button>
                                    </form>
                                    <?php elseif ($progress['status'] === 'completed'): ?>
                                    <div class="mt-6 text-center">
                                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full">
                                            ✓ Module Completed
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-6">
                                        <a href="../student/module.php?id=3" 
                                           class="block text-center bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                                            Continue Learning
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="register.php" 
                                   class="mt-6 block text-center bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                                    Register to Enroll
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Why Choose Us -->
            <section class="mt-20">
                <div class="bg-primary rounded-2xl p-12 text-center">
                    <h2 class="text-3xl font-bold text-secondary mb-8">Why Choose Our Program?</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-white">
                            <i class="fas fa-laptop-code text-4xl text-secondary mb-4"></i>
                            <h3 class="text-xl font-bold mb-2">Practical Learning</h3>
                            <p class="text-gray-300">Hands-on projects and real-world applications</p>
                        </div>
                        <div class="text-white">
                            <i class="fas fa-users text-4xl text-secondary mb-4"></i>
                            <h3 class="text-xl font-bold mb-2">Expert Instructors</h3>
                            <p class="text-gray-300">Learn from industry professionals</p>
                        </div>
                        <div class="text-white">
                            <i class="fas fa-certificate text-4xl text-secondary mb-4"></i>
                            <h3 class="text-xl font-bold mb-2">Certification</h3>
                            <p class="text-gray-300">Receive recognized certification upon completion</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="mt-20 text-center">
                <h2 class="text-3xl font-bold text-primary mb-6">Ready to Start Your Journey?</h2>
                <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                    Join our community of learners and take the first step towards becoming a professional web developer.
                </p>
                <a href="register.php" class="inline-block px-8 py-4 bg-primary text-white rounded-full hover:bg-secondary hover:text-primary transition duration-300">
                    Register Now
                </a>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-20">
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
