 </main>
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <a class="footer-brand" href="<?php echo e(base_url('index.php')); ?>"><?php echo e(APP_NAME); ?></a>
        <p class="text-muted mb-3">A curated marketplace connecting inspired sellers to thoughtful buyers. Shop confidently with transparent policies and real human support.</p>
        <div class="d-flex gap-3">
          <span class="social-pill"><i class="bi bi-facebook"></i></span>
          <span class="social-pill"><i class="bi bi-instagram"></i></span>
          <span class="social-pill"><i class="bi bi-twitter-x"></i></span>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Marketplace</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?php echo e(base_url('index.php')); ?>">Browse</a></li>
          <li><a href="<?php echo e(base_url('buyer/cart.php')); ?>">Cart</a></li>
          <li><a href="<?php echo e(base_url('buyer/orders.php')); ?>">Orders</a></li>
          <li><a href="<?php echo e(base_url('seller/store_public.php?seller_id=1')); ?>">Stores</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Sellers</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?php echo e(base_url('seller/index.php')); ?>">Dashboard</a></li>
          <li><a href="<?php echo e(base_url('seller/orders.php')); ?>">Orders</a></li>
          <li><a href="<?php echo e(base_url('seller/chat_admin.php')); ?>">Support</a></li>
          <li><a href="<?php echo e(base_url('seller/index.php?preview=1')); ?>">Buyer Preview</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="footer-heading">Stay in the loop</h6>
        <p class="text-muted">Monthly design inspo, seller spotlights, and product drops straight to your inbox.</p>
        <form class="footer-form">
          <input type="email" class="form-control" placeholder="Email address">
          <button class="btn btn-primary w-100 mt-2">Subscribe</button>
        </form>
      </div>
    </div>
    <hr class="my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted">
      <span>© <?php echo date('Y'); ?> <?php echo e(APP_NAME); ?> · All rights reserved.</span>
      <span class="mt-2 mt-md-0">This prototype site is for portfolio purposes only and is not a functional storefront. Crafted by Westlie Casuncad, Full Stack Developer.</span>
      <div class="d-flex gap-3">
        <a href="#" class="text-muted text-decoration-none">Privacy</a>
        <a href="#" class="text-muted text-decoration-none">Terms</a>
        <a href="mailto:support@example.com" class="text-muted text-decoration-none">support@example.com</a>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if(isset($u) && $u): ?>
<script>
(function(){
  const notifBadge = document.querySelector('[data-notif-count]');
  const chatBadges = Array.from(document.querySelectorAll('[data-chat-count]'));
  const pulseEndpoint = '<?php echo e(base_url('public/pulse_counts.php')); ?>';
  if(!pulseEndpoint) return;
  const updateBadge = (el, value) => {
    if(!el) return;
    if(Number(value) > 0) {
      el.textContent = value;
      el.classList.remove('d-none');
    } else {
      el.classList.add('d-none');
    }
  };
  const poll = async () => {
    try {
      const res = await fetch(pulseEndpoint, {credentials: 'same-origin'});
      if(!res.ok) return;
      const data = await res.json();
      if(data) {
        if(typeof data.notifications !== 'undefined') {
          updateBadge(notifBadge, data.notifications);
        }
        if(typeof data.chat !== 'undefined') {
          // update all chat badges on page
          chatBadges.forEach(b => updateBadge(b, data.chat));
          // small pulse to indicate new message (CSS animation)
          if(window.lastChatCount !== undefined && Number(data.chat) > Number(window.lastChatCount)) {
            chatBadges.forEach(b => b.classList.add('pulse'));
            setTimeout(()=> chatBadges.forEach(b => b.classList.remove('pulse')), 900);
          }
          window.lastChatCount = Number(data.chat);
        }
      }
    } catch (err) {
      console.error('Pulse poll error', err);
    }
  };
  poll();
  setInterval(poll, 3000);
})();
</script>
<?php endif; ?>
</body>
</html>
