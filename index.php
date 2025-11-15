<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/templates/header.php';

$allProducts = search_products(null, null, null, 100); // get all active products
$topCategories = get_top_categories();
$categoryCount = count($topCategories);
?>

<section class="hero-section p-4 p-md-5 mb-5 position-relative">
  <div class="row g-4 align-items-center position-relative" style="z-index:1;">
    <div class="col-lg-7">
      <div class="badge-chip mb-3"><i class="bi bi-stars text-warning"></i> Fresh drops every week</div>
      <h1 class="display-4 fw-bold mb-3">Shop with joy at <?php echo e(APP_NAME); ?> 😊</h1>
      <p class="lead text-muted-soft mb-4">Discover trusted sellers, curated collections, and joyful checkout moments in one modern marketplace.</p>
      <div class="d-flex flex-wrap gap-3">
        <a href="#products" class="pill-button btn-primary text-decoration-none">
          <i class="bi bi-shop"></i> Browse Products
        </a>
        <a href="<?php echo e(base_url('register.php')); ?>" class="pill-button pill-button--ghost text-decoration-none">
          <i class="bi bi-person-plus"></i> Join the community
        </a>
      </div>
      <div class="row g-3 mt-4">
        <div class="col-6 col-md-4">
          <div class="stats-card">
            <div class="stats-card__icon"><i class="bi bi-box-seam"></i></div>
            <div>
              <p class="text-muted-soft mb-0 small">Products</p>
              <h4 class="mb-0"><?php echo number_format(count($allProducts)); ?></h4>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="stats-card">
            <div class="stats-card__icon"><i class="bi bi-grid"></i></div>
            <div>
              <p class="text-muted-soft mb-0 small">Categories</p>
              <h4 class="mb-0"><?php echo number_format($categoryCount); ?></h4>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="stats-card">
            <div class="stats-card__icon"><i class="bi bi-shield-check"></i></div>
            <div>
              <p class="text-muted-soft mb-0 small">Buyer Protection</p>
              <h4 class="mb-0">24/7</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="hero-icon"><i class="bi bi-bag-heart fs-3"></i></div>
            <div>
              <p class="text-muted-soft mb-1">Why shop with us?</p>
              <h5 class="mb-0">Safe, fast, delightful</h5>
            </div>
          </div>
          <ul class="list-unstyled mb-4 text-muted-soft">
            <li class="mb-2"><i class="bi bi-shield-lock text-success me-2"></i>Secure checkout & buyer protection</li>
            <li class="mb-2"><i class="bi bi-truck text-primary me-2"></i>Nationwide fast delivery options</li>
            <li><i class="bi bi-stars text-warning me-2"></i>Quality-vetted sellers and items</li>
          </ul>
          <div class="bg-muted rounded-4 p-3">
            <p class="text-muted-soft mb-1 small">Need help deciding?</p>
            <h6 class="mb-0">Try the smart search below</h6>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
      <div class="text-center mb-3">
        <p class="section-label mb-1">Search smarter</p>
        <h5 class="mb-0">Find your perfect product</h5>
      </div>
      <form action="<?php echo e(base_url('public/search.php')); ?>" method="get" class="row g-3 align-items-end" id="searchForm">
        <div class="col-12 col-lg-4">
          <label class="form-label">Search products</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-2" style="border-color:#e9ecef;border-radius:0.75rem 0 0 0.75rem;">
              <i class="bi bi-search text-primary"></i>
            </span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="e.g. earbuds, hoodie" style="border-left:none!important;" />
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">I want to find</label>
          <select name="type" id="homeSearchType" class="form-select">
            <option value="products">Products</option>
            <option value="stores">Stores</option>
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Category</label>
          <select name="cat" id="homeTopCategory" class="form-select">
            <option value="">All Categories</option>
            <?php foreach($topCategories as $tc): ?>
              <option value="<?php echo (int)$tc['category_id']; ?>"><?php echo e($tc['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Subcategory</label>
          <select name="sub" id="homeSubCategory" class="form-select" disabled>
            <option value="">Select Category First</option>
          </select>
        </div>
        <div class="col-12 col-lg-2 d-grid">
          <button class="btn btn-outline-primary h-100">
            <i class="bi bi-funnel me-2"></i>Search
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php if($topCategories): ?>
<div class="card border-0 shadow-sm mb-5">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <p class="section-label mb-1">Popular picks</p>
        <h5 class="mb-0">Explore categories</h5>
      </div>
      <a href="<?php echo e(base_url('public/category.php')); ?>" class="pill-button pill-button--ghost text-decoration-none">
        <i class="bi bi-compass"></i> View all
      </a>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <?php foreach($topCategories as $tc): ?>
        <a href="<?php echo e(base_url('public/category.php?id='.(int)$tc['category_id'])); ?>" class="badge-chip text-decoration-none">
          <i class="bi bi-tag"></i> <?php echo e($tc['name']); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4" id="products">
  <div>
    <p class="section-label mb-1">Curated for you</p>
    <h3 class="mb-0">All Products</h3>
  </div>
  <div class="text-muted-soft small">
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
            <span class="position-absolute top-0 end-0 m-2 badge badge-rose">
              <i class="bi bi-lightning-fill"></i> Low stock
            </span>
          <?php endif; ?>
        </div>
        <div class="p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge-chip"><i class="bi bi-tag"></i><?php echo e($p['category_name'] ?? 'Uncategorized'); ?></span>
            <span class="text-muted-soft small">#<?php echo (int)$p['product_id']; ?></span>
          </div>
          <h6 class="fw-semibold mb-3" style="min-height:2.5rem;line-height:1.25rem;"><?php echo e($p['name']); ?></h6>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="text-muted-soft mb-1 small">Price</p>
              <div class="text-primary fw-bold fs-5">$<?php echo number_format((float)$p['price'],2); ?></div>
            </div>
            <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="pill-button pill-button--ghost text-decoration-none">
              <i class="bi bi-eye"></i> View
            </a>
          </div>
        </div>
        <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="stretched-link" aria-label="Open product"></a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(!$allProducts): ?>
        <div class="col-12">
          <div class="empty-state-card text-center">
            <i class="bi bi-emoji-smile fs-1 text-primary d-block mb-3"></i>
            <h5 class="mb-1">No products yet</h5>
            <p class="text-muted-soft mb-0">Follow your favorite shops for fresh drops soon.</p>
          </div>
        </div>
  <?php endif; ?>
</div>

<script>
const homeTopCategory = document.getElementById('homeTopCategory');
const homeSubCategory = document.getElementById('homeSubCategory');
const homeSearchType = document.getElementById('homeSearchType');

function loadHomeSubCategories(topId) {
  if (!homeSubCategory) { return; }
  homeSubCategory.innerHTML = '<option value="">Loading...</option>';
  homeSubCategory.disabled = true;
  if (!topId) {
    homeSubCategory.innerHTML = '<option value="">Select Category First</option>';
    return;
  }
  fetch('<?php echo e(base_url('public/subcategories.php?parent=')); ?>' + encodeURIComponent(topId))
    .then(r => r.json())
    .then(data => {
      homeSubCategory.innerHTML = '<option value="">All Subcategories</option>';
      data.forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc.category_id;
        opt.textContent = sc.name;
        homeSubCategory.appendChild(opt);
      });
      homeSubCategory.disabled = false;
    })
    .catch(() => {
      homeSubCategory.innerHTML = '<option value="">Error loading</option>';
    });
}

function handleHomeTypeChange() {
  if (!homeSearchType || !homeTopCategory || !homeSubCategory) { return; }
  const isStoreSearch = homeSearchType.value === 'stores';
  if (isStoreSearch) {
    homeTopCategory.value = '';
    homeTopCategory.disabled = true;
    homeSubCategory.innerHTML = '<option value="">Stores do not use categories</option>';
    homeSubCategory.disabled = true;
  } else {
    homeTopCategory.disabled = false;
    homeSubCategory.innerHTML = '<option value="">Select Category First</option>';
    homeSubCategory.disabled = true;
  }
}

if (homeTopCategory) {
  homeTopCategory.addEventListener('change', function () {
    if (homeSearchType && homeSearchType.value === 'stores') { return; }
    loadHomeSubCategories(this.value);
  });
}
if (homeSearchType) {
  homeSearchType.addEventListener('change', handleHomeTypeChange);
  handleHomeTypeChange();
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
