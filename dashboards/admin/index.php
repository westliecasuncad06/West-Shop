<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_role('admin');

// Pending sellers
$pending = $pdo->query("SELECT user_id, name, email, status FROM users WHERE role='seller' AND status='pending' ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/../../templates/header.php';
?>
<h4 class="mb-3">Admin Dashboard</h4>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Pending Sellers</h5>
        <a href="<?php echo e(base_url('admin/sellers.php')); ?>" class="btn btn-sm btn-outline-primary">Manage</a>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach(array_slice($pending,0,5) as $s): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?php echo e($s['name']); ?> <small class="text-muted"><?php echo e($s['email']); ?></small></span>
            <span class="badge bg-warning text-dark"><?php echo e($s['status']); ?></span>
          </li>
        <?php endforeach; ?>
        <?php if(!$pending): ?><li class="list-group-item text-muted">No pending sellers.</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h5>Quick Links</h5>
      <div class="d-grid gap-2">
        <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/categories.php')); ?>">Manage Categories</a>
        <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/reports.php')); ?>">Reports</a>
        <a class="btn btn-outline-primary" href="<?php echo e(base_url('admin/announcements.php')); ?>">Announcements</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
