<?php
session_start();
require_once '../includes/db_connect.php';

// Only logged in users can opt in/out, but page is public
$user_id = $_SESSION['user_id'] ?? null;
$opted_in = false;
if ($user_id) {
    $stmt = $pdo->prepare('SELECT opted_in FROM alumni_optin WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $opted_in = $stmt->fetchColumn() ? true : false;
    // Handle opt-in/out
    if (isset($_POST['alumni_optin'])) {
        if ($_POST['alumni_optin'] === '1') {
            $pdo->prepare('REPLACE INTO alumni_optin (user_id, opted_in) VALUES (?, 1)')->execute([$user_id]);
            $opted_in = true;
            // Award Alumni badge if not already present
            $badge_id = $pdo->query("SELECT id FROM badges WHERE criteria = 'alumni_optin'")->fetchColumn();
            $stmt = $pdo->prepare('INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)');
            if ($stmt->execute([$user_id, $badge_id]) && $stmt->rowCount() > 0) {
                echo '<div class="max-w-lg mx-auto mb-4 bg-yellow-100 text-yellow-800 border border-yellow-300 rounded p-4 text-center font-semibold">🎓 Congrats! You earned the Alumni badge!</div>';
            }
        } else {
            $pdo->prepare('DELETE FROM alumni_optin WHERE user_id = ?')->execute([$user_id]);
            $opted_in = false;
        }
    }
}
// List all alumni (users who completed any course and opted in)
$alumni = $pdo->query('SELECT u.id, u.name, u.email, MAX(e.completed_at) as last_completed FROM users u JOIN enrollments e ON u.id = e.user_id JOIN alumni_optin ao ON u.id = ao.user_id WHERE e.status = "completed" AND ao.opted_in = 1 GROUP BY u.id ORDER BY last_completed DESC')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "BCH Alumni Community";
include '../includes/header.php';
?>
<main class="container mx-auto px-4 py-10">
  <h1 class="text-3xl font-bold text-primary mb-6">BCH Alumni Community</h1>
  <p class="mb-6 text-gray-700 max-w-2xl">Welcome to the BCH Alumni network! Connect with fellow graduates, share your achievements, and grow your professional network. Opt in below to join the alumni list and discussion board.</p>
  <?php if ($user_id): ?>
    <form method="post" class="mb-8 flex items-center gap-4">
      <label class="font-semibold">Alumni Opt-In:</label>
      <select name="alumni_optin" class="border rounded px-3 py-2">
        <option value="1" <?= $opted_in ? 'selected' : '' ?>>Yes, show me as alumni</option>
        <option value="0" <?= !$opted_in ? 'selected' : '' ?>>No, hide me from alumni</option>
      </select>
      <button type="submit" class="ml-2 bg-primary text-white px-4 py-2 rounded">Update</button>
    </form>
  <?php endif; ?>
  <section class="mb-10">
    <h2 class="text-2xl font-semibold text-primary mb-4">Alumni List</h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($alumni as $alum): ?>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="w-16 h-16 rounded-full bg-yellow-400 flex items-center justify-center text-2xl font-bold text-white mb-3">
            <?= strtoupper(substr($alum['name'], 0, 2)) ?>
          </div>
          <div class="font-bold text-lg text-primary mb-1"><?= htmlspecialchars($alum['name']) ?></div>
          <div class="text-gray-600 text-sm mb-1">Last completed: <?= date('M Y', strtotime($alum['last_completed'])) ?></div>
          <div class="text-gray-500 text-xs">Email: <?= htmlspecialchars($alum['email']) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($alumni)): ?>
        <div class="col-span-full text-gray-500 text-center">No alumni yet. Complete a course and opt in to join!</div>
      <?php endif; ?>
    </div>
  </section>
  <section>
    <h2 class="text-2xl font-semibold text-primary mb-4">Alumni Discussion Board</h2>
    <a href="alumni_forum.php" class="inline-block bg-yellow-400 text-white font-semibold px-6 py-2 rounded shadow hover:bg-yellow-500 transition">Go to Alumni Forum</a>
  </section>
</main>
<?php include '../includes/footer.php'; ?>
