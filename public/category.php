<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../templates/header.php';

$cid = isset($_GET['c']) ? (int)$_GET['c'] : 0;
$stmt = $pdo->prepare('SELECT * FROM categories WHERE category_id = ?');
$stmt->execute([$cid]);
$cat = $stmt->fetch();

$products = [];
if ($cat) {
  $q = $pdo->prepare('SELECT * FROM products WHERE category_id = ? AND status = "active" ORDER BY created_at DESC');
  $q->execute([$cid]);
  $products = $q->fetchAll();
}
?>

<h4 class="mb-3"><?php echo $cat ? ('Category: '.e($cat['name'])) : 'Category not found'; ?></h4>

<div class="row g-3">
  <?php foreach($products as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card product-card h-100">
        <img src="<?php echo e($p['image'] ? base_url('assets/images/'.$p['image']) : 'https://via.placeholder.com/400x300?text=Product'); ?>" class="w-100" alt="">
        <div class="p-3">
          <div class="fw-semibold"><?php echo e($p['name']); ?></div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-bold">$<?php echo number_format((float)$p['price'],2); ?></div>
            <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="btn btn-sm btn-outline-primary">View</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(!$products): ?>
    <div class="col-12"><div class="alert alert-info">No products in this category.</div></div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
