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

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">My Orders</h4>
<div class="table-responsive">
<table class="table align-middle">
  <thead><tr><th>ID</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
  <tbody>
    <?php foreach($orders as $o): ?>
      <tr>
        <td>#<?php echo (int)$o['order_id']; ?></td>
        <td>$<?php echo number_format((float)$o['total_amount'],2); ?></td>
        <td><?php echo e($o['payment_method']); ?></td>
        <td><?php echo e($o['status']); ?></td>
        <td><?php echo e($o['created_at']); ?></td>
        <td>
          <?php if($o['status']==='Pending'): ?>
            <form method="post" class="d-inline">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>">
              <button class="btn btn-sm btn-outline-danger">Cancel</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$orders): ?><tr><td colspan="6" class="text-center text-muted">No orders yet.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
