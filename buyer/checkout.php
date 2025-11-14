<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');

$u = current_user();
$items = cart_items_with_products();
if (!$items) { set_flash('warning','Your cart is empty.'); header('Location: '.base_url('buyer/cart.php')); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
  $pm = $_POST['payment_method'] ?? 'COD';
  $couponCode = trim($_POST['coupon'] ?? '');
  $coupon = null; $discountAmount = 0.00;
  if ($couponCode !== '') {
    $c = get_valid_coupon($couponCode);
    if ($c) {
      $discountAmount = calc_coupon_discount($c, array_reduce($items, fn($s,$it)=>$s+$it['line_total'],0));
      $coupon = ['code'=>$c['code'], 'discount_amount'=>$discountAmount];
    } else {
      set_flash('danger', 'Invalid or expired coupon code.');
      header('Location: '.base_url('buyer/checkout.php'));
      exit;
    }
  }
  $orderId = order_create_from_cart((int)$u['user_id'], $pm, $coupon);
  if ($orderId) {
    set_flash('success', 'Order placed! Order #'.$orderId);
    header('Location: '.base_url('buyer/orders.php'));
    exit;
  } else {
    set_flash('danger', 'Failed to place order.');
  }
}

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Checkout</h4>
<div class="card p-3 mb-3">
  <div class="table-responsive">
  <table class="table">
    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
    <tbody>
      <?php $grand=0; foreach($items as $it): $grand+=$it['line_total']; ?>
        <tr>
          <td><?php echo e($it['name']); ?></td>
          <td><?php echo (int)$it['cart_qty']; ?></td>
          <td>$<?php echo number_format((float)$it['price'],2); ?></td>
          <td>$<?php echo number_format((float)$it['line_total'],2); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <div class="text-end fw-semibold fs-5">Total: $<?php echo number_format((float)$grand,2); ?></div>
</div>

<form method="post" class="card p-3">
  <?php echo csrf_field(); ?>
  <div class="mb-3">
    <label class="form-label">Coupon code (optional)</label>
    <input name="coupon" class="form-control" placeholder="Enter coupon code">
  </div>
  <div class="mb-3">
    <label class="form-label">Payment Method</label>
    <select name="payment_method" class="form-select">
      <option value="COD">Cash on Delivery</option>
      <option value="Card">Credit/Debit Card (simulated)</option>
      <option value="PayPal">PayPal (simulated)</option>
    </select>
  </div>
  <button class="btn btn-primary">Place Order</button>
</form>

<?php include __DIR__ . '/../templates/footer.php'; ?>
