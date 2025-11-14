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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
  if (isset($_POST['action']) && $_POST['action']==='add') {
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    cart_add($p['product_id'], $qty);
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
  }
}
?>

<div class="row g-4">
  <div class="col-md-5">
    <img src="<?php echo e($p['image'] ? base_url('assets/images/'.$p['image']) : 'https://via.placeholder.com/600x450?text=Product'); ?>" class="img-fluid rounded-4 shadow-sm" alt="">
  </div>
  <div class="col-md-7">
    <h3 class="fw-semibold mb-1"><?php echo e($p['name']); ?></h3>
    <div class="text-muted mb-2"><?php echo e($p['category_name'] ?? ''); ?> • by <?php echo e($p['seller_name'] ?? ''); ?></div>
    <p class="lead">$<?php echo number_format((float)$p['price'],2); ?></p>
    <div class="mb-2">Rating: <strong><?php echo number_format(get_product_rating((int)$p['product_id']),1); ?></strong> / 5</div>
    <p><?php echo nl2br(e($p['description'] ?? '')); ?></p>
    <form method="post" class="d-flex align-items-center gap-2">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="add">
      <input type="number" name="qty" class="form-control" style="max-width:120px" min="1" value="1">
      <button class="btn btn-primary">Add to Cart</button>
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

<?php include __DIR__ . '/../templates/footer.php'; ?>
