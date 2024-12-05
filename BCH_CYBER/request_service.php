<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service = htmlspecialchars($_POST['service']);
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);

    // Process the request (store in database, send email, etc.)
    $message = "Thank you, $name. Your request for $service has been received!";
    // In a real-world scenario, you'd also send an email or save the data to a database.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Request Service | BCH Cyber Services</title>
    <style>
    /* General Reset */
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        line-height: 1.6;
        background-color: #f4f4f9; /* Light gray for contrast */
        color: #333;
    }

    /* Header Styling */
    header {
        background-color: #003366; /* BCH Blue */
        color: white;
        padding: 20px 0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    header .container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    header h1 a {
        font-size: 2rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    nav ul {
        list-style: none;
        padding: 0;
        display: flex;
        margin: 0;
    }

    nav ul li {
        margin-right: 20px;
    }

    nav ul li a {
        color: white;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1rem;
        transition: color 0.3s;
    }

    nav ul li a:hover {
        color: #FFD700; /* BCH Gold on hover */
    }

    /* Section Styling */
    #service-request {
        max-width: 600px;
        margin: 50px auto;
        background-color: white;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        border: 1px solid #003366; /* Blue border for branding */
    }

    #service-request label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #003366; /* BCH Blue */
    }

    #service-request select,
    #service-request input,
    #service-request textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1rem;
        font-family: Arial, sans-serif;
    }

    #service-request textarea {
        resize: vertical; /* Allow vertical resizing */
        min-height: 100px; /* Ensure it's visually balanced */
    }

    #service-request button {
        width: 100%;
        padding: 10px;
        background-color: #FFB700; /* BCH Blue */
        color: white;
        border: none;
        font-size: 1.2rem;
        font-weight: bold;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    #service-request button:hover {
        background-color: #002244; /* Darker Blue */
        transform: translateY(-2px);
    }

    #service-request button:active {
        background-color: #001122; /* Even Darker Blue */
        transform: translateY(0);
    }

    /* Confirmation Message */
    .confirmation-message {
        background-color: #FFD700; /* BCH Gold */
        color: #003366; /* BCH Blue */
        border: 1px solid #003366;
        padding: 15px;
        border-radius: 4px;
        margin-top: 20px;
        font-weight: bold;
        text-align: center;
    }

    /* Footer Styling */
    footer {
        text-align: center;
        padding: 20px 0;
        background-color: #003366; /* BCH Blue */
        color: white;
        margin-top: 50px;
    }

    footer p {
        margin: 0;
        font-size: 1rem;
    }
</style>


    <script>
        function validateForm() {
            var name = document.getElementById("name").value;
            var email = document.getElementById("email").value;
            var service = document.getElementById("service").value;

            if (name == "" || email == "" || service == "") {
                alert("All fields are required!");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <h1><a href="index.html" style="color: #fff; text-decoration: none;">BCH Cyber Services</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="services.html">Our Services</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
    </div>
</header>


<!-- Service Request Form -->
<section id="service-request">
    <form action="request_service.php" method="POST" onsubmit="return validateForm()">
        <label for="service">Select Service:</label>
        <select name="service" id="service" required>
            <option value="">-- Select a Service --</option>
            <option value="Loan Application">Loan Application</option>
            <option value="Driving License">Driving License</option>
            <option value="Photocopying & Printing">Photocopying & Printing</option>
            <option value="Typing Services">Typing Services</option>
            <option value="Tax Compliance Certificate Application">Tax Compliance Certificate Application</option>
            <option value="Payslip Processing">Payslip Processing</option>
            <option value="ID Replacement Application">ID Replacement Application</option>
            <option value="Birth Certificate Application">Birth Certificate Application</option>
            <option value="Passport Application">Passport Application</option>
            <option value="KRA PIN Registration">KRA PIN Registration</option>
            <option value="Online Job Applications">Online Job Applications</option>
            <option value="Document Scanning">Document Scanning</option>
            <option value="Graphic Design Services">Graphic Design Services</option>
            <option value="Data Entry Services">Data Entry Services</option>
        </select>

        <label for="name">Your Name:</label>
        <input type="text" name="name" id="name" placeholder="Enter your full name" required>

        <label for="email">Your Email:</label>
        <input type="email" name="email" id="email" placeholder="Enter your email address" required>

        <label for="phone">Your Phone Number:</label>
        <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" pattern="[+0-9]{10,15}" required>
        <!-- Phone pattern allows for numbers with +, -, and digits -->

        <label for="message">Additional Information (Optional):</label>
        <textarea name="message" id="message" rows="4" placeholder="Provide additional details about your request (optional)"></textarea>

        <button type="submit">Submit Request</button>
    </form>
</section>


        <?php if (isset($message)): ?>
            <div class="confirmation-message">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
    </section>

    <footer>
        <p>&copy; 2024 BCH Cyber Services | All Rights Reserved</p>
    </footer>
</body>
</html>
