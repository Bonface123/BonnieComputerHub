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
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Bonnie Computer Hub' : 'Bonnie Computer Hub'; ?></title>

    <!-- Favicon and Apple Touch Icon -->
    <link rel="icon" type="image/jpeg" href="<?php echo getBaseUrl(); ?>assets/images/BCH.jpg">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo getBaseUrl(); ?>assets/images/BCH.jpg">

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
        <img src="../../assets/images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
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
            <a href="<?php echo getBaseUrl(); ?>index.php" class="hover:text-bch-gold <?php echo isCurrentPage('index.php') ? 'font-bold text-bch-gold' : 'text-bch-blue'; ?> transition duration-300">
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
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/login.php" class="ml-4 px-4 py-2 border border-bch-blue-light rounded hover:bg-bch-blue-light hover:text-white-600 text-sm transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </a>
            <a href="<?php echo getBaseUrl(); ?>LMS/pages/register.php" class="ml-4 px-4 py-2 bg-yellow-600 text-white rounded hover:bg-bch-gold-dark text-sm transition duration-300">
                <i class="fas fa-user-plus mr-2"></i> Register
            </a>
            <?php endif; ?>
        </nav>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php
                // Fetch notifications for the logged-in user (limit 10, undismissed)
                require_once __DIR__ . '/db_connect.php';
                $notif_stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_dismissed = 0 ORDER BY created_at DESC LIMIT 10');
                $notif_stmt->execute([$_SESSION['user_id']]);
                $notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
                $unread_count = 0;
                foreach ($notifications as $n) { if (!$n['is_read']) $unread_count++; }
            ?>
            <!-- Notification Bell -->
            <div class="relative ml-4">
                <button id="notif-bell" class="relative focus:outline-none focus:ring-2 focus:ring-bch-gold" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-bell text-bch-blue text-2xl"></i>
                    <?php if($unread_count > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-bch-gold text-white text-xs rounded-full px-1.5 py-0.5 border-2 border-white animate-pulse" aria-label="<?php echo $unread_count; ?> unread notifications"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </button>
                <!-- Dropdown -->
                <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-bch-gray-100 z-50" role="menu" aria-labelledby="notif-bell">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-bch-gray-100">
                        <span class="font-semibold text-bch-blue">Notifications</span>
                        <button id="notif-close" class="text-gray-400 hover:text-bch-danger focus:outline-none" aria-label="Close notifications"><i class="fas fa-times"></i></button>
                    </div>
                    <ul class="max-h-96 overflow-y-auto divide-y divide-bch-gray-100" aria-live="polite">
                        <?php if(count($notifications) === 0): ?>
                            <li class="px-4 py-6 text-center text-bch-gray-900">No new notifications.</li>
                        <?php else: ?>
                            <?php foreach($notifications as $notif): ?>
                                <li class="px-4 py-3 flex items-start gap-2 <?php echo !$notif['is_read'] ? 'bg-bch-gold-light/30' : ''; ?> group">
                                    <div class="flex-1">
                                        <div class="font-medium text-bch-blue mb-1 text-sm">
                                            <?php echo htmlspecialchars($notif['title'] ?? 'Notification'); ?>
                                        </div>
                                        <div class="text-xs text-bch-gray-900 mb-2">
                                            <?php echo htmlspecialchars($notif['message']); ?>
                                        </div>
                                        <div class="text-xs text-bch-gray-900 opacity-60">
                                            <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1 items-end ml-2">
                                        <?php if(!$notif['is_read']): ?>
                                            <button class="mark-read text-xs text-bch-success hover:underline focus:outline-none" data-id="<?php echo $notif['id']; ?>" tabindex="0" aria-label="Mark as read"><i class="fa fa-check-circle"></i></button>
                                        <?php endif; ?>
                                        <button class="dismiss-notif text-xs text-bch-danger hover:underline focus:outline-none" data-id="<?php echo $notif['id']; ?>" tabindex="0" aria-label="Dismiss notification"><i class="fa fa-times-circle"></i></button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
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

    // Notification Bell Dropdown Logic
    document.addEventListener('DOMContentLoaded', function () {
        const bell = document.getElementById('notif-bell');
        const dropdown = document.getElementById('notif-dropdown');
        const closeBtn = document.getElementById('notif-close');
        if (bell && dropdown) {
            bell.addEventListener('click', function (e) {
                dropdown.classList.toggle('hidden');
                bell.setAttribute('aria-expanded', dropdown.classList.contains('hidden') ? 'false' : 'true');
            });
            // Close dropdown when clicking outside
            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    bell.setAttribute('aria-expanded', 'false');
                }
            });
            // Close button
            if (closeBtn) closeBtn.addEventListener('click', function () {
                dropdown.classList.add('hidden');
                bell.setAttribute('aria-expanded', 'false');
            });
        }
        // AJAX: Mark as Read
        document.querySelectorAll('.mark-read').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const notifId = this.dataset.id;
                fetch('../pages/mark_notification_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'notification_id=' + encodeURIComponent(notifId)
                })
                .then(res => res.json())
                .then(data => { if (data.success) location.reload(); });
            });
        });
        // AJAX: Dismiss
        document.querySelectorAll('.dismiss-notif').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const notifId = this.dataset.id;
                fetch('../pages/dismiss_notification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'notification_id=' + encodeURIComponent(notifId)
                })
                .then(res => res.json())
                .then(data => { if (data.success) location.reload(); });
            });
        });
    });
</script>