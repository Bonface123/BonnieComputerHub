   
   <!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Bonnie Computer Hub' : 'Bonnie Computer Hub' ?></title>
    <meta name="description" content="Bonnie Computer Hub offers expert web development, digital skills training, computer services, and affordable tech devices in Kenya. Empowering students, businesses, and professionals through technology." />
    <meta name="keywords" content="Bonnie Computer Hub, web development Kenya, tech education, digital skills, computer services, online courses, laptops, software, ICT training, affordable gadgets, Kenya" />
    <meta name="author" content="Bonnie Computer Hub" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://bonniecomputerhub.com/" />
    <link rel="icon" type="image/jpeg" href="assets/images/BCH.jpg">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/BCH.jpg">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Bonnie Computer Hub | Web Development, Tech Education & Services in Kenya" />
    <meta property="og:description" content="Expert web development, digital skills training, and computer services in Kenya. Join BCH for courses, tech solutions, and affordable devices." />
    <meta property="og:image" content="assets/images/og-image.jpg" />
    <meta property="og:url" content="https://bonniecomputerhub.com/" />
    <meta property="og:site_name" content="Bonnie Computer Hub" />
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Bonnie Computer Hub | Web Development, Tech Education & Services in Kenya" />
    <meta name="twitter:description" content="Expert web development, digital skills training, and computer services in Kenya. Join BCH for courses, tech solutions, and affordable devices." />
    <meta name="twitter:image" content="https://bonniecomputerhub.com/assets/images/og-image.jpg" />
    <!-- Structured Data: Organization -->
 
    <!-- End SEO Enhancements -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body, html {
            font-family: 'Century Gothic', 'AppleGothic', sans-serif;
            font-size: 16px;
            background-color: #fff;
        }
    
        
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147', // BCH Blue
                        secondary: '#E6B800', // BCH Gold
                        'bch-blue': '#002147',
                        'bch-dark-blue': '#002147',
                        'bch-gold': '#E6B800',
                        'bch-gold-dark': '#E6B800',
                        'bch-blue-light': '#3B82F6',
                        'bch-blue-dark': '#002147',
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-white">
<!-- Skip to main content link for accessibility -->
<a href="#main-content" class="sr-only focus:not-sr-only absolute top-2 left-2 z-50 bg-[E6B800] text-[#1E3A8A] px-4 py-2 rounded focus:outline-none focus:ring-4 focus:ring-[#E6B800] transition-all">Skip to main content</a>

<!-- Header - Sticky Navigation with CTA -->
<header class="sticky top-0 z-50 bg-white shadow-lg border-b border-[#E6B800] transition-all duration-300" id="sticky-header" role="banner">
  <div class="container mx-auto px-4 py-3 flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <img src="assets/images/BCH.jpg" alt="Bonnie Computer Hub main logo, a stylized computer hub icon" class="h-12 w-12 rounded-full object-cover" />
      <div class="flex flex-col">
        <a href="index.php" class="text-2xl font-bold bch-section-title tracking-tight text-yellow-600">
          BONNIE COMPUTER HUB
        </a>
        <span class="text-sm text-gray-500 font-medium tracking-wide ml-1">Empowering Through Technology</span>
      </div>
    </div>
    <nav class="hidden md:flex items-center space-x-2 lg:space-x-4" role="navigation" aria-label="Main navigation">
  <a href="index.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-[#1E3A8A] hover:bg-[#F0F6FF] text-[#002147] hover:text-yellow-600 font-semibold transition flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-house mr-2 text-base"></i>Home
  </a>
  <a href="services.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-[#1E3A8A] hover:bg-[#F0F6FF] text-[#002147] hover:text-yellow-600 font-semibold transition flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-gears mr-2 text-base"></i>Services
  </a>
  <a href="LMS/pages/courses.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-[#1E3A8A] hover:bg-[#F0F6FF] text-[#002147] hover:text-yellow-600 font-semibold transition flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-chalkboard-teacher mr-2 text-base"></i>Classes
  </a>
  <a href="blogs.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-[#1E3A8A] hover:bg-[#F0F6FF] text-[#002147] hover:text-yellow-600 font-semibold transition flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-blog mr-2 text-base"></i>Blog
  </a>
  <a href="about.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-[#1E3A8A] hover:bg-[#F0F6FF] text-[#002147] hover:text-yellow-600 font-semibold transition flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-user-group mr-2 text-base"></i>About
  </a>
  <a href="LMS/pages/contact.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-[#1E3A8A] hover:bg-[#F0F6FF] text-[#002147] hover:text-yellow-600 font-semibold transition flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-envelope mr-2 text-base"></i>Contact
  </a>
  <a href="LMS/pages/courses.php" class="ml-2 bg-[#002147] text-white hover:bg-[#E6B800] hover:text-[#002147] focus:outline-[#E6B800] px-4 py-2 rounded-full font-bold transition duration-300 shadow-lg flex items-center focus:outline-[#E6B800]">
    <i class="fas fa-arrow-right-to-bracket mr-2 text-base"></i>Join Class
  </a>
</nav>
    <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-[#E6B800]" id="mobile-menu-button" aria-label="Toggle mobile menu" aria-controls="mobile-menu" aria-expanded="false">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>
  <!-- Mobile Navigation -->
  <nav class="hidden md:hidden px-4 pb-4" id="mobile-menu" aria-label="Mobile navigation" role="navigation">
    <div class="flex flex-col space-y-4 mt-4">
      <a href="../index.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-[#F0F6FF] text-[#002147] hover:text-[#E6B800] font-medium transition">Home</a>
      <a href="#services" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-[#F0F6FF] text-[#002147] hover:text-[#E6B800] font-medium transition">Services</a>
      <a href="/LMS/pages/courses.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-[#F0F6FF] text-[#002147] hover:text-[#E6B800] font-medium transition">Classes</a>
      <a href="../blogs.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-[#F0F6FF] text-[#002147] hover:text-[#E6B800] font-medium transition">Blog</a>
      <a href="../LMS/pages/about.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-[#F0F6FF] text-[#002147] hover:text-[#E6B800] font-medium transition">About</a>
      <a href="../LMS/pages/contact.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-[#F0F6FF] text-[#002147] hover:text-[#E6B800] font-medium transition">Contact</a>
      <a href="../LMS/pages/courses.php" class="mt-2 inline-block bg-[#002147] text-white hover:bg-[#E6B800] hover:text-[#002147] focus:outline-[#E6B800] px-4 py-2 rounded-full font-bold transition duration-300 shadow-lg focus:outline-[#E6B800]">Join Class</a>
    </div>
  </nav>
</header>