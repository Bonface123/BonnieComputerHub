   
   <!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bonnie Computer Hub | Web Development, Tech Education & Services in Kenya, Africa and Beyond</title>
    <meta name="description" content="Bonnie Computer Hub offers expert web development, digital skills training, computer services, and affordable tech devices in Kenya. Empowering students, businesses, and professionals through technology." />
    <meta name="keywords" content="Bonnie Computer Hub, web development Kenya, tech education, digital skills, computer services, online courses, laptops, software, ICT training, affordable gadgets, Kenya" />
    <meta name="author" content="Bonnie Computer Hub" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://bonniecomputerhub.com/" />
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/apple-touch-icon.png">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Bonnie Computer Hub | Web Development, Tech Education & Services in Kenya" />
    <meta property="og:description" content="Expert web development, digital skills training, and computer services in Kenya. Join BCH for courses, tech solutions, and affordable devices." />
    <meta property="og:image" content="https://bonniecomputerhub.com/assets/images/og-image.jpg" />
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
    
        .bg-gradient-to-r {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
        }
    
        .bch-outline {
            outline: 3px solid #FFD700 !important;
            outline-offset: 2px;
        }
    
        .bch-badge-gold {
            background: linear-gradient(90deg, #FFD700 60%, #FFF8DC 100%);
            color: #2563EB;
            box-shadow: 0 2px 8px #FFD70044;
        }
    
        .bch-card {
            background: #fff;
            border: 1px solid #e0e7ef;
            box-shadow: 0 4px 24px 0 #2563EB11;
            border-radius: 1.25rem;
            padding: 1.5rem;
        }
    
        .bch-btn-primary {
            background-color: #2563EB; /* Updated Blue */
            color: #fff;
            border: none;
            transition: background 0.3s, color 0.3s;
        }
    
        .bch-btn-primary:hover,
        .bch-btn-primary:focus {
            background-color: #1D4ED8; /* Darker Blue for hover */
            color: #FFD700; /* BCH Gold */
            outline: 3px solid #FFD700;
        }
    
        .bch-btn-outline {
            background: #fff;
            color: #2563EB; /* Updated Blue */
            border: 2px solid #2563EB;
            transition: background 0.3s, color 0.3s;
        }
    
        .bch-btn-outline:hover,
        .bch-btn-outline:focus {
            background: #F1F5FE;
            color: #1D4ED8; /* Darker Blue */
            outline: 3px solid #FFD700;
        }
    
        .bch-section-title {
            color: #2563EB;
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.02em;
        }
    
        .bch-highlight {
            color: #FFD700;
        }
    
        .bch-card-shadow {
            box-shadow: 0 4px 24px 0 #2563EB11;
        }
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF', // BCH Blue
                        secondary: '#FFD700', // BCH Gold
                        'bch-blue': '#1E40AF',
                        'bch-dark-blue': '#1E3A8A',
                        'bch-gold': '#FFD700',
                        'bch-gold-dark': '#E6B800',
                        'bch-blue-light': '#3B82F6',
                        'bch-blue-dark': '#1E3A8A',
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-white"></body>
   
   <!-- Header - Sticky Navigation with CTA -->
    <header class="sticky top-0 z-50 bg-white shadow-lg border-b border-blue-100 transition-all duration-300" id="sticky-header">
  <div class="container mx-auto px-4 py-3 flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <img src="assets/images/logo.jpg" alt="Bonnie Computer Hub Logo" class="h-12 w-12 rounded-full object-cover" />
      <div class="flex flex-col">
        <a href="../index.php" class="text-2xl font-black bch-section-title tracking-tight text-yellow-600">
          BONNIE COMPUTER HUB
        </a>
        <span class="text-sm text-gray-500 font-medium tracking-wide ml-1">Empowering Through Technology</span>
      </div>
    </div>
    <nav class="hidden md:flex items-center space-x-2 lg:space-x-4">
  <a href="index.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-bch-dark-blue hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-semibold transition flex items-center focus:bch-outline">
    <i class="fas fa-house mr-2 text-base"></i>Home
  </a>
  <a href="services.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-bch-dark-blue hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-semibold transition flex items-center focus:bch-outline">
    <i class="fas fa-gears mr-2 text-base"></i>Services
  </a>
  <a href="LMS/pages/courses.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-bch-dark-blue hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-semibold transition flex items-center focus:bch-outline">
    <i class="fas fa-chalkboard-teacher mr-2 text-base"></i>Classes
  </a>
  <a href="blogs.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-bch-dark-blue hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-semibold transition flex items-center focus:bch-outline">
    <i class="fas fa-blog mr-2 text-base"></i>Blog
  </a>
  <a href="LMS/pages/about.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-bch-dark-blue hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-semibold transition flex items-center focus:bch-outline">
    <i class="fas fa-user-group mr-2 text-base"></i>About
  </a>
  <a href="LMS/pages/contact.php" class="px-3 py-1 rounded-lg border-l-4 border-transparent hover:border-bch-dark-blue hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-semibold transition flex items-center focus:bch-outline">
    <i class="fas fa-envelope mr-2 text-base"></i>Contact
  </a>
  <a href="LMS/pages/courses.php" class="ml-2 bch-btn-primary px-4 py-2 rounded-full font-bold transition duration-300 shadow-lg flex items-center focus:bch-outline">
    <i class="fas fa-arrow-right-to-bracket mr-2 text-base"></i>Join Class
  </a>
</nav>
    <button class="md:hidden p-2 rounded-lg hover:bg-gray-100" id="mobile-menu-button" aria-label="Toggle mobile menu">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </div>
  <!-- Mobile Navigation -->
  <nav class="hidden md:hidden px-4 pb-4" id="mobile-menu" aria-label="Mobile navigation">
    <div class="flex flex-col space-y-4 mt-4">
      <a href="../index.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-medium transition">Home</a>
      <a href="#services" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-medium transition">Services</a>
      <a href="/LMS/pages/courses.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-medium transition">Classes</a>
      <a href="../blogs.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-medium transition">Blog</a>
      <a href="../LMS/pages/about.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-medium transition">About</a>
      <a href="../LMS/pages/contact.php" class="px-3 py-2 rounded-lg border-l-4 border-transparent hover:border-primary hover:bg-blue-50 text-primary hover:text-bch-gold-dark font-medium transition">Contact</a>
      <a href="../LMS/pages/courses.php" class="mt-2 inline-block bch-btn-primary px-4 py-2 rounded-full font-bold transition duration-300 shadow-lg focus:bch-outline">Join Class</a>
    </div>
  </nav>
</header>