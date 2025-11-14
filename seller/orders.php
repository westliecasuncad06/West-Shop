<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);
    // Update order status only if this seller has at least one item in the order
    if (in_array($action, ['Confirmed','Shipped','Delivered','Cancelled'], true)) {
        // For simplicity, update entire order status.
        $stmt = $pdo->prepare('UPDATE orders o
            SET o.status = ?
            WHERE o.order_id = ? AND EXISTS (
              SELECT 1 FROM order_items oi JOIN products p ON oi.product_id=p.product_id
              WHERE oi.order_id=o.order_id AND p.seller_id=?
            )');
        $stmt->execute([$action, $orderId, $u['user_id']]);
        set_flash('success', 'Order status updated.');
    }
}

$orders = $pdo->prepare('SELECT DISTINCT o.* FROM orders o
  JOIN order_items oi ON o.order_id=oi.order_id
  JOIN products p ON oi.product_id=p.product_id
  WHERE p.seller_id=? ORDER BY o.created_at DESC');
$orders->execute([$u['user_id']]);
$orders = $orders->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Incoming Orders</h4>
<div class="table-responsive">
<table class="table align-middle">
  <thead><tr><th>ID</th><th>Buyer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
  <tbody>
    <?php foreach($orders as $o): ?>
      <tr>
        <td>#<?php echo (int)$o['order_id']; ?></td>
        <td>
          <?php $b=$pdo->prepare('SELECT name FROM users WHERE user_id=?'); $b->execute([$o['buyer_id']]); $bn=$b->fetchColumn(); echo e($bn ?: ''); ?>
        </td>
        <td>$<?php echo number_format((float)$o['total_amount'],2); ?></td>
        <td><?php echo e($o['status']); ?></td>
        <td><?php echo e($o['created_at']); ?></td>
        <td>
          <form method="post" class="d-flex gap-1">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>">
            <button name="action" value="Confirmed" class="btn btn-sm btn-outline-secondary">Confirm</button>
            <button name="action" value="Shipped" class="btn btn-sm btn-outline-primary">Ship</button>
            <button name="action" value="Delivered" class="btn btn-sm btn-outline-success">Deliver</button>
            <button name="action" value="Cancelled" class="btn btn-sm btn-outline-danger">Cancel</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$orders): ?><tr><td colspan="6" class="text-center text-muted">No orders yet.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
