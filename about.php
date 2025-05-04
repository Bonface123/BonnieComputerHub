<?php include 'includes/header.php'; ?>

<main id="main-content" tabindex="-1" role="main">
    <!-- Hero/About Banner -->
    <section class="container mx-auto py-12 px-4 sm:px-8 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md mt-16 mb-12 flex flex-col items-center text-center">
        <h1 class="text-4xl sm:text-5xl font-bold text-primary mb-4">About Bonnie Computer Hub</h1>
        <p class="text-lg text-blue-900 max-w-2xl mx-auto mb-6">Empowering individuals and businesses through technology education, services, and solutions. We are passionate about delivering exceptional quality, fostering innovation, and shaping the future of tech talent in Africa and beyond.</p>
        <img src="assets/images/BCH.jpg" alt="Bonnie Computer Hub team at work" class="h-32 w-32 rounded-full object-cover shadow-lg border-4 border-yellow-400 mx-auto mb-4" />
    </section>

    <!-- Our Mission, Vision, and Values -->
    <section class="container mx-auto py-12 px-4 sm:px-8 grid md:grid-cols-3 gap-10 mb-16">
        <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col items-center">
            <h2 class="text-2xl font-bold text-yellow-600 mb-2">Our Mission</h2>
            <p class="text-gray-700">To empower learners and organizations with practical digital skills and innovative software solutions for real-world impact.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col items-center">
            <h2 class="text-2xl font-bold text-yellow-600 mb-2">Our Vision</h2>
            <p class="text-gray-700">To be Africa’s leading hub for tech education and digital transformation, inspiring the next generation of creators and problem-solvers.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-8 flex flex-col items-center">
            <h2 class="text-2xl font-bold text-yellow-600 mb-2">Our Values</h2>
            <ul class="list-disc list-inside text-gray-700 text-left">
                <li>Excellence & Innovation</li>
                <li>Integrity & Inclusion</li>
                <li>Empowerment & Collaboration</li>
                <li>Community Impact</li>
            </ul>
        </div>
    </section>

    <!-- Why Choose Us / Unique Selling Points -->
    <section class="container mx-auto py-12 px-4 sm:px-8 mb-16">
        <h2 class="text-3xl font-bold text-primary text-center mb-8">Why Choose Bonnie Computer Hub?</h2>
        <div class="grid md:grid-cols-3 gap-10">
            <div class="bg-blue-50 rounded-2xl shadow-md p-8 flex flex-col items-center">
                <i class="fas fa-chalkboard-teacher text-yellow-600 text-4xl mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Expert Instructors</h3>
                <p class="text-gray-700 text-center">Learn from experienced professionals who are passionate about teaching and mentoring.</p>
            </div>
            <div class="bg-blue-50 rounded-2xl shadow-md p-8 flex flex-col items-center">
                <i class="fas fa-rocket text-yellow-600 text-4xl mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Hands-on Learning</h3>
                <p class="text-gray-700 text-center">Our programs emphasize practical skills, real projects, and active learning for career readiness.</p>
            </div>
            <div class="bg-blue-50 rounded-2xl shadow-md p-8 flex flex-col items-center">
                <i class="fas fa-users text-yellow-600 text-4xl mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Supportive Community</h3>
                <p class="text-gray-700 text-center">Join a vibrant network of learners, alumni, and industry partners for growth and collaboration.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="container mx-auto py-12 px-4 sm:px-8 text-center mb-16">
        <h2 class="text-3xl font-bold text-primary mb-4">Ready to Start Your Tech Journey?</h2>
        <p class="text-lg text-blue-900 mb-6">Explore our courses or get in touch to discover how we can help you achieve your goals.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="LMS/pages/courses.php" class="bch-btn-primary px-8 py-3 rounded-md text-base font-semibold focus:outline-none focus:ring-4 focus:ring-yellow-200 transition-all shadow-md" aria-label="Explore our courses">Explore Courses</a>
            <a href="index.php#contact" class="bch-btn-outline px-8 py-3 rounded-md text-base font-semibold focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all" aria-label="Contact us">Contact Us</a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
