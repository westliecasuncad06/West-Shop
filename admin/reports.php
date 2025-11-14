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
<h4 class="mb-3">Reports & Analytics</h4>
<div class="row g-3">
  <div class="col-md-4"><div class="card p-3">Total Sales<br><div class="fs-4 fw-semibold">$<?php echo number_format((float)$totalSales,2); ?></div></div></div>
  <div class="col-md-4"><div class="card p-3">Orders<br><div class="fs-4 fw-semibold"><?php echo (int)$ordersCount; ?></div></div></div>
  <div class="col-md-4"><div class="card p-3">Coupons<br><div class="fs-4 fw-semibold"><?php echo (int)$pdo->query('SELECT COUNT(*) FROM coupons')->fetchColumn(); ?></div></div></div>
</div>
<div class="mt-4">
  <h6>Top Products</h6>
  <ul class="list-group">
    <?php foreach($top as $t): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span><?php echo e($t['name']); ?></span>
        <span class="badge bg-primary"><?php echo (int)$t['sold']; ?> sold</span>
      </li>
    <?php endforeach; ?>
    <?php if(!$top): ?><li class="list-group-item text-muted">No sales data yet.</li><?php endif; ?>
  </ul>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
