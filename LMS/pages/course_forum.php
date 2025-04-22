<?php
session_start();
require_once '../includes/db_connect.php';
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if (!$course_id) {
    echo '<div class="text-center text-red-600 font-bold py-12">Invalid course.</div>';
    exit;
}
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'guest';
// Handle new thread
if ($user_id && isset($_POST['new_thread'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $pdo->prepare('INSERT INTO alumni_forum_threads (user_id, title, category, course_id) VALUES (?, ?, ?, ?)')->execute([$user_id, $title, 'Course Q&A', $course_id]);
        $thread_id = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO alumni_forum_posts (thread_id, user_id, content) VALUES (?, ?, ?)')->execute([$thread_id, $user_id, $content]);
        header('Location: course_forum.php?course_id=' . $course_id);
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
    header('Location: course_forum.php?course_id=' . $course_id);
    exit;
}
// Fetch threads for this course
$threads = $pdo->prepare('SELECT t.*, u.name FROM alumni_forum_threads t JOIN users u ON t.user_id = u.id WHERE t.course_id = ? ORDER BY t.pinned DESC, t.created_at DESC');
$threads->execute([$course_id]);
$threads = $threads->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = "Course Forum";
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold text-primary mb-6">Course Q&amp;A Forum</h1>
  <p class="mb-6 text-gray-700 max-w-2xl">Ask questions and discuss topics related to this course. Instructors and fellow students can help!</p>
  <?php if ($user_id): ?>
    <form method="post" class="mb-10 bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-3">Start a New Thread</h2>
      <input name="title" required maxlength="255" placeholder="Thread title" class="w-full border rounded px-3 py-2 mb-2">
      <textarea name="content" required placeholder="Write your post..." class="w-full border rounded px-3 py-2 mb-2"></textarea>
      <button type="submit" name="new_thread" class="bg-primary text-white px-6 py-2 rounded">Post</button>
    </form>
  <?php endif; ?>
  <section>
    <h2 class="text-2xl font-semibold text-primary mb-4">Threads for This Course</h2>
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
        <div class="text-gray-500 text-center">No threads yet for this course.</div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include '../includes/footer.php'; ?>
