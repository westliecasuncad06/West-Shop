<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('buyer');
$u = current_user();

// Build a list of sellers the buyer interacted with via orders
$sellers = $pdo->prepare('SELECT DISTINCT u.user_id, COALESCE(NULLIF(s.store_name, ""), u.name) AS store_name
  FROM users u
  LEFT JOIN stores s ON s.seller_id = u.user_id
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
    chat_mark_conversation_read((int)$u['user_id'], $receiver);
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
            <a class="text-decoration-none <?php echo ($receiver===(int)$s['user_id'])?'text-white':''; ?>" href="<?php echo e(base_url('buyer/chat.php?to='.(int)$s['user_id'])); ?>"><?php echo e($s['store_name']); ?></a>
          </li>
        <?php endforeach; ?>
        <?php if(!$sellers): ?><li class="list-group-item text-muted">No sellers yet.</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card p-3" style="min-height:400px;">
      <div class="mb-3" style="height:300px; overflow:auto; background:#f9fbff; border-radius:.75rem; padding:1rem;" data-chat-scroll>
        <div id="chat-thread" data-partner="<?php echo (int)$receiver; ?>" data-endpoint="<?php echo e(base_url('public/chat_poll.php')); ?>" data-self="<?php echo (int)$u['user_id']; ?>">
          <div class="chat-thread-body">
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
        </div>
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
<?php if($receiver > 0): ?>
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
  const emptyState = '<div class="text-muted">Start the conversation.</div>';

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
    const total = Number(payload.primary ?? payload.chat ?? 0);
    const chatBadges = Array.from(document.querySelectorAll('[data-chat-count]'));
    chatBadges.forEach((chatBadge) => {
      if(total > 0) { chatBadge.textContent = total; chatBadge.classList.remove('d-none'); }
      else { chatBadge.classList.add('d-none'); }
    });
    if(window._lastChatCount !== undefined && total > Number(window._lastChatCount)) {
      chatBadges.forEach(b => b.classList.add('pulse'));
      setTimeout(()=> chatBadges.forEach(b => b.classList.remove('pulse')), 900);
    }
    window._lastChatCount = total;
  };

  const poll = async () => {
    try {
      const res = await fetch(url, {credentials: 'same-origin'});
      if(!res.ok) return;
      const data = await res.json();
      if(data && Array.isArray(data.messages)) {
        renderMessages(data.messages);
        if (data.chat_badges) {
          updateBadges(data.chat_badges);
        } else if(typeof data.chat_unread !== 'undefined') {
          updateBadges({primary: Number(data.chat_unread) || 0});
        }
      }
    } catch (err) {
      console.error('Chat poll error', err);
    }
  };

  const sendForm = document.querySelector('form[action*="chat.php"]') || document.querySelector('form');
  if(sendForm) {
    sendForm.addEventListener('submit', () => {
      setTimeout(scrollToBottom, 50);
    });
  }

  scrollToBottom();
  poll();
  setInterval(poll, 3000);
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/../templates/footer.php'; ?>
