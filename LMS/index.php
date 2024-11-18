<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonnie Computer Hub - Learning Management System</title>
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
                <!-- Logo Section -->
                <div class="flex items-center space-x-4">
                    <img src="images/BCH.jpg" alt="Bonnie Computer Hub Logo" 
                         class="h-12 w-12 rounded-full object-cover">
                    <div class="flex flex-col">
                        <a href="../index.html" class="text-xl font-bold text-secondary hover:text-white transition">
                            BONNIE COMPUTER HUB
                        </a>
                        <span class="text-sm text-gray-300">Empowering Through Technology</span>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-secondary hover:text-white transition">Home</a>
                    <a href="pages/courses.php" class="text-secondary hover:text-white transition">Courses</a>
                    <a href="pages/contact.php" class="text-secondary hover:text-white transition">Contact</a>
                    <div class="flex space-x-4">
                        <a href="pages/login.php" 
                           class="px-4 py-2 text-primary bg-secondary rounded-full hover:bg-white transition">
                            Login
                        </a>
                        <a href="pages/register.php" 
                           class="px-4 py-2 border-2 border-secondary text-secondary rounded-full hover:bg-secondary hover:text-primary transition">
                            Register
                        </a>
                    </div>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-secondary" id="mobile-menu-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <nav class="hidden md:hidden pb-4" id="mobile-menu">
                <div class="flex flex-col space-y-4">
                    <a href="index.php" class="text-secondary hover:text-white transition">Home</a>
                    <a href="pages/courses.php" class="text-secondary hover:text-white transition">Courses</a>
                    <a href="pages/contact.php" class="text-secondary hover:text-white transition">Contact</a>
                    <a href="pages/login.php" class="text-secondary hover:text-white transition">Login</a>
                    <a href="pages/register.php" class="text-secondary hover:text-white transition">Register</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative py-32 bg-gradient-to-r from-primary to-blue-900">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h1 class="text-4xl md:text-6xl font-bold mb-6" data-aos="fade-up">
                    Welcome to Bonnie Computer Hub LMS
                </h1>
                <p class="text-xl md:text-2xl mb-12 text-gray-200" data-aos="fade-up" data-aos-delay="200">
                    Empowering learners with cutting-edge technology and personalized education.
                </p>
                <a href="pages/register.php" 
                   class="inline-block px-8 py-4 bg-secondary text-primary font-bold rounded-full hover:bg-white transition duration-300 transform hover:scale-105"
                   data-aos="fade-up" data-aos-delay="400">
                    Get Started
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-20">
        <div class="container mx-auto px-4">
            <!-- About Section -->
            <section class="max-w-4xl mx-auto text-center mb-20" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-primary mb-6">About Our LMS</h2>
                <p class="text-lg text-gray-600 mb-8">
                    Bonnie Computer Hub's Learning Management System (LMS) offers a comprehensive platform designed for 
                    students, instructors, and administrators to engage in effective, interactive, and seamless course management.
                </p>
            </section>

            <!-- Features Grid -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-20">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-xl shadow-lg" data-aos="fade-up">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-2">Expert Instruction</h3>
                    <p class="text-gray-600">Learn from industry professionals with real-world experience.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-2">Interactive Learning</h3>
                    <p class="text-gray-600">Engage with course materials through our interactive platform.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-2">Community Support</h3>
                    <p class="text-gray-600">Join a community of learners and collaborate with peers.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-6 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-secondary text-3xl mb-4">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-2">Certification</h3>
                    <p class="text-gray-600">Earn recognized certificates upon course completion.</p>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="bg-primary rounded-2xl p-12 text-center" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-secondary mb-6">Join Our Community</h2>
                <p class="text-white text-lg mb-8">
                    Ready to take your learning experience to the next level? Register today and unlock a world of knowledge and skills.
                </p>
                <a href="pages/register.php" 
                   class="inline-block px-8 py-4 bg-secondary text-primary font-bold rounded-full hover:bg-white transition duration-300 transform hover:scale-105">
                    Register Now
                </a>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p class="text-gray-400 mb-4">
                    &copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.
                </p>
                <p class="text-secondary italic">
                    "I can do all things through Christ who strengthens me." - Philippians 4:13
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
