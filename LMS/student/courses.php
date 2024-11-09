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
            /* Style for the brand link */
.brand-link {
    font-family: 'Arial', sans-serif; /* Use a clean sans-serif font */
    font-size: 1.8rem; /* Larger font size for emphasis */
    font-weight: bold; /* Bold text to make it stand out */
    color: #FFD700; /* Gold color from brand palette */
    text-decoration: none; /* Remove underline */
    display: inline-block;
    margin: 0; /* Remove any default margin */
}

.brand-link:hover {
    color: #007bff; /* Blue color from the brand palette for hover effect */
    text-decoration: underline; /* Underline text on hover */
}

/* Optional: Adjust the <br> for spacing if needed */
.brand-link br {
    display: none; /* Remove the line break if not necessary, you can customize this */
}

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

        .course-container h1 {
            font-size: 2.5rem;
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

        /* General Styles for Module Cards */
.module-card {
    background-color: #f4f4f4;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.module-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.module-card h2 {
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
}

.module-card li {
    font-size: 14px;
    color: #666;
    margin: 5px 0;
}

/* Enroll Now Button Styling */
.btn-enroll {
    display: inline-block;
    background-color: #FFD700; /* Gold color */
    color: white;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
    margin-top: 15px;
}

.btn-enroll:hover {
    background-color: #ffcc00; /* Darker gold on hover */
    transform: scale(1.05);
}

.btn-enroll:active {
    background-color: #e6b800; /* Slightly darker on click */
}

.btn-enroll:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.5); /* Focus ring */
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
            header {
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

            .module-card h2 {
                font-size: 1.5rem;
            }

            .module-card p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
  <!-- Header with Logo and Navigation -->
   <header>
        <div class="logo">
        <img src="images/BCH.jpg" alt="Bonnie Computer Hub Logo">
            <a href="../index.php" class="brand-link">BONNIE COMPUTER HUB - BCH </a>
            <span style="color: #FFD700; font-size: 20px; margin-left: 10px;">Empowering Through Technology</span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php" style="color: #FFD700;">Login</a></li>
                <li><a href="register.php" style="color: #FFD700;">Register</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div class="course-container">
            <h1>Our Courses</h1>
            <p>At Bonnie Computer Hub, we offer a comprehensive set of courses designed to equip you with essential web development skills. These courses are structured in three modules, each lasting 8 weeks, spread over 6 months.</p>

            <!-- Grid Layout for Courses -->
            <div class="course-grid">

                <!-- Course 1 -->
                <div class="module-card">
                    <h2>Module 1: Introduction to Web Development (HTML & CSS)</h2>
                    <p>Learn the basics of building websites with HTML and CSS.</p>
                    <ul>
                        <li>Week 1-2: HTML Basics</li>
                        <li>Week 3-4: Advanced HTML</li>
                        <li>Week 5-6: CSS Basics</li>
                        <li>Week 7-8: Advanced CSS</li>
                    </ul>
                    <a href="register.php" class="btn-enroll">Enroll Now</a>
                </div>

                <!-- Course 2 -->
                <div class="module-card">
                    <h2>Module 2: JavaScript Essentials</h2>
                    <p>Master JavaScript to make your websites dynamic and interactive.</p>
                    <ul>
                        <li>Week 1-2: Introduction to JavaScript</li>
                        <li>Week 3-4: Functions and Loops</li>
                        <li>Week 5-6: DOM Manipulation</li>
                        <li>Week 7-8: JavaScript Events</li>
                    </ul>
                    <a href="register.php" class="btn-enroll">Enroll Now</a>
                </div>

                <!-- Course 3 -->
                <div class="module-card">
                    <h2>Module 3: PHP and Backend Development</h2>
                    <p>Learn PHP and server-side scripting for dynamic web applications.</p>
                    <ul>
                        <li>Week 1-2: Introduction to PHP</li>
                        <li>Week 3-4: PHP and Forms</li>
                        <li>Week 5-6: Working with MySQL</li>
                        <li>Week 7-8: Advanced PHP</li>
                    </ul>
                    <a href="register.php" class="btn-enroll">Enroll Now</a>
                </div>

            </div>

            <div class="course-duration">
                <h3>Course Duration:</h3>
                <p>The entire program is offered over a period of 6 months, divided into 3 modules, with each module lasting 8 weeks. Upon completion, you will be equipped with the essential skills needed to pursue a career in web development.</p>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Bonnie Computer Hub</p>
    </footer>
</body>
</html>
