<?php
session_start();
require_once '../includes/db_connect.php';
$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$thread_id) {
    echo '<div class="text-center text-red-600 font-bold py-12">Invalid thread.</div>';
    exit;
}
// Fetch thread info
$thread = $pdo->prepare('SELECT t.*, u.name FROM alumni_forum_threads t JOIN users u ON t.user_id = u.id WHERE t.id = ?');
$thread->execute([$thread_id]);
$thread = $thread->fetch(PDO::FETCH_ASSOC);
if (!$thread) {
    echo '<div class="text-center text-red-600 font-bold py-12">Thread not found.</div>';
    exit;
}
// Fetch posts
$posts = $pdo->prepare('SELECT p.*, u.name FROM alumni_forum_posts p JOIN users u ON p.user_id = u.id WHERE p.thread_id = ? ORDER BY p.created_at ASC');
$posts->execute([$thread_id]);
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);
// Only alumni can reply
$user_id = $_SESSION['user_id'] ?? null;
$is_alumni = false;
if ($user_id) {
    $stmt = $pdo->prepare('SELECT opted_in FROM alumni_optin WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $is_alumni = $stmt->fetchColumn() ? true : false;
    // Handle reply
    if ($is_alumni && isset($_POST['reply'])) {
        $content = trim($_POST['content'] ?? '');
        if ($content) {
            $pdo->prepare('INSERT INTO alumni_forum_posts (thread_id, user_id, content) VALUES (?, ?, ?)')->execute([$thread_id, $user_id, $content]);
            header('Location: alumni_thread.php?id=' . $thread_id);
            exit;
        }
    }
}
$pageTitle = htmlspecialchars($thread['title']) . " - Alumni Forum";
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold text-primary mb-6">Alumni Discussion: <?= htmlspecialchars($thread['title']) ?></h1>
  <div class="mb-8 bg-white rounded shadow p-6">
    <div class="font-bold text-lg text-primary mb-1">By <?= htmlspecialchars($thread['name']) ?> &bull; <?= date('M j, Y', strtotime($thread['created_at'])) ?></div>
    <div class="text-gray-700 mb-2"><?= nl2br(htmlspecialchars($thread['content'] ?? '')) ?></div>
  </div>
  <section class="mb-10">
    <h2 class="text-xl font-semibold text-primary mb-4">Replies</h2>
    <div class="space-y-6">
      <?php foreach ($posts as $post): ?>
        <div class="bg-gray-50 border-l-4 border-yellow-400 rounded p-4">
          <div class="font-semibold text-primary mb-1"><?= htmlspecialchars($post['name']) ?> <span class="text-xs text-gray-400">&bull; <?= date('M j, Y H:i', strtotime($post['created_at'])) ?></span></div>
          <div class="text-gray-700"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($posts)): ?>
        <div class="text-gray-500 text-center">No replies yet.</div>
      <?php endif; ?>
    </div>
  </section>
  <?php if ($is_alumni): ?>
    <form method="post" class="bg-white p-6 rounded shadow">
      <h3 class="text-lg font-semibold mb-2">Post a Reply</h3>
      <textarea name="content" required placeholder="Write your reply..." class="w-full border rounded px-3 py-2 mb-2"></textarea>
      <button type="submit" name="reply" class="bg-primary text-white px-6 py-2 rounded">Reply</button>
    </form>
  <?php else: ?>
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded px-4 py-2 text-center mt-6">
      Only BCH alumni can post replies. <a href="alumni.php" class="underline text-yellow-700">Learn more</a>
    </div>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>
