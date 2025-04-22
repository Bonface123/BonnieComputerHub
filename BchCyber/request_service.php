<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Service | BCH Cyber Services</title>
    <style>
        /* General Reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Arial', sans-serif;
  line-height: 1.6;
  background-color: #f4f4f4;
  color: #333;
  padding: 0;
  margin: 0;
}

/* General Reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Arial', sans-serif;
  line-height: 1.6;
  background-color: #f9f9f9; /* Light background for readability */
  color: #333;
  padding: 0;
  margin: 0;
}

/* Header Section */
header {
  background: linear-gradient(to right, #003366, #002244); /* BCH blue shades */
  color: white;
  padding: 20px;
  text-align: center;
}

header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

header .logo h1 {
  font-size: 2em;
  font-weight: bold;
  color: #ffcc00; /* Golden accent for logo */
}

header nav ul {
  list-style: none;
  display: flex;
  gap: 20px;
}

header nav ul li a {
  text-decoration: none;
  color: white;
  font-weight: bold;
  font-size: 1em;
  padding: 5px 10px;
  border-radius: 5px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

header nav ul li a:hover,
header nav ul li a.active {
  background-color: #ffcc00; /* Golden hover */
  color: #003366;
}

/* Service Request Section */
#service-request {
  background-color: white;
  max-width: 900px;
  margin: 40px auto;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border: 2px solid #003366; /* Blue border for brand alignment */
}

#service-request h2 {
  text-align: center;
  color: #003366;
  font-size: 2.2em;
  margin-bottom: 20px;
  font-weight: bold;
}

#service-request form label {
  display: block;
  font-weight: bold;
  margin-bottom: 8px;
  color: #333;
}

#service-request form input,
#service-request form select,
#service-request form textarea {
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1em;
  transition: border-color 0.3s ease;
}

#service-request form input:focus,
#service-request form select:focus,
#service-request form textarea:focus {
  border-color: #003366; /* Focused border color */
  outline: none;
}

#service-request form button {
  display: block;
  width: 100%;
  background-color: #003366;
  color: #ffcc00;
  font-size: 1.2em;
  font-weight: bold;
  padding: 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease, color 0.3s ease;
}

#service-request form button:hover {
  background-color: #ffcc00;
  color: #003366;
}

/* Confirmation Message */
.confirmation-message {
  max-width: 900px;
  margin: 20px auto;
  padding: 15px;
  background-color: #e6f7ff; /* Light blue for success */
  border: 1px solid #003366;
  color: #003366;
  border-radius: 5px;
  font-size: 1em;
  text-align: center;
}

/* Footer Section */
footer {
  background-color: #003366;
  color: white;
  text-align: center;
  padding: 20px;
}

footer p {
  font-size: 0.9em;
  margin: 5px 0;
}

footer p.text-gray-400 {
  color: #ffcc00; /* Golden text for the Bible verse */
  font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
  header .container {
    flex-direction: column;
  }

  header nav ul {
    flex-direction: column;
    gap: 10px;
  }

  #service-request {
    padding: 20px;
  }

  #service-request h2 {
    font-size: 1.8em;
  }
}

    </style>
    
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <h1>BCH Cyber Services</h1>
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

<section id="service-request">
    <h2>Request a Service</h2>
    <form action="https://formspree.io/f/xkgwarar" method="POST" id="">
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


<footer>
    <p class="text-gray-400 italic mb-4">"I can do all things through Christ who strengthens me." - Philippians 4:13</p>
    <p>&copy; 2024 BCH Cyber Services | All Rights Reserved</p>
</footer>
</body>
</html>
