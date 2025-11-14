<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
  if (isset($_POST['remove_id'])) {
    cart_remove((int)$_POST['remove_id']);
    set_flash('success', 'Item removed');
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
      foreach (($_POST['qty'] ?? []) as $pid => $qty) {
        cart_set((int)$pid, (int)$qty);
      }
      set_flash('success', 'Cart updated');
    } elseif ($action === 'clear') {
      cart_clear();
      set_flash('success', 'Cart cleared');
    }
  }
}

$items = cart_items_with_products();
$total = 0; foreach($items as $it){ $total += $it['line_total']; }

include __DIR__ . '/../templates/header.php';
?>

<section class="section-shell">
  <div class="section-heading">
    <div>
      <p class="section-heading__eyebrow mb-1">Buyer cart</p>
      <h1 class="section-heading__title mb-0">Review your picks</h1>
      <p class="page-subtitle mt-2">Adjust quantities, remove items, or jump straight to checkout. Everything stays in sync in real time.</p>
    </div>
    <div class="section-heading__actions">
      <a href="<?php echo e(base_url('index.php')); ?>" class="pill-link"><i class="bi bi-arrow-left"></i> Continue shopping</a>
    </div>
  </div>

  <?php if($items): ?>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="surface-card p-0 overflow-hidden">
          <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update">
            <div class="table-responsive">
              <table class="table table-modern align-middle mb-0">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th style="width:130px">Quantity</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($items as $it): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?php echo e(product_image_src($it['image'] ?? null)); ?>" alt="<?php echo e($it['name']); ?>" class="rounded" style="width:64px;height:64px;object-fit:cover;">
                          <div>
                            <div class="fw-semibold"><?php echo e($it['name']); ?></div>
                            <div class="text-muted-soft small">SKU #<?php echo (int)$it['product_id']; ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <input type="number" class="form-control" name="qty[<?php echo (int)$it['product_id']; ?>]" value="<?php echo (int)$it['cart_qty']; ?>" min="0">
                      </td>
                      <td class="text-end fw-semibold text-muted-soft">$<?php echo number_format((float)$it['price'],2); ?></td>
                      <td class="text-end fw-semibold">$<?php echo number_format((float)$it['line_total'],2); ?></td>
                      <td class="text-end">
                        <button class="btn btn-sm btn-link text-danger" type="submit" name="remove_id" value="<?php echo (int)$it['product_id']; ?>" formnovalidate>
                          Remove
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="p-3 d-flex justify-content-between flex-wrap gap-2 align-items-center">
              <button class="btn btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i> Update cart</button>
              <span class="fw-semibold">Grand Total: <span class="text-primary fs-5">$<?php echo number_format((float)$total,2); ?></span></span>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="surface-card surface-card--muted h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Order summary</h5>
            <span class="badge-soft"><?php echo count($items); ?> items</span>
          </div>
          <ul class="stacked-list mb-3">
            <?php foreach($items as $it): ?>
              <li class="stacked-list__item">
                <span><?php echo e($it['name']); ?></span>
                <span class="fw-semibold">x<?php echo (int)$it['cart_qty']; ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted-soft">Subtotal</span>
            <span class="fw-semibold">$<?php echo number_format((float)$total,2); ?></span>
          </div>
          <p class="small text-muted-soft mb-3">Shipping & discounts appear in checkout.</p>
          <div class="d-grid gap-2">
            <a href="<?php echo e(base_url('buyer/checkout.php')); ?>" class="btn btn-primary">
              <i class="bi bi-shield-lock me-1"></i> Proceed to checkout
            </a>
            <form method="post">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="clear">
              <button class="btn btn-outline-danger w-100" type="submit">
                <i class="bi bi-x-circle me-1"></i> Clear cart
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="empty-state-card text-center">
      <i class="bi bi-emoji-smile fs-1 text-primary d-block mb-3"></i>
      <h5 class="mb-1">Your cart is empty</h5>
      <p class="text-muted-soft mb-3">Explore curated drops and add items you love—they’ll appear here instantly.</p>
      <a href="<?php echo e(base_url('index.php#products')); ?>" class="btn btn-primary">
        <i class="bi bi-compass"></i> Browse products
      </a>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
