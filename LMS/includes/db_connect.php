<?php
// Database connection for BCH LMS

$host = 'localhost';
$db   = 'bch-lms';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// === LOGIN STREAK LOGIC ===
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $streak = $pdo->prepare('SELECT current_streak, longest_streak, last_active FROM user_streaks WHERE user_id = ?');
    $streak->execute([$uid]);
    $row = $streak->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        if ($row['last_active'] === $today) {
            // Already logged today; do nothing
        } elseif ($row['last_active'] === $yesterday) {
            // Continue streak
            $new_streak = $row['current_streak'] + 1;
            $longest = max($row['longest_streak'], $new_streak);
            $upd = $pdo->prepare('UPDATE user_streaks SET current_streak = ?, longest_streak = ?, last_active = ? WHERE user_id = ?');
            $upd->execute([$new_streak, $longest, $today, $uid]);
        } else {
            // Reset streak
            $upd = $pdo->prepare('UPDATE user_streaks SET current_streak = 1, last_active = ? WHERE user_id = ?');
            $upd->execute([$today, $uid]);
        }
    } else {
        // First login, create streak record
        $ins = $pdo->prepare('INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_active) VALUES (?, 1, 1, ?)');
        $ins->execute([$uid, $today]);
    }
}
// === END LOGIN STREAK LOGIC ===
