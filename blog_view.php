<?php
// blog_view.php - Shows a single blog post with full content, sharing, and comments
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/LMS/includes/db_connect.php';

$blogId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($blogId <= 0) {
    header('Location: blogs.php');
    exit;
}

// Fetch blog post
$stmt = $pdo->prepare("SELECT b.*, u.name as author_name FROM blogs b JOIN users u ON b.author_id = u.id WHERE b.id = ?");
$stmt->execute([$blogId]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) {
    header('Location: blogs.php');
    exit;
}

// Fetch approved comments (with threaded replies)
$commentsStmt = $pdo->prepare("SELECT c.*, u.name, u.email, u.role, u.avatar FROM blog_comments c LEFT JOIN users u ON c.author_id = u.id WHERE c.blog_id = ? AND c.approved = 1 ORDER BY c.created_at ASC");
$commentsStmt->execute([$blogId]);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
// Organize comments by parent_id for threading
$threaded = [];
foreach ($comments as $c) {
    $parent = $c['parent_id'] ?? null;
    $threaded[$parent][] = $c;
}
function render_comments($parent_id, $threaded, $level = 0) {
    if (!isset($threaded[$parent_id])) return;
    foreach ($threaded[$parent_id] as $c) {
        $isAdmin = isset($c['role']) && $c['role'] === 'admin';
        $avatarUrl = 'LMS/admin/get_avatar.php?id=' . (int)($c['author_id'] ?? 0);
        echo '<div class="flex gap-3 mb-4 ml-'.($level*32).'">';
        echo '<img src="'.$avatarUrl.'" alt="avatar" class="w-8 h-8 rounded-full border">';
        echo '<div class="flex-1">';
        echo '<div class="flex items-center gap-2">';
        echo '<span class="font-semibold">'.htmlspecialchars($c['name'] ?? 'Guest').'</span>';
        if ($isAdmin) echo '<span class="ml-1 px-2 py-0.5 bg-yellow-600 text-white text-xs rounded">Admin</span>';
        echo '<span class="ml-2 text-xs text-bch-gray-900">'.date('F j, Y H:i', strtotime($c['created_at'])).'</span>';
        echo '</div>';
        echo '<div class="text-bch-gray-900">'.nl2br(htmlspecialchars($c['content'])).'</div>';
        echo '<button class="text-xs text-bch-blue mt-1 reply-btn" data-comment="'.$c['id'].'">Reply</button>';
        echo '<div class="reply-form mt-2" id="reply-form-'.$c['id'].'" style="display:none"></div>';
        echo '</div></div>';
        render_comments($c['id'], $threaded, $level+1);
    }
}

