<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../templates/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$sub = isset($_GET['sub']) ? (int)$_GET['sub'] : 0;

$results = search_products($q ?: null, $cat ?: null, $sub ?: null, 60);
?>
<h4 class="mb-3">Search Results</h4>
<div class="mb-3 small text-muted">Showing <?php echo count($results); ?> result(s)
<?php if($q): ?> for <strong><?php echo e($q); ?></strong><?php endif; ?>
<?php if($sub): ?> in subcategory <?php echo e($sub); ?><?php elseif($cat): ?> in category <?php echo e($cat); ?><?php endif; ?>
</div>

<form action="<?php echo e(base_url('public/search.php')); ?>" method="get" class="row g-2 mb-4">
  <div class="col-12 col-md-4">
    <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control" placeholder="Search again...">
  </div>
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
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-primary">Filter</button>
  </div>
</form>

<div class="row g-3">
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
</div>

<script>
// Dynamic loading of subcategories (same logic as homepage)
const topSelect = document.getElementById('topCategory');
const subSelect = document.getElementById('subCategory');
function loadSubs(topId){
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
if(topSelect){ topSelect.addEventListener('change', e=> loadSubs(e.target.value)); }
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
