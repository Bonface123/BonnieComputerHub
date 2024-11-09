<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bonnie Computer Hub - Student Dashboard</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333;
        }

        /* Header Styles */
        header {
            background-color: #002147; /* BCH Blue */
            color: #FFD700; /* BCH Gold */
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Adding a shadow for better visibility */
        }

        /* Logo Styling */
        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px; /* Adjust image size */
            margin-right: 15px;
        }

        /* Brand Link Styling */
        .brand-link {
            font-size: 1.8rem;
            font-weight: bold;
            color: #FFD700;
            text-decoration: none;
            display: inline-block;
            margin: 0;
            text-transform: uppercase;
        }

        .brand-link:hover {
            color: #007bff; /* Blue on hover */
            text-decoration: underline;
        }

        /* Navigation Styles */
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }

        nav ul li {
            font-size: 1.2rem;
        }

        nav ul li a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }

        nav ul li a:hover {
            color: #ffffff; /* Change hover color to white for contrast */
        }

        /* Hero Section */
        .hero-section {
            background-color: #002147; /* BCH Blue */
            color: white;
            text-align: center;
            padding: 80px 20px;
        }

        .hero-section h1 {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .hero-section p {
            font-size: 1.5em;
            margin-bottom: 30px;
        }

        .hero-section .cta-button {
            background-color: #FFD700; /* BCH Gold */
            color: #002147; /* BCH Blue */
            padding: 15px 30px;
            font-size: 1.2em;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .hero-section .cta-button:hover {
            background-color: #e5a100; /* Slightly darker Gold */
            color: white;
        }

        /* Main Content */
        main {
            padding: 40px 20px;
        }

        section h2 {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
        }

        section p {
            font-size: 1.2em;
            line-height: 1.6;
            color: #555;
        }

        .feature-list ul {
            list-style-type: none;
            padding: 0;
            font-size: 1.1em;
            color: #555;
        }

        .feature-list ul li {
            margin: 10px 0;
            padding-left: 20px;
            position: relative;
        }

        .feature-list ul li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #FFD700; /* BCH Gold */
        }

        /* Highlight Section */
        .highlight-section {
            background-color: #FFD700; /* BCH Gold */
            color: #002147; /* BCH Blue */
            text-align: center;
            padding: 50px 20px;
            margin-top: 40px;
        }

        .highlight-section h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .highlight-section .cta-button {
            background-color: #002147; /* BCH Blue */
            color: #FFD700; /* BCH Gold */
            padding: 15px 30px;
            font-size: 1.2em;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .highlight-section .cta-button:hover {
            background-color: #e5a100; /* Slightly darker Gold */
            color: white;
        }

        /* Footer Styles */
        footer {
            background-color: #002147; /* BCH Blue */
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 1em;
        }

        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Header with Logo and Navigation -->
    <header>
        <div class="logo">
            <img src="images/BCH.jpg" alt="Bonnie Computer Hub Logo">
            <a href="index.php" class="brand-link">BONNIE COMPUTER HUB - BCH</a>
            <span style="color: #FFD700; font-size: 20px; margin-left: 10px;">Empowering Through Technology</span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="pages/courses.php">Courses</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="pages/contact.php">Contact</a></li>
                <li><a href="pages/login.php" style="color: #FFD700;">Login</a></li>
                <li><a href="pages/register.php" style="color: #FFD700;">Register</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1>Welcome to Bonnie Computer Hub LMS</h1>
        <p>Empowering learners with cutting-edge technology and personalized education.</p>
        <a href="pages/register.php" class="cta-button">Get Started</a>
    </section>

    <!-- Main Content -->
    <main>
        <section>
            <h2 style="color: #002147;">About Us</h2>
            <p>Bonnie Computer Hub's Learning Management System (LMS) offers a comprehensive platform designed for students, instructors, and administrators to engage in effective, interactive, and seamless course management.</p>
        </section>
        
        <section class="feature-list">
            <h2 style="color: #002147;">Key Features</h2>
            <ul>
                <li>Flexible course management for instructors and admins</li>
                <li>Secure login and personalized dashboards for students, instructors, and admins</li>
                <li>Interactive learning materials with progress tracking</li>
                <li>Discussion forums for collaboration and peer-to-peer interaction</li>
            </ul>
        </section>

        <section class="highlight-section">
            <h2 style="color: #FFD700;">Join Our Community</h2>
            <p>Ready to take your learning experience to the next level? Register today and unlock a world of knowledge and skills.</p>
            <a href="pages/register.php" class="cta-button">Register Now</a>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.</p>
    </footer>
</body>
</html>
