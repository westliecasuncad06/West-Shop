<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, u.name AS seller_name, c.name AS category_name
  FROM products p
  LEFT JOIN users u ON p.seller_id = u.user_id
  LEFT JOIN categories c ON p.category_id = c.category_id
  WHERE p.product_id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
  include __DIR__ . '/../templates/header.php';
  echo '<div class="alert alert-warning">Product not found.</div>';
  include __DIR__ . '/../templates/footer.php';
  exit;
}

// Preload store info for UI/actions
$sellerId = (int)($p['seller_id'] ?? 0);
$store = $sellerId ? get_seller_profile($sellerId) : null;
$storeRating = $sellerId ? get_store_rating($sellerId) : ['avg'=>0,'cnt'=>0];
$productRating = (float) get_product_rating((int)$p['product_id']);
$reviews = get_reviews((int)$p['product_id']);
$reviewsCount = count($reviews);
$activeUser = $_SESSION['user'] ?? null;
$userRole = $activeUser['role'] ?? null;
$isBuyer = $userRole === 'buyer';
$isSellerOrAdmin = in_array($userRole, ['seller','admin'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
  if (isset($_POST['action']) && $_POST['action']==='add') {
    // Require logged-in buyer before adding to cart
    $isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'buyer') {
      if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false, 'requiresLogin'=>true, 'message'=>'Login required']);
        exit;
      }
      set_flash('warning', 'Please login to add items to cart.');
      redirect('login.php');
    }

    $qty = max(1, (int)($_POST['qty'] ?? 1));
    cart_add($p['product_id'], $qty);
    if ($isAjax) {
      header('Content-Type: application/json');
      echo json_encode(['ok'=>true,'message'=>'Added to cart']);
      exit;
    }
    set_flash('success', 'Added to cart!');
    redirect('buyer/cart.php');
  } elseif (isset($_POST['action']) && $_POST['action']==='review') {
    // Submit review (buyers only)
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'buyer') {
      set_flash('danger', 'Only buyers can leave reviews.');
    } else {
      $rating = (int)($_POST['rating'] ?? 5);
      $comment = trim($_POST['comment'] ?? '');
      $stmt = $pdo->prepare('INSERT INTO reviews(product_id,buyer_id,rating,comment) VALUES (?,?,?,?)');
      $stmt->execute([$p['product_id'], $_SESSION['user']['user_id'], $rating, $comment]);
      set_flash('success', 'Thank you for your review.');
    }
    redirect('public/product.php?id=' . $p['product_id']);
  } elseif (isset($_POST['action']) && in_array($_POST['action'], ['follow_store','unfollow_store','rate_store'], true)) {
    if (!$sellerId) { redirect('public/product.php?id=' . $p['product_id']); }
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'buyer') { set_flash('warning','Login as buyer to interact with the store.'); redirect('login.php'); }
    if ($_POST['action'] === 'follow_store') {
      $ins = $pdo->prepare('INSERT IGNORE INTO store_follows(buyer_id, seller_id) VALUES (?,?)');
      $ins->execute([$user['user_id'], $sellerId]);
      set_flash('success','Following the store.');
    } elseif ($_POST['action'] === 'unfollow_store') {
      $del = $pdo->prepare('DELETE FROM store_follows WHERE buyer_id=? AND seller_id=?');
      $del->execute([$user['user_id'], $sellerId]);
      set_flash('success','Unfollowed the store.');
    } else { // rate_store
      $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
      $comment = trim($_POST['comment'] ?? '');
      $hasStoreId = store_reviews_has_column('store_id');
      $hasSellerId = store_reviews_has_column('seller_id');

      $columns = [];
      $params = [];
      if ($hasStoreId) {
        $sid = get_store_id_for_seller($sellerId);
        if (!$sid) { set_flash('danger','Store is not available for rating.'); redirect('public/product.php?id=' . $p['product_id']); }
        $columns[] = 'store_id';
        $params[] = $sid;
      }
      if ($hasSellerId) {
        $columns[] = 'seller_id';
        $params[] = $sellerId;
      }
      $columns = array_merge($columns, ['buyer_id','rating','comment']);
      $params = array_merge($params, [$user['user_id'], $rating, $comment]);

      $placeholders = rtrim(str_repeat('?,', count($columns)), ',');
      $sql = 'INSERT INTO store_reviews(' . implode(',', $columns) . ')
                           VALUES (' . $placeholders . ')
                           ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)';
      if (store_reviews_has_updated_at()) {
        $sql .= ', updated_at=NOW()';
      }
      $up = $pdo->prepare($sql);
      $up->execute($params);
      set_flash('success','Thanks for rating the store.');
    }
    redirect('public/product.php?id=' . $p['product_id']);
  }
}

include __DIR__ . '/../templates/header.php';
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
  <a href="<?php echo e(base_url('index.php')); ?>" class="text-decoration-none text-muted d-flex align-items-center gap-2 small">
    <i class="bi bi-arrow-left"></i> Back to discoveries
  </a>
  <span class="badge-chip">Product ID #<?php echo (int)$p['product_id']; ?></span>
