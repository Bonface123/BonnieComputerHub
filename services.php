
<?php include 'includes/header.php'; ?>

<main id="main-content" tabindex="-1">
  
    <!-- Hero Section -->
    <section class="w-full bg-gradient-to-b from-blue-500 to-blue-700 text-white py-10 text-center animate-fade-in">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-2 tracking-tight">We Teach. We Build.</h1>
        <p class="text-lg md:text-xl mb-4 font-medium">Empowering you to learn web development and offering professional web solutions for your business.</p>
    </section>
    <!-- Services & Classes Section -->
    <section class="max-w-6xl mx-auto flex flex-col md:flex-row gap-8 my-12 px-4">
        <!-- Web Development Services Card -->
        <div class="bch-card bg-white shadow-md rounded-lg flex-1 animate-slide-up focus-within:bch-outline" aria-labelledby="services-title">
            <h2 id="services-title" class="text-2xl md:text-3xl font-bold mb-3 text-blue-500">Web Development Services</h2>
            <ul class="list-disc pl-5 mb-4 text-gray-700 text-base md:text-lg">
                <li>Custom Website Design & Development</li>
                <li>E-commerce Solutions</li>
                <li>Website Maintenance & Optimization</li>
                <li>Landing Pages & Microsites</li>
                <li>Content Management Systems (CMS)</li>
                <li>Responsive & Accessible Web Apps</li>
            </ul>
            <a href="#contact" class="bch-btn-primary bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg w-full md:w-auto transition duration-300 shadow-md focus:bch-outline" aria-label="Inquire about web development services">Request a Quote</a>
        </div>
        <!-- Web Development Classes Card -->
        <div class="bch-card bg-white shadow-md rounded-lg flex-1 animate-slide-up delay-150 focus-within:bch-outline" aria-labelledby="classes-title">
            <h2 id="classes-title" class="text-2xl md:text-3xl font-bold mb-3 text-blue-500">Web Development Classes</h2>
            <ul class="list-disc pl-5 mb-4 text-gray-700 text-base md:text-lg">
                <li>Beginner to Advanced Web Development</li>
                <li>Real-world Projects & Mentorship</li>
                <li>Flexible Schedules & Online Options</li>
                <li>Certification & Career Support</li>
                <li>Access all courses: <a href="LMS/pages/courses.php" class="text-yellow-600 underline">View Courses</a></li>
            </ul>
            <a href="/BonnieComputerHub/LMS/courses.php" class="bch-btn-outline bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg w-full md:w-auto transition duration-300 shadow-md focus:bch-outline" aria-label="Browse web development courses">View Courses</a>
            <a href="/BonnieComputerHub/LMS/courses.php" class="bch-btn-primary bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg w-full md:w-auto ml-2 transition duration-300 shadow-md focus:bch-outline" aria-label="Enroll in web development classes">Enroll Now</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="max-w-6xl mx-auto my-16 px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 text-blue-500">Why Choose BonnieComputerHub?</h2>
        <div class="grid gap-8 md:grid-cols-3">
            <div class="bch-card bg-white shadow-md rounded-lg flex flex-col items-center text-center animate-fade-in focus-within:bch-outline">
                <span class="text-4xl md:text-5xl mb-2" aria-hidden="true">🎓</span>
                <h3 class="font-semibold text-lg md:text-xl mb-1">Expert Instructors</h3>
                <p class="text-gray-700">Learn from industry professionals with real-world experience.</p>
            </div>
            <div class="bch-card bg-white shadow-md rounded-lg flex flex-col items-center text-center animate-fade-in delay-100 focus-within:bch-outline">
                <span class="text-4xl md:text-5xl mb-2" aria-hidden="true">💡</span>
                <h3 class="font-semibold text-lg md:text-xl mb-1">Hands-on Learning</h3>
                <p class="text-gray-700">Build projects and gain practical skills for today’s tech landscape.</p>
            </div>
            <div class="bch-card bg-white shadow-md rounded-lg flex flex-col items-center text-center animate-fade-in delay-200 focus-within:bch-outline">
                <span class="text-4xl md:text-5xl mb-2" aria-hidden="true">🌐</span>
                <h3 class="font-semibold text-lg md:text-xl mb-1">Modern Tech Stack</h3>
                <p class="text-gray-700">Stay ahead with up-to-date tools and frameworks in our curriculum and services.</p>
            </div>
        </div>
    </section>
    <!-- Contact/CTA Section -->
    <section id="contact" class="w-full bg-gradient-to-b from-blue-500 to-blue-700 text-white py-10 mt-14 text-center animate-fade-in">
        <h2 class="text-2xl md:text-3xl font-bold mb-2">Ready to Start?</h2>
        <p class="mb-4 text-lg md:text-xl font-medium">Whether you want to build your website or learn to code, we’re here to help you succeed.</p>
        <a href="mailto:info@bonniecomputerhub.com" class="bch-btn-primary bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg w-full md:w-auto transition duration-300 shadow-lg focus:bch-outline" aria-label="Contact BonnieComputerHub">Contact Us</a>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
