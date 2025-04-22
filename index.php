
<?php include 'includes/header.php'; ?>

<?php
require_once __DIR__ . '/LMS/includes/db_connect.php';
$today = date('Y-m-d');
$upcoming_stmt = $pdo->prepare("SELECT * FROM courses WHERE status = 'active' AND next_intake_date IS NOT NULL AND next_intake_date > ? ORDER BY next_intake_date ASC LIMIT 6");
$upcoming_stmt->execute([$today]);
$upcoming_courses = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($upcoming_courses): ?>
<section class="container mx-auto mb-12 py-8 px-2 sm:px-8 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-primary flex items-center gap-2">
            <i class="fas fa-calendar-alt text-secondary"></i> Upcoming Course Intakes
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

<!-- Hero Section with Dual CTAs -->
<section class="relative min-h-[70vh] bg-gradient-to-r from-[#0F172A] to-[#1E293B] text-white overflow-hidden flex flex-col justify-center">
    <!-- Video Background (Optional) -->
    <div class="absolute inset-0 opacity-20">
        <video class="w-full h-full object-cover" autoplay loop muted playsinline>
            <source src="assets/videos/coding-bg.mp4" type="video/mp4">
            <!-- Fallback background if video doesn't load -->
        </video>
    </div>
    
    <!-- Content Container -->
    <div class="relative z-10 container mx-auto px-4 flex-1 flex flex-col justify-center items-center pt-28">
        <div class="text-center w-full max-w-2xl mx-auto flex flex-col items-center" data-aos="fade-up" data-aos-duration="1000">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 text-white leading-tight">
    Learn. Build. <span class="text-yellow-600">Grow.</span>
</h1>
            <p class="text-base sm:text-lg md:text-xl text-gray-500 mb-8 max-w-2xl mx-auto">
    Master digital skills and get tailor-made software solutions under one roof.