</div>

<div class="row g-4 align-items-start">
  <div class="col-lg-6">
    <div class="product-gallery position-relative">
      <img src="<?php echo e(product_image_src($p['image'] ?? null, 'https://via.placeholder.com/700x520?text=Product')); ?>" alt="<?php echo e($p['name']); ?> visual">
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card product-info-card p-4 h-100">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <span class="badge-chip"><?php echo e($p['category_name'] ?? 'General'); ?></span>
        <span class="rating-pill"><i class="bi bi-star-fill"></i> <?php echo number_format($productRating,1); ?> / 5 · <?php echo $reviewsCount; ?> review<?php echo $reviewsCount===1?'':'s'; ?></span>
      </div>
      <h2 class="fw-semibold mb-2"><?php echo e($p['name']); ?></h2>
      <div class="text-muted mb-4">by <span class="fw-semibold"><?php echo e($p['seller_name'] ?? 'Marketplace seller'); ?></span></div>
      <div>
        <div class="price-tag">$<?php echo number_format((float)$p['price'],2); ?></div>
        <div class="price-caption">Price includes VAT. Free cancellation within 24h.</div>
      </div>
      <div class="perks-list mt-4">
        <div class="perks-list__item"><span class="perks-list__icon"><i class="bi bi-truck"></i></span>Fast shipping nationwide</div>
        <div class="perks-list__item"><span class="perks-list__icon"><i class="bi bi-shield-check"></i></span>Buyer protection up to $500</div>
        <div class="perks-list__item"><span class="perks-list__icon"><i class="bi bi-arrow-repeat"></i></span>7-day easy returns</div>
      </div>
      <form method="post" id="addToCartForm" class="d-flex flex-column gap-3 mt-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="add">
        <label class="fw-semibold small text-uppercase">Quantity</label>
        <div class="qty-input-group d-inline-flex align-items-center gap-2">
          <input type="number" name="qty" min="1" value="1" class="form-control" aria-label="Quantity" <?php echo $isSellerOrAdmin ? 'disabled' : ''; ?>>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100" id="addToCartBtn" <?php echo $isSellerOrAdmin ? 'disabled aria-disabled="true"' : ''; ?>>Add to cart</button>
        <?php if($isSellerOrAdmin): ?>
          <div class="alert alert-warning small mb-0" role="alert">
            You're currently logged in as a <?php echo e($userRole); ?>. Please switch to a buyer account to place orders.
          </div>
        <?php endif; ?>
        <div class="small text-muted text-center">Secure checkout powered by West Shop Pay</div>
      </form>

      <?php if($sellerId && $store): ?>
        <div class="card store-card p-4 mt-4">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="store-avatar"><?php echo strtoupper(substr($store['store_name'] ?? ($p['seller_name'] ?? 'W'), 0, 1)); ?></div>
            <div>
              <div class="fw-semibold"><?php echo e($store['store_name'] ?? ($p['seller_name'] ?? 'Featured Store')); ?></div>
              <div class="text-muted small">Rating <?php echo number_format((float)$storeRating['avg'],1); ?> · <?php echo (int)$storeRating['cnt']; ?> review<?php echo ((int)$storeRating['cnt'])===1?'':'s'; ?></div>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-outline-primary" href="<?php echo e(base_url('seller/store_public.php?seller_id='.(int)$sellerId)); ?>">Visit Store</a>
            <?php if(isset($_SESSION['user']) && $_SESSION['user']['role']==='buyer'): ?>
              <?php $isFollowing = is_store_followed($_SESSION['user']['user_id'], $sellerId); ?>
              <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $isFollowing ? 'unfollow_store' : 'follow_store'; ?>">
                <button class="btn btn-<?php echo $isFollowing ? 'secondary' : 'primary'; ?>"><?php echo $isFollowing ? 'Unfollow Store' : 'Follow Store'; ?></button>
              </form>
              <button class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#rateStoreForm">Rate Store</button>
            <?php else: ?>
              <a class="btn btn-outline-secondary" href="<?php echo e(base_url('login.php')); ?>">Login to follow</a>
            <?php endif; ?>
          </div>
          <?php if(isset($_SESSION['user']) && $_SESSION['user']['role']==='buyer'): ?>
            <div id="rateStoreForm" class="collapse mt-3">
              <form method="post" class="row g-2">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="rate_store">
                <div class="col-12 col-md-4">
                  <select name="rating" class="form-select">
                    <?php for($i=5;$i>=1;$i--): ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php endfor; ?>
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <input name="comment" class="form-control" placeholder="Optional feedback">
                </div>
                <div class="col-12 col-md-2 d-grid">
                  <button class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-4 mt-2">
  <div class="col-lg-8">
    <div class="card p-4 h-100">
      <div class="section-label mb-2">Product details</div>
      <?php if(!empty($p['description'])): ?>
        <p class="mb-0"><?php echo nl2br(e($p['description'])); ?></p>
      <?php else: ?>
        <div class="text-muted">Seller has not provided a detailed description yet.</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-4 h-100">
      <div class="section-label mb-2">Why customers love it</div>
      <ul class="feature-list">
        <li><i class="bi bi-check"></i>Premium materials sourced from verified suppliers.</li>
        <li><i class="bi bi-check"></i>Dedicated support channel with same-day responses.</li>
        <li><i class="bi bi-check"></i>Guarantee of replacement when damaged on arrival.</li>
      </ul>
    </div>
  </div>
