<?php include 'includes/header.php'; ?>

<main id="main-content" tabindex="-1" role="main">
    <!-- Hero Section -->
    <section class="w-full bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 text-[#1E40AF] py-12 text-center rounded-2xl shadow-md mt-16 mb-12" aria-labelledby="services-hero-title">
        <h1 id="services-hero-title" class="text-4xl md:text-5xl font-extrabold mb-2 tracking-tight text-[#1E40AF]">
            We Teach. We Build.
        </h1>
        <p class="text-lg md:text-xl mb-4 font-medium text-[#1E3A8A]">Empowering you to learn web development and offering professional web solutions for your business.</p>
    </section>
    <!-- Services & Classes Section -->
    <section class="max-w-6xl mx-auto flex flex-col md:flex-row gap-8 my-12 px-4" aria-label="Our Services and Classes">
        <!-- Web Development Services Card -->
        <div class="bch-card bg-white shadow-lg rounded-2xl flex-1 focus-within:outline-[#FFD700]" aria-labelledby="services-title">
            <h2 id="services-title" class="text-2xl md:text-3xl font-bold mb-3 text-[#1E40AF]">Web Development Services</h2>
            <ul class="list-disc pl-5 mb-4 text-[#1E3A8A] text-base md:text-lg">
                <li>Custom Website Design & Development</li>
                <li>E-commerce Solutions</li>
                <li>Website Maintenance & Optimization</li>
                <li>Landing Pages & Microsites</li>
                <li>Content Management Systems (CMS)</li>
                <li>Responsive & Accessible Web Apps</li>
            </ul>
            <a href="#contact" class="bch-btn-primary bg-[#1E40AF] text-white hover:bg-[#FFD700] hover:text-[#1E40AF] font-bold py-2 px-4 rounded-lg w-full md:w-auto transition duration-300 shadow-md focus:outline-[#FFD700]" aria-label="Inquire about web development services">Request a Quote</a>
        </div>
        <!-- Web Development Classes Card -->
        <div class="bch-card bg-white shadow-lg rounded-2xl flex-1 focus-within:outline-[#FFD700]" aria-labelledby="classes-title">
            <h2 id="classes-title" class="text-2xl md:text-3xl font-bold mb-3 text-[#1E40AF]">Web Development Classes</h2>
            <ul class="list-disc pl-5 mb-4 text-[#1E3A8A] text-base md:text-lg">
                <li>Beginner to Advanced Web Development</li>
                <li>Real-world Projects & Mentorship</li>
                <li>Flexible Schedules & Online Options</li>
                <li>Certification & Career Support</li>
                <li>Access all courses: <a href="LMS/pages/courses.php" class="text-[#FFD700] underline">View Courses</a></li>
            </ul>
            <a href="/BonnieComputerHub/LMS/courses.php" class="bch-btn-outline border-2 border-[#FFD700] text-[#1E40AF] font-bold py-2 px-4 rounded-lg w-full md:w-auto transition duration-300 shadow-md hover:bg-[#FFD700] hover:text-[#1E40AF] focus:outline-[#FFD700]" aria-label="Browse web development courses">View Courses</a>
            <a href="/BonnieComputerHub/LMS/courses.php" class="bch-btn-primary bg-[#1E40AF] text-white hover:bg-[#FFD700] hover:text-[#1E40AF] font-bold py-2 px-4 rounded-lg w-full md:w-auto ml-2 transition duration-300 shadow-md focus:outline-[#FFD700]" aria-label="Enroll in web development classes">Enroll Now</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="max-w-6xl mx-auto my-16 px-4" aria-label="Why Choose Us">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 text-[#1E40AF]">Why Choose Bonnie Computer Hub?</h2>
        <div class="grid gap-8 md:grid-cols-3">
            <div class="bch-card bg-white shadow-lg rounded-2xl flex flex-col items-center text-center focus-within:outline-[#FFD700]">
                <span class="text-4xl md:text-5xl mb-2 text-[#FFD700]" aria-hidden="true"><i class="fas fa-user-tie"></i></span>
                <h3 class="font-semibold text-lg md:text-xl mb-1 text-[#1E3A8A]">Expert Instructors</h3>
                <p class="text-[#6b7280]">Learn from industry professionals with real-world experience.</p>
            </div>
            <div class="bch-card bg-white shadow-lg rounded-2xl flex flex-col items-center text-center focus-within:outline-[#FFD700]">
                <span class="text-4xl md:text-5xl mb-2 text-[#FFD700]" aria-hidden="true"><i class="fas fa-lightbulb"></i></span>
                <h3 class="font-semibold text-lg md:text-xl mb-1 text-[#1E3A8A]">Hands-on Learning</h3>
                <p class="text-[#6b7280]">Build projects and gain practical skills for today’s tech landscape.</p>
            </div>
            <div class="bch-card bg-white shadow-lg rounded-2xl flex flex-col items-center text-center focus-within:outline-[#FFD700]">
                <span class="text-4xl md:text-5xl mb-2 text-[#FFD700]" aria-hidden="true"><i class="fas fa-globe"></i></span>
                <h3 class="font-semibold text-lg md:text-xl mb-1 text-[#1E3A8A]">Modern Tech Stack</h3>
                <p class="text-[#6b7280]">Stay ahead with up-to-date tools and frameworks in our curriculum and services.</p>
            </div>
        </div>
    </section>
    <!-- Contact/CTA Section -->
    <section id="contact" class="w-full bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 text-[#1E40AF] py-12 mt-14 text-center rounded-2xl shadow-md" aria-labelledby="cta-title">
        <h2 id="cta-title" class="text-2xl md:text-3xl font-bold mb-2 text-[#1E40AF]">Ready to Start?</h2>
        <p class="mb-4 text-lg md:text-xl font-medium text-[#1E3A8A]">Whether you want to build your website or learn to code, we’re here to help you succeed.</p>
        <a href="mailto:info@bonniecomputerhub.com" class="bch-btn-primary bg-[#FFD700] text-[#1E40AF] hover:bg-[#1E40AF] hover:text-[#FFD700] font-bold py-2 px-4 rounded-lg w-full md:w-auto transition duration-300 shadow-lg focus:outline-[#FFD700]" aria-label="Contact Bonnie Computer Hub">Contact Us</a>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
