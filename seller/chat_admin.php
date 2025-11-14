<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');
$u = current_user();

// Find an admin to chat with (first admin)
$adminId = (int)($pdo->query("SELECT user_id FROM users WHERE role='admin' ORDER BY user_id ASC LIMIT 1")->fetchColumn());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $msg = trim($_POST['message'] ?? '');
    if ($msg !== '' && $adminId) {
        $stmt = $pdo->prepare('INSERT INTO chat_messages(sender_id, receiver_id, message) VALUES (?,?,?)');
        $stmt->execute([$u['user_id'], $adminId, $msg]);
    }
    header('Location: '.base_url('seller/chat_admin.php'));
    exit;
}

$conversation = [];
if ($adminId) {
    $stmt = $pdo->prepare('SELECT * FROM chat_messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY timestamp ASC');
    $stmt->execute([$u['user_id'], $adminId, $adminId, $u['user_id']]);
    $conversation = $stmt->fetchAll();
}

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Support Chat</h4>
<div class="card p-3" style="min-height:400px;">
  <div class="mb-3" style="height:300px; overflow:auto; background:#f9fbff; border-radius:.75rem; padding:1rem;">
    <?php foreach($conversation as $m): ?>
      <div class="mb-2 d-flex <?php echo ($m['sender_id']===$u['user_id'])?'justify-content-end':''; ?>">
        <div class="px-3 py-2 rounded-3 <?php echo ($m['sender_id']===$u['user_id'])?'bg-primary text-white':'bg-white'; ?> shadow-sm" style="max-width:75%;">
          <div class="small"><?php echo nl2br(e($m['message'])); ?></div>
          <div class="text-muted small mt-1"><?php echo e($m['timestamp']); ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if(!$conversation): ?><div class="text-muted">No messages yet.</div><?php endif; ?>
  </div>
  <form method="post" class="d-flex gap-2">
    <?php echo csrf_field(); ?>
    <input class="form-control" name="message" placeholder="Type your message..." required>
    <button class="btn btn-primary">Send</button>
  </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
