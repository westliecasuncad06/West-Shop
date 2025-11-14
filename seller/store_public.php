<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$seller_id = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : 0;
if (!$seller_id) { echo '<div class="alert alert-danger">Store not found</div>'; include __DIR__.'/../templates/footer.php'; exit; }

$store = get_seller_profile($seller_id);
if (!$store) { echo '<div class="alert alert-danger">Store not found</div>'; include __DIR__.'/../templates/footer.php'; exit; }

// Handle follow / rate actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $user = $_SESSION['user'] ?? null;
    if ($action === 'follow_store' && $user && ($user['role'] ?? '') === 'buyer') {
        $ins = $pdo->prepare('INSERT IGNORE INTO store_follows(buyer_id, seller_id) VALUES (?,?)');
        $ins->execute([$user['user_id'], $seller_id]);
        set_flash('success','You are now following this store');
        header('Location: '.base_url('seller/store_public.php?seller_id='.$seller_id)); exit;
    }
    if ($action === 'unfollow_store' && $user && ($user['role'] ?? '') === 'buyer') {
        $del = $pdo->prepare('DELETE FROM store_follows WHERE buyer_id=? AND seller_id=?');
        $del->execute([$user['user_id'], $seller_id]);
        set_flash('success','Unfollowed the store');
        header('Location: '.base_url('seller/store_public.php?seller_id='.$seller_id)); exit;
    }
    if ($action === 'rate_store' && $user && ($user['role'] ?? '') === 'buyer') {
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $comment = trim($_POST['comment'] ?? '');
        $up = $pdo->prepare('INSERT INTO store_reviews(seller_id,buyer_id,rating,comment) VALUES (?,?,?,?)
                             ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment), updated_at=NOW()');
        $up->execute([$seller_id, $user['user_id'], $rating, $comment]);
        set_flash('success','Thanks for rating the store');
        header('Location: '.base_url('seller/store_public.php?seller_id='.$seller_id)); exit;
    }
}

$rating = get_store_rating($seller_id);
$isBuyer = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'buyer';
$isFollowing = $isBuyer ? is_store_followed($_SESSION['user']['user_id'], $seller_id) : false;
$followers = count_store_followers($seller_id);

// fetch products
$p = $pdo->prepare('SELECT * FROM products WHERE seller_id = ? AND status = "active" ORDER BY created_at DESC');
$p->execute([$seller_id]); $products = $p->fetchAll();

// Fetch active coupons for this store (robust to missing `stores` table)
$storeId = null;
try {
  $storeStmt = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
  $storeStmt->execute([$seller_id]);
  $storeId = $storeStmt->fetchColumn();
} catch (Exception $e) {
  $storeId = null; // stores table might not exist yet
}

$activeCoupons = [];
try {
  if ($storeId) {
    $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE store_id = ? ORDER BY created_at DESC');
    $couponStmt->execute([$storeId]);
    $allCoupons = $couponStmt->fetchAll();
  } else {
    // Fallback: show coupons created by this seller (pre-store migration)
    $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE created_by = ? ORDER BY created_at DESC');
    $couponStmt->execute([$seller_id]);
    $allCoupons = $couponStmt->fetchAll();
  }
  // Filter for active/valid coupons
  $activeCoupons = array_filter($allCoupons, function($c) {
    $notExpired = !$c['expires_at'] || strtotime($c['expires_at']) >= time();
    $notMaxed = $c['max_uses'] == 0 || $c['used_count'] < $c['max_uses'];
    return $notExpired && $notMaxed;
  });
} catch (Exception $e) {
  $activeCoupons = [];
}

include __DIR__ . '/../templates/header.php';
?>

<!-- Store Banner & Profile -->
<div class="card mb-4 border-0 shadow-sm overflow-hidden">
  <div style="height:300px;overflow:hidden;background:#f8f9fa;">
    <img src="<?php echo e($store['banner'] ? base_url($store['banner']) : base_url('assets/images/products/placeholder.png')); ?>" 
         class="w-100 h-100" style="object-fit:cover">
  </div>
  <div class="p-4 bg-white">
    <div class="d-flex align-items-start gap-3">
      <img src="<?php echo e($store['logo'] ? base_url($store['logo']) : base_url('assets/images/products/placeholder.png')); ?>" 
           width="100" height="100" class="rounded-circle border" style="object-fit:cover;margin-top:-50px;background:white;">
      <div class="flex-grow-1" style="margin-top:-30px;">
        <h3 class="mb-1 fw-bold"><?php echo e($store['shop_name'] ?? ($store['seller_name'].' Shop')); ?> 
          <small class="text-muted fs-6 fw-normal">by <?php echo e($store['seller_name']); ?></small>
        </h3>
        <div class="text-muted small mb-2">
          Rating: <?php echo number_format((float)$rating['avg'],1); ?> (<?php echo (int)$rating['cnt']; ?>) • 
          Followers: <?php echo (int)$followers; ?>
        </div>
        <?php if($isBuyer): ?>
          <form method="post" class="d-inline">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="<?php echo $isFollowing ? 'unfollow_store' : 'follow_store'; ?>">
            <button class="btn btn-sm btn-<?php echo $isFollowing ? 'secondary' : 'primary'; ?> px-4">
              <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Active Coupons Section -->
