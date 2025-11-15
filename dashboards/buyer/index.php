<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_role('buyer');

$buyer = current_user();
$buyerId = (int)($buyer['user_id'] ?? 0);
$cartCount = array_sum(cart_get());

$ordersCountStmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE buyer_id = ?');
$ordersCountStmt->execute([$buyerId]);
$ordersCount = (int)$ordersCountStmt->fetchColumn();

$openOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND status IN ('Pending','Confirmed','Shipped')");
$openOrdersStmt->execute([$buyerId]);
$openOrders = (int)$openOrdersStmt->fetchColumn();

$deliveredStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND status = 'Delivered'");
$deliveredStmt->execute([$buyerId]);
$deliveredOrders = (int)$deliveredStmt->fetchColumn();

$recentOrdersStmt = $pdo->prepare('SELECT order_id, total_amount, status, created_at FROM orders WHERE buyer_id = ? ORDER BY created_at DESC LIMIT 4');
$recentOrdersStmt->execute([$buyerId]);
$recentOrders = $recentOrdersStmt->fetchAll();

include __DIR__ . '/../../templates/header.php';
?>

<section class="section-shell">
  <div class="surface-card surface-card--muted">
    <div class="d-flex flex-column flex-lg-row gap-4 align-items-lg-center justify-content-between">
      <div>
        <p class="section-heading__eyebrow mb-1">Welcome back</p>
        <h2 class="mb-2">Hi <?php echo e($buyer['name']); ?>, your shopping hub is ready</h2>
        <p class="text-muted-soft mb-0">Track orders, pick up abandoned carts, and message sellers without leaving this dashboard.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <span class="metric-pill"><i class="bi bi-bag-check"></i> <?php echo $ordersCount; ?> orders</span>
        <span class="metric-pill metric-pill--rose"><i class="bi bi-chat-dots"></i> Instant chat</span>
      </div>
    </div>
  </div>
</section>

<section class="section-shell">
  <div class="section-heading dashboard-header">
    <div>
      <p class="section-heading__eyebrow mb-1">Overview</p>
      <h3 class="section-heading__title mb-0">Snapshot of your activity</h3>
    </div>
    <div class="section-heading__actions">
      <a href="<?php echo e(base_url('buyer/orders.php')); ?>" class="pill-link"><i class="bi bi-clock-history"></i> View history</a>
    </div>
  </div>
  <div class="dashboard-grid">
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-box-seam"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Orders placed</p>
        <h4 class="mb-0"><?php echo number_format($ordersCount); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-truck"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">In progress</p>
        <h4 class="mb-0"><?php echo number_format($openOrders); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-bag-heart"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Delivered</p>
        <h4 class="mb-0"><?php echo number_format($deliveredOrders); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-cart3"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Items in cart</p>
        <h4 class="mb-0"><?php echo number_format($cartCount); ?></h4>
      </div>
    </div>
  </div>
</section>

<section class="section-shell">
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="surface-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="section-heading__eyebrow mb-1">Recent orders</p>
            <h5 class="mb-0">Latest updates</h5>
          </div>
          <a href="<?php echo e(base_url('buyer/orders.php')); ?>" class="text-decoration-none fw-semibold">See all</a>
        </div>
        <?php if($recentOrders): ?>
          <ul class="stacked-list">
            <?php foreach($recentOrders as $order): ?>
              <li class="stacked-list__item">
                <div>
                  <div class="fw-semibold">Order #<?php echo (int)$order['order_id']; ?></div>
                  <small class="text-muted-soft"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                </div>
                <div class="text-end">
                  <span class="badge-soft <?php echo ($order['status'] === 'Delivered') ? 'badge-soft--success' : 'badge-soft--warning'; ?>"><?php echo e($order['status']); ?></span>
                  <div class="fw-semibold mt-1">$<?php echo number_format((float)$order['total_amount'],2); ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state-card text-center">
            <p class="mb-1 fw-semibold">No orders yet</p>
            <p class="text-muted-soft mb-0">Browse featured drops and place your first order.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="surface-card h-100">
        <div class="mb-3">
          <p class="section-heading__eyebrow mb-1">Quick actions</p>
          <h5 class="mb-0">What would you like to do?</h5>
        </div>
        <div class="d-grid gap-2">
          <a href="<?php echo e(base_url('buyer/cart.php')); ?>" class="btn btn-primary btn-lg">Go to cart</a>
          <a href="<?php echo e(base_url('buyer/chat.php')); ?>" class="btn btn-outline-primary btn-lg">Chat with sellers</a>
          <a href="<?php echo e(base_url('public/category.php')); ?>" class="btn btn-light btn-lg">Explore categories</a>
        </div>
        <hr class="my-4">
        <div class="small text-muted-soft">Need a hand? Reach out anytime and we’ll pair you with a verified seller.</div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../../templates/footer.php'; ?>
