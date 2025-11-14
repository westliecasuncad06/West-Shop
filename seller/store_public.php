<?php
require_once __DIR__ . '/templates/header.php';
global $pdo;
$seller_id = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : 0;
if (!$seller_id) { echo '<div class="alert alert-danger">Store not found</div>'; require __DIR__.'/templates/footer.php'; exit; }

$st = $pdo->prepare('SELECT s.*, u.name AS seller_name FROM stores s JOIN users u ON u.user_id=s.seller_id WHERE s.seller_id = ? LIMIT 1');
$st->execute([$seller_id]); $store = $st->fetch();
if (!$store) { echo '<div class="alert alert-danger">Store not found</div>'; require __DIR__.'/templates/footer.php'; exit; }

// ratings
$r = $pdo->prepare('SELECT AVG(rating) as avg, COUNT(*) as cnt FROM store_reviews WHERE store_id = ?');
$r->execute([$store['store_id']]); $rating = $r->fetch();

// fetch products
$p = $pdo->prepare('SELECT * FROM products WHERE seller_id = ? AND status = "active" ORDER BY created_at DESC');
$p->execute([$seller_id]); $products = $p->fetchAll();

?>
<div class="card mb-3 p-0 overflow-hidden">
  <img src="<?php echo e($store['banner'] ? base_url($store['banner']) : base_url('assets/images/products/placeholder.png')); ?>" class="w-100" style="height:260px;object-fit:cover">
  <div class="p-3 bg-white">
    <div class="d-flex align-items-center">
      <img src="<?php echo e($store['logo'] ? base_url($store['logo']) : base_url('assets/images/products/placeholder.png')); ?>" width="96" height="96" class="rounded-circle me-3" style="object-fit:cover">
      <div>
        <h4 class="mb-0"><?php echo e($store['store_name']); ?> <small class="text-muted">by <?php echo e($store['seller_name']); ?></small></h4>
        <div class="small text-muted">Rating: <?php echo number_format((float)$rating['avg'],1) ?: '0.0'; ?> (<?php echo (int)$rating['cnt']; ?>)</div>
      </div>
    </div>
    <p class="mt-3 mb-0 text-muted"><?php echo e($store['short_description']); ?></p>
  </div>
</div>

<div class="row">
  <div class="col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5>Products</h5>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="<?php echo e(base_url('store_profile.php')); ?>">Contact Seller</a>
      </div>
    </div>
    <div class="row g-3">
      <?php foreach($products as $prod): ?>
        <div class="col-md-4">
          <div class="card product-card">
            <img src="<?php echo e($prod['image'] ?: base_url('assets/images/products/placeholder.png')); ?>" class="w-100" style="height:180px;object-fit:cover">
            <div class="card-body">
              <h6><?php echo e($prod['name']); ?></h6>
              <div class="text-muted small">$<?php echo number_format($prod['price'],2); ?> <?php if($prod['discount_price']>0): ?><span class="text-danger">$<?php echo number_format($prod['discount_price'],2); ?></span><?php endif; ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card p-3">
      <h6>About</h6>
      <p class="small text-muted"><?php echo e($store['description']); ?></p>
      <hr>
      <h6>Policies</h6>
      <div class="small text-muted"><strong>Shipping:</strong> <?php echo e($store['shipping_policy'] ?: 'N/A'); ?></div>
      <div class="small text-muted"><strong>Returns:</strong> <?php echo e($store['return_policy'] ?: 'N/A'); ?></div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
