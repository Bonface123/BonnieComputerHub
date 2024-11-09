<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9; /* Light background */
            color: #333; /* Dark text for contrast */
            margin: 0;
            padding: 20px;
        }

        header {
            text-align: center;
            padding: 20px 0;
        }

        h1 {
            color: #0056b3; /* Primary brand color (blue) */
        }

        p {
            margin: 10px 0;
        }

        form {
            background-color: #ffffff; /* White background for form */
            border: 1px solid #ccc; /* Light border */
            border-radius: 5px; /* Rounded corners */
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            max-width: 600px; /* Limit form width */
            margin: 0 auto; /* Center form */
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold; /* Bold labels */
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%; /* Full width inputs */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc; /* Light border */
            border-radius: 4px; /* Rounded corners */
            font-size: 16px; /* Font size */
        }

        button {
            background-color: #ffd700; /* Gold button color */
            color: black; /* Black text */
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 16px; /* Font size */
        }

        button:hover {
            background-color: #ffcc00; /* Darker gold on hover */
        }

        .back-link {
            display: inline-block; /* Block level for padding */
            margin-top: 20px;
            text-decoration: none;
            color: #0056b3; /* Brand color (blue) */
            font-weight: bold; /* Bold link */
            padding: 10px 15px; /* Padding for link */
            border: 2px solid #0056b3; /* Blue border */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s, color 0.3s; /* Smooth transition */
        }

        .back-link:hover {
            background-color: #0056b3; /* Change background color on hover */
            color: #ffffff; /* Change text color to white on hover */
        }
    </style>
</head>
<body>
    <header>
        <h1>Contact Us</h1>
        <p>If you have any questions or inquiries, please reach out to us using the contact form below.</p>
    </header>
    
    <form action="https://formspree.io/f/xkgwarar" method="POST" id="contactForm">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="message">Message:</label>
        <textarea id="message" name="message" required></textarea>

        <button type="submit">Send Message</button>
        <a href="index.html" class="back-link">Back to Home</a>
    </form>

    
</body>
</html>
