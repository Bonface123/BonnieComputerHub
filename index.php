<?php include 'includes/header.php'; ?>

<main id="main-content" tabindex="-1" role="main">
<?php
require_once __DIR__ . '/LMS/includes/db_connect.php';
$today = date('Y-m-d');
$upcoming_stmt = $pdo->prepare("SELECT * FROM courses WHERE status = 'active' AND next_intake_date IS NOT NULL AND next_intake_date > ? ORDER BY next_intake_date ASC LIMIT 6");
$upcoming_stmt->execute([$today]);
$upcoming_courses = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section with Dual CTAs -->
<section class="relative container mx-auto mb-12 py-12 px-4 sm:px-8 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md mt-16 overflow-hidden flex flex-col justify-center items-center" aria-labelledby="hero-title">
    <h2 id="hero-title" class="sr-only">Hero Section</h2>
    <!-- Video Background (Optional) -->
    <div class="absolute inset-0 opacity-15 pointer-events-none rounded-2xl overflow-hidden">
        <video class="w-full h-full object-cover rounded-2xl" autoplay loop muted playsinline aria-label="Background video showing coding activity">
    <source src="assets/videos/coding-bg.mp4" type="video/mp4">
</video>
    </div>
    <!-- Content Container -->
    <div class="relative z-10 w-full max-w-3xl mx-auto flex flex-col items-center text-center">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 text-primary leading-tight flex flex-col items-center gap-2" id="hero-title-visible">
            Learn. Build. <span class="text-yellow-600">Grow.</span>
        </h1>
        <p class="text-base sm:text-lg md:text-xl text-gray-500 mb-8 max-w-2xl mx-auto">
            Master digital skills and get tailor-made software solutions under one roof.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6 mb-8">
            <button type="button" onclick="openApplyModal()" class="px-8 py-3 rounded-md text-base font-semibold bg-[#002147] text-white hover:bg-[#E6B800] hover:text-[#002147] focus:outline-none focus:ring-4 focus:ring-[#FFD700] transition-all shadow-md" aria-label="Join our class">
                Join Our Class
            </button>
            <a href="services.php" class="px-8 py-3 rounded-md text-base font-semibold border-2 border-[#002147] text-[#002147] hover:border-[#E6B800] hover:text-[#E6B800] bg-white focus:outline-none focus:ring-2 focus:ring-[#002147] transition-all" aria-label="Explore our services">
                Explore Our Services
            </a>
        </div>
        <!-- Scroll Indicator -->
        <div class="flex justify-center mt-4 animate-bounce">
            <a href="#courses" class="flex flex-col items-center opacity-80 hover:opacity-100 transition scroll-indicator-link text-[#FFD700]" aria-label="Scroll down to learn more">
                <span class="text-sm mb-2 text-[#1E3A8A]">Scroll Down</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="#E6B800">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </a>
        </div>
    </div>
    <!-- Decorative Elements -->
    <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-[#0F172A] to-transparent rounded-b-2xl"></div>
</section>

<?php if ($upcoming_courses): ?>
<section class="container mx-auto mb-12 py-8 px-2 sm:px-8 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md mt-16" aria-labelledby="upcoming-courses-title">
    <h2 id="upcoming-courses-title" class="sr-only">Upcoming Course Intakes</h2> <!-- Visually hidden heading for screen readers -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-primary flex items-center gap-2" id="upcoming-courses-title-visible">
    <i class="fas fa-calendar-alt text-secondary" aria-hidden="true"></i> Upcoming Course Intakes
</h2>
        <a href="LMS/pages/courses.php#course-catalog" class="text-blue-700 hover:underline font-medium text-sm">See All Courses</a>
    </div>
    <div class="flex gap-6 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-blue-200">
        <?php foreach ($upcoming_courses as $uc): ?>
        <div class="min-w-[270px] max-w-xs bg-white border border-blue-100 rounded-2xl shadow-md hover:shadow-xl transition-all duration-200 p-6 flex flex-col justify-between group focus-within:ring-4 focus-within:ring-yellow-200">
            <div class="mb-3">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold"><i class="fas fa-clock mr-1"></i> <?= htmlspecialchars(date('M j, Y', strtotime($uc['next_intake_date']))) ?></span>
                    <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-semibold"><i class="fas fa-graduation-cap mr-1"></i> <?= htmlspecialchars($uc['skill_level'] ?? 'Beginner') ?></span>
                </div>
                <h3 class="text-lg font-bold text-primary mb-1 truncate" title="<?= htmlspecialchars($uc['course_name']) ?>"><?= htmlspecialchars($uc['course_name']) ?></h3>
                <div class="text-xs text-gray-600 mb-2">
                    <?= htmlspecialchars_decode(mb_strimwidth(strip_tags($uc['description'], '<b><i><strong><em><ul><ol><li><br>'), 0, 100, '...')) ?>
                </div>
                <div class="flex gap-2 mt-2">
                    <a href="LMS/pages/course_detail.php?id=<?= $uc['id'] ?>" class="bch-btn-outline px-4 py-1 rounded-md text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all" aria-label="Read more about <?= htmlspecialchars($uc['course_name']) ?>">Read More</a>
                </div>
            </div>
            <div class="flex flex-col gap-2 mt-3">
                <a href="LMS/pages/course_detail.php?id=<?= $uc['id'] ?>" class="bch-btn-primary text-center rounded-md py-2 px-4 font-semibold text-sm shadow-sm hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all" aria-label="View details for <?= htmlspecialchars($uc['course_name']) ?>">View Course</a>
                <button type="button" onclick="openApplyModal(<?= $uc['id'] ?>)" class="w-full bg-yellow-500 text-primary font-semibold py-2 rounded-md hover:bg-yellow-400 transition text-center focus:outline-none focus:ring-4 focus:ring-yellow-300 shadow-sm" aria-label="Apply for <?= htmlspecialchars($uc['course_name']) ?>">Apply</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>


    

<!-- Swiper & AOS Init Scripts, Mobile Menu Toggle, Custom Scrollbar Styles (only one instance) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper(".mySwiper", {
            loop: true,
            autoplay: {
                delay: 7000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
        AOS.init();
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function () {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        // Smooth scroll for scroll indicator
        var scrollIndicator = document.querySelector('.scroll-indicator-link');
        if (scrollIndicator) {
            scrollIndicator.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.getElementById('services');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                } else {
                    // fallback to default anchor if #services not found
                    window.location.hash = 'services';
                }
            });
        }
    });
