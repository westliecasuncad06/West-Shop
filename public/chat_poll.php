<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');

$u = current_user();
$userId = (int)$u['user_id'];
$partnerId = (int)($_GET['partner_id'] ?? 0);

if ($partnerId <= 0) {
    $chatBadges = chat_unread_breakdown($userId, $u['role'] ?? '');
    $totalUnread = chat_unread_count($userId);
    echo json_encode([
        'messages' => [],
        'chat_unread' => $totalUnread,
        'chat_badges' => $chatBadges,
    ]);
    exit;
}

$stmt = $pdo->prepare('SELECT message_id, sender_id, receiver_id, message, timestamp
    FROM chat_messages
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY timestamp ASC LIMIT 200');
$stmt->execute([$userId, $partnerId, $partnerId, $userId]);
$messages = $stmt->fetchAll();

chat_mark_conversation_read($userId, $partnerId);

$chatBadges = chat_unread_breakdown($userId, $u['role'] ?? '');
$totalUnread = chat_unread_count($userId);

echo json_encode([
    'messages' => $messages,
    'chat_unread' => $totalUnread,
    'chat_badges' => $chatBadges,
]);
