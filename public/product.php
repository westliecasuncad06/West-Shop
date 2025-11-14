<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../templates/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, u.name AS seller_name, c.name AS category_name
  FROM products p
  LEFT JOIN users u ON p.seller_id = u.user_id
  LEFT JOIN categories c ON p.category_id = c.category_id
  WHERE p.product_id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
  echo '<div class="alert alert-warning">Product not found.</div>';
  include __DIR__ . '/../templates/footer.php';
  exit;
}

// Preload store info for UI/actions
$sellerId = (int)($p['seller_id'] ?? 0);
$store = $sellerId ? get_seller_profile($sellerId) : null;
$storeRating = $sellerId ? get_store_rating($sellerId) : ['avg'=>0,'cnt'=>0];

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
      $up = $pdo->prepare('INSERT INTO store_reviews(seller_id,buyer_id,rating,comment) VALUES (?,?,?,?)
                           ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment), updated_at=NOW()');
      $up->execute([$sellerId, $user['user_id'], $rating, $comment]);
      set_flash('success','Thanks for rating the store.');
    }
    redirect('public/product.php?id=' . $p['product_id']);
  }
}
?>

<div class="row g-4">
  <div class="col-md-5">
    <img src="<?php echo e(product_image_src($p['image'] ?? null, 'https://via.placeholder.com/600x450?text=Product')); ?>" class="img-fluid rounded-4 shadow-sm" alt="">
  </div>
  <div class="col-md-7">
    <h3 class="fw-semibold mb-1"><?php echo e($p['name']); ?></h3>
    <div class="text-muted mb-2"><?php echo e($p['category_name'] ?? ''); ?> • by <?php echo e($p['seller_name'] ?? ''); ?></div>
    <?php if($sellerId && $store): ?>
      <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(base_url('seller/store_public.php?seller_id='.(int)$sellerId)); ?>">Visit Store</a>
        <span class="small text-muted">Store rating: <strong><?php echo number_format((float)$storeRating['avg'],1); ?></strong> (<?php echo (int)$storeRating['cnt']; ?>)</span>
        <?php if(isset($_SESSION['user']) && $_SESSION['user']['role']==='buyer'): ?>
          <?php $isFollowing = is_store_followed($_SESSION['user']['user_id'], $sellerId); ?>
          <form method="post" class="d-inline">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="<?php echo $isFollowing ? 'unfollow_store' : 'follow_store'; ?>">
            <button class="btn btn-sm btn-<?php echo $isFollowing ? 'secondary' : 'primary'; ?>"><?php echo $isFollowing ? 'Unfollow Store' : 'Follow Store'; ?></button>
          </form>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#rateStoreForm">Rate Store</button>
        <?php else: ?>
          <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(base_url('login.php')); ?>">Login to follow/rate</a>
        <?php endif; ?>
      </div>
      <?php if(isset($_SESSION['user']) && $_SESSION['user']['role']==='buyer'): ?>
      <div id="rateStoreForm" class="collapse mb-2">
        <form method="post" class="d-flex align-items-center gap-2 flex-wrap">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="rate_store">
          <label class="small text-muted me-1">Your rating:</label>
          <select name="rating" class="form-select form-select-sm" style="width:80px">
            <?php for($i=5;$i>=1;$i--): ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php endfor; ?>
          </select>
          <input name="comment" class="form-control form-control-sm" style="max-width:320px" placeholder="Optional comment">
          <button class="btn btn-sm btn-outline-primary">Submit</button>
        </form>
      </div>
      <?php endif; ?>
    <?php endif; ?>
    <p class="lead">$<?php echo number_format((float)$p['price'],2); ?></p>
    <div class="mb-2">Rating: <strong><?php echo number_format(get_product_rating((int)$p['product_id']),1); ?></strong> / 5</div>
    <p><?php echo nl2br(e($p['description'] ?? '')); ?></p>
    <form method="post" id="addToCartForm" class="d-flex align-items-center gap-2">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="add">
      <input type="number" name="qty" class="form-control" style="max-width:120px" min="1" value="1">
      <button type="submit" class="btn btn-primary" id="addToCartBtn">Add to Cart</button>
    </form>
  </div>
</div>

<hr>
<h5>Reviews</h5>
<?php foreach(get_reviews((int)$p['product_id']) as $r): ?>
  <div class="card p-3 mb-2">
    <div class="d-flex justify-content-between">
      <div><strong><?php echo e($r['buyer_name'] ?? 'Buyer'); ?></strong></div>
      <div class="text-muted small"><?php echo e($r['created_at']); ?></div>
    </div>
    <div class="mt-2">Rating: <?php echo (int)$r['rating']; ?> / 5</div>
    <div class="mt-2"><?php echo nl2br(e($r['comment'])); ?></div>
  </div>
<?php endforeach; ?>

<?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer'): ?>
  <div class="card p-3">
    <h6>Leave a review</h6>
    <form method="post">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="review">
      <div class="mb-2"><label class="form-label">Rating</label>
        <select name="rating" class="form-select" style="max-width:120px">
          <?php for($i=5;$i>=1;$i--): ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php endfor; ?>
        </select>
      </div>
      <div class="mb-2"><textarea name="comment" class="form-control" rows="3" placeholder="Write your review"></textarea></div>
      <button class="btn btn-outline-primary">Submit Review</button>
    </form>
  </div>
<?php else: ?>
  <div class="text-muted">Only buyers can leave reviews. Please <a href="<?php echo e(base_url('login.php')); ?>">login</a> or create an account.</div>
<?php endif; ?>

<?php if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'buyer'): ?>
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
