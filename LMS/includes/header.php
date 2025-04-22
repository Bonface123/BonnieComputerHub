<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Tailwind CSS CDN for rapid prototyping -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        'bch-blue': '#002147',
        'bch-gold': '#FFD700',
        'bch-blue-light': '#2a4d6e',
        'bch-blue-dark': '#001224',
        'bch-gold-light': '#ffe866',
        'bch-gold-dark': '#d4b000',
        'bch-success': '#28a745',
        'bch-danger': '#dc3545',
        'bch-accent': '#fd7e14',
        'bch-gray-100': '#f8f9fa',
        'bch-gray-900': '#212529',
      }
    }
  }
}
</script>
    <!-- End Tailwind CDN -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bonnie Computer Hub - Empowering Through Technology. A leading center for IT training,web development classes,software engineering services, web development services, and solutions.">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Bonnie Computer Hub' : 'Bonnie Computer Hub'; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo getBaseUrl(); ?>images/favicon.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Design System Stylesheet -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/bch-master.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php if(isset($extraStyles)): ?>
        <?php echo $extraStyles; ?>
    <?php endif; ?>

    <?php 
    function getBaseUrl() {
        $currentPath = $_SERVER['PHP_SELF'];
        $hostName = $_SERVER['HTTP_HOST'];
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $baseUrl = "";

        if (strpos($currentPath, '/LMS/') !== false) {
            $depth = substr_count(str_replace('/LMS/', '', $currentPath), '/');
            for ($i = 0; $i < $depth; $i++) {
                $baseUrl .= "../";
            }
        }

        return $baseUrl;
    }

    function isCurrentPage($path) {
        return strpos($_SERVER['PHP_SELF'], $path) !== false;
    }
    ?>
</head>
<body class="bg-white text-gray-800 font-sans">

<!-- Skip Link -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-yellow-200 text-black p-2 z-50">Skip to main content</a>

<!-- Header -->
<header class="bg-white shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="<?php echo getBaseUrl(); ?>index.php" class="flex items-center space-x-2">
        <img src="../../assets/images/Logo.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
            <div>
                <h1 class="text-xl font-bold text-yellow-600">BONNIE COMPUTER HUB</h1>
                <p class="text-sm text-gray-500 -mt-1">Empowering Through Technology</p>
            </div>
        </a>

        <!-- Mobile toggle -->
        <button type="button" id="mobile-menu-button" class="text-gray-600 md:hidden text-xl">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Navigation -->
        <nav class="hidden md:flex space-x-6 items-center" id="main-nav">
            <a href="<?php echo getBaseUrl(); ?>index.html" class="hover:text-bch-gold <?php echo isCurrentPage('index.php') ? 'font-bold text-bch-gold' : 'text-bch-blue'; ?> transition duration-300">
            <i class="fas fa-home mr-2"></i> Home
            </a>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/courses.php" class="hover:text-bch-gold <?php echo isCurrentPage('courses.php') ? 'font-bold text-bch-gold' : 'text-bch-blue'; ?> transition duration-300"></a>
            <i class="fas fa-book mr-2"></i> Courses
            </a>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/contact.php" class="hover:text-bch-gold <?php echo isCurrentPage('contact.php') ? 'font-bold text-bch-gold' : 'text-bch-blue'; ?> transition duration-300">
            <i class="fas fa-envelope mr-2"></i> Contact
            </a>

            <?php if(isset($_SESSION['user_id'])): ?>
            <?php 
                $dashboardUrl = "";
                switch ($_SESSION['role']) {
                case 'admin':
                    $dashboardUrl = getBaseUrl() . "LMS/admin/admin_dashboard.php";
                    break;
                case 'instructor':
                    $dashboardUrl = getBaseUrl() . "LMS/instructor/instructor_dashboard.php";
                    break;
                default:
                    $dashboardUrl = getBaseUrl() . "LMS/student/dashboard.php";
                    break;
                }
            ?>
            <a href="<?php echo $dashboardUrl; ?>" class="hover:text-bch-gold text-bch-blue transition duration-300">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="<?php echo getBaseUrl(); ?>LMS/logout.php" class="ml-4 px-4 py-2 border border-bch-blue-light rounded hover:bg-bch-blue-light hover:text-white text-sm transition duration-300">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
            <?php else: ?>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/login.php" class="ml-4 px-4 py-2 border border-bch-blue-light rounded hover:bg-bch-blue-light hover:text-white text-sm transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/register.php" class="ml-4 px-4 py-2 bg-bch-gold text-white rounded hover:bg-bch-gold-dark text-sm transition duration-300">
                <i class="fas fa-user-plus mr-2"></i> Register
            </a>
            <?php endif; ?>
        </nav>
        </div>

    <!-- Mobile nav -->
    <div class="md:hidden px-4 pb-4 hidden" id="mobile-nav">
        <a href="<?php echo getBaseUrl(); ?>index.php" class="block py-2 text-gray-700 <?php echo isCurrentPage('index.php') ? 'font-bold text-yellow-600' : ''; ?>">Home</a>
        <a href="<?php echo getBaseUrl(); ?>pages/courses.php" class="block py-2 text-gray-700 <?php echo isCurrentPage('courses.php') ? 'font-bold text-yellow-600' : ''; ?>">Courses</a>
        <a href="<?php echo getBaseUrl(); ?>pages/contact.php" class="block py-2 text-gray-700 <?php echo isCurrentPage('contact.php') ? 'font-bold text-yellow-600' : ''; ?>">Contact</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="<?php echo $dashboardUrl; ?>" class="block py-2 text-gray-700">Dashboard</a>
            <a href="<?php echo getBaseUrl(); ?>logout.php" class="block py-2 text-gray-700">Logout</a>
        <?php else: ?>
            <a href="<?php echo getBaseUrl(); ?>pages/login.php" class="block py-2 text-gray-700">Login</a>
            <a href="<?php echo getBaseUrl(); ?>pages/register.php" class="block py-2 text-yellow-600 font-bold">Register</a>
        <?php endif; ?>
    </div>
</header>

<!-- Main content wrapper -->
<main id="main-content" class="pt-6">
    <!-- Breadcrumb (optional) -->
    <?php if(isset($breadcrumbs)): ?>
    <div class="max-w-7xl mx-auto px-4 mb-4">
        <nav class="text-sm text-gray-500" aria-label="breadcrumb">
            <ol class="flex space-x-2">
                <?php foreach($breadcrumbs as $label => $url): ?>
                    <?php if($url): ?>
                        <li><a href="<?php echo $url; ?>" class="hover:underline"><?php echo $label; ?></a><span class="mx-1">/</span></li>
                    <?php else: ?>
                        <li class="text-yellow-600 font-medium"><?php echo $label; ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
    <?php endif; ?>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button')?.addEventListener('click', function () {
        const nav = document.getElementById('mobile-nav');
        nav.classList.toggle('hidden');
    });
</script>
