<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_role('admin');

// Pending sellers
$pending = $pdo->query("SELECT user_id, name, email, status, created_at FROM users WHERE role='seller' AND status='pending' ORDER BY created_at DESC")->fetchAll();

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSellers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='seller'")->fetchColumn();
$totalBuyers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn();
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalSales = (float)$pdo->query('SELECT IFNULL(SUM(total_amount),0) FROM orders')->fetchColumn();

include __DIR__ . '/../../templates/header.php';
?>
<section class="section-shell">
  <div class="section-heading">
    <div>
      <p class="section-heading__eyebrow mb-1">Operations center</p>
      <h1 class="section-heading__title mb-0">Admin Dashboard</h1>
      <p class="page-subtitle mt-2">Monitor store performance, approve sellers, and keep announcements aligned across the marketplace.</p>
    </div>
    <div class="section-heading__actions">
      <a href="<?php echo e(base_url('admin/announcements.php')); ?>" class="pill-link"><i class="bi bi-broadcast"></i> Publish update</a>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-people"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Total users</p>
        <h4 class="mb-0"><?php echo number_format($totalUsers); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-shop"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Active sellers</p>
        <h4 class="mb-0"><?php echo number_format($totalSellers); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-bag"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Total orders</p>
        <h4 class="mb-0"><?php echo number_format($totalOrders); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-cash-stack"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Sales volume</p>
        <h4 class="mb-0">$<?php echo number_format($totalSales,2); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-box2"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Products</p>
        <h4 class="mb-0"><?php echo number_format($totalProducts); ?></h4>
      </div>
    </div>
    <div class="dashboard-card">
      <div class="dashboard-card__icon"><i class="bi bi-person-check"></i></div>
      <div>
        <p class="dashboard-card__label mb-1">Buyers</p>
        <h4 class="mb-0"><?php echo number_format($totalBuyers); ?></h4>
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
            <p class="section-heading__eyebrow mb-1">Pending sellers</p>
            <h5 class="mb-0">Needs your review</h5>
          </div>
          <a href="<?php echo e(base_url('admin/sellers.php')); ?>" class="text-decoration-none fw-semibold">Manage</a>
        </div>
        <?php if($pending): ?>
          <ul class="stacked-list">
            <?php foreach(array_slice($pending,0,6) as $s): ?>
              <li class="stacked-list__item">
                <div>
                  <div class="fw-semibold"><?php echo e($s['name']); ?></div>
                  <div class="text-muted-soft small"><?php echo e($s['email']); ?></div>
                </div>
                <div class="text-end">
                  <span class="badge-soft badge-soft--warning text-uppercase">Pending</span>
                  <div class="small text-muted-soft mt-1">Joined <?php echo date('M d', strtotime($s['created_at'])); ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state-card text-center">
            <h6 class="mb-1">All sellers reviewed</h6>
            <p class="text-muted-soft mb-0">We’ll notify you once new applications arrive.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="surface-card h-100">
        <p class="section-heading__eyebrow mb-1">Admin tools</p>
        <h5 class="mb-3">Shortcuts</h5>
        <div class="d-grid gap-2">
          <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/categories.php')); ?>"><i class="bi bi-tags"></i> Manage categories</a>
          <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/reports.php')); ?>"><i class="bi bi-bar-chart"></i> Reports</a>
          <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/announcements.php')); ?>"><i class="bi bi-megaphone"></i> Announcements</a>
          <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/vouchers.php')); ?>"><i class="bi bi-ticket"></i> Coupons & vouchers</a>
        </div>
        <hr class="my-4">
        <div class="small text-muted-soft">Pro tip: use announcements to broadcast promos to both buyers and sellers in one go.</div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../../templates/footer.php'; ?>
