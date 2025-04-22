<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
}

/* Header Section */
header {
  background-color: #003366; /* Hero background color */
  color: white;
  text-align: center;
  padding: 40px 20px;
  font-size: 18px;
}

header p {
  font-weight: 300;
  margin: 0;
}

/* Contact Us Section */
#contact {
  background-color: #ffffff;
  padding: 60px 20px;
  text-align: center;
}

#contact h2 {
  font-size: 36px;
  color: #002f6c; /* Dark blue for consistency */
  margin-bottom: 30px;
}

form {
  max-width: 600px;
  margin: 0 auto;
  display: grid;
  gap: 20px;
}

form input,
form textarea {
  width: 100%;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  color: #333;
  transition: border-color 0.3s ease;
}

form input:focus,
form textarea:focus {
  border-color: #ffab00; /* Gold color on focus */
  outline: none;
}

form button {
  padding: 15px;
  background-color: #ffab00;
  color: #000;
  font-size: 18px;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

form button:hover {
  background-color: #f77f00;
  transform: scale(1.05); /* Slightly increase the size of the button on hover */
}

/* Contact Info */
.contact-info {
  margin-top: 40px;
  font-size: 18px;
  color: #333;
}

.contact-info p {
  margin: 10px 0;
}

.contact-info strong {
  color: #002f6c;
}

/* Responsive Design */
@media (max-width: 768px) {
  #contact h2 {
    font-size: 30px;
  }

  form input,
  form textarea {
    font-size: 14px;
    padding: 12px;
  }

  form button {
    font-size: 16px;
    padding: 12px;
  }

  .contact-info {
    font-size: 16px;
  }
}

    </style>
    
    
</head>
<body>
    <header>
        <p>If you have any questions or inquiries, please reach out to us using the contact form below.</p>
    </header>
    
    <!-- Contact Us Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <form action="https://formspree.io/f/xkgwarar" method="POST" id="contactForm">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                <button type="submit">Send Message</button>
            </form>
            <div class="contact-info">
                <p><strong>Email:</strong> Bonniecomputerhub24@gmail.com</p>
                <p><strong>Phone:</strong> +254729820689</p>
            </div>
        </div>
    </section>
</body>
</html>
