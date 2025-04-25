<?php
require_once '../includes/db_connect.php';
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && !empty($row['avatar'])) {
    $avatarData = $row['avatar'];
    // Detect image type from binary signature
    if (substr($avatarData, 0, 2) === "\xFF\xD8") {
        header('Content-Type: image/jpeg');
    } elseif (substr($avatarData, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A") {
        header('Content-Type: image/png');
    } elseif (substr($avatarData, 0, 6) === "GIF87a" || substr($avatarData, 0, 6) === "GIF89a") {
        header('Content-Type: image/gif');
    } elseif (substr($avatarData, 0, 4) === "RIFF" && substr($avatarData, 8, 4) === "WEBP") {
        header('Content-Type: image/webp');
    } else {
        // Unknown or corrupt image, fallback to default
        header('Content-Type: image/png');
        readfile('../assets/images/default-avatar.png');
        exit;
    }
    echo $avatarData;
    exit;
} else {
    header('Content-Type: image/png');
    readfile('../assets/images/default-avatar.png');
    exit;
}