</div>

<section class="mt-5" id="reviews">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h5 class="mb-0">Customer Reviews</h5>
      <div class="text-muted small"><?php echo $reviewsCount; ?> review<?php echo $reviewsCount===1?'':'s'; ?> · Average <?php echo number_format($productRating,1); ?>/5</div>
    </div>
    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer'): ?>
      <a href="#write-review" class="btn btn-outline-primary btn-sm">Write a review</a>
    <?php endif; ?>
  </div>

  <?php if($reviews): ?>
    <div class="d-flex flex-column gap-3">
      <?php foreach($reviews as $r): ?>
        <div class="review-card">
          <div class="review-meta">
            <div>
              <strong><?php echo e($r['buyer_name'] ?? 'Buyer'); ?></strong>
              <div class="text-muted small">Rated <?php echo (int)$r['rating']; ?>/5</div>
            </div>
            <span class="text-muted small"><?php echo e($r['created_at']); ?></span>
          </div>
          <?php if(!empty($r['comment'])): ?>
            <p class="mt-3 mb-0"><?php echo nl2br(e($r['comment'])); ?></p>
          <?php else: ?>
            <p class="mt-3 mb-0 text-muted">No comment provided.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-panel">
      <h6 class="mb-1">No reviews yet</h6>
      <p class="text-muted mb-0">Be the first to share how this product helped you.</p>
    </div>
  <?php endif; ?>
</section>

<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer'): ?>
  <div class="card p-4 mt-4" id="write-review">
    <h6 class="mb-3">Leave a review</h6>
    <form method="post" class="row g-3">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="review">
      <div class="col-12 col-md-3">
        <label class="form-label">Rating</label>
        <select name="rating" class="form-select">
          <?php for($i=5;$i>=1;$i--): ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php endfor; ?>
        </select>
      </div>
      <div class="col-12 col-md-9">
        <label class="form-label">Comments</label>
        <textarea name="comment" class="form-control" rows="3" placeholder="Share details that would help other buyers"></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Submit review</button>
      </div>
    </form>
  </div>
<?php else: ?>
  <div class="text-muted mt-4">Only buyers can leave reviews. Please <a href="<?php echo e(base_url('login.php')); ?>">login</a> or create an account.</div>
<?php endif; ?>

<?php if(!isset($_SESSION['user'])): ?>
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login to continue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="loginForm">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="text-danger small" id="loginError" style="display:none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="loginSubmitBtn" class="btn btn-primary">Login</button>
      </div>
    </div>
  </div>
  </div>

<script>
(function(){
  const addForm = document.getElementById('addToCartForm');
  const addBtn = document.getElementById('addToCartBtn');
  const loginModalEl = document.getElementById('loginModal');
  const loginModal = new bootstrap.Modal(loginModalEl);
  const loginBtn = document.getElementById('loginSubmitBtn');
  const loginForm = document.getElementById('loginForm');
  const errBox = document.getElementById('loginError');

  addForm.addEventListener('submit', function(ev){
    ev.preventDefault();
    // User not logged in -> show modal
    loginModal.show();
  });

  loginBtn.addEventListener('click', async function(){
    errBox.style.display='none'; errBox.textContent='';
    const fd = new FormData(loginForm);
    loginBtn.disabled = true; loginBtn.textContent = 'Logging in...';
    try {
      const res = await fetch('<?php echo e(base_url('public/ajax_login.php')); ?>', {method:'POST', body: fd, credentials:'same-origin'});
      const data = await res.json();
      if(!data.ok){ errBox.textContent = data.message || 'Login failed.'; errBox.style.display='block'; }
      else {
        // After successful login, perform AJAX add-to-cart
        const cartFd = new FormData(addForm);
        cartFd.append('ajax','1');
        const r2 = await fetch(window.location.href, {method:'POST', body: cartFd, credentials:'same-origin'});
        const d2 = await r2.json();
        if(d2 && d2.ok){
          loginModal.hide();
          alert('Product added to cart');
          // Optionally update cart UI or redirect
        } else {
          alert('Could not add to cart.');
        }
      }
    } catch(e){
      errBox.textContent = 'Network error. Please try again.'; errBox.style.display='block';
    } finally {
      loginBtn.disabled = false; loginBtn.textContent = 'Login';
    }
  });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../templates/footer.php'; ?>
