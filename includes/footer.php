</main>

    <footer class="bg-white shadow-md border-t border-gray-200 pt-12 pb-8 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-3 rounded-lg bg-white shadow-md p-8">
            <!-- About BCH -->
            <div>
                <div class="flex items-center mb-4">
                <img src="assets/images/Logo.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                <h3 class="text-lg font-bold text-gray-900 ml-4">BONNIE COMPUTER HUB</h3>
                </div>
                <p class="text-gray-600 mb-4">
                    Empowering individuals and businesses through technology education, services, and solutions. We're committed to delivering exceptional quality and fostering innovation.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>index.php" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded flex items-center">
                            <i class="fas fa-house mr-2 text-xs"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>LMS/pages" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded flex items-center">
                            <i class="fas fa-chalkboard-teacher mr-2 text-xs"></i> Courses
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>pages/about.php" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded flex items-center">
                            <i class="fas fa-user-group mr-2 text-xs"></i> About Us
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>pages/contact.php" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded flex items-center">
                            <i class="fas fa-envelope mr-2 text-xs"></i> Contact Us
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>pages/faq.php" class="text-gray-600 hover:text-yellow-400 transition hover:border-l-2 hover:border-yellow-400 hover:bg-yellow-100 py-2 px-4 rounded flex items-center">
                            <i class="fas fa-circle-question mr-2 text-xs"></i> FAQ
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Contact Us</h3>
                <ul class="space-y-3">
                     <li class="flex items-start"></li>
                        <i class="fas fa-map-marker-alt text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-600">Nairobi, Kenya</span>
                    </li>    <li class="flex items-start">
                        <i class="fas fa-phone-alt text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-600">+254 729 820 689</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-600">Bonniecomputerhub24@gmail.com</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-clock text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-600">Monday - Friday: 9:00 AM - 5:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-200 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
            <p class="mb-3 md:mb-0">&copy; <?php echo (date("Y") > 2024) ? "2024 - " . date("Y") : "2024"; ?> Bonnie Computer Hub. All rights reserved.</p>
            <p class="text-yellow-600 italic text-center md:text-right">
                "I can do all things through Christ who strengthens me." - Philippians 4:13
            </p>
        </div>
    </div>
</footer>

    <!-- JavaScript for mobile menu toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mainNav = document.getElementById('main-nav');
            
            if (mobileMenuButton && mainNav) {
                mobileMenuButton.addEventListener('click', function() {
                    mainNav.classList.toggle('show');
                    const isExpanded = mainNav.classList.contains('show');
                    mobileMenuButton.setAttribute('aria-expanded', isExpanded);
                });
            }
        });
    </script>

