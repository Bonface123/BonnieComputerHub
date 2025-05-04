<!-- Footer -->
<footer class="bg-[#002147] text-[#FFFFFF] shadow-2xl">
  <div class="container mx-auto px-6 lg:px-12 py-16">
    
    <!-- Main Footer Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-10">
      
<!-- Company Info -->
      <!-- About BCH -->
      <div>
        <div class="flex items-center mb-4">
          <img src="assets/images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full border-2 border-[#E6B800]">
          <h3 class="text-lg font-bold text-[#FFFFFF] ml-4">BONNIE COMPUTER HUB</h3>
        </div>
        <p class="text-[#6b7280] mb-4">
          Empowering individuals and businesses through technology education, services, and solutions. We're committed to delivering exceptional quality and fostering innovation.
        </p>
        <div class="flex space-x-4">
          <a href="https://www.facebook.com/profile.php?id=61561957532525" class="text-[#6b7280] hover:text-[#E6B800] transition" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-[#6b7280] hover:text-[#E6B800] transition" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-[#6b7280] hover:text-[#E6B800] transition" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://www.linkedin.com/in/bonnie-computer-hub-bch-5b7b42360" class="text-[#6b7280] hover:text-[#E6B800] transition" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>


      <!-- Quick Links -->
      <div>
        <h3 class="text-xl font-bold mb-4 text-[#002147]">Quick Links</h3>
        <ul class="space-y-2 text-[#6b7280]">
          <li><a href="../BonnieComputerHub/about.php" class="hover:text-[#E6B800] transition">About Us</a></li>
          <li><a href="#services" class="hover:text-[#E6B800] transition">Services</a></li>
          <li><a href="LMS/pages/courses.php" class="hover:text-[#E6B800] transition">Courses</a></li>
          <li><a href="#team" class="hover:text-[#E6B800] transition">Our Team</a></li>
          <li><a href="#contact" class="hover:text-[#E6B800] transition">Contact</a></li>
        </ul>
      </div>

      <!-- Services -->
      <div>
        <h3 class="text-xl font-bold mb-4 text-[#002147]">Our Services</h3>
        <ul class="space-y-2 text-[#6b7280]">
          <li><a href="../BonnieComputerHub/services.php" class="hover:text-[#E6B800] transition">Web Development</a></li>
          <li><a href="../BonnieComputerHub/services.php" class="hover:text-[#E6B800] transition">Online Courses</a></li>
          <li><a href="../BonnieComputerHub/services.php" class="hover:text-[#E6B800] transition">Cyber Services</a></li>
          <li><a href="../BonnieComputerHub/services.php" class="hover:text-[#E6B800] transition">Laptop Sales</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div>
        <h3 class="text-xl font-bold mb-4 text-[#002147]">Contact Info</h3>
        <ul class="space-y-3 text-[#6b7280]">
          <li class="flex items-center">
            <i class="fas fa-phone mr-3 text-yellow-500"></i>
            <a href="tel:+254729820689" class="hover:text-[#E6B800] transition">+254 729 820 689</a>
          </li>
          <li class="flex items-center">
            <i class=""></i>
            <a href="https://www.linkedin.com/in/bonnie-computer-hub-bch-5b7b42360" class="hover:text-[#E6B800] transition"></a>
         </li>
          <li class="flex items-center">
            <i class="fas fa-envelope mr-3 text-yellow-500"></i>
            <a href="mailto:bonniecomputerhub24@gmail.com" class="hover:text-[#E6B800] transition">bonniecomputerhub24@gmail.com</a>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bible Verse -->
    <div class="mt-12 border-t border-[#002147] pt-6 text-center">
      <p class="italic text-yellow-700 focus:ring-[#E6B800] text-lg">"I can do all things through Christ who strengthens me." - Philippians 4:13</p>
    </div>

    <!-- Copyright -->
    <div class="mt-4 text-center text-[#002147] text-sm">
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
