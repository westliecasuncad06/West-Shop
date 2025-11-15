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
$totalNotif = count($list);
$unreadNotif = array_reduce($list, fn($carry,$row)=>$carry + ((int)$row['is_read'] === 0 ? 1 : 0), 0);

include __DIR__ . '/templates/header.php';
?>
<style>
.notif-hero {
  background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
  border-radius: 1.2rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
}
.notif-hero h4 { margin: 0; }
.notif-card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 25px 45px rgba(15,23,42,.08);
}
.notif-item + .notif-item { border-top: 1px solid #f1f5f9; }
.notif-item {
  padding: 1rem 0;
  display: flex;
  gap: 1rem;
}
.notif-item.unread {
  background: #f8fbff;
  border-radius: .9rem;
  padding: 1rem;
}
.notif-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: .5rem;
  background: #38bdf8;
}
.notif-meta {
  font-size: .85rem;
  color: #94a3b8;
}
.notif-empty {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
}
.notif-actions .btn { min-width: 160px; }
</style>

<div class="notif-hero d-flex flex-wrap justify-content-between align-items-center gap-3" id="notif-hero" data-endpoint="<?php echo e(base_url('public/notifications_poll.php')); ?>">
  <div>
    <div class="text-uppercase small text-muted">Inbox</div>
    <h4>Notifications</h4>
    <div class="text-muted small">Stay in sync with order updates, coupons, and messages.</div>
  </div>
  <div class="d-flex align-items-center gap-3">
    <div>
      <div class="fw-bold fs-4" data-notif-total><?php echo number_format($totalNotif); ?></div>
      <div class="text-muted small">Total</div>
    </div>
    <div>
      <div class="fw-bold fs-4 text-primary" data-notif-unread><?php echo number_format($unreadNotif); ?></div>
      <div class="text-muted small">Unread</div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end notif-actions mb-3">
  <form method="post">
    <?php echo csrf_field(); ?>
    <button class="btn btn-outline-primary"><i class="bi bi-check2-circle me-1"></i>Mark all as read</button>
  </form>
</div>

<div class="card notif-card p-4" id="notif-list">
  <?php if($list): ?>
    <?php foreach($list as $n): ?>
      <div class="notif-item <?php echo !$n['is_read'] ? 'unread' : ''; ?>">
        <span class="notif-dot"></span>
        <div class="flex-grow-1">
          <div class="fw-semibold"><?php echo e($n['message']); ?></div>
          <div class="notif-meta">Received <?php echo e(date('M d, Y g:i A', strtotime($n['created_at']))); ?><?php echo !$n['is_read'] ? ' • Unread' : ''; ?></div>
        </div>
        <?php if(!$n['is_read']): ?><span class="badge bg-info text-dark align-self-start">New</span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="notif-empty">
      <i class="bi bi-bell-slash fs-3 mb-2 d-block"></i>
      Nothing to catch up on yet. Keep shopping!
    </div>
  <?php endif; ?>
</div>
<script>
(function(){
  const hero = document.getElementById('notif-hero');
  const listEl = document.getElementById('notif-list');
  if(!hero || !listEl) return;
  const endpoint = hero.dataset.endpoint;
  if(!endpoint) return;
  const totalEl = hero.querySelector('[data-notif-total]');
  const unreadEl = hero.querySelector('[data-notif-unread]');
  const navBadge = document.querySelector('[data-notif-count]');

  const renderList = (items) => {
    if(!Array.isArray(items) || items.length === 0) {
      listEl.innerHTML = '<div class="notif-empty"><i class="bi bi-bell-slash fs-3 mb-2 d-block"></i>Nothing to catch up on yet. Keep shopping!</div>';
      return;
    }
    const html = items.map(item => {
      const unread = Number(item.is_read) === 0;
      const message = item.message ? item.message.replace(/[&<>"']/g, ch => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[ch] || ch)) : '';
      const time = item.created_at ? new Date(item.created_at).toLocaleString() : '';
      return `<div class="notif-item ${unread ? 'unread' : ''}">
        <span class="notif-dot"></span>
        <div class="flex-grow-1">
          <div class="fw-semibold">${message}</div>
          <div class="notif-meta">Received ${time}${unread ? ' • Unread' : ''}</div>
        </div>
        ${unread ? '<span class="badge bg-info text-dark align-self-start">New</span>' : ''}
      </div>`;
    }).join('');
    listEl.innerHTML = html;
  };

  const poll = async () => {
    try {
      const res = await fetch(endpoint, {credentials: 'same-origin'});
      if(!res.ok) return;
      const data = await res.json();
      if(!data) return;
      if(Array.isArray(data.notifications)) {
        renderList(data.notifications);
      }
      if(totalEl && typeof data.total !== 'undefined') {
        totalEl.textContent = Number(data.total).toLocaleString();
      }
      if(unreadEl && typeof data.unread !== 'undefined') {
        unreadEl.textContent = Number(data.unread).toLocaleString();
      }
      if(navBadge && typeof data.notif_unread !== 'undefined') {
        if(Number(data.notif_unread) > 0) {
          navBadge.textContent = data.notif_unread;
          navBadge.classList.remove('d-none');
        } else {
          navBadge.classList.add('d-none');
        }
      }
    } catch (err) {
      console.error('Notifications poll error', err);
    }
  };

  poll();
  setInterval(poll, 3000);
})();
</script>
<?php include __DIR__ . '/templates/footer.php'; ?>
