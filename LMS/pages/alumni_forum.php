<?php
session_start();
require_once '../includes/db_connect.php';

// Only alumni can post, but anyone can view
$user_id = $_SESSION['user_id'] ?? null;
$is_alumni = false;
if ($user_id) {
    $stmt = $pdo->prepare('SELECT opted_in FROM alumni_optin WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $is_alumni = $stmt->fetchColumn() ? true : false;
}
// Handle new thread
if ($is_alumni && isset($_POST['new_thread'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $pdo->prepare('INSERT INTO alumni_forum_threads (user_id, title) VALUES (?, ?)')->execute([$user_id, $title]);
        $thread_id = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO alumni_forum_posts (thread_id, user_id, content) VALUES (?, ?, ?)')->execute([$thread_id, $user_id, $content]);
        header('Location: alumni_forum.php');
        exit;
    }
}
// Fetch threads
$threads = $pdo->query('SELECT t.*, u.name FROM alumni_forum_threads t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Alumni Forum";
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold text-primary mb-6">Alumni Discussion Board</h1>
  <p class="mb-6 text-gray-700 max-w-2xl">Network, share, and discuss with fellow BCH alumni. Only alumni can post, but all can read.</p>
  <?php if ($is_alumni): ?>
    <form method="post" class="mb-10 bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-3">Start a New Discussion</h2>
      <input name="title" required maxlength="255" placeholder="Thread title" class="w-full border rounded px-3 py-2 mb-2">
      <textarea name="content" required placeholder="Write your post..." class="w-full border rounded px-3 py-2 mb-2"></textarea>
      <button type="submit" name="new_thread" class="bg-primary text-white px-6 py-2 rounded">Post</button>
    </form>
  <?php endif; ?>
  <section>
    <h2 class="text-2xl font-semibold text-primary mb-4">Recent Threads</h2>
    <div class="space-y-6">
      <?php foreach ($threads as $thread): ?>
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-bold text-primary mb-1">
            <a href="alumni_thread.php?id=<?= $thread['id'] ?>" class="hover:underline"><?= htmlspecialchars($thread['title']) ?></a>
          </h3>
          <div class="text-gray-600 text-sm mb-2">By <?= htmlspecialchars($thread['name']) ?> &bull; <?= date('M j, Y', strtotime($thread['created_at'])) ?></div>
          <a href="alumni_thread.php?id=<?= $thread['id'] ?>" class="text-blue-700 underline">View Discussion</a>
        </div>
      <?php endforeach; ?>
      <?php if (empty($threads)): ?>
        <div class="text-gray-500 text-center">No discussions yet. Be the first to start a thread!</div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include '../includes/footer.php'; ?>
