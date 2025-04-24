<!-- Footer -->
<footer class="bg-black text-white shadow-2xl">
  <div class="container mx-auto px-6 lg:px-12 py-16">
    
    <!-- Main Footer Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10">
      
<!-- Company Info -->
      <!-- About BCH -->
      <div>
        <div class="flex items-center mb-4">
          <img src="assets/images/Logo.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full border-2 border-yellow-600">
          <h3 class="text-lg font-bold text-white ml-4">BONNIE COMPUTER HUB</h3>
        </div>
        <p class="text-gray-400 mb-4">
          Empowering individuals and businesses through technology education, services, and solutions. We're committed to delivering exceptional quality and fostering innovation.
        </p>
        <div class="flex space-x-4">
          <a href="#" class="text-gray-400 hover:text-yellow-400 transition" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-gray-400 hover:text-yellow-400 transition" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-400 hover:text-yellow-400 transition" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-gray-400 hover:text-yellow-400 transition" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>


      <!-- Quick Links -->
      <div>
        <h3 class="text-xl font-bold mb-4 text-blue-600">Quick Links</h3>
        <ul class="space-y-2 text-gray-400">
          <li><a href="#about" class="hover:text-yellow-500 transition">About Us</a></li>
          <li><a href="#services" class="hover:text-yellow-500 transition">Services</a></li>
          <li><a href="LMS/pages/courses.php" class="hover:text-yellow-500 transition">Courses</a></li>
          <li><a href="#team" class="hover:text-yellow-500 transition">Our Team</a></li>
          <li><a href="#contact" class="hover:text-yellow-500 transition">Contact</a></li>
        </ul>
      </div>

      <!-- Services -->
      <div>
        <h3 class="text-xl font-bold mb-4 text-blue-600">Our Services</h3>
        <ul class="space-y-2 text-gray-400">
          <li><a href="#" class="hover:text-yellow-500 transition">Web Development</a></li>
          <li><a href="#" class="hover:text-yellow-500 transition">Online Courses</a></li>
          <li><a href="#" class="hover:text-yellow-500 transition">Cyber Services</a></li>
          <li><a href="#" class="hover:text-yellow-500 transition">Laptop Sales</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div>
        <h3 class="text-xl font-bold mb-4 text-blue-600">Contact Info</h3>
        <ul class="space-y-3 text-gray-400">
          <li class="flex items-center">
            <i class="fas fa-phone mr-3 text-yellow-500"></i>
            <a href="tel:+254729820689" class="hover:text-yellow-500 transition">+254 729 820 689</a>
          </li>
          <li class="flex items-center">
            <i class="fas fa-envelope mr-3 text-yellow-500"></i>
            <a href="mailto:bonniecomputerhub24@gmail.com" class="hover:text-yellow-500 transition">bonniecomputerhub24@gmail.com</a>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bible Verse -->
    <div class="mt-12 border-t border-gray-700 pt-6 text-center">
      <p class="italic text-yellow-600 text-lg">"I can do all things through Christ who strengthens me." - Philippians 4:13</p>
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

<!-- BCH Live Chat Widget (main site only) -->
<link rel="stylesheet" href="assets/css/livechat.css">
<div id="bch-livechat-toggle" aria-label="Open live chat" tabindex="0" role="button" aria-haspopup="dialog">
  <i class="fas fa-comments"></i>
</div>
<div id="bch-livechat-box" class="hidden" role="dialog" aria-modal="true" aria-labelledby="bch-livechat-title">
  <div class="bch-chat-header">
    <span id="bch-livechat-title">Live Chat</span>
    <button id="bch-livechat-close" aria-label="Close chat" tabindex="0">&times;</button>
  </div>
  <div id="bch-livechat-messages" aria-live="polite" aria-atomic="false"></div>
  <div class="bch-chat-input-row">
    <input id="bch-livechat-input" type="text" placeholder="Type your message..." aria-label="Type your message" autocomplete="off" />
    <button id="bch-livechat-send" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>
<script src="assets/js/livechat.js"></script>
<script>
// Only show widget on main site, not LMS
(function() {
  var isLMS = window.location.pathname.toLowerCase().indexOf('/lms/') !== -1;
  if (!isLMS) {
    document.getElementById('bch-livechat-toggle').style.display = '';
    document.getElementById('bch-livechat-box').style.display = '';
  }
})();
</script>
</footer>
