<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');

$u = current_user();

// Collect buyers from fulfilled orders
$orderBuyersStmt = $pdo->prepare('SELECT DISTINCT u.user_id, u.name, u.email
  FROM users u
  JOIN orders o ON o.buyer_id = u.user_id
  JOIN order_items oi ON oi.order_id = o.order_id
  JOIN products p ON p.product_id = oi.product_id
  WHERE p.seller_id = ?
  ORDER BY u.name');
$orderBuyersStmt->execute([$u['user_id']]);
$orderBuyers = $orderBuyersStmt->fetchAll();

// Collect buyers who already exchanged chat messages (even without orders)
$chatBuyersStmt = $pdo->prepare('
  SELECT DISTINCT u.user_id, u.name, u.email
  FROM chat_messages cm
  JOIN users u ON u.user_id = cm.sender_id
  WHERE cm.receiver_id = ? AND u.role = "buyer"
  UNION
  SELECT DISTINCT u.user_id, u.name, u.email
  FROM chat_messages cm
  JOIN users u ON u.user_id = cm.receiver_id
  WHERE cm.sender_id = ? AND u.role = "buyer"
');
$chatBuyersStmt->execute([$u['user_id'], $u['user_id']]);
$chatBuyers = $chatBuyersStmt->fetchAll();

// Merge the collections keyed by user_id
$buyersMap = [];
foreach (array_merge($orderBuyers, $chatBuyers) as $buyer) {
    $buyersMap[(int)$buyer['user_id']] = [
        'user_id' => (int)$buyer['user_id'],
        'name' => $buyer['name'],
        'email' => $buyer['email'],
    ];
}

$buyers = array_values($buyersMap);
usort($buyers, function ($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

$receiver = isset($_GET['to']) ? (int)$_GET['to'] : (isset($buyers[0]['user_id']) ? (int)$buyers[0]['user_id'] : 0);
if ($receiver && !isset($buyersMap[$receiver])) {
    $receiver = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $message = trim($_POST['message'] ?? '');
    $to = isset($_POST['to']) ? (int)$_POST['to'] : 0;
    if ($message === '' || !$to || !isset($buyersMap[$to])) {
        set_flash('warning', 'Select a valid buyer to chat with.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO chat_messages(sender_id, receiver_id, message) VALUES (?,?,?)');
        $stmt->execute([$u['user_id'], $to, $message]);
    }
    header('Location: ' . base_url('seller/chat.php?to=' . (int)$to));
    exit;
}

$conversation = [];
if ($receiver) {
    $stmt = $pdo->prepare('SELECT * FROM chat_messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY timestamp ASC');
    $stmt->execute([$u['user_id'], $receiver, $receiver, $u['user_id']]);
    $conversation = $stmt->fetchAll();
  chat_mark_conversation_read((int)$u['user_id'], $receiver);
}

include __DIR__ . '/../templates/header.php';
?>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3 h-100">
      <h6 class="mb-3">Buyer Messages</h6>
      <ul class="list-group list-group-flush" style="max-height:420px;overflow:auto;">
        <?php foreach ($buyers as $buyer): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center <?php echo ($receiver === (int)$buyer['user_id']) ? 'active text-white' : ''; ?>">
            <div>
              <div class="fw-semibold"><?php echo e($buyer['name']); ?></div>
              <div class="small <?php echo ($receiver === (int)$buyer['user_id']) ? 'text-white-50' : 'text-muted'; ?>"><?php echo e($buyer['email']); ?></div>
            </div>
            <a class="stretched-link" href="<?php echo e(base_url('seller/chat.php?to=' . (int)$buyer['user_id'])); ?>" aria-label="Open chat"></a>
          </li>
        <?php endforeach; ?>
        <?php if (!$buyers): ?>
          <li class="list-group-item text-muted">No buyers have messaged you yet.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card p-3" style="min-height:400px;">
      <?php if ($receiver && isset($buyersMap[$receiver])): ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div>
            <h6 class="mb-0">Chatting with <?php echo e($buyersMap[$receiver]['name']); ?></h6>
            <div class="small text-muted"><?php echo e($buyersMap[$receiver]['email']); ?></div>
          </div>
          <span class="badge bg-light text-dark">Buyer</span>
        </div>
        <div class="mb-3" style="height:300px; overflow:auto; background:#f9fbff; border-radius:.75rem; padding:1rem;" data-chat-scroll>
          <div id="chat-thread" data-partner="<?php echo (int)$receiver; ?>" data-endpoint="<?php echo e(base_url('public/chat_poll.php')); ?>" data-self="<?php echo (int)$u['user_id']; ?>">
            <div class="chat-thread-body">
              <?php foreach ($conversation as $msg): ?>
                <div class="mb-2 d-flex <?php echo ($msg['sender_id'] === $u['user_id']) ? 'justify-content-end' : ''; ?>">
                  <div class="px-3 py-2 rounded-3 <?php echo ($msg['sender_id'] === $u['user_id']) ? 'bg-primary text-white' : 'bg-white'; ?> shadow-sm" style="max-width:75%;">
                    <div class="small"><?php echo nl2br(e($msg['message'])); ?></div>
                    <div class="text-muted small mt-1"><?php echo e($msg['timestamp']); ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (!$conversation): ?>
                <div class="text-muted">No messages yet. Start the conversation!</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <form method="post" class="d-flex gap-2">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="to" value="<?php echo (int)$receiver; ?>">
          <input class="form-control" name="message" placeholder="Type your reply..." required>
          <button class="btn btn-primary">Send</button>
        </form>
      <?php else: ?>
        <div class="h-100 d-flex align-items-center justify-content-center text-muted">
          Select a buyer from the list to start chatting.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if ($receiver): ?>
<script>
(function(){
  const wrapper = document.getElementById('chat-thread');
  if(!wrapper) return;
  const body = wrapper.querySelector('.chat-thread-body');
  const scrollBox = wrapper.closest('[data-chat-scroll]') || wrapper.parentElement;
  const endpoint = wrapper.dataset.endpoint;
  const partnerId = wrapper.dataset.partner;
  const selfId = parseInt(wrapper.dataset.self, 10);
  if(!partnerId || !endpoint) return;
  const url = endpoint + '?partner_id=' + encodeURIComponent(partnerId);
  const emptyState = '<div class="text-muted">No messages yet. Start the conversation!</div>';

  const scrollToBottom = () => {
    if(!body || !scrollBox) return;
    requestAnimationFrame(() => {
      scrollBox.scrollTop = scrollBox.scrollHeight;
    });
  };

  const escapeHtml = (str) => (str || '').replace(/[&<>"']/g, (ch) => ({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[ch] || ch));

  const renderMessages = (messages) => {
    if(!Array.isArray(messages) || messages.length === 0) {
      body.innerHTML = emptyState;
      scrollToBottom();
      return;
    }
    const html = messages.map(msg => {
      const isSelf = Number(msg.sender_id) === selfId;
      const bubbleClass = isSelf ? 'bg-primary text-white' : 'bg-white';
      const alignClass = isSelf ? 'justify-content-end' : '';
      const safeMessage = escapeHtml(msg.message || '').replace(/\n/g,'<br>');
      const time = escapeHtml(msg.timestamp || '');
      return `<div class="mb-2 d-flex ${alignClass}">
        <div class="px-3 py-2 rounded-3 ${bubbleClass} shadow-sm" style="max-width:75%;">
          <div class="small">${safeMessage}</div>
          <div class="text-muted small mt-1">${time}</div>
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
    try {
      const res = await fetch(url, {credentials: 'same-origin'});
      if(!res.ok) return;
      const data = await res.json();
      if(data && Array.isArray(data.messages)) {
        renderMessages(data.messages);
        const badges = data.chat_badges;
        if (badges) {
          updateBadges(badges);
        } else if(typeof data.chat_unread !== 'undefined') {
          updateBadges({primary: Number(data.chat_unread) || 0});
        }
      }
    } catch (err) {
      console.error('Chat poll error', err);
    }
  };

  const sendForm = document.querySelector('form[action*="seller/chat.php"]') || document.querySelector('form');
  if(sendForm) {
    sendForm.addEventListener('submit', () => {
      setTimeout(scrollToBottom, 50);
    });
  }

  // Ensure anchored to bottom immediately on load
  scrollToBottom();
  poll();
  setInterval(poll, 3000);
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/../templates/footer.php'; ?>
