<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    mark_notifications_read((int)$u['user_id']);
    header('Location: '.base_url('notifications.php'));
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$u['user_id']]);
$list = $stmt->fetchAll();

include __DIR__ . '/templates/header.php';
?>
<h4 class="mb-3">Notifications</h4>
<form method="post" class="mb-3">
  <?php echo csrf_field(); ?>
  <button class="btn btn-outline-primary">Mark all as read</button>
  </form>
<ul class="list-group">
  <?php foreach($list as $n): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
      <span><?php echo e($n['message']); ?></span>
      <?php if(!$n['is_read']): ?><span class="badge bg-warning text-dark">New</span><?php endif; ?>
    </li>
  <?php endforeach; ?>
  <?php if(!$list): ?><li class="list-group-item text-muted">No notifications.</li><?php endif; ?>
</ul>
<?php include __DIR__ . '/templates/footer.php'; ?>
