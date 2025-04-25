<?php
session_start();
require_once '../includes/db_connect.php';
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'guest';
// Handle new thread
if ($user_id && isset($_POST['new_thread'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'General';
    if ($title && $content) {
        $pdo->prepare('INSERT INTO alumni_forum_threads (user_id, title, category) VALUES (?, ?, ?)')->execute([$user_id, $title, $category]);
        $thread_id = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO alumni_forum_posts (thread_id, user_id, content) VALUES (?, ?, ?)')->execute([$thread_id, $user_id, $content]);
        header('Location: community_forum.php');
        exit;
    }
}
// Handle moderation actions
if (($user_role === 'admin' || $user_role === 'instructor') && isset($_POST['mod_action'], $_POST['mod_thread_id'])) {
    $tid = intval($_POST['mod_thread_id']);
    if ($_POST['mod_action'] === 'pin') {
        $pdo->prepare('UPDATE alumni_forum_threads SET pinned = 1 WHERE id = ?')->execute([$tid]);
    } elseif ($_POST['mod_action'] === 'unpin') {
        $pdo->prepare('UPDATE alumni_forum_threads SET pinned = 0 WHERE id = ?')->execute([$tid]);
    } elseif ($_POST['mod_action'] === 'close') {
        $pdo->prepare('UPDATE alumni_forum_threads SET closed = 1 WHERE id = ?')->execute([$tid]);
    } elseif ($_POST['mod_action'] === 'open') {
        $pdo->prepare('UPDATE alumni_forum_threads SET closed = 0 WHERE id = ?')->execute([$tid]);
    } elseif ($_POST['mod_action'] === 'delete') {
        $pdo->prepare('DELETE FROM alumni_forum_threads WHERE id = ?')->execute([$tid]);
        $pdo->prepare('DELETE FROM alumni_forum_posts WHERE thread_id = ?')->execute([$tid]);
    }
    header('Location: community_forum.php');
    exit;
}

// Fetch threads (all, not just alumni)
$threads = $pdo->query('SELECT t.*, u.name FROM alumni_forum_threads t JOIN users u ON t.user_id = u.id ORDER BY t.pinned DESC, t.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = "Community Forum";
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold text-primary mb-6">Community Forum</h1>
  <p class="mb-6 text-gray-700 max-w-2xl">Discuss, ask questions, and share with the entire BCH community. All students, alumni, instructors, and admins can post and reply here.</p>
  <?php if ($user_id): ?>
    <form method="post" class="mb-10 bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-3">Start a New Discussion</h2>
      <label class="block mb-2">Category:</label>
      <select name="category" class="border rounded px-3 py-2 mb-2">
        <option value="General">General</option>
        <option value="Course Q&A">Course Q&amp;A</option>
        <option value="Announcements">Announcements</option>
      </select>
      <input name="title" required maxlength="255" placeholder="Thread title" class="w-full border rounded px-3 py-2 mb-2">
      <textarea name="content" required placeholder="Write your post..." class="w-full border rounded px-3 py-2 mb-2"></textarea>
      <button type="submit" name="new_thread" class="bg-primary text-white px-6 py-2 rounded">Post</button>
    </form>
  <?php endif; ?>
  <section>
    <h2 class="text-2xl font-semibold text-primary mb-4">Recent Threads</h2>
    <div class="space-y-6">
      <?php foreach ($threads as $thread): ?>
        <div class="bg-white rounded-xl shadow p-6 relative">
          <div class="absolute top-4 right-4 flex gap-2">
            <?php if ($thread['pinned']): ?>
              <span title="Pinned" class="inline-block bg-yellow-300 text-yellow-900 text-xs font-bold px-2 py-1 rounded mr-1">PINNED</span>
            <?php endif; ?>
            <?php if ($thread['closed']): ?>
              <span title="Closed" class="inline-block bg-gray-400 text-white text-xs font-bold px-2 py-1 rounded">CLOSED</span>
            <?php endif; ?>
            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded ml-1"><?= htmlspecialchars($thread['category']) ?></span>
          </div>
          <h3 class="text-lg font-bold text-primary mb-1">
            <a href="community_thread.php?id=<?= $thread['id'] ?>" class="hover:underline"><?= htmlspecialchars($thread['title']) ?></a>
          </h3>
          <div class="text-gray-600 text-sm mb-2">By <?= htmlspecialchars($thread['name']) ?> &bull; <?= date('M j, Y', strtotime($thread['created_at'])) ?></div>
          <a href="community_thread.php?id=<?= $thread['id'] ?>" class="text-blue-700 underline">View Discussion</a>
          <?php if ($user_role === 'admin' || $user_role === 'instructor'): ?>
            <form method="post" class="mt-2 flex gap-2">
              <input type="hidden" name="mod_thread_id" value="<?= $thread['id'] ?>">
              <?php if (!$thread['pinned']): ?>
                <button type="submit" name="mod_action" value="pin" class="text-xs bg-yellow-200 text-yellow-900 rounded px-2 py-1">Pin</button>
              <?php else: ?>
                <button type="submit" name="mod_action" value="unpin" class="text-xs bg-yellow-100 text-yellow-900 rounded px-2 py-1">Unpin</button>
              <?php endif; ?>
              <?php if (!$thread['closed']): ?>
                <button type="submit" name="mod_action" value="close" class="text-xs bg-gray-300 text-gray-800 rounded px-2 py-1">Close</button>
              <?php else: ?>
                <button type="submit" name="mod_action" value="open" class="text-xs bg-green-200 text-green-800 rounded px-2 py-1">Open</button>
              <?php endif; ?>
              <button type="submit" name="mod_action" value="delete" class="text-xs bg-red-200 text-red-800 rounded px-2 py-1" onclick="return confirm('Delete this thread?')">Delete</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (empty($threads)): ?>
        <div class="text-gray-500 text-center">No discussions yet. Be the first to start a thread!</div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include '../includes/footer.php'; ?>
