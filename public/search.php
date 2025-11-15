<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../templates/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) && strtolower((string)$_GET['type']) === 'stores' ? 'stores' : 'products';
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$sub = isset($_GET['sub']) ? (int)$_GET['sub'] : 0;

$results = [];
if ($type === 'stores') {
  $results = search_stores($q ?: null, 60);
} else {
  $results = search_products($q ?: null, $cat ?: null, $sub ?: null, 60);
}
?>
<h4 class="mb-3">Search Results (<?php echo $type === 'stores' ? 'Stores' : 'Products'; ?>)</h4>
<div class="mb-3 small text-muted">Showing <?php echo count($results); ?> result(s)
<?php if($q): ?> for <strong><?php echo e($q); ?></strong><?php endif; ?>
<?php if($type === 'products'): ?>
  <?php if($sub): ?> in subcategory <?php echo e($sub); ?><?php elseif($cat): ?> in category <?php echo e($cat); ?><?php endif; ?>
<?php endif; ?>
</div>

<form action="<?php echo e(base_url('public/search.php')); ?>" method="get" class="row g-2 mb-4 align-items-end">
  <div class="col-12 col-md-4">
    <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control" placeholder="Search again...">
  </div>
  <div class="col-6 col-md-3">
    <select name="type" id="searchFilterType" class="form-select">
      <option value="products" <?php echo $type==='products'?'selected':''; ?>>Products</option>
      <option value="stores" <?php echo $type==='stores'?'selected':''; ?>>Stores</option>
    </select>
  </div>
  <?php if($type === 'products'): ?>
  <div class="col-6 col-md-3">
    <select name="cat" id="topCategory" class="form-select">
      <option value="">All Categories</option>
      <?php foreach(get_top_categories() as $tc): ?>
        <option value="<?php echo (int)$tc['category_id']; ?>" <?php echo $cat==$tc['category_id']?'selected':''; ?>><?php echo e($tc['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-3">
    <select name="sub" id="subCategory" class="form-select" <?php echo $cat? '':'disabled'; ?>>
      <option value="">All Subcategories</option>
      <?php if($cat): foreach(get_subcategories($cat) as $sc): ?>
        <option value="<?php echo (int)$sc['category_id']; ?>" <?php echo $sub==$sc['category_id']?'selected':''; ?>><?php echo e($sc['name']); ?></option>
      <?php endforeach; endif; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-primary">Filter</button>
  </div>
</form>

<div class="row g-3">
<?php if($type === 'stores'): ?>
  <?php foreach($results as $store): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="p-3 d-flex align-items-center gap-3">
          <div class="store-avatar rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
            <?php if(!empty($store['logo'])): ?>
              <img src="<?php echo e(product_image_src($store['logo'])); ?>" alt="<?php echo e($store['store_name'] ?? 'Store'); ?> logo" class="img-fluid rounded-circle" style="width:56px;height:56px;object-fit:cover;">
            <?php else: ?>
              <span class="fw-bold"><?php echo strtoupper(substr($store['store_name'] ?? $store['seller_name'] ?? 'S', 0, 1)); ?></span>
            <?php endif; ?>
          </div>
          <div>
            <div class="fw-semibold mb-0"><?php echo e($store['store_name'] ?? $store['seller_name'].' Shop'); ?></div>
            <div class="text-muted small">By <?php echo e($store['seller_name'] ?? 'seller'); ?></div>
          </div>
        </div>
        <div class="px-3 pb-3">
          <?php
            $desc = trim($store['description'] ?? '');
            $plain = strip_tags($desc);
            if ($plain !== '') {
              if (function_exists('mb_strimwidth')) {
                $plain = mb_strimwidth($plain, 0, 120, '…');
              } elseif (strlen($plain) > 120) {
                $plain = substr($plain, 0, 117) . '...';
              }
            }
          ?>
          <p class="text-muted small mb-3"><?php echo $plain !== '' ? e($plain) : 'No description yet.'; ?></p>
          <div class="d-flex justify-content-between align-items-center">
            <span class="badge bg-light text-dark">Followers: <?php echo (int)($store['follower_total'] ?? 0); ?></span>
            <a href="<?php echo e(base_url('seller/store_public.php?seller_id='.(int)$store['seller_id'])); ?>" class="btn btn-sm btn-outline-primary">Visit Store</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(!$results): ?>
    <div class="col-12"><div class="alert alert-info">No matching stores found.</div></div>
  <?php endif; ?>
<?php else: ?>
  <?php foreach($results as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card product-card h-100">
        <img src="<?php echo e(product_image_src($p['image'] ?? null)); ?>" class="w-100" alt="">
        <div class="p-3">
          <div class="fw-semibold"><?php echo e($p['name']); ?></div>
          <div class="text-muted small mb-2"><?php echo e($p['category_name'] ?? ''); ?></div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-bold">$<?php echo number_format((float)$p['price'],2); ?></div>
            <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="btn btn-sm btn-outline-primary">View</a>
          </div>
        </div>
        <a href="<?php echo e(base_url('public/product.php?id='.(int)$p['product_id'])); ?>" class="stretched-link" aria-label="Open product"></a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if(!$results): ?>
    <div class="col-12"><div class="alert alert-info">No matching products found.</div></div>
  <?php endif; ?>
<?php endif; ?>
</div>

<script>
// Dynamic loading of subcategories (same logic as homepage)
const topSelect = document.getElementById('topCategory');
const subSelect = document.getElementById('subCategory');
const typeSelect = document.getElementById('searchFilterType');

function loadSubs(topId){
  if(!subSelect){ return; }
  subSelect.innerHTML = '<option value="">Loading...</option>'; subSelect.disabled = true;
  if(!topId){ subSelect.innerHTML = '<option value="">Select category first</option>'; return; }
  fetch('<?php echo e(base_url('public/subcategories.php?parent=')); ?>'+encodeURIComponent(topId))
    .then(r=>r.json())
    .then(data=>{
      subSelect.innerHTML = '<option value="">All Subcategories</option>';
      data.forEach(c=>{ const opt=document.createElement('option'); opt.value=c.category_id; opt.textContent=c.name; subSelect.appendChild(opt); });
      subSelect.disabled = false;
    })
    .catch(()=>{ subSelect.innerHTML='<option>Error</option>'; });
}
function handleTypeToggle(){
  if(!typeSelect || !topSelect || !subSelect){ return; }
  const isStore = typeSelect.value === 'stores';
  if(isStore){
    topSelect.value = '';
    topSelect.disabled = true;
    subSelect.innerHTML = '<option value="">Stores do not use categories</option>';
    subSelect.disabled = true;
  } else {
    topSelect.disabled = false;
    if(!topSelect.value){
      subSelect.innerHTML = '<option value="">Select category first</option>';
      subSelect.disabled = true;
    }
  }
}
if(topSelect){ topSelect.addEventListener('change', e=> { if(typeSelect && typeSelect.value==='stores'){ return; } loadSubs(e.target.value); }); }
if(typeSelect){
  typeSelect.addEventListener('change', handleTypeToggle);
  handleTypeToggle();
}
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
