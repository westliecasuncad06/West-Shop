<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$u = current_user();
$userId = (int)$u['user_id'];
$isAjax = false;
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $isAjax = true;
}
if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
    $isAjax = true;
}

function chat_send_response(array $payload, bool $isAjax, string $redirectPath = 'index.php'): void {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
    if (!empty($payload['success'])) {
        set_flash('success', 'Message sent successfully.');
    } else {
        $msg = isset($payload['error']) ? (string)$payload['error'] : 'Unable to send message.';
        set_flash('danger', $msg);
    }
    redirect($redirectPath);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chat_send_response([
        'success' => false,
        'error' => 'Invalid request method.',
    ], $isAjax);
}

if (!csrf_verify()) {
    chat_send_response([
        'success' => false,
        'error' => 'Security token mismatch.',
    ], $isAjax, $_POST['redirect_to'] ?? 'index.php');
}

$partnerId = (int)($_POST['partner_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$redirectTo = trim($_POST['redirect_to'] ?? 'index.php');
if ($redirectTo === '') {
    $redirectTo = 'index.php';
}

if ($partnerId <= 0 || $message === '') {
    chat_send_response([
        'success' => false,
        'error' => 'Choose a recipient and enter a message.',
    ], $isAjax, $redirectTo);
}

$stmt = $pdo->prepare('INSERT INTO chat_messages(sender_id, receiver_id, message) VALUES (?,?,?)');
$stmt->execute([$userId, $partnerId, $message]);
$newId = (int)$pdo->lastInsertId();

$fetch = $pdo->prepare('SELECT message_id, sender_id, receiver_id, message, timestamp FROM chat_messages WHERE message_id = ? LIMIT 1');
$fetch->execute([$newId]);
$newMessage = $fetch->fetch();

chat_send_response([
    'success' => true,
    'message' => $newMessage ?: [
        'message_id' => $newId,
        'sender_id' => $userId,
        'receiver_id' => $partnerId,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
    ],
    'chat_unread' => chat_unread_count($userId),
    'chat_badges' => chat_unread_breakdown($userId, $u['role'] ?? ''),
], $isAjax, $redirectTo);
