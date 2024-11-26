<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333;
        }
        
        header {
            background-color: #1e1e1e;
            color: #ffa500;
            padding: 10px 20px;
            text-align: center;
        }
        
        header p {
            margin: 0;
            font-size: 1.2rem;
        }
        
        /* Contact Section */
        .contact {
            padding: 60px 20px;
            background-color: #f9f9f9;
        }
        
        .contact h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            color: #1e1e1e;
        }
        
        .contact form {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 600px;
            margin: 0 auto;
            gap: 20px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .contact input,
        .contact textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .contact input:focus,
        .contact textarea:focus {
            border-color: #ffa500;
            outline: none;
        }
        
        .contact button {
            padding: 15px 30px;
            border: none;
            background-color: #ffa500; /* Golden/Orange */
            color: #1e1e1e; /* Black */
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .contact button:hover {
            background-color: #ff8c00; /* Darker Orange */
        }
        
        .contact-info {
            margin-top: 20px;
            text-align: center;
        }
        
        .contact-info p {
            margin: 5px 0;
            font-size: 1rem;
        }
        
        .contact-info strong {
            color: #1e1e1e;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact h2 {
                font-size: 2rem;
            }
            
            .contact form {
                padding: 15px;
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
