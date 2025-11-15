<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');

$u = current_user();
$userId = (int)$u['user_id'];

$stmt = $pdo->prepare('SELECT notification_id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
$stmt->execute([$userId]);
$list = $stmt->fetchAll();

$total = count($list);
$unread = array_reduce($list, fn($carry, $row) => $carry + ((int)$row['is_read'] === 0 ? 1 : 0), 0);

echo json_encode([
    'notifications' => $list,
    'total' => $total,
    'unread' => $unread,
    'notif_unread' => unread_notifications_count($userId),
]);
