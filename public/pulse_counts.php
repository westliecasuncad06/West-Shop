<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');

$u = current_user();
$userId = (int)$u['user_id'];

echo json_encode([
    'notifications' => unread_notifications_count($userId),
    'chat' => chat_unread_count($userId),
]);