<?php if (count($activeCoupons) > 0): ?>
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">🎟️ Available Coupons</h5>
    <div class="row g-3">
      <?php foreach ($activeCoupons as $coupon): ?>
        <div class="col-md-4">
          <div class="card bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body p-3 text-white">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0 fw-bold"><?php echo e($coupon['code']); ?></h5>
                <span class="badge bg-warning text-dark">
                  <?php if ($coupon['type'] === 'percent'): ?>
                    <?php echo e($coupon['value']); ?>% OFF
                  <?php else: ?>
                    $<?php echo e($coupon['value']); ?> OFF
                  <?php endif; ?>
                </span>
              </div>
              <?php if ($coupon['expires_at']): ?>
                <div class="small opacity-75">
                  Expires: <?php echo e(date('M j, Y', strtotime($coupon['expires_at']))); ?>
                </div>
              <?php else: ?>
                <div class="small opacity-75">No expiration</div>
              <?php endif; ?>
              <?php if ($coupon['max_uses'] > 0): ?>
                <div class="small opacity-75">
                  <?php echo ($coupon['max_uses'] - $coupon['used_count']); ?> uses left
                </div>
              <?php endif; ?>
              <div class="mt-2 pt-2 border-top border-light">
                <small class="opacity-75">Use this code at checkout</small>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Main Content -->
  <div class="col-lg-8">
    <!-- Contact Seller Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 fw-bold">Products</h5>
      <a class="btn btn-outline-primary btn-sm" href="<?php echo e(base_url('buyer/chat.php?to='.(int)$seller_id)); ?>">
        Contact Seller
      </a>
    </div>

    <!-- Products Grid -->
    <div class="row g-3">
      <?php foreach($products as $prod): ?>
        <div class="col-md-6 col-lg-4">
          <a href="<?php echo e(base_url('public/product.php?id='.(int)$prod['product_id'])); ?>" 
             class="text-decoration-none text-reset">
            <div class="card border-0 shadow-sm h-100 product-card-hover">
              <div style="height:200px;overflow:hidden;background:#f8f9fa;">
                <img src="<?php echo e($prod['image'] ?: base_url('assets/images/products/placeholder.png')); ?>" 
                     class="w-100 h-100" style="object-fit:cover;">
              </div>
              <div class="card-body">
                <h6 class="mb-2 text-truncate"><?php echo e($prod['name']); ?></h6>
                <div class="fw-bold text-primary">$<?php echo number_format($prod['price'],2); ?></div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if(!$products): ?>
        <div class="col-12">
          <div class="alert alert-light text-center">No products available yet.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <!-- Active Coupons in Sidebar (if available) -->
    <?php if (count($activeCoupons) > 0 && count($activeCoupons) <= 2): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">🎟️ Coupons</h6>
        <?php foreach ($activeCoupons as $coupon): ?>
          <div class="mb-2 p-2 bg-light rounded">
            <div class="fw-bold text-success"><?php echo e($coupon['code']); ?></div>
            <div class="small text-muted">
              <?php if ($coupon['type'] === 'percent'): ?>
                Save <?php echo e($coupon['value']); ?>% on your order
              <?php else: ?>
                Save $<?php echo e($coupon['value']); ?> on your order
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- About -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">About</h6>
        <p class="text-muted small mb-0">
          <?php echo e($store['description'] ?: 'The shop where you can find happiness'); ?>
        </p>
      </div>
    </div>

    <!-- Rate Store -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Rate this store</h6>
        <?php if($isBuyer): ?>
          <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="rate_store">
            <div class="mb-2">
              <select name="rating" class="form-select form-select-sm">
                <option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
              </select>
            </div>
            <div class="mb-2">
              <textarea name="comment" class="form-control form-control-sm" 
                        rows="3" placeholder="Optional comment"></textarea>
            </div>
            <button class="btn btn-primary btn-sm w-100">Submit Rating</button>
          </form>
        <?php else: ?>
          <div class="small text-muted">Login as buyer to rate this store.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Policies -->
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Policies</h6>
        <div class="mb-2">
          <div class="small fw-semibold text-dark">Shipping:</div>
          <div class="small text-muted"><?php echo e($store['shipping_policy'] ?: 'N/A'); ?></div>
        </div>
        <div>
          <div class="small fw-semibold text-dark">Returns:</div>
          <div class="small text-muted"><?php echo e($store['return_policy'] ?: 'N/A'); ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.product-card-hover {
  transition: transform 0.2s, box-shadow 0.2s;
}
.product-card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>

<?php include __DIR__ . '/../templates/footer.php'; ?>
