<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');

$u = current_user();

// Cancel action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $oid = (int)($_POST['order_id'] ?? 0);
    $stmt = $pdo->prepare('UPDATE orders SET status = "Cancelled" WHERE order_id = ? AND buyer_id = ? AND status = "Pending"');
    $stmt->execute([$oid, $u['user_id']]);
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC');
$stmt->execute([$u['user_id']]);
$orders = $stmt->fetchAll();
$orderItems = [];
if ($orders) {
    $orderIds = array_map(fn($o) => (int)$o['order_id'], $orders);
    $in = implode(',', array_fill(0, count($orderIds), '?'));
    $itemStmt = $pdo->prepare("SELECT oi.*, p.name AS product_name, p.image AS product_image, u.name AS seller_name\n        FROM order_items oi\n        JOIN products p ON oi.product_id = p.product_id\n        LEFT JOIN users u ON p.seller_id = u.user_id\n        WHERE oi.order_id IN ($in)\n        ORDER BY oi.order_id, oi.item_id");
    foreach ($orderIds as $idx => $orderId) {
        $itemStmt->bindValue($idx + 1, $orderId, PDO::PARAM_INT);
    }
    $itemStmt->execute();
    while ($row = $itemStmt->fetch()) {
        $oid = (int)$row['order_id'];
        if (!isset($orderItems[$oid])) {
            $orderItems[$oid] = [];
        }
        $orderItems[$oid][] = $row;
    }
}

$statusSteps = ['Pending','Confirmed','Shipped','Delivered'];
$statusBadges = [
    'Pending' => 'badge bg-warning text-dark',
    'Confirmed' => 'badge bg-info text-dark',
    'Shipped' => 'badge bg-primary',
    'Delivered' => 'badge bg-success',
    'Cancelled' => 'badge bg-danger',
];

include __DIR__ . '/../templates/header.php';
?>
<style>
.order-card { border: none; border-radius: 1rem; }
.order-card + .order-card { margin-top: 1.25rem; }
.order-card .card-header { background: #f7f9fc; border-bottom: none; border-radius: 1rem 1rem 0 0; }
.order-item:last-child { border-bottom: none !important; }
.tracker-step { flex: 1; text-align: center; position: relative; }
.tracker-step .dot { width: 14px; height: 14px; border-radius: 50%; display: inline-block; background: #cfd8e3; }
.tracker-step.completed .dot { background: #2563eb; }
.tracker-step:not(:last-child)::after { content: ''; position: absolute; top: 6px; right: -50%; width: 100%; height: 2px; background: #cfd8e3; }
.tracker-step.completed:not(:last-child)::after { background: #2563eb; }
.tracker-step .label { display: block; font-size: .8rem; margin-top: .35rem; color: #6b7280; }
.tracker-step.completed .label { color: #2563eb; font-weight: 600; }
</style>

<h4 class="mb-3">My Orders</h4>

<?php foreach($orders as $o): $oid = (int)$o['order_id']; $orderStatus = $o['status']; $statusIndex = array_search($orderStatus, $statusSteps, true); ?>
  <div class="card shadow-sm order-card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <div class="fw-semibold">Order #<?php echo $oid; ?></div>
        <div class="text-muted small">Placed on <?php echo e(date('M d, Y g:i A', strtotime($o['created_at']))); ?></div>
      </div>
      <div class="text-end">
        <div class="fs-5 fw-semibold">$<?php echo number_format((float)$o['total_amount'],2); ?></div>
        <span class="<?php echo $statusBadges[$orderStatus] ?? 'badge bg-secondary'; ?>"><?php echo e($orderStatus); ?></span>
      </div>
    </div>
    <div class="card-body">
      <?php $items = $orderItems[$oid] ?? []; ?>
      <?php foreach($items as $item): ?>
        <div class="order-item d-flex align-items-center py-3 border-bottom">
          <img src="<?php echo e(product_image_src($item['product_image'] ?? null)); ?>" alt="<?php echo e($item['product_name']); ?>" class="rounded" style="width:70px;height:70px;object-fit:cover;">
          <div class="ms-3 flex-grow-1">
            <div class="fw-semibold"><?php echo e($item['product_name']); ?></div>
            <div class="text-muted small">Sold by <?php echo e($item['seller_name'] ?? 'Seller'); ?></div>
          </div>
          <div class="text-end">
            <div class="fw-semibold">$<?php echo number_format((float)$item['price'],2); ?></div>
            <div class="text-muted small">Qty <?php echo (int)$item['quantity']; ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if(!$items): ?>
        <div class="text-muted">Items for this order are no longer available.</div>
      <?php endif; ?>

      <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 text-muted small">
        <span>Payment: <?php echo e($o['payment_method']); ?></span>
        <?php if((float)$o['discount_amount'] > 0): ?>
          <span>Discount: $<?php echo number_format((float)$o['discount_amount'], 2); ?></span>
        <?php endif; ?>
        <?php if($o['coupon_code']): ?>
          <span>Coupon: <?php echo e($o['coupon_code']); ?></span>
        <?php endif; ?>
      </div>

      <?php if($orderStatus === 'Cancelled'): ?>
        <div class="alert alert-danger mt-3 mb-0 small">
          <div>This order has been cancelled.</div>
          <?php if(!empty($o['cancel_reason'])): ?>
            <div class="mt-2"><strong>Seller note:</strong> <?php echo e($o['cancel_reason']); ?></div>
          <?php else: ?>
            <div class="mt-2">Reach out to the store if you need more details.</div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="order-tracker d-flex gap-2 mt-4">
          <?php foreach($statusSteps as $idx => $label): $completed = ($statusIndex !== false && $idx <= $statusIndex); ?>
            <div class="tracker-step <?php echo $completed ? 'completed' : ''; ?>">
              <span class="dot"></span>
              <span class="label"><?php echo $label; ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if($orderStatus === 'Pending'): ?>
        <form method="post" class="mt-4 text-end">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
          <button class="btn btn-outline-danger">Cancel Order</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php if(!$orders): ?>
  <div class="text-center text-muted py-5">No orders yet. Start shopping to fill this space.</div>
<?php endif; ?>

<?php include __DIR__ . '/../templates/footer.php'; ?>
