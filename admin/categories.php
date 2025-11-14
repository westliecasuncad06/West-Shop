<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Create / Update / Delete categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories(name, description) VALUES (?,?)');
            $stmt->execute([$name, $desc]);
            set_flash('success', 'Category created.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM categories WHERE category_id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Category deleted.');
    }
}

$topCategories = get_top_categories();
$filterTop = isset($_GET['top']) ? (int)$_GET['top'] : 0;
$filterSub = isset($_GET['sub']) ? (int)$_GET['sub'] : 0;

// Preload subcategories if top filter set
$subCategories = $filterTop > 0 ? get_subcategories($filterTop) : [];

if ($filterSub > 0) {
  $stmt = $pdo->prepare('SELECT * FROM categories WHERE category_id = ? ORDER BY name');
  $stmt->execute([$filterSub]);
  $cats = $stmt->fetchAll();
} elseif ($filterTop > 0) {
  $stmt = $pdo->prepare('SELECT * FROM categories WHERE category_id = ? OR parent_id = ? ORDER BY name');
  $stmt->execute([$filterTop, $filterTop]);
  $cats = $stmt->fetchAll();
} else {
  $cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

include __DIR__ . '/../templates/header.php';
?>
<section class="section-shell">
  <div class="section-heading">
    <div>
      <p class="section-heading__eyebrow mb-1">Catalog structure</p>
      <h1 class="section-heading__title mb-0">Categories</h1>
      <p class="page-subtitle mt-2">Organize storefront browsing with clear category labels and descriptions.</p>
    </div>
  </div>
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="surface-card h-100">
        <h5 class="mb-3">Add category</h5>
        <form method="post" class="d-flex flex-column gap-3">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="create">
          <div>
            <label class="form-label">Name</label>
            <input class="form-control" name="name" placeholder="e.g. Home Decor" required>
          </div>
          <div>
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Optional short copy"></textarea>
          </div>
          <button class="btn btn-primary">Create category</button>
        </form>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="surface-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="mb-0">All categories</h5>
            <small class="text-muted-soft">Filter by parent and subcategory to narrow the list.</small>
          </div>
          <div class="d-flex align-items-center gap-2">
            <form method="get" id="catFilterForm" class="d-flex gap-2 align-items-center">
              <select name="top" id="topFilter" class="form-select form-select-sm">
                <option value="">All top categories</option>
                <?php foreach($topCategories as $tc): ?>
                  <option value="<?php echo (int)$tc['category_id']; ?>" <?php echo ($filterTop===(int)$tc['category_id'])? 'selected' : ''; ?>><?php echo e($tc['name']); ?></option>
                <?php endforeach; ?>
              </select>
              <select name="sub" id="subFilter" class="form-select form-select-sm" <?php echo ($filterTop>0)? '' : 'disabled'; ?>>
                <option value="">All subcategories</option>
                <?php foreach($subCategories as $sc): ?>
                  <option value="<?php echo (int)$sc['category_id']; ?>" <?php echo ($filterSub===(int)$sc['category_id'])? 'selected' : ''; ?>><?php echo e($sc['name']); ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-outline-primary">Apply</button>
              <a href="<?php echo e(base_url('admin/categories.php')); ?>" class="btn btn-sm btn-link">Clear</a>
            </form>
            <span class="badge-soft"><?php echo count($cats); ?> total</span>
          </div>
        </div>
        <?php if($cats): ?>
          <ul class="stacked-list">
            <?php foreach($cats as $c): ?>
              <li class="stacked-list__item">
                <div>
                  <div class="fw-semibold"><?php echo e($c['name']); ?></div>
                  <?php if(!empty($c['description'])): ?>
                    <div class="text-muted-soft small"><?php echo e($c['description']); ?></div>
                  <?php endif; ?>
                </div>
                <form method="post" class="mb-0">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="category_id" value="<?php echo (int)$c['category_id']; ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state-card text-center">
            <h6 class="mb-1">No categories yet</h6>
            <p class="text-muted-soft mb-0">Create your first category to help buyers find products faster.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
<script>
document.getElementById('topFilter').addEventListener('change', function(){
  const topId = this.value;
  const sub = document.getElementById('subFilter');
  sub.innerHTML = '<option>Loading...</option>';
  sub.disabled = true;
  if(!topId){ sub.innerHTML = '<option value="">All subcategories</option>'; sub.disabled = true; return; }
  fetch('<?php echo e(base_url('public/subcategories.php?parent=')); ?>'+encodeURIComponent(topId))
    .then(r => r.json())
    .then(data => {
      sub.innerHTML = '<option value="">All subcategories</option>';
      data.forEach(function(c){ const o = document.createElement('option'); o.value = c.category_id; o.textContent = c.name; sub.appendChild(o); });
      sub.disabled = false;
    })
    .catch(()=> { sub.innerHTML = '<option value="">Error</option>'; });
});
</script>
