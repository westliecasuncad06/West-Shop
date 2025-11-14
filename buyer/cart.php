<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $pid => $qty) {
            cart_set((int)$pid, (int)$qty);
        }
        set_flash('success', 'Cart updated');
    } elseif ($action === 'remove') {
        cart_remove((int)($_POST['product_id'] ?? 0));
        set_flash('success', 'Item removed');
    } elseif ($action === 'clear') {
        cart_clear();
        set_flash('success', 'Cart cleared');
    }
}

$items = cart_items_with_products();
$total = 0; foreach($items as $it){ $total += $it['line_total']; }

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Your Cart</h4>
<form method="post" class="mb-3">
  <?php echo csrf_field(); ?>
  <input type="hidden" name="action" value="update">
  <div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Product</th><th style="width:140px">Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
    <tbody>
      <?php foreach($items as $it): ?>
        <tr>
          <td><?php echo e($it['name']); ?></td>
          <td><input type="number" class="form-control" name="qty[<?php echo (int)$it['product_id']; ?>]" value="<?php echo (int)$it['cart_qty']; ?>" min="0"></td>
          <td>$<?php echo number_format((float)$it['price'],2); ?></td>
          <td>$<?php echo number_format((float)$it['line_total'],2); ?></td>
          <td>
            <form method="post">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?php echo (int)$it['product_id']; ?>">
              <button class="btn btn-sm btn-link text-danger">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$items): ?><tr><td colspan="5" class="text-center text-muted">Your cart is empty.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
  <div class="d-flex justify-content-between align-items-center">
    <button class="btn btn-outline-primary" name="save" value="1">Update Cart</button>
    <div class="fs-5 fw-semibold">Grand Total: $<?php echo number_format((float)$total,2); ?></div>
  </div>
</form>

<div class="d-flex justify-content-between">
  <form method="post">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="clear">
    <button class="btn btn-outline-danger">Clear Cart</button>
  </form>
  <a href="<?php echo e(base_url('buyer/checkout.php')); ?>" class="btn btn-primary">Proceed to Checkout</a>
  </div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
