<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="styles.css">
    <style>
         p {
            margin: 10px 0;
            text-align: center;
        }
   /* Contact Section */
.contact {
  padding: 60px 0;
  background-color: #f9f9f9;
}

.contact h2 {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 40px;
}

.contact form {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 600px;
  margin: 0 auto;
  gap: 20px;
}

.contact input,
.contact textarea {
  width: 100%;
  padding: 10px;
  border: 2px solid #ccc;
  border-radius: 5px;
  font-size: 1rem;
}

.contact button {
  padding: 10px 20px;
  border: none;
  background-color: #ffa500; /* Golden/Orange */
  color: #1e1e1e; /* Black */
  font-size: 1rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.contact button:hover {
  background-color: #ff8c00; /* Darker orange */
}

.contact-info {
  margin-top: 20px;
  text-align: center;
}

    </style>
</head>
<body>
    <header>
        <p >If you have any questions or inquiries, please reach out to us using the contact form below.</p>
    </header>
    
    <!-- Contact Us Section -->
<section id="contact" class="contact">
  <div class="container">
    <h2>Contact Us</h2>
    <form action="https://formspree.io/f/xkgwarar" method="POST" id="contactForm">
      <input type="text" name="name" placeholder="Your Name" required>
      <input type="email" name="email" placeholder="Your Email" required>
      <textarea name="message" placeholder="Your Message" required></textarea>
      <button type="submit">Send Message</button>
    </form>
    <div class="contact-info">
      <p><strong>Email:</strong>Bonniecomputerhub24@gmail.com</p>
      <p><strong>Phone:</strong> +254729820689</p>
    </div>
  </div>
</section>

    
</body>
</html>
