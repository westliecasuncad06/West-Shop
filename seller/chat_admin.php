<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');
$u = current_user();

// Find an admin to chat with (first admin)
$adminId = (int)($pdo->query("SELECT user_id FROM users WHERE role='admin' ORDER BY user_id ASC LIMIT 1")->fetchColumn());

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
      <div class="chat-thread" id="chatThread"></div>
      <form method="post" class="chat-input-row" action="<?php echo e(base_url('public/chat_send.php')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="partner_id" value="<?php echo (int)$adminId; ?>">
        <input type="hidden" name="redirect_to" value="seller/chat_admin.php">
        <input class="form-control chat-message-input" name="message" placeholder="Type your message..." required>
        <button class="btn btn-primary"><i class="bi bi-send"></i> Send</button>
      </form>
    </div>
  </div>
</div>

<script>
  (function(){
    const thread = document.getElementById('chatThread');
    if (!thread) return;
    const endpoint = '<?php echo e(base_url('public/chat_poll.php?partner_id=' . (int)$adminId)); ?>';
    const sendEndpoint = '<?php echo e(base_url('public/chat_send.php')); ?>';
    const selfId = <?php echo (int)$u['user_id']; ?>;
    let latestMessages = [];

    const scrollToBottom = () => {
      requestAnimationFrame(() => {
        thread.scrollTop = thread.scrollHeight;
      });
    };

    const escapeHtml = (str) => (str || '').replace(/[&<>"']/g, (ch) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[ch] || ch));

    const renderMessages = (messages) => {
      latestMessages = Array.isArray(messages) ? messages.slice() : [];
      if (!Array.isArray(messages) || messages.length === 0) {
        thread.innerHTML = '<div class="empty-panel">Say hello to start the conversation.</div>';
        scrollToBottom();
        return;
      }
      const html = messages.map(msg => {
        const isSelf = Number(msg.sender_id) === selfId;
        const bubbleClass = isSelf ? 'chat-message chat-message--self' : 'chat-message';
        const safeMsg = escapeHtml(msg.message || '').replace(/\n/g,'<br>');
        const time = escapeHtml(msg.timestamp || '');
        return `<div class="${bubbleClass}">
          <div class="chat-bubble">
            <div>${safeMsg}</div>
            <span class="chat-timestamp">${time}</span>
          </div>
        </div>`;
      }).join('');
      thread.innerHTML = html;
      scrollToBottom();
    };

      const updateBadges = (badges) => {
        const total = Number(badges.primary ?? badges.chat ?? 0);
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
        const res = await fetch(endpoint, {credentials: 'same-origin'});
        if (!res.ok) return;
        const data = await res.json();
        if (data && Array.isArray(data.messages)) {
          renderMessages(data.messages);
        }
        if (data && data.chat_badges) {
          updateBadges(data.chat_badges);
        }
      } catch (err) {
        console.error('Support chat poll error', err);
      }
    };

    const form = document.querySelector('.chat-input-row');
    if (form) {
      const messageInput = form.querySelector('.chat-message-input');
      const tokenInput = form.querySelector('input[name="_token"]');
      const partnerInput = form.querySelector('input[name="partner_id"]');
      form.addEventListener('submit', async (evt) => {
        evt.preventDefault();
        if (!messageInput || !tokenInput || !partnerInput) return;
        const text = messageInput.value.trim();
        if (text === '') return;
        const fd = new FormData();
        fd.append('_token', tokenInput.value);
        fd.append('partner_id', partnerInput.value || '<?php echo (int)$adminId; ?>');
        fd.append('message', text);
        fd.append('redirect_to', 'seller/chat_admin.php');
        try {
          const res = await fetch(sendEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: fd,
          });
          if (!res.ok) throw new Error('Failed to send');
          const data = await res.json();
          if (data && data.success && data.message) {
            messageInput.value = '';
            const nextMessages = latestMessages.slice();
            nextMessages.push(data.message);
            renderMessages(nextMessages);
          }
          if (data && data.chat_badges) {
            updateBadges(data.chat_badges);
          }
        } catch (err) {
          console.error('Support chat send error', err);
        }
      });
    }

    poll();
    setInterval(poll, 3000);
  })();
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
