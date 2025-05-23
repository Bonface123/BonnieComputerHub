</main>

<footer class="bg-black text-white pt-12 pb-8 mt-16 shadow-md border-t border-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid gap-10 md:grid-cols-3 rounded-lg bg-black p-8">
      <!-- About BCH -->
      <div>
        <div class="flex items-center mb-4">
          <img src="../../assets/images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full border-2 border-yellow-600">
          <h3 class="text-lg font-bold text-white ml-4">BONNIE COMPUTER HUB</h3>
        </div>
        <p class="text-gray-400 mb-4">
          Empowering individuals and businesses through technology education, services, and solutions. We're committed to delivering exceptional quality and fostering innovation.
        </p>
        <div class="flex space-x-4">
          <a href="https://www.facebook.com/profile.php?id=61561957532525" class="text-gray-400 hover:text-yellow-600 transition" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-gray-400 hover:text-yellow-600 transition" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-400 hover:text-yellow-600 transition" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://www.linkedin.com/in/bonnie-computer-hub-bch-5b7b42360" class="text-gray-400 hover:text-yellow-600 transition" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h3 class="text-lg font-bold text-white mb-4">Quick Links</h3>
        <ul class="space-y-2">
          <li>
            <a href="<?php echo getBaseUrl(); ?>index.php" class="text-gray-400 hover:text-yellow-600 transition flex items-center">
              <i class="fas fa-house mr-2 text-xs"></i> Home
            </a>
          </li>
          <li>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/courses.php" class="text-gray-400 hover:text-yellow-600 transition flex items-center">
              <i class="fas fa-chalkboard-teacher mr-2 text-xs"></i> Courses
            </a>
          </li>
          <li>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/about.php" class="text-gray-400 hover:text-yellow-600 transition flex items-center">
              <i class="fas fa-user-group mr-2 text-xs"></i> About Us
            </a>
          </li>
          <li>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/contact.php" class="text-gray-400 hover:text-yellow-600 transition flex items-center">
              <i class="fas fa-envelope mr-2 text-xs"></i> Contact Us
            </a>
          </li>
          <li>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/faq.php" class="text-gray-400 hover:text-yellow-600 transition flex items-center">
              <i class="fas fa-circle-question mr-2 text-xs"></i> FAQ
            </a>
          </li>
        </ul>
      </div>
      
    
      

      <!-- Contact Info -->
      <div>
        <h3 class="text-lg font-bold text-white mb-4">Contact Us</h3>
        <ul class="space-y-3">
          <li class="flex items-start">
            <i class="fas fa-map-marker-alt text-yellow-600 mt-1 mr-3"></i>
            <span class="text-gray-400">Nairobi, Kenya</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-phone-alt text-yellow-600 mt-1 mr-3"></i>
            <span class="text-gray-400">+254 729 820 689</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-envelope text-yellow-600 mt-1 mr-3"></i>
            <span class="text-gray-400">Bonniecomputerhub24@gmail.com</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-clock text-yellow-600 mt-1 mr-3"></i>
            <span class="text-gray-400">Monday - Friday: 9:00 AM - 5:00 PM</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Copyright -->
    <div class="border-t border-gray-800 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
      <p class="mb-3 md:mb-0">&copy; <?php echo (date("Y") > 2024) ? "2024 - " . date("Y") : "2024"; ?> Bonnie Computer Hub. All rights reserved.</p>
      <p class="text-yellow-600 italic text-center md:text-right">
        "I can do all things through Christ who strengthens me." - Philippians 4:13
      </p>
    </div>
  </div>
</footer>

<!-- JavaScript for mobile menu toggle -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mainNav = document.getElementById('main-nav');

    if (mobileMenuButton && mainNav) {
      mobileMenuButton.addEventListener('click', function () {
        mainNav.classList.toggle('show');
        const isExpanded = mainNav.classList.contains('show');
        mobileMenuButton.setAttribute('aria-expanded', isExpanded);
      });
    }
  });
</script>
</body>
</html>