// Handle new comment submission
$commentError = '';
$commentSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $commentContent = trim($_POST['comment_content']);
    $commentAuthor = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $parentId = isset($_POST['parent_id']) && is_numeric($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    if ($commentContent === '') {
        $commentError = 'Comment cannot be empty.';
    } else {
        $isReply = $parentId ? true : false;
        $approved = $isReply ? 1 : 0;
        $insertComment = $pdo->prepare("INSERT INTO blog_comments (blog_id, author_id, content, created_at, approved, parent_id) VALUES (?, ?, ?, NOW(), ?, ?)");
        $insertComment->execute([$blogId, $commentAuthor, $commentContent, $approved, $parentId]);
        // Send email notification to admin
        $adminEmail = 'info@bonniecomputerhub.com';
        $subject = "New Blog Comment Pending Approval";
        $message = "A new comment has been submitted for approval on blog: ".htmlspecialchars($blog['title'])."\n\nContent:\n".htmlspecialchars($commentContent);
        @mail($adminEmail, $subject, $message);
        // Notify parent comment author if this is a reply
        if ($parentId) {
            $parentStmt = $pdo->prepare("SELECT u.email, u.name FROM blog_comments c LEFT JOIN users u ON c.author_id = u.id WHERE c.id = ?");
            $parentStmt->execute([$parentId]);
            $parentInfo = $parentStmt->fetch(PDO::FETCH_ASSOC);
            if ($parentInfo && !empty($parentInfo['email'])) {
                $replySubject = "You have a new reply to your comment on Bonnie Computer Hub";
                $replyMessage = "Hi ".$parentInfo['name'].",\n\nYou have received a reply to your comment on the blog: ".htmlspecialchars($blog['title']).".\n\nReply Content:\n".htmlspecialchars($commentContent)."\n\nVisit the blog to view the reply.";
                @mail($parentInfo['email'], $replySubject, $replyMessage);
            }
        }
        $commentSuccess = 'Your comment has been submitted and is pending approval.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog['title']) ?> | Bonnie Computer Hub</title>
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($blog['title']) ?>" />
    <meta property="og:description" content="<?= strip_tags(mb_substr($blog['content'],0,150)) ?>" />
    <meta property="og:image" content="<?= $blog['image'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/assets/images/blogs/' . htmlspecialchars($blog['image']) : '' ?>" />
    <meta property="og:url" content="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />
    <meta property="og:type" content="article" />
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($blog['title']) ?>" />
    <meta name="twitter:description" content="<?= strip_tags(mb_substr($blog['content'],0,150)) ?>" />
    <meta name="twitter:image" content="<?= $blog['image'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/assets/images/blogs/' . htmlspecialchars($blog['image']) : '' ?>" />
    <meta name="description" content="<?= strip_tags(mb_substr($blog['content'],0,150)) ?>" />
    <link href="assets/css/tailwind.min.css" rel="stylesheet">
    <link href="assets/css/design-system.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/utilities.css" rel="stylesheet">
    <!-- Amazon Ember font via onlinewebfonts, fallback to Inter, Arial, sans-serif -->
    <link rel="stylesheet" href="//db.onlinewebfonts.com/c/157c6cc36dd65b1b2adc9e7f3329c761?family=Amazon+Ember">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap">
    <link rel="stylesheet" href="assets/css/bch-global.css">
    
    
    <script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-50 font-inter min-h-screen flex flex-col">
    <?php include 'LMS/includes/header.php'; ?>
    <?php
        $contentStripped = strip_tags($blog['content']);
        $wordCount = str_word_count($contentStripped);
        $readingTime = max(1, ceil($wordCount / 200));
    ?>
    <main class="flex-1 px-4 py-8 max-w-3xl mx-auto w-full">
        <div class="bg-bch-gray-100 rounded-xl shadow-lg p-8 border border-bch-blue-light mb-8">
            <?php if ($blog['image']): ?>
                <div class="relative w-full h-64 mb-6 rounded-lg overflow-hidden">
                    <img src="assets/images/blogs/<?= htmlspecialchars($blog['image']) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent flex flex-col justify-end p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div>
                                <span class="inline-block bg-yellow-600 text-white text-xs font-semibold px-3 py-1 rounded-full mb-2">Live from Bonnie Computer Hub</span>
                                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-white mb-1 drop-shadow-lg"><?= htmlspecialchars($blog['title']) ?></h1>
                                <div class="flex items-center gap-3 text-gray-200 text-xs">
                                    <span><i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($blog['created_at'])) ?></span>
                                    <span class="hidden md:inline">|</span>
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($blog['author_name']) ?></span>
                                    <span class="hidden md:inline">|</span>
                                    <span class="italic"><i class="fas fa-clock"></i> <?= $readingTime ?> min read</span>
                                </div>
                            </div>
                            <a href="blogs.php" class="inline-block mt-2 md:mt-0 bg-bch-blue hover:bg-bch-gold-dark text-white font-semibold px-4 py-2 rounded-full shadow transition">&larr; Back to Blog List</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="prose max-w-none mb-6">
                <?= $blog['content'] ?>
            </div>
            <!-- Share Buttons -->
            <div class="flex gap-4 mb-6">
                <span class="font-semibold text-bch-gray-900">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="hover:opacity-80"><i class="fab fa-facebook fa-lg text-blue-600"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($blog['title']) ?>" target="_blank" class="hover:opacity-80"><i class="fab fa-twitter fa-lg text-blue-400"></i></a>
                <a href="https://wa.me/?text=<?= urlencode($blog['title'] . ' ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="hover:opacity-80"><i class="fab fa-whatsapp fa-lg text-green-500"></i></a>
                <button onclick="navigator.clipboard.writeText(window.location.href);this.innerText='Copied!'" class="text-bch-gray-900 hover:text-bch-blue focus:outline-none"><i class="fas fa-link fa-lg"></i></button>
            </div>
            <!-- Comments -->
            <div class="mt-10">
                <h2 class="text-xl font-bold text-bch-blue mb-4">Comments</h2>
                <?php if (!empty($commentSuccess)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded">
                        <?= htmlspecialchars($commentSuccess) ?>
                    </div>
                <?php endif; ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-600 text-yellow-700 p-3 mb-4 rounded">
                    Comments are subject to admin approval and may not appear immediately.
                </div>
                <?php if (!empty($commentError)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                        <?= htmlspecialchars($commentError) ?>
                    </div>
                <?php endif; ?>
                <form method="POST" class="mb-8">
                    <textarea name="comment_content" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-bch-yellow-600 focus:border-transparent" placeholder="Leave a comment..." required></textarea>
                    <input type="hidden" name="parent_id" id="parent_id" value="">
                    <button type="submit" class="mt-2 bg-yellow-600 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-bold shadow transition-all">Post Comment</button>
                </form>
                <div class="space-y-6" id="comments-list">
                    <?php render_comments(null, $threaded); ?>
                    <?php if (empty($comments)): ?>
                        <div class="text-bch-gray-900 italic">No comments yet. Be the first to comment!</div>
                    <?php endif; ?>
                </div>
            </div>
            <script>
            document.querySelectorAll('.reply-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-comment');
                    document.querySelectorAll('.reply-form').forEach(f => f.style.display = 'none');
                    const form = document.getElementById('reply-form-' + commentId);
                    if (form) {
                        form.innerHTML = `<form method=\'POST\' class=\'mb-2\'><textarea name=\'comment_content\' rows=\'2\' class=\'w-full px-3 py-1 border rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent\' placeholder=\'Reply...\' required></textarea><input type=\'hidden\' name=\'parent_id\' value=\'${commentId}\'><button type=\'submit\' class=\'mt-1 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-1 rounded-lg font-bold shadow transition-all text-sm\'>Reply</button></form>`;
                        form.style.display = '';
                    }
                });
            });
            </script>
        </div>
        <div class="mt-6">
            <a href="blogs.php" class="text-bch-blue hover:underline">&larr; Back to Blog List</a>
        </div>
    </main>
    <?php include 'LMS/includes/footer.php'; ?>
</body>
</html>
