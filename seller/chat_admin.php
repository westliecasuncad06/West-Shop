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
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <p class="section-label mb-1">Need a hand?</p>
    <h4 class="mb-0">Support Chat</h4>
  </div>
  <span class="badge-chip">Live with admin desk</span>
</div>

<div class="chat-support-shell card overflow-hidden">
  <div class="row g-0">
    <div class="col-lg-4 chat-support-sidebar">
      <div class="p-4 h-100 d-flex flex-column gap-4">
        <div>
          <p class="text-uppercase small text-muted mb-1">Channel</p>
          <h5 class="mb-0">Seller Success Team</h5>
          <p class="text-muted small mb-0">Expect human replies within minutes.</p>
        </div>
        <div class="support-card">
          <span class="support-card__icon">👩‍💼</span>
          <div>
            <strong>Admin Desk</strong>
            <p class="text-muted small mb-0">Online 9AM - 6PM</p>
          </div>
        </div>
        <ul class="list-unstyled text-muted small mb-0">
          <li><i class="bi bi-check2-circle text-primary me-2"></i>Escalations handled in one thread</li>
          <li><i class="bi bi-check2-circle text-primary me-2"></i>Attach screenshots or SKU codes</li>
          <li><i class="bi bi-check2-circle text-primary me-2"></i>We reply in chronological order</li>
        </ul>
      </div>
    </div>
    <div class="col-lg-8 border-start">
      <div class="chat-thread" id="chatThread">
        <?php foreach($conversation as $m): ?>
          <?php $isSeller = ($m['sender_id']===$u['user_id']); ?>
          <div class="chat-message <?php echo $isSeller ? 'chat-message--self' : ''; ?>">
            <div class="chat-bubble">
              <div><?php echo nl2br(e($m['message'])); ?></div>
              <span class="chat-timestamp"><?php echo date('M d, g:i A', strtotime($m['timestamp'])); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if(!$conversation): ?><div class="empty-panel">Say hello to start the conversation.</div><?php endif; ?>
      </div>
      <form method="post" class="chat-input-row">
        <?php echo csrf_field(); ?>
        <input class="form-control chat-message-input" name="message" placeholder="Type your message..." required>
        <button class="btn btn-primary"><i class="bi bi-send"></i> Send</button>
      </form>
    </div>
  </div>
</div>

<script>
  (function(){
    var thread = document.getElementById('chatThread');
    if (thread) {
      thread.scrollTop = thread.scrollHeight;
    }
  })();
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
