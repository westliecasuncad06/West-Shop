<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/templates/header.php';

$allProducts = search_products(null, null, null, 100); // get all active products
$topCategories = get_top_categories();
?>

<div class="p-4 p-md-5 mb-5 bg-white rounded-4 shadow-sm hero-section position-relative">
  <div class="row align-items-center position-relative" style="z-index: 1;">
    <div class="col-md-7 mb-4 mb-md-0">
      <div class="mb-3">
        <span class="badge badge-accent">✨ New Arrivals Daily</span>
      </div>
      <h1 class="display-4 fw-bold mb-3">Shop with joy at <?php echo e(APP_NAME); ?> 😊</h1>
      <p class="lead text-muted mb-4">Discover trusted sellers, great deals, and a delightful shopping experience with our curated marketplace.</p>
      <div class="d-flex gap-3 flex-wrap">
        <a href="#products" class="btn btn-primary rounded-pill px-4 py-2">
          <i class="bi bi-shop me-2"></i>Browse Products
        </a>
        <a href="<?php echo e(base_url('register.php')); ?>" class="btn btn-outline-primary rounded-pill px-4 py-2">
          <i class="bi bi-person-plus me-2"></i>Join Now
        </a>
      </div>
    </div>
    <div class="col-md-5 text-center">
      <div class="p-4">
        <div class="mb-3">
          <i class="bi bi-bag-heart display-1 text-primary" style="opacity: 0.8;"></i>
        </div>
        <h5 class="mb-2">Why Shop With Us?</h5>
        <div class="text-muted small">
          <div class="mb-2"><i class="bi bi-shield-check text-success me-2"></i>Secure Checkout</div>
          <div class="mb-2"><i class="bi bi-truck text-primary me-2"></i>Fast Delivery</div>
          <div><i class="bi bi-star-fill text-warning me-2"></i>Quality Products</div>
        </div>
      </div>
    </div>
  </div>
  
  <hr class="my-4">
  
  <div class="position-relative" style="z-index: 1;">
    <h5 class="mb-3 text-center">
      <i class="bi bi-search me-2"></i>Find Your Perfect Product
    </h5>
    <form action="<?php echo e(base_url('public/search.php')); ?>" method="get" class="row g-3 align-items-end" id="searchForm">
      <div class="col-12 col-md-4">
        <label class="form-label">Search Products</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-2" style="border-color: #e9ecef; border-radius: 0.75rem 0 0 0.75rem;">
            <i class="bi bi-search text-primary"></i>
          </span>
          <input type="text" name="q" class="form-control border-start-0" placeholder="e.g. earbuds, hoodie" style="border-left: none !important;" />
        </div>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Category</label>
        <select name="cat" id="topCategory" class="form-select">
          <option value="">All Categories</option>
          <?php foreach($topCategories as $tc): ?>
            <option value="<?php echo (int)$tc['category_id']; ?>"><?php echo e($tc['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Subcategory</label>
        <select name="sub" id="subCategory" class="form-select" disabled>
          <option value="">Select Category First</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-outline-primary" style="height: 100%;">
          <i class="bi bi-funnel me-2"></i>Search
        </button>
      </div>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4" id="products">
  <div>
    <h3 class="mb-1">
      <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>All Products
    </h3>
    <p class="text-muted small mb-0">Explore our amazing collection</p>
  </div>
  <div class="text-muted small">
    <i class="bi bi-box-seam me-1"></i><?php echo count($allProducts); ?> products
  </div>
</div>

<div class="row g-4">
  <?php foreach($allProducts as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card product-card h-100">
        <div class="position-relative">
          <img src="<?php echo e(product_image_src($p['image'] ?? null)); ?>" class="w-100" alt="<?php echo e($p['name']); ?>">
          <?php if(isset($p['stock']) && (int)$p['stock'] < 10 && (int)$p['stock'] > 0): ?>
            <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark">
              <i class="bi bi-lightning-fill"></i> Low Stock
            </span>
          <?php endif; ?>
        </div>
        <div class="p-3">
          <div class="mb-2">
            <span class="badge bg-light text-primary small"><?php echo e($p['category_name'] ?? 'Uncategorized'); ?></span>
          </div>
          <h6 class="fw-semibold mb-2" style="min-height: 2.5rem; line-height: 1.25rem;"><?php echo e($p['name']); ?></h6>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-primary fw-bold fs-5">$<?php echo number_format((float)$p['price'],2); ?></div>
            </div>
            <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="btn btn-sm btn-primary rounded-pill">
              <i class="bi bi-eye me-1"></i>View
            </a>
          </div>
        </div>
        <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="stretched-link" aria-label="Open product"></a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(!$allProducts): ?>
    <div class="col-12">
      <div class="alert alert-info d-flex align-items-center">
        <i class="bi bi-info-circle me-3 fs-3"></i>
        <div>
          <h5 class="alert-heading mb-1">No products yet</h5>
          <p class="mb-0">Check back soon for amazing deals!</p>
        </div>
      </div>
    </div>
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
