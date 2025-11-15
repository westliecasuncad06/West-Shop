<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$u = current_user();

$sellerStmt = $pdo->query('SELECT u.user_id, COALESCE(NULLIF(sp.shop_name, ""), u.name) AS store_name
  FROM users u
  LEFT JOIN seller_profiles sp ON sp.seller_id = u.user_id
  WHERE u.role = "seller"
  ORDER BY store_name ASC');
$sellers = $sellerStmt->fetchAll();

$defaultSellerId = $sellers ? (int)$sellers[0]['user_id'] : 0;
$sellerId = (int)($_GET['seller'] ?? $defaultSellerId);
if ($sellerId === 0 && $defaultSellerId > 0) {
    $sellerId = $defaultSellerId;
}

// Map unread counts per seller for the admin
$unreadMap = [];
$unreadStmt = $pdo->prepare('SELECT sender_id, COUNT(*) AS unread
    FROM chat_messages
    WHERE receiver_id = ? AND is_read = 0
    GROUP BY sender_id');
$unreadStmt->execute([$u['user_id']]);
foreach ($unreadStmt->fetchAll() as $row) {
    $unreadMap[(int)$row['sender_id']] = (int)$row['unread'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $message = trim($_POST['message'] ?? '');
    $targetId = (int)($_POST['seller_id'] ?? 0);
    if ($message !== '' && $targetId > 0) {
        $stmt = $pdo->prepare('INSERT INTO chat_messages(sender_id, receiver_id, message) VALUES (?,?,?)');
        $stmt->execute([$u['user_id'], $targetId, $message]);
        set_flash('success', 'Message sent');
    }
    redirect('admin/chat.php?seller=' . $targetId);
}

$conversation = [];
if ($sellerId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM chat_messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY timestamp ASC');
    $stmt->execute([$u['user_id'], $sellerId, $sellerId, $u['user_id']]);
    $conversation = $stmt->fetchAll();
    chat_mark_conversation_read((int)$u['user_id'], $sellerId);
}

include __DIR__ . '/../templates/header.php';
?>
<div class="row g-4 align-items-stretch">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body d-flex flex-column gap-3">
        <div>
          <p class="section-label mb-1">Seller inbox</p>
          <h5 class="mb-0">Support Desk</h5>
          <p class="text-muted small mb-0">View every seller thread from a single panel.</p>
        </div>
        <div class="list-group flex-grow-1 overflow-auto">
          <?php foreach ($sellers as $seller):
            $sid = (int)$seller['user_id'];
            $isActive = ($sid === $sellerId);
            $unread = $unreadMap[$sid] ?? 0;
          ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo e(base_url('admin/chat.php?seller=' . $sid)); ?>">
              <span><?php echo e($seller['store_name']); ?></span>
              <?php if($unread > 0): ?><span class="badge bg-warning text-dark"><?php echo $unread; ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
          <?php if(!$sellers): ?>
            <div class="list-group-item text-muted">No sellers found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-body d-flex flex-column gap-3">
        <div>
          <p class="section-label mb-1">Conversation</p>
          <h5 class="mb-1">Seller chat</h5>
          <p class="text-muted small mb-0">Messages sync with the seller support panel.</p>
        </div>
        <div class="flex-grow-1 bg-light rounded-4 p-3" style="min-height:320px; overflow:auto;" data-chat-scroll>
          <div id="admin-chat-thread" data-partner="<?php echo (int)$sellerId; ?>" data-endpoint="<?php echo e(base_url('public/chat_poll.php')); ?>" data-self="<?php echo (int)$u['user_id']; ?>">
            <div class="chat-thread-body">
              <?php foreach ($conversation as $msg): ?>
                <?php $isAdmin = ($msg['sender_id'] === (int)$u['user_id']); ?>
                <div class="mb-3 d-flex <?php echo $isAdmin ? 'justify-content-end' : ''; ?>">
                  <div class="px-3 py-2 rounded-3 shadow-sm <?php echo $isAdmin ? 'bg-primary text-white' : 'bg-white'; ?>" style="max-width:80%;">
                    <div class="small"><?php echo nl2br(e($msg['message'])); ?></div>
                    <div class="text-muted small mt-1"><?php echo date('M j, g:i A', strtotime($msg['timestamp'])); ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if(!$conversation): ?><div class="text-muted">Select a seller to view history.</div><?php endif; ?>
            </div>
          </div>
        </div>
        <?php if($sellerId > 0): ?>
        <form method="post" class="d-flex gap-2 align-items-start">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="seller_id" value="<?php echo (int)$sellerId; ?>">
          <textarea class="form-control" name="message" rows="2" placeholder="Write a reply..." required></textarea>
          <button class="btn btn-primary">
            <i class="bi bi-send"></i>
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php if($sellerId > 0): ?>
<script>
(function(){
  const wrapper = document.getElementById('admin-chat-thread');
  if(!wrapper) return;
  const body = wrapper.querySelector('.chat-thread-body');
  const scrollBox = wrapper.closest('[data-chat-scroll]');
  const endpoint = wrapper.dataset.endpoint;
  const partnerId = wrapper.dataset.partner;
  const selfId = Number(wrapper.dataset.self || 0);
  if(!endpoint || !partnerId) return;

  const scrollToBottom = () => {
    if(!scrollBox) return;
    requestAnimationFrame(() => {
      scrollBox.scrollTop = scrollBox.scrollHeight;
    });
  };

  const escapeHtml = (str) => (str || '').replace(/[&<>"']/g, (ch) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[ch] || ch));

  const renderMessages = (messages) => {
    if(!body) return;
    if(!Array.isArray(messages) || messages.length === 0) {
      body.innerHTML = '<div class="text-muted">Select a seller to view history.</div>';
      scrollToBottom();
      return;
    }
    const html = messages.map(msg => {
      const isSelf = Number(msg.sender_id) === selfId;
      const bubbleClass = isSelf ? 'bg-primary text-white' : 'bg-white';
      const alignClass = isSelf ? 'justify-content-end' : '';
      const safeMsg = escapeHtml(msg.message || '').replace(/\n/g,'<br>');
      const ts = escapeHtml(msg.timestamp || '');
      return `<div class="mb-3 d-flex ${alignClass}">
        <div class="px-3 py-2 rounded-3 shadow-sm ${bubbleClass}" style="max-width:80%;">
          <div class="small">${safeMsg}</div>
          <div class="text-muted small mt-1">${ts}</div>
        </div>
      </div>`;
    }).join('');
    body.innerHTML = html;
    scrollToBottom();
  };

  const updateBadges = (payload) => {
    if(!payload || typeof payload !== 'object') return;
    const chatBadge = document.querySelector('[data-chat-count]');
    if(chatBadge) {
      const total = Number(payload.primary ?? payload.chat ?? 0);
      if(total > 0) {
        chatBadge.textContent = total;
        chatBadge.classList.remove('d-none');
      } else {
        chatBadge.classList.add('d-none');
      }
    }
  };

  const poll = async () => {
    const url = endpoint + '?partner_id=' + encodeURIComponent(partnerId);
    try {
      const res = await fetch(url, {credentials: 'same-origin'});
      if(!res.ok) return;
      const data = await res.json();
      if(data && Array.isArray(data.messages)) {
        renderMessages(data.messages);
        if (data.chat_badges) {
          updateBadges(data.chat_badges);
        }
      }
    } catch (err) {
      console.error('Admin chat poll error', err);
    }
  };

  scrollToBottom();
  poll();
  setInterval(poll, 3000);
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/../templates/footer.php'; ?>
