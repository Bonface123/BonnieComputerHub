<?php
session_start();
include('../includes/db_connect.php');

$currentPage = 'courses'; // Set the current page for active navigation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Courses - Bonnie Computer Hub</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Header Styles */
        header, .courses-header {
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
            font-family: Arial, sans-serif;
            font-size: 1.8rem;
            font-weight: bold;
            color: #FFD700;
            text-decoration: none;
        }

        .brand-link:hover {
            color: #007bff;
            text-decoration: underline;
        }

        .logo img {
            height: 50px;
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

        /* Courses Section Header */
        .courses-header h1 {
            font-size: 2.5rem;
            color: #FFD700;
            text-align: center;
            flex-grow: 1;
            margin: 0;
        }

        /* Main Content Styles */
        main {
            padding: 50px 20px;
        }

        .course-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .course-container h2 {
            font-size: 2.2rem;
            color: #002147;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Grid Layout for Courses */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        /* Module Card Styles */
        .module-card {
            background-color: #f4f4f4;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .module-card h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .module-card p {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
        }

        .module-card ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            
        }

        .module-card li {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
            content: '✔';
        }

        /* Enroll Button Styling */
        .btn-enroll {
            display: inline-block;
            background-color: #FFD700;
            color:black;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 15px;
        }

        .btn-enroll:hover {
            background-color: #ffcc00;
            transform: scale(1.05);
        }

        .btn-enroll:active {
            background-color: #e6b800;
        }

        .btn-enroll:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.5);
        }

        /* Call to Action Section */
        .cta-section {
            background-color: #002147;
            color: #FFD700;
            padding: 40px;
            text-align: center;
            margin-top: 40px;
            border-radius: 8px;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .cta-btn {
            background-color: #FFD700;
            color:#000;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .cta-btn:hover {
            background-color: #ffcc00;
            transform: scale(1.05);
        }

        /* Footer Styles */
        footer {
            background-color: #002147;
            color: white;
            text-align: center;
            padding: 20px;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header, .courses-header {
                flex-direction: column;
                text-align: center;
            }

            nav ul {
                flex-direction: column;
                gap: 10px;
            }

            .course-container {
                padding: 20px;
            }

            .module-card h3 {
                font-size: 1.5rem;
            }

            .module-card p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

    <!-- Courses Section Header -->
    <div class="courses-header">
        <h1>Our Courses</h1>
    </div>

    <!-- Main Content -->
    <main>
        <div class="course-container">
            <h2>Explore the Courses we Offer</h2>
            <p>At Bonnie Computer Hub, we offer a comprehensive set of courses designed to equip you with essential web development skills. These courses are structured in three modules, each lasting 8 weeks, spread over 6 months.</p>

            <!-- Grid Layout for Courses -->
            <div class="course-grid">

                <!-- Course 1 -->
                <div class="module-card">
                    <h3>Module 1: Introduction to Web Development (HTML & CSS)</h3>
                    <p>Learn the basics of building websites with HTML and CSS.</p>
                    <ul>
                        <li><b>Week 1-2:</b> HTML Basics</li>
                        <li><b>Week 3-4:</b> Advanced HTML</li>
                        <li><b>Week 5-6:</b> CSS Basics</li>
                        <li><b>Week 7-8:</b> Advanced CSS</li>
                    </ul>
                    <a href="register.php" class="btn-enroll">Enroll Now</a>
                </div>

                <!-- Course 2 -->
                <div class="module-card">
                    <h3>Module 2: JavaScript Essentials</h3>
                    <p>Master JavaScript to make your websites dynamic and interactive.</p>
                    <ul>
                        <li><b>Week 1-2:</b> JavaScript Basics</li>
                        <li><b>Week 3-4:</b> DOM Manipulation</li>
                        <li><b>Week 5-6:</b> Event Handling</li>
                        <li><b>Week 7-8:</b> Advanced JavaScript</li>
                    </ul>
                    <a href="register.php" class="btn-enroll">Enroll Now</a>
                </div>

                <!-- Course 3 -->
                <div class="module-card">
                    <h3>Module 3: Backend Development (PHP & MySQL)</h3>
                    <p>Learn server-side programming and database management to create dynamic web applications.</p>
                    <ul>
                        <li> <b>Week 1-2:</b> Introduction to PHP</li>
                        <li><b>Week 3-4:</b> Advanced PHP</li>
                        <li><b>Week 5-6:</b> Working with MySQL</li>
                        <li><b>Week 7-8:</b> Integrating PHP and MySQL</li>
                    </ul>
                    <a href="register.php" class="btn-enroll">Enroll Now</a>
                </div>

            </div>

            <!-- CTA Section -->
            <div class="cta-section">
                <h2>Ready to start your journey?</h2>
                <p>Join us today and take the first step toward becoming a skilled web developer. Our experienced instructors will guide you every step of the way.</p>
                <a href="register.php" class="cta-btn">Sign Up Now</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Bonnie Computer Hub | All Rights Reserved</p>
    </footer>

</body>
</html>