</script>

  </script>
    <!--contact Section -->
<section id="contact" class="py-20 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md mt-16 mb-12">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl sm:text-5xl font-bold text-primary text-center mb-12" data-aos="fade-up">
            <span class="text-yellow-600">Let’s Connect</span>
        </h2>

        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- Contact Form -->
                <div class="bch-card shadow-lg" data-aos="fade-up">
                    <form action="https://formspree.io/f/xkgwarar" method="POST" class="space-y-6" aria-label="Contact form">
                        <div class="relative">
                            <label for="name" class="sr-only">Your Name</label>
                            <input type="text" id="name" name="name" required placeholder="Your Name" autocomplete="name" class="bch-input w-full px-4 py-3 rounded-lg border border-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
                            <div class="absolute top-3.5 left-4 text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <label for="email" class="sr-only">Email</label>
                            <input type="email" id="email" name="email" required placeholder="Your Email" autocomplete="email" class="bch-input w-full px-4 py-3 rounded-lg border border-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition">
                            <div class="absolute top-3.5 left-4 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <label for="message" class="sr-only">Message</label>
                            <textarea id="message" name="message" rows="4" required placeholder="Your Message" class="bch-input w-full px-4 py-3 rounded-lg border border-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition resize-none"></textarea>
                            <div class="absolute top-4 left-4 text-gray-400">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                        </div>

                        <button type="submit" class="bch-btn-primary w-full py-3 rounded-lg font-bold text-lg focus:outline-none focus:ring-4 focus:ring-yellow-300 transition" aria-label="Send your message">
                            Send Message
                        </button>

                        <div id="form-success" class="hidden text-green-500 text-center mt-4 font-medium">
                            ✅ Message sent successfully!
                        </div>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="bch-card p-8 text-blue-900" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold mb-6">Get in Touch</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bch-badge-gold p-3 rounded-full shadow-lg">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1">Location</h4>
                                <p>Kenya</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bch-badge-gold p-3 rounded-full shadow-lg">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1">Phone</h4>
                                <a href="tel:+254729820689" class="hover:text-bch-dark-blue">+254 729 820 689</a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bch-badge-gold p-3 rounded-full shadow-lg">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1">Email</h4>
                                <a href="mailto:bonniecomputerhub24@gmail.com" class="hover:text-bch-dark-blue">bonniecomputerhub24@gmail.com</a>
                            </div>
                        </div>

                        <div class="pt-6">
                            <h4 class="font-semibold mb-4">Follow Us</h4>
                            <div class="flex space-x-4">
                                <a href="https://www.linkedin.com/in/bonniecomputerhub-273753307/" target="_blank"
                                   class="group relative">
                                    <i class="fab fa-linkedin-in bg-primary text-white p-3 rounded-full group-hover:bg-bch-gold-dark transition duration-300"></i>
                                    <span class="absolute hidden group-hover:block text-sm text-white bg-black px-2 py-1 rounded -top-10 left-1/2 -translate-x-1/2">LinkedIn</span>
                                </a>
                                <a href="https://github.com/bonniecomputerhub24" target="_blank"
                                   class="group relative">
                                    <i class="fab fa-github bg-primary text-white p-3 rounded-full group-hover:bg-bch-gold-dark transition duration-300"></i>
                                    <span class="absolute hidden group-hover:block text-sm text-white bg-black px-2 py-1 rounded -top-10 left-1/2 -translate-x-1/2">GitHub</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>
</main>


    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/apply-modal.js?v=20250422"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
<script src="assets/js/apply-modal.js"></script>
<script>
// BCH Read More/Show Less toggle for course cards
function toggleCourseDesc(courseId, showFull) {
    var shortEl = document.getElementById('desc-short-' + courseId);
    var fullEl = document.getElementById('desc-full-' + courseId);
    if (!shortEl || !fullEl) return;
    if (showFull) {
        shortEl.style.display = 'none';
        fullEl.style.display = '';
        // Accessibility
        var btn = fullEl.querySelector('.bch-showless-btn');
        if (btn) btn.focus();
        var readBtn = shortEl.querySelector('.bch-readmore-btn');
        if (readBtn) readBtn.setAttribute('aria-expanded', 'true');
    } else {
        shortEl.style.display = '';
        fullEl.style.display = 'none';
        var btn = shortEl.querySelector('.bch-readmore-btn');
        if (btn) btn.focus();
        var showBtn = fullEl.querySelector('.bch-showless-btn');
        if (showBtn) showBtn.setAttribute('aria-expanded', 'false');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bch-readmore-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var courseId = btn.getAttribute('data-course-id');
            toggleCourseDesc(courseId, true);
        });
    });
    document.querySelectorAll('.bch-showless-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var courseId = btn.getAttribute('data-course-id');
            toggleCourseDesc(courseId, false);
        });
    });
});
</script>
</body>
</html>