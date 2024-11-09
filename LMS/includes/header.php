<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonnie Computer Hub - BCH</title>
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
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

        /* Hero Section Styles */
        .hero {
            background: url('images/hero-background.jpg') no-repeat center center/cover; /* Add a background image for the hero section */
            color: #FFD700;
            padding: 100px 20px;
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .hero .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .cta-buttons a {
            background-color: #FFD700;
            color: #002147;
            padding: 15px 30px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .cta-buttons a:hover {
            background-color: #007bff; /* Hover effect for the buttons */
            color: #ffffff;
        }

        /* Small Device Adjustments */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            .logo {
                margin-bottom: 10px;
            }

            nav ul {
                flex-direction: column;
                gap: 10px;
            }

            nav ul li {
                font-size: 1.1rem;
            }

            .hero {
                padding: 50px 20px;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .cta-buttons a {
                padding: 10px 20px;
                font-size: 1.2rem;
            }
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
                <li><a href="pages/contact.php">Contact</a></li>
                <li><a href="login.php" style="color: #FFD700;">Login</a></li>
                <li><a href="register.php" style="color: #FFD700;">Register</a></li>
            </ul>
        </nav>
    </header>
</body>
</html>