</p>
            <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-5 mb-6">
                <button type="button" onclick="openApplyModal()" class="bg-yellow-600 text-bch-gray-900 px-8 py-4 rounded-full font-semibold hover:bg-bch-gold-dark transition duration-300 transform hover:scale-105 shadow-lg focus:outline-none focus:ring-4 focus:ring-yellow-300">Join Our Class</button>
                <a href="#services" class="bg-transparent border-2 border-bch-blue-light text-white px-8 py-4 rounded-full font-semibold hover:bg-bch-blue-light/10 transition duration-300 transform hover:scale-105 shadow-lg">
                    Explore Our Services
                </a>
            </div>
            <!-- Scroll Indicator -->
            <div class="flex justify-center mt-4 animate-bounce">
                <a href="#about" class="text-white flex flex-col items-center opacity-80 hover:opacity-100 transition scroll-indicator-link">
                    <span class="text-sm mb-2">Scroll Down</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    <!-- Decorative Elements -->
    <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-[#0F172A] to-transparent"></div>
</section>

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
<style>
    /* Make scrollbar visible and enhance its appearance */
    ::-webkit-scrollbar {
      width: 8px;
    }
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    ::-webkit-scrollbar-thumb {
      background: #1E40AF;
      border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #1E3A8A;
    }
    /* For Firefox */
    html {
      scrollbar-width: thin;
      scrollbar-color: #1E40AF #f1f1f1;
    }
</style>
  </script>

    <!-- Services Section -
    <section id="services" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12" data-aos="fade-up">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-3">Online Web Development Classes</h3>
                        <p class="text-gray-600 mb-4">Become a full-stack web developer with our hands-on, instructor-led courses. Learn HTML, CSS, JavaScript, React, and more.</p>
                        <a href="LMS/index.php" class="bch-highlight" hover:text-bch-gold-dark flex items-center group">
                            Learn More 
                            <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-2 transition-transform"></i>
                        </a>
                    </div>
                </div>
                <!-- Web Development Classes 
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300" data-aos="fade-up">
                    <img src="assets/images/Classes.png" alt="Web Development Classes" class="w-full h-48 object-cover">

                <!-- Web Development Services 
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                    <img src="assets/images/WebServices.jpg" alt="Web Development Services" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-3">Custom Web Development</h3>
                        <p class="text-gray-600 mb-4">We create custom, responsive websites and web applications designed to meet your unique business needs and help you stand out online.</p>
                        <a href="#contact" class="bch-highlight" hover:text-bch-gold-dark flex items-center group">
                            Learn More 
                            <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-2 transition-transform"></i>
                        </a>
                    </div>
                </div>

                <!-- Cyber Services 
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300" data-aos="fade-up" data-aos-delay="200">
                    <img src="assets/images/CyberCafe.png" alt="Cyber Services" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-3">Cyber Cafe Services</h3>
                        <p class="text-gray-600 mb-4">Professional printing, online applications, document processing, and comprehensive business solutions for all your needs.</p>
                        <a href="#contact" class="bch-highlight" hover:text-bch-gold-dark flex items-center group">
                            Learn More 
                            <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-2 transition-transform"></i>
                        </a>
                    </div>
                </div>

                <!-- Laptop Sales
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300" data-aos="fade-up" data-aos-delay="300">
                    <img src="assets/images/Laptops.jpg" alt="Laptop Sales" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-3">Laptop Sales & Repairs</h3>
                        <p class="text-gray-600 mb-4">Quality laptops at competitive prices plus expert repair services to keep your devices running smoothly.</p>
                        <a href="#contact" class="bch-highlight" hover:text-bch-gold-dark flex items-center group">
                            Shop Now 
                            <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-2 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Team Section 
    <section id="team" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16" data-aos="fade-up">Meet the BCH Team</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16">
                <!-- Onduso Bonface -
                <div class="group" data-aos="fade-up">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Bonnie1.png" alt="Onduso Bonface" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Onduso Bonface</h3>
                                <p class="text-gray-200">Central Manager</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Onduso Bonface</h3>
                        <p class="text-gray-400 text-base">Central Manager</p>
                    </div>
                </div>

                <!-- Shadrack Onyango 
                <div class="group" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Shadrack1.png" alt="Shadrack Onyango" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Shadrack Onyango</h3>
                                <p class="text-gray-200">Project Manager</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Shadrack Onyango</h3>
                        <p class="text-gray-400 text-base">Project Manager</p>
                    </div>
                </div>

                <!-- Emmanuel Kipkemboi 
                <div class="group" data-aos="fade-up" data-aos-delay="200">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Manuu1.png" alt="Emmanuel Kipkemboi" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Emmanuel Kipkemboi</h3>
                                <p class="text-gray-200">Software Engineer</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Emmanuel Kipkemboi</h3>
                        <p class="text-gray-400 text-base">Software Engineer</p>
                    </div>
                </div>

                <!-- Paul Ruoya 
                <div class="group" data-aos="fade-up" data-aos-delay="300">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Paul1.png" alt="Paul Ruoya" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Paul Ruoya</h3>
                                <p class="text-gray-200">Online Tutor</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Paul Ruoya</h3>
                        <p class="text-gray-400 text-base">Online Tutor</p>
                    </div>
                </div>

                <!-- Manasseh Njoroge 
                <div class="group" data-aos="fade-up" data-aos-delay="400">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Manasseh1.png" alt="Manasseh Njoroge" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Manasseh Njoroge</h3>
                                <p class="text-gray-200">Online Tutor</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Manasseh Njoroge</h3>
                        <p class="text-gray-400 text-base">Online Tutor</p>
                    </div>
                </div>

                <!-- Dennis Omiti 
                <div class="group" data-aos="fade-up" data-aos-delay="500">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Omiti.png" alt="Dennis Omiti" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Dennis Omiti</h3>
                                <p class="text-gray-200">Academic Coordinator</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Dennis Omiti</h3>
                        <p class="text-gray-400 text-base">Academic Coordinator</p>
                    </div>
                </div>

                <!-- Dennis Langat 
                <div class="group" data-aos="fade-up" data-aos-delay="600">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Langat.png" alt="Dennis Langat" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Dennis Langat</h3>
                                <p class="text-gray-200">Academic Coordinator</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Dennis Langat</h3>
                        <p class="text-gray-400 text-base">Academic Coordinator</p>
                    </div>
                </div>

                <!-- Rufftone Juma 
                <div class="group" data-aos="fade-up" data-aos-delay="700">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Rufftone1.png" alt="Rufftone Juma" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Rufftone Juma</h3>
                                <p class="text-gray-200">Sales Manager</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Rufftone Juma</h3>
                        <p class="text-gray-400 text-base">Sales Manager</p>
                    </div>
                </div>

                <!-- Shirleen Maunda
                <div class="group" data-aos="fade-up" data-aos-delay="800">
                    <div class="relative h-[400px] mb-6 rounded-2xl overflow-hidden shadow-lg">
                        <img src="assets/images/Shirleen.png" alt="Shirleen Maunda" 
                             class="w-full h-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-xl font-bold text-white mb-1">Shirleen Maunda</h3>
                                <p class="text-gray-200">Fellowship Coordinator</p>
                                <div class="flex gap-4 mt-4">
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#" class="text-white hover:text-primary transition-colors">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Shirleen Maunda</h3>
                        <p class="text-gray-400 text-base">Fellowship Coordinator</p>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Testimonials Section 
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12" data-aos="fade-up">What Our Clients Say</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 
                <div class="bg-white p-8 rounded-xl shadow-lg transform hover:-translate-y-2 transition duration-300" data-aos="fade-up">
                    <div class="flex items-center mb-6">
                        <img src="assets/images/Shirley2.png" alt="Shirley" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="text-xl font-bold">Shirley</h4>
                            <div class="bch-highlight">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"BCH helped our company scale our operations with an amazing website. Their team is professional and highly skilled!"</p>
                </div>

                <!-- Testimonial 2 
                <div class="bg-white p-8 rounded-xl shadow-lg transform hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-6">
                        <img src="assets/images/Langat.png" alt="Dennis Langat" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="text-xl font-bold">Dennis Langat</h4>
                            <div class="bch-highlight">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Their services are top-notch! I highly recommend BCH for web development and digital services."</p>
                </div>

                <!-- Testimonial 3 
                <div class="bg-white p-8 rounded-xl shadow-lg transform hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-6">
                        <img src="assets/images/Comfort1.png" alt="Comfort Mwanga" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="text-xl font-bold">Comfort Mwanga</h4>
                            <div class="bch-highlight">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Excellent customer service, and their design ideas were very creative. I'm impressed with BCH's dedication."</p>
                </div>
            </div>
        </div>
    </section>
<!-- Contact Section -->
<section id="contact" class="py-20 bg-cover bg-center relative" style="background-image: url('assets/images/');">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
    <div class="relative container mx-auto px-4 z-10">
        <h2 class="text-5xl font-bold text-white text-center mb-12" data-aos="fade-up">
            Let’s Connect
        </h2>

        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- Contact Form -->
                <div class="bch-card shadow-lg" data-aos="fade-up">
                    <form action="https://formspree.io/f/xkgwarar" method="POST" class="space-y-6">
                        <div class="relative">
                            <input type="text" id="name" name="name" required placeholder="Your Name"
                                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:border-bch-blue focus:ring-2 focus:ring-bch-blue/20 outline-none transition">
                            <div class="absolute top-3.5 left-4 text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <input type="email" id="email" name="email" required placeholder="Your Email"
                                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:border-bch-blue focus:ring-2 focus:ring-bch-blue/20 outline-none transition">
                            <div class="absolute top-3.5 left-4 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <textarea id="message" name="message" rows="5" required placeholder="Your Message"
                                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:border-bch-blue focus:ring-2 focus:ring-bch-blue/20 outline-none transition"></textarea>
                            <div class="absolute top-4 left-4 text-gray-400">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-bch-gold-dark transition duration-300 transform hover:scale-105">
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

    <!-- Footer -->
<footer class="bg-bch-dark-blue text-white shadow-lg">
    <div class="container mx-auto px-6 lg:px-12 py-16">
        <!-- Main Footer Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10">
            
            <!-- Company Info -->
            <div>
                <h3 class="text-2xl font-bold mb-4">Bonnie Computer Hub</h3>
                <p class="text-gray-400 mb-6 text-lg">Empowering individuals and businesses through technology and education.</p>
                <div class="flex space-x-4">
                    <a href="https://www.linkedin.com/in/bonniecomputerhub-273753307/" target="_blank"
                       class="text-gray-400 hover:text-white transition transform hover:scale-110">
                        <i class="fab fa-linkedin text-2xl"></i>
                    </a>
                    <a href="https://github.com/bonniecomputerhub24" target="_blank"
                       class="text-gray-400 hover:text-white transition transform hover:scale-110">
                        <i class="fab fa-github text-2xl"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-4xl font-extrabold mb-2 text-blue-700">Quick Links</h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="#about" class="hover:text-bch-gold transition">About Us</a></li>
                    <li><a href="#services" class="hover:text-bch-gold transition">Services</a></li>
                    <li><a href="LMS/pages/courses.php" class="hover:text-bch-gold transition">Courses</a></li>
                    <li><a href="#team" class="hover:text-bch-gold transition">Our Team</a></li>
                    <li><a href="#contact" class="hover:text-bch-gold transition">Contact</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div>
                <h3 class="text-4xl font-extrabold mb-2 text-blue-700">Our Services</h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="#" class="hover:text-bch-gold transition">Web Development</a></li>
                    <li><a href="#" class="hover:text-bch-gold transition">Online Courses</a></li>
                    <li><a href="#" class="hover:text-bch-gold transition">Cyber Services</a></li>
                    <li><a href="#" class="hover:text-bch-gold transition">Laptop Sales</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-4xl font-extrabold mb-2 text-blue-700">Contact Info</h3>
                <ul class="space-y-3 text-gray-400">
                    <li class="flex items-center">
                        <i class="fas fa-phone mr-3 text-primary"></i>
                        <a href="tel:+254729820689" class="hover:text-bch-gold transition">+254 729 820 689</a>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope mr-3 text-primary"></i>
                        <a href="mailto:bonniecomputerhub24@gmail.com" class="hover:text-bch-gold transition">bonniecomputerhub24@gmail.com</a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Bible Verse -->
        <div class="mt-12 border-t border-gray-800 pt-6 text-center">
            <p class="italic text-gray-400">"I can do all things through Christ who strengthens me." - Philippians 4:13</p>
        </div>

        <!-- Copyright -->
        <div class="mt-4 text-center text-gray-500 text-sm">
  &copy; <span id="copyright-year"></span> Bonnie Computer Hub. All Rights Reserved.
</div>
<script>
  const copyrightStart = 2024;
  const currentYear = new Date().getFullYear();
  document.getElementById('copyright-year').textContent =
    (currentYear > copyrightStart)
      ? `${copyrightStart} - ${currentYear}`
      : `${copyrightStart}`;
</script>
    </div>
</footer>


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
