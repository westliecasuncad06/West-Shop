<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/templates/header.php';

$categories = get_categories();
$latest = get_featured_products(8); // currently returns newest active products
$topCategories = get_top_categories();
?>

<div class="p-4 p-md-5 mb-4 bg-white rounded-4 shadow-sm">
  <div class="row align-items-center">
    <div class="col-md-7">
      <h1 class="display-6 fw-semibold text-primary">Shop with joy at <?php echo e(APP_NAME); ?> 😊</h1>
      <p class="lead">Discover trusted sellers, great deals, and a delightful shopping experience.</p>
      <a href="#categories" class="btn btn-primary rounded-pill px-4">Browse Categories</a>
    </div>
    <div class="col-md-5 text-center">
      <span class="badge badge-accent px-3 py-2">Pastel vibes, secure checkout, happy customers</span>
    </div>
  </div>
  <hr class="my-4">
  <form action="<?php echo e(base_url('public/search.php')); ?>" method="get" class="row g-2 align-items-end" id="searchForm">
    <div class="col-12 col-md-4">
      <label class="form-label small text-muted">Search Products</label>
      <input type="text" name="q" class="form-control" placeholder="e.g. earbuds, hoodie" />
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small text-muted">Category</label>
      <select name="cat" id="topCategory" class="form-select">
        <option value="">All Categories</option>
        <?php foreach($topCategories as $tc): ?>
          <option value="<?php echo (int)$tc['category_id']; ?>"><?php echo e($tc['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small text-muted">Subcategory</label>
      <select name="sub" id="subCategory" class="form-select" disabled>
        <option value="">Select Category First</option>
      </select>
    </div>
    <div class="col-12 col-md-2 d-grid">
      <button class="btn btn-outline-primary mt-md-0 mt-2">Search</button>
    </div>
  </form>
  </div>

<h5 id="categories" class="mb-3">Categories</h5>
<div class="row g-3 mb-4">
  <?php foreach($categories as $c): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="<?php echo e(base_url('public/category.php?c='.(int)$c['category_id'])); ?>">
        <div class="card p-3 h-100">
          <div class="fw-semibold text-dark"><?php echo e($c['name']); ?></div>
          <div class="text-muted small"><?php echo e($c['description'] ?? ''); ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
  <?php if(!$categories): ?>
    <div class="col-12"><div class="alert alert-info">No categories yet.</div></div>
  <?php endif; ?>
  </div>

<h5 class="mb-3">Latest Products</h5>
<div class="row g-3">
  <?php foreach($latest as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card product-card h-100">
        <img src="<?php echo e($p['image'] ? base_url('assets/images/'.$p['image']) : 'https://via.placeholder.com/400x300?text=Product'); ?>" class="w-100" alt="">
        <div class="p-3">
          <div class="fw-semibold"><?php echo e($p['name']); ?></div>
          <div class="text-muted small mb-2"><?php echo e($p['category_name'] ?? ''); ?></div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-bold">$<?php echo number_format((float)$p['price'],2); ?></div>
            <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="btn btn-sm btn-outline-primary">View</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(!$latest): ?>
    <div class="col-12"><div class="alert alert-info">No products yet. Check back soon!</div></div>
  <?php endif; ?>
</div>

<script>
document.getElementById('topCategory').addEventListener('change', function(){
  const topId = this.value;
  const subSelect = document.getElementById('subCategory');
  subSelect.innerHTML = '<option value="">Loading...</option>';
  subSelect.disabled = true;
  if(!topId) { subSelect.innerHTML = '<option value="">Select Category First</option>'; return; }
  fetch('<?php echo e(base_url('public/subcategories.php?parent=')); ?>'+encodeURIComponent(topId))
    .then(r => r.json())
    .then(data => {
      subSelect.innerHTML = '<option value="">All Subcategories</option>';
      data.forEach(function(c){
        const opt = document.createElement('option');
        opt.value = c.category_id; opt.textContent = c.name; subSelect.appendChild(opt);
      });
      subSelect.disabled = false;
    })
    .catch(()=> { subSelect.innerHTML = '<option value="">Error loading</option>'; });
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
