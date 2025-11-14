<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('buyer');
$u = current_user();

// Build a list of sellers the buyer interacted with via orders
$sellers = $pdo->prepare('SELECT DISTINCT u.user_id, u.name
  FROM users u
  JOIN products p ON p.seller_id = u.user_id
  JOIN order_items oi ON oi.product_id = p.product_id
  JOIN orders o ON o.order_id = oi.order_id AND o.buyer_id = ?
  WHERE u.role = "seller"');
$sellers->execute([$u['user_id']]);
$sellers = $sellers->fetchAll();

$receiver = isset($_GET['to']) ? (int)$_GET['to'] : ((isset($sellers[0]['user_id'])) ? (int)$sellers[0]['user_id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $msg = trim($_POST['message'] ?? '');
    $to = (int)($_POST['to'] ?? 0);
    if ($msg !== '' && $to > 0) {
        $stmt = $pdo->prepare('INSERT INTO chat_messages(sender_id, receiver_id, message) VALUES (?,?,?)');
        $stmt->execute([$u['user_id'], $to, $msg]);
    }
    header('Location: '.base_url('buyer/chat.php?to='.$to));
    exit;
}

// Fetch conversation
$conversation = [];
if ($receiver > 0) {
    $stmt = $pdo->prepare('SELECT * FROM chat_messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY timestamp ASC');
    $stmt->execute([$u['user_id'], $receiver, $receiver, $u['user_id']]);
    $conversation = $stmt->fetchAll();
}

include __DIR__ . '/../templates/header.php';
?>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3">
      <h6>My Sellers</h6>
      <ul class="list-group list-group-flush">
        <?php foreach($sellers as $s): ?>
          <li class="list-group-item <?php echo ($receiver===(int)$s['user_id'])?'active':''; ?>">
            <a class="text-decoration-none <?php echo ($receiver===(int)$s['user_id'])?'text-white':''; ?>" href="<?php echo e(base_url('buyer/chat.php?to='.(int)$s['user_id'])); ?>"><?php echo e($s['name']); ?></a>
          </li>
        <?php endforeach; ?>
        <?php if(!$sellers): ?><li class="list-group-item text-muted">No sellers yet.</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-8">
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
        <?php if(!$conversation): ?><div class="text-muted">Start the conversation.</div><?php endif; ?>
      </div>
      <?php if($receiver>0): ?>
      <form method="post" class="d-flex gap-2">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="to" value="<?php echo (int)$receiver; ?>">
        <input class="form-control" name="message" placeholder="Type your message..." required>
        <button class="btn btn-primary">Send</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
