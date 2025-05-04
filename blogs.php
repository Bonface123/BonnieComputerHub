<?php
// blogs.php - Blogs management page for BonnieComputerHub (Admin managed)
// Follows design system: Tailwind CSS, Inter font, accessibility, responsive
session_start();

// Determine if user is admin for management actions
$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');

require_once __DIR__ . '/LMS/includes/db_connect.php'; // Uses $pdo

// --- Search, Filter, and Pagination Logic ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE title LIKE :search OR content LIKE :search";
    $params[':search'] = "%$search%";
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM blogs $where";
$countStmt = $pdo->prepare($countSql);
if (!empty($params)) {
    $countStmt->execute($params);
} else {
    $countStmt->execute();
}
$totalBlogs = $countStmt->fetchColumn();
$totalPages = ceil($totalBlogs / $perPage);

// Fetch paginated blogs
$sql = "SELECT * FROM blogs $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
   <?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogs | Bonnie Computer Hub</title>
    <link href="assets/css/tailwind.min.css" rel="stylesheet">
    <link href="assets/css/design-system.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/utilities.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap">
    <link rel="stylesheet" href="assets/css/bch-global.css">
    <script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
 

    <main class="flex-1 max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <h1 class="text-3xl font-bold text-primary">Bonnie Computer Hub Blogs</h1>
            <form method="GET" action="" class="flex flex-1 gap-2 max-w-md md:ml-auto">
                <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Search blogs..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" />
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 transition-all">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
        <?php if (empty($blogs)): ?>
            <div class="flex flex-col items-center justify-center py-16">
                <div class="mb-4">
                    <svg class="w-20 h-20 text-blue-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 48 48" aria-hidden="true">
                        <circle cx="24" cy="24" r="22" stroke="#1E40AF" stroke-width="2" fill="#EFF6FF"/>
                        <path d="M16 24h16M24 16v16" stroke="#1E40AF" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-700 mb-2">No blog posts found</h2>
                <?php if ($search !== ''): ?>
                    <p class="text-gray-500 mb-4">We couldn’t find any blogs matching "<span class="font-semibold text-primary"><?= htmlspecialchars($search) ?></span>".</p>
                    <a href="blogs.php" class="inline-block bg-primary text-white px-6 py-2 rounded-lg font-semibold shadow hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-secondary transition">Clear Search</a>
                <?php else: ?>
                    <p class="text-gray-500 mb-4">There are currently no blog posts.<?= $isAdmin ? ' Click <a href=\"add_blog.php\" class=\"underline text-primary\">Add New Blog</a> to create your first post.' : '' ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($blogs as $blog): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 flex flex-col overflow-hidden group transition-transform hover:-translate-y-1">
                    <a href="blog_view.php?id=<?= $blog['id'] ?>" class="block focus:outline-none">
                        <?php if ($blog['image']): ?>
                            <img src="assets/images/blogs/<?= htmlspecialchars($blog['image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="w-full h-48 object-cover group-hover:opacity-90 transition" loading="lazy">
                        <?php endif; ?>
                        <div class="p-6 flex-1 flex flex-col">
                            <h2 class="text-xl font-bold text-primary mb-2 group-hover:text-yellow-600 transition"><?= htmlspecialchars($blog['title']) ?></h2>
                            <div class="text-gray-500 text-sm mb-2 flex gap-4">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($blog['author_id']) ?></span>
                                <span><i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($blog['created_at'])) ?></span>
                            </div>
                            <div class="text-gray-700 mb-4 line-clamp-3">
                                <?= strip_tags(mb_substr($blog['content'], 0, 150)) ?><?= mb_strlen($blog['content']) > 150 ? '...' : '' ?>
                            </div>
                            <div class="mt-auto flex justify-end">
                                <span class="inline-block bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-700 transition-all">Read More</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-8">
            <nav class="inline-flex rounded-md shadow-sm" aria-label="Pagination">
                <?php $baseUrl = '?'.($search !== '' ? 'search='.urlencode($search).'&' : ''); ?>
                <a href="<?= $page > 1 ? $baseUrl.'page='.($page-1) : '#' ?>" class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $page == 1 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-50' ?>">&laquo; Prev</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= $baseUrl.'page='.$i ?>" class="px-4 py-2 border-t border-b border-gray-300 <?= $i == $page ? 'bg-primary text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-50' ?> text-sm "><?= $i ?></a>
                <?php endfor; ?>
                <a href="<?= $page < $totalPages ? $baseUrl.'page='.($page+1) : '#' ?>" class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $page == $totalPages ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-50' ?>">Next &raquo;</a>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>
