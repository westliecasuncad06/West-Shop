<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Sales summary
$totalSales = $pdo->query('SELECT IFNULL(SUM(total_amount),0) FROM orders')->fetchColumn();
$ordersCount = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

// Top products
$top = $pdo->query('SELECT p.product_id, p.name, SUM(oi.quantity) AS sold
  FROM order_items oi
  JOIN products p ON oi.product_id = p.product_id
  GROUP BY p.product_id ORDER BY sold DESC LIMIT 10')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<section class="section-shell">
  <div class="section-heading">
    <div>
      <p class="section-heading__eyebrow mb-1">Performance</p>
      <h1 class="section-heading__title mb-0">Reports & Analytics</h1>
      <p class="page-subtitle mt-2">High level stats at a glance with drill-downs to your most-loved products.</p>
    </div>
  </div>
  <div class="dashboard-grid">
    <div class="surface-card">
      <p class="text-muted-soft mb-1">Total sales</p>
      <h3 class="mb-0">$<?php echo number_format((float)$totalSales,2); ?></h3>
    </div>
    <div class="surface-card">
      <p class="text-muted-soft mb-1">Orders</p>
      <h3 class="mb-0"><?php echo number_format((int)$ordersCount); ?></h3>
    </div>
    <div class="surface-card">
      <p class="text-muted-soft mb-1">Coupons issued</p>
      <h3 class="mb-0"><?php echo number_format((int)$pdo->query('SELECT COUNT(*) FROM coupons')->fetchColumn()); ?></h3>
    </div>
  </div>
</section>

<section class="section-shell">
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="surface-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="section-heading__eyebrow mb-1">Top performers</p>
            <h5 class="mb-0">Products by units sold</h5>
          </div>
        </div>
        <?php if($top): ?>
          <ul class="stacked-list">
            <?php foreach($top as $t): ?>
              <li class="stacked-list__item">
                <span><?php echo e($t['name']); ?></span>
                <span class="badge-soft badge-soft--success"><?php echo (int)$t['sold']; ?> sold</span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state-card text-center">
            <h6 class="mb-1">No sales data yet</h6>
            <p class="text-muted-soft mb-0">Start fulfilling orders to populate insights.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="surface-card h-100">
        <p class="section-heading__eyebrow mb-1">Insights</p>
        <h5 class="mb-3">Next actions</h5>
        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
          <li class="d-flex gap-3">
            <div class="dashboard-card__icon"><i class="bi bi-graph-up"></i></div>
            <div>
              <h6 class="mb-1">Watch trending SKUs</h6>
              <p class="text-muted-soft mb-0">Keep inventory padded for products above baseline demand.</p>
            </div>
          </li>
          <li class="d-flex gap-3">
            <div class="dashboard-card__icon"><i class="bi bi-broadcast"></i></div>
            <div>
              <h6 class="mb-1">Broadcast promos</h6>
              <p class="text-muted-soft mb-0">Use announcements to nudge buyers on low-stock exclusives.</p>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
