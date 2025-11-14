<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_role('buyer');
include __DIR__ . '/../../templates/header.php';
?>
<div class="row g-3">
  <div class="col-md-4">
    <a class="text-decoration-none" href="<?php echo e(base_url('buyer/orders.php')); ?>">
      <div class="card p-4 text-center">
        <div class="fs-1">🧾</div>
        <div class="fw-semibold mt-2">My Orders</div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a class="text-decoration-none" href="<?php echo e(base_url('buyer/cart.php')); ?>">
      <div class="card p-4 text-center">
        <div class="fs-1">🛒</div>
        <div class="fw-semibold mt-2">Cart</div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a class="text-decoration-none" href="<?php echo e(base_url('buyer/chat.php')); ?>">
      <div class="card p-4 text-center">
        <div class="fs-1">💬</div>
        <div class="fw-semibold mt-2">Chat with Seller</div>
      </div>
    </a>
  </div>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
