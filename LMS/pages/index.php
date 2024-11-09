<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Bonnie Computer Hub - Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #002147;
            color: #FFD700;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .brand-link {
            font-size: 1.8rem;
            font-weight: bold;
            color: #FFD700;
            text-decoration: none;
            margin: 0;
        }
        .brand-link:hover {
            color: #007bff;
            text-decoration: underline;
        }
        .logo img {
            height: 50px;
            background-color: #F0F0F0;
        }
        nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        nav a {
            color: #FFFFFF;
            text-decoration: none;
            font-weight: bold;
        }
        nav a:hover {
            color: #FFD700;
        }
        .hero-section {
            background-image: url('images/Bonnie.jpg');
            background-size: cover;
            background-position: center;
            color: #FFFFFF;
            text-align: center;
            padding: 60px 20px;
            background-attachment: fixed;
        }
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .cta-button {
            background-color: #FFD700;
            color: #002147;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        main {
            padding: 20px;
        }
        section {
            margin-bottom: 40px;
        }
        .feature-list ul {
            list-style-type: disc;
            margin-left: 20px;
        }
        .highlight-section {
            background-color: #F0F0F0;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        footer {
            background-color: #002147;
            color: #FFFFFF;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header with Logo and Navigation -->
    <header>
        <div class="logo">
            <img src="images/BCH_LOGO.jpg" alt="Bonnie Computer Hub Logo">
            <a href="index.php" class="brand-link">BONNIE COMPUTER HUB - BCH</a>
            <span style="color: #FFD700; font-size: 20px; margin-left: 10px;">Empowering Through Technology</span>
        </div>
        <nav>
            <ul>
                <li><a href="pages/index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
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
