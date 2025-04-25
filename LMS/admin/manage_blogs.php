<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}



// --- Search, Filter, and Pagination Logic ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE title LIKE :search OR content LIKE :search';
    $params[':search'] = "%$search%";
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM blogs $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalBlogs = $countStmt->fetchColumn();
$totalPages = ceil($totalBlogs / $perPage);

// Fetch paginated blogs
$sql = "SELECT * FROM blogs $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs | Admin - Bonnie Computer Hub</title>
    <link href="../../assets/css/tailwind.min.css" rel="stylesheet">
    <link href="../../assets/css/design-system.css" rel="stylesheet">
    <link href="../../assets/css/components.css" rel="stylesheet">
    <link href="../../assets/css/utilities.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap">
    <script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50 font-inter min-h-screen flex flex-col">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="bg-gradient-to-r from-primary via-blue-100 to-white rounded-xl shadow-lg p-6 mb-8 flex items-center gap-4 border border-blue-200">
            <div class="flex-shrink-0 bg-white rounded-full p-4 shadow">
                <i class="fas fa-blog text-primary text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Manage Blogs</h1>
                <div class="text-base md:text-lg text-primary">Create and manage blog posts for Bonnie Computer Hub</div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <?= $_SESSION['success_msg'] ?>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <?= $_SESSION['error_msg'] ?>
                <?php unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Search & Add Blog -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <form method="GET" action="" class="flex flex-1 gap-2">
                <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Search blogs..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" />
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 transition-all">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
            <a href="add_blog.php" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-bold shadow flex items-center gap-2 transition-all duration-200 focus:ring-2 focus:ring-yellow-400 focus:outline-none">
                <i class="fas fa-plus"></i> Add New Blog
            </a>
        </div>

        <!-- Blogs List Table -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="mb-4 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
                <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="mb-4 text-red-800 bg-red-100 border border-red-200 px-4 py-3 rounded">
                <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2">Manage Blogs</h1>
            <h2 class="text-lg font-semibold text-primary mb-4 flex items-center gap-2"><i class="fas fa-list"></i> Existing Blogs</h2>
            <?php if (empty($blogs)): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                    No blog posts found. Try adjusting your search or <a href="add_blog.php" class="underline text-primary">add a new blog</a>.
                </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($blogs as $blog): ?>
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-base font-semibold text-primary flex items-center gap-2">
                                    <i class="fas fa-blog"></i>
                                    <?= htmlspecialchars($blog['title']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-h-20 overflow-y-auto">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($blog['content']), 0, 120, '...')) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($blog['created_at'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_comments WHERE blog_id = ?");
                                $countStmt->execute([$blog['id']]);
                                $commentCount = $countStmt->fetchColumn();
                                ?>
                                <a href="admin_blog_comments.php?id=<?= $blog['id'] ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded-lg font-semibold shadow transition-all">
                                    <?= $commentCount ?> Comments
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="edit_blog.php?id=<?= $blog['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <a href="delete_blog.php?id=<?= $blog['id'] ?>" class="text-red-600 hover:text-red-800 font-medium ml-4" onclick="return confirm('Are you sure you want to delete this blog post?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-6">
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
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
