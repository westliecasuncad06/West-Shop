<?php
require_once __DIR__ . '/../templates/header.php';
require_role('seller'); global $pdo; $user = current_user();

// fetch orders that include this seller's products
$q = $pdo->prepare("SELECT DISTINCT o.* FROM orders o
  JOIN order_items oi ON oi.order_id=o.order_id
  JOIN products p ON p.product_id=oi.product_id
  WHERE p.seller_id = ? ORDER BY o.created_at DESC");
$q->execute([$user['user_id']]); $orders = $q->fetchAll();

// simple status update
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
  if (!csrf_verify()) { set_flash('danger','Invalid token'); redirect('seller/admin_orders.php'); }
  $up = $pdo->prepare('UPDATE orders SET status = ?, tracking_number = ? WHERE order_id = ?');
  $up->execute([$_POST['status'], $_POST['tracking'] ?? null, (int)$_POST['order_id']]);
  set_flash('success','Order updated'); redirect('seller/admin_orders.php');
}

?>
<h4>Orders (Admin)</h4>
<?php if(!$orders): ?><div class="alert alert-info">No orders yet.</div><?php endif; ?>
<?php foreach($orders as $o): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <strong>Order #<?php echo $o['order_id']; ?></strong>
          <div class="small text-muted">Placed: <?php echo $o['created_at']; ?></div>
        </div>
        <div>
          <span class="badge bg-secondary"><?php echo e(ucfirst($o['status'])); ?></span>
        </div>
      </div>
      <div class="mt-2">
        <a class="btn btn-sm btn-outline-primary" href="<?php echo e(base_url('buyer/orders.php?order_id='.$o['order_id'])); ?>">View Details</a>
      </div>
      <div class="mt-3">
        <form method="post" class="row g-2">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
          <div class="col-auto">
            <select name="status" class="form-select">
              <?php foreach(['pending','confirmed','packed','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php if($o['status']===$s) echo 'selected'; ?>><?php echo ucfirst($s); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-auto"><input name="tracking" class="form-control" placeholder="Tracking #" value="<?php echo e($o['tracking_number'] ?? ''); ?>"></div>
          <div class="col-auto"><button class="btn btn-primary">Update</button></div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../templates/footer.php'; ?>
