<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');

$u = current_user();
$items = cart_items_with_products();
if (!$items) { set_flash('warning','Your cart is empty.'); header('Location: '.base_url('buyer/cart.php')); exit; }
$paymentValue = $_POST['payment_method'] ?? 'COD';
$couponValue = $_POST['coupon'] ?? '';

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
<?php $subtotal = array_reduce($items, fn($sum,$it)=>$sum + $it['line_total'], 0); ?>

<style>
.checkout-hero {
  background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 100%);
  border-radius: 1.2rem;
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.progress-steps {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}
.progress-steps .step {
  flex: 1;
  min-width: 120px;
  text-align: center;
}
.progress-steps .bubble {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  margin: 0 auto .35rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #fff;
  background: #6366f1;
  box-shadow: 0 6px 14px rgba(99,102,241,.2);
}
.checkout-card {
  border: none;
  border-radius: 1.1rem;
  box-shadow: 0 24px 45px rgba(15,23,42,.08);
}
.payment-option {
  border: 1px solid #e2e8f0;
  border-radius: .9rem;
  padding: 1rem;
  transition: border-color .2s, background .2s;
}
.payment-option input[type="radio"] {
  accent-color: #2563eb;
}
.payment-option:hover,
.payment-option.active {
  border-color: #2563eb;
  background: #eff6ff;
}
.summary-item + .summary-item { border-top: 1px dashed #e2e8f0; }
.summary-item {
  padding: .75rem 0;
}
.cart-row img {
  width: 56px;
  height: 56px;
  object-fit: cover;
  border-radius: .75rem;
}
</style>

<div class="checkout-hero">
  <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
    <div>
      <div class="text-muted text-uppercase small">Secure Checkout</div>
      <h3 class="m-0">Complete your purchase</h3>
    </div>
    <div class="text-muted small">Logged in as <?php echo e($u['name']); ?> • <?php echo e($u['email']); ?></div>
  </div>
  <div class="progress-steps mt-3">
    <div class="step">
      <div class="bubble">1</div>
      <div class="fw-semibold">Review Cart</div>
      <small class="text-muted">Items ready</small>
    </div>
    <div class="step">
      <div class="bubble">2</div>
      <div class="fw-semibold text-primary">Checkout</div>
      <small class="text-muted">Details & payment</small>
    </div>
    <div class="step">
      <div class="bubble" style="background:#cbd5f5;color:#475569;">3</div>
      <div class="fw-semibold">Confirmation</div>
      <small class="text-muted">Receive receipt</small>
    </div>
  </div>
</div>

<form method="post" class="row g-4 align-items-start">
  <?php echo csrf_field(); ?>
  <div class="col-lg-8">
    <div class="card checkout-card mb-4 p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="mb-1">Delivery Details</h5>
          <div class="text-muted small">We will ship based on your saved profile</div>
        </div>
        <span class="badge bg-light text-dark">Buyer Account</span>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label text-muted small">Full name</label>
          <div class="fw-semibold"><?php echo e($u['name']); ?></div>
        </div>
        <div class="col-md-6">
          <label class="form-label text-muted small">Contact email</label>
          <div class="fw-semibold"><?php echo e($u['email']); ?></div>
        </div>
        <div class="col-12">
          <label class="form-label text-muted small">Shipping address</label>
          <div class="fw-semibold"><?php echo $u['address'] ? e($u['address']) : 'No address on file yet'; ?></div>
        </div>
      </div>
    </div>

    <div class="card checkout-card mb-4 p-4">
      <h5 class="mb-3">Payment Method</h5>
      <div class="row g-3">
        <?php
          $paymentOptions = [
            ['value' => 'COD', 'label' => 'Cash on Delivery', 'desc' => 'Pay with cash once the package arrives.'],
            ['value' => 'Card', 'label' => 'Credit/Debit Card', 'desc' => 'Visa, MasterCard, AmEx (simulated).'],
            ['value' => 'PayPal', 'label' => 'PayPal', 'desc' => 'Use your PayPal wallet (simulated).'],
          ];
        ?>
        <?php foreach($paymentOptions as $opt): $active = $paymentValue === $opt['value']; ?>
          <div class="col-md-4">
            <label class="payment-option w-100 <?php echo $active ? 'active' : ''; ?>">
              <div class="d-flex align-items-start gap-2">
                <input type="radio" name="payment_method" value="<?php echo $opt['value']; ?>" <?php echo $active ? 'checked' : ''; ?>>
                <div>
                  <div class="fw-semibold"><?php echo $opt['label']; ?></div>
                  <div class="text-muted small"><?php echo $opt['desc']; ?></div>
                </div>
              </div>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card checkout-card p-4">
      <h5 class="mb-3">Special instructions</h5>
      <textarea class="form-control" rows="4" placeholder="Add delivery instructions for the courier (optional)" disabled></textarea>
      <small class="text-muted">Coming soon: add delivery notes for each order.</small>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card checkout-card p-4">
      <h5 class="mb-3">Order Summary</h5>
      <div class="summary-list">
        <?php foreach($items as $it): ?>
          <div class="summary-item d-flex align-items-center cart-row">
            <img src="<?php echo e(product_image_src($it['image'] ?? null)); ?>" alt="<?php echo e($it['name']); ?>">
            <div class="ms-3 flex-grow-1">
              <div class="fw-semibold"><?php echo e($it['name']); ?></div>
              <div class="text-muted small">Qty <?php echo (int)$it['cart_qty']; ?></div>
            </div>
            <div class="fw-semibold">$<?php echo number_format((float)$it['line_total'],2); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="summary-item d-flex justify-content-between">
        <span class="text-muted">Subtotal</span>
        <span class="fw-semibold">$<?php echo number_format((float)$subtotal, 2); ?></span>
      </div>
      <div class="summary-item d-flex justify-content-between">
        <span class="text-muted">Shipping</span>
        <span class="fw-semibold text-success">Free</span>
      </div>
      <div class="summary-item">
        <label class="form-label text-muted small mb-1">Coupon code</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-gift"></i></span>
          <input name="coupon" value="<?php echo e($couponValue); ?>" class="form-control" placeholder="Enter coupon code">
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-2">
        <span class="text-muted">Discount</span>
        <span class="fw-semibold">Calculated at payment</span>
      </div>
      <div class="summary-item d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Total</span>
        <span class="fs-4 fw-bold text-primary">$<?php echo number_format((float)$subtotal,2); ?></span>
      </div>
      <button class="btn btn-primary w-100 mt-3 py-2">Place Order</button>
      <div class="text-center text-muted small mt-2">By placing the order you agree to our Terms & Refund Policy.</div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/../templates/footer.php'; ?>
