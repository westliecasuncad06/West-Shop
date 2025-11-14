<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');

$u = current_user();

// Fetch or create seller profile
$sp = $pdo->prepare('SELECT * FROM seller_profiles WHERE seller_id=? LIMIT 1');
$sp->execute([$u['user_id']]);
$profile = $sp->fetch();
if (!$profile) {
    $ins = $pdo->prepare('INSERT INTO seller_profiles(seller_id, shop_name) VALUES (?, ?)');
    $ins->execute([$u['user_id'], ($u['name'] . ' Shop')]);
    $sp->execute([$u['user_id']]);
    $profile = $sp->fetch();
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $shop_name = trim($_POST['shop_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($shop_name !== '') {
            $up = $pdo->prepare('UPDATE seller_profiles SET shop_name=?, description=? WHERE seller_id=?');
            $up->execute([$shop_name, $description, $u['user_id']]);
            set_flash('success', 'Store profile updated');
        }
        redirect('seller/index.php');
    }

    if ($action === 'upload_logo' || $action === 'upload_banner') {
        $field = ($action === 'upload_logo') ? 'logo' : 'banner';
        if (!empty($_FILES['image']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadRel = 'assets/images/shops';
            $uploadDir = __DIR__ . '/../' . $uploadRel;
            if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
            $f = $_FILES['image'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($f['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $name = 's' . (int)$u['user_id'] . '_' . $field . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $name;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $path = $uploadRel . '/' . $name;
                    $up = $pdo->prepare("UPDATE seller_profiles SET {$field}=? WHERE seller_id=?");
                    $up->execute([$path, $u['user_id']]);
                    set_flash('success', ucfirst($field) . ' updated');
                } else {
                    set_flash('danger', 'Failed to save uploaded file');
                }
            } else {
                set_flash('warning', 'Unsupported image type');
            }
        } else {
            set_flash('warning', 'Please select an image to upload');
        }
        redirect('seller/index.php');
    }

    if ($action === 'update_product') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $status = in_array(($_POST['status'] ?? 'active'), ['active','inactive']) ? $_POST['status'] : 'active';
        if ($pid && $name !== '') {
            $up = $pdo->prepare('UPDATE products SET name=?, price=?, stock=?, status=? WHERE product_id=? AND seller_id=?');
            $up->execute([$name, $price, $stock, $status, $pid, $u['user_id']]);
            set_flash('success', 'Product updated');
        }
        redirect('seller/index.php');
    }

    if ($action === 'add_coupon') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = ($_POST['type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed';
        $value = (float)($_POST['value'] ?? 0);
        $expires_at = trim($_POST['expires_at'] ?? '');
        $max_uses = (int)($_POST['max_uses'] ?? 0);
        if ($code !== '' && $value > 0) {
            try {
                $ins = $pdo->prepare('INSERT INTO coupons(code,type,value,expires_at,max_uses,created_by) VALUES (?,?,?,?,?,?)');
                $ins->execute([$code, $type, $value, ($expires_at ?: null), $max_uses, $u['user_id']]);
                set_flash('success', 'Coupon created');
            } catch (Exception $e) {
                set_flash('danger', 'Coupon code already exists');
            }
        } else {
            set_flash('warning', 'Provide a valid code and value');
        }
        redirect('seller/index.php');
    }
}

// Quick stats
$pid = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ?'); $pid->execute([$u['user_id']]); $prodCount = (int)$pid->fetchColumn();
$oid = $pdo->prepare('SELECT COUNT(*) FROM order_items oi JOIN products p ON oi.product_id=p.product_id WHERE p.seller_id=?'); $oid->execute([$u['user_id']]); $soldCount = (int)$oid->fetchColumn();

// Search / filter inputs (GET)
$q = trim($_GET['q'] ?? '');
$top_cat = (int)($_GET['top_cat'] ?? 0);
$sub_cat = (int)($_GET['sub_cat'] ?? 0);

// Load categories (top-level and all for JS mapping)
$topCatsStmt = $pdo->prepare('SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name');
$topCatsStmt->execute();
$topCategories = $topCatsStmt->fetchAll();
$allCats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// Load products with optional search/category filters (for seller dashboard)
$params = [$u['user_id']];
$sql = 'SELECT * FROM products WHERE seller_id = ?';
if ($q !== '') {
  $sql .= ' AND (name LIKE ? OR description LIKE ?)';
  $params[] = "%{$q}%";
  $params[] = "%{$q}%";
}
if ($sub_cat) {
  $sql .= ' AND category_id = ?';
  $params[] = $sub_cat;
} elseif ($top_cat) {
  $sql .= ' AND (category_id = ? OR category_id IN (SELECT category_id FROM categories WHERE parent_id = ?))';
  $params[] = $top_cat;
  $params[] = $top_cat;
}
$sql .= ' ORDER BY created_at DESC';
$pp = $pdo->prepare($sql);
$pp->execute($params);
$products = $pp->fetchAll();

// Detect buyer preview
$isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';

// For preview view show latest 6 active products (ignore dashboard filters)
if ($isPreview) {
  $pv = $pdo->prepare("SELECT * FROM products WHERE seller_id=? AND status='active' ORDER BY created_at DESC LIMIT 6");
  $pv->execute([$u['user_id']]);
  $previewProducts = $pv->fetchAll();
}

include __DIR__ . '/../templates/header.php';
?>

<?php if ($isPreview): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Store Preview</h4>
    <a class="btn btn-outline-secondary" href="<?php echo e(base_url('seller/index.php')); ?>">Exit Preview</a>
  </div>
  <div class="card mb-3 p-0 overflow-hidden">
    <img src="<?php echo e($profile['banner'] ? base_url($profile['banner']) : base_url('assets/images/products/placeholder.png')); ?>" class="w-100" style="height:240px;object-fit:cover">
    <div class="p-3 bg-white">
      <div class="d-flex align-items-center">
        <img src="<?php echo e($profile['logo'] ? base_url($profile['logo']) : base_url('assets/images/products/placeholder.png')); ?>" width="84" height="84" class="rounded-circle me-3" style="object-fit:cover">
        <div>
          <h4 class="mb-0"><?php echo e($profile['shop_name']); ?></h4>
          <div class="small text-muted">by <?php echo e($u['name']); ?></div>
        </div>
      </div>
      <?php if(!empty($profile['description'])): ?><p class="mt-3 mb-0 text-muted"><?php echo e($profile['description']); ?></p><?php endif; ?>
    </div>
  </div>
  <div class="row g-3">
    <?php foreach(($previewProducts ?? []) as $p): ?>
      <div class="col-md-4">
        <div class="card h-100">
          <img src="<?php echo e($p['image'] && (strpos($p['image'],'http') === 0) ? $p['image'] : ($p['image'] ? base_url($p['image']) : base_url('assets/images/products/placeholder.png'))); ?>" class="w-100" style="height:180px;object-fit:cover">
          <div class="card-body">
            <h6 class="mb-1"><?php echo e($p['name']); ?></h6>
            <div class="text-muted small">$<?php echo number_format((float)$p['price'],2); ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if(!$products): ?><div class="col-12 text-center text-muted">No products yet.</div><?php endif; ?>
  </div>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Seller Dashboard</h4>
    <a class="btn btn-outline-secondary" href="<?php echo e(base_url('seller/index.php?preview=1')); ?>">Buyer View</a>
  </div>

  <div class="row g-3">
    <div class="col-md-3"><div class="card p-4"><div class="text-muted">Products</div><div class="fs-3 fw-semibold"><?php echo $prodCount; ?></div></div></div>
    <div class="col-md-3"><div class="card p-4"><div class="text-muted">Items Sold</div><div class="fs-3 fw-semibold"><?php echo $soldCount; ?></div></div></div>
    <div class="col-md-3"><a class="text-decoration-none" href="<?php echo e(base_url('seller/products.php')); ?>"><div class="card p-4"><div class="fs-1">➕</div><div class="fw-semibold">Manage Products</div></div></a></div>
    <div class="col-md-3"><a class="text-decoration-none" href="<?php echo e(base_url('seller/chat_admin.php')); ?>"><div class="card p-4"><div class="fs-1">💬</div><div class="fw-semibold">Support Chat</div></div></a></div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-lg-4">
      <div class="card p-3">
        <h6 class="mb-3">Store Profile</h6>
        <div class="mb-3">
          <div class="ratio ratio-16x9 mb-2" style="background:#f6f6f6;border:1px dashed #ddd;border-radius:6px;overflow:hidden;">
            <?php if($profile['banner']): ?>
              <img src="<?php echo e(base_url($profile['banner'])); ?>" style="object-fit:cover;">
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center text-muted">No banner yet</div>
            <?php endif; ?>
          </div>
          <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="upload_banner">
            <input type="file" name="image" class="form-control" accept="image/*" required>
            <button class="btn btn-outline-primary">Upload</button>
          </form>
        </div>
        <div class="mb-3 d-flex align-items-center">
          <div style="width:76px;height:76px;border-radius:50%;overflow:hidden;background:#f0f0f0;border:1px dashed #ddd;" class="me-2">
            <?php if($profile['logo']): ?>
              <img src="<?php echo e(base_url($profile['logo'])); ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php endif; ?>
          </div>
          <form method="post" enctype="multipart/form-data" class="flex-grow-1 d-flex gap-2">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="upload_logo">
            <input type="file" name="image" class="form-control" accept="image/*" required>
            <button class="btn btn-outline-primary">Upload</button>
          </form>
        </div>
        <form method="post">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="update_profile">
          <div class="mb-2"><input class="form-control" name="shop_name" placeholder="Store name" value="<?php echo e($profile['shop_name']); ?>" required></div>
          <div class="mb-2"><textarea class="form-control" name="description" placeholder="Short description" rows="3"><?php echo e($profile['description'] ?? ''); ?></textarea></div>
          <button class="btn btn-primary w-100">Save</button>
        </form>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">My Products</h6>
          <a href="<?php echo e(base_url('seller/products.php')); ?>" class="small">Open full manager</a>
        </div>

        <form method="get" class="row g-2 mb-2">
          <div class="col-md-6">
            <input type="search" name="q" class="form-control" placeholder="Search products" value="<?php echo e($q); ?>">
          </div>
          <div class="col-md-3">
            <select id="top_cat" name="top_cat" class="form-select">
              <option value="">All categories</option>
              <?php foreach($topCategories as $tc): ?>
                <option value="<?php echo (int)$tc['category_id']; ?>" <?php echo ($top_cat==(int)$tc['category_id']?'selected':''); ?>><?php echo e($tc['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select id="sub_cat" name="sub_cat" class="form-select">
              <option value="">All subcategories</option>
              <?php if($top_cat):
                $subStmt = $pdo->prepare('SELECT * FROM categories WHERE parent_id = ? ORDER BY name');
                $subStmt->execute([$top_cat]);
                $subList = $subStmt->fetchAll();
                foreach($subList as $sc): ?>
                  <option value="<?php echo (int)$sc['category_id']; ?>" <?php echo ($sub_cat==(int)$sc['category_id']?'selected':''); ?>><?php echo e($sc['name']); ?></option>
                <?php endforeach; endif; ?>
            </select>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Item</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
            <tbody>
              <?php foreach($products as $p): ?>
                <tr>
                  <td class="d-flex align-items-center" style="gap:.5rem;">
                    <img src="<?php echo e($p['image'] && (strpos($p['image'],'http') === 0) ? $p['image'] : ($p['image'] ? base_url($p['image']) : base_url('assets/images/products/placeholder.png'))); ?>" width="44" height="44" style="object-fit:cover;border-radius:6px;">
                    <span><?php echo e($p['name']); ?></span>
                  </td>
                  <td>$<?php echo number_format((float)$p['price'],2); ?></td>
                  <td><?php echo (int)$p['stock']; ?></td>
                  <td><span class="badge bg-<?php echo ($p['status']==='active'?'success':'secondary'); ?>"><?php echo e($p['status']); ?></span></td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit_<?php echo (int)$p['product_id']; ?>">Edit</button>
                  </td>
                </tr>
                <tr class="collapse" id="edit_<?php echo (int)$p['product_id']; ?>">
                  <td colspan="5">
                    <form method="post" class="row g-2">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="update_product">
                      <input type="hidden" name="product_id" value="<?php echo (int)$p['product_id']; ?>">
                      <div class="col-md-5"><input class="form-control" name="name" value="<?php echo e($p['name']); ?>" required></div>
                      <div class="col-md-2"><input type="number" step="0.01" class="form-control" name="price" value="<?php echo e($p['price']); ?>" required></div>
                      <div class="col-md-2"><input type="number" class="form-control" name="stock" value="<?php echo (int)$p['stock']; ?>" required></div>
                      <div class="col-md-2">
                        <select class="form-select" name="status">
                          <option value="active" <?php echo ($p['status']==='active'?'selected':''); ?>>Active</option>
                          <option value="inactive" <?php echo ($p['status']==='inactive'?'selected':''); ?>>Inactive</option>
                        </select>
                      </div>
                      <div class="col-md-1 d-grid"><button class="btn btn-primary">Save</button></div>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if(!$products): ?><tr><td colspan="5" class="text-center text-muted">No products yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="card p-3">
        <h6 class="mb-2">Add Coupon</h6>
        <form method="post" class="small">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="add_coupon">
          <div class="mb-2"><input class="form-control" name="code" placeholder="CODE2025" required></div>
          <div class="mb-2">
            <div class="input-group">
              <select name="type" class="form-select" style="max-width:45%">
                <option value="fixed">Fixed</option>
                <option value="percent">Percent</option>
              </select>
              <input type="number" step="0.01" name="value" class="form-control" placeholder="Value" required>
            </div>
          </div>
          <div class="mb-2"><input type="datetime-local" name="expires_at" class="form-control"></div>
          <div class="mb-3"><input type="number" name="max_uses" class="form-control" placeholder="Max uses (0 = unlimited)" value="0"></div>
          <button class="btn btn-primary w-100">Create</button>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../templates/footer.php'; ?>
<script>
  (function(){
    var allCats = <?php echo json_encode($allCats ?: []); ?>;
    var map = {};
    allCats.forEach(function(c){
      var pid = c.parent_id === null ? 0 : Number(c.parent_id);
      if (!map[pid]) map[pid] = [];
      map[pid].push(c);
    });
    var top = document.getElementById('top_cat');
    var sub = document.getElementById('sub_cat');
    if (!top || !sub) return;
    top.addEventListener('change', function(){
      var val = parseInt(this.value) || 0;
      // clear subs
      sub.innerHTML = '<option value="">All subcategories</option>';
      if (map[val]){
        map[val].forEach(function(c){
          var o = document.createElement('option');
          o.value = c.category_id;
          o.textContent = c.name;
          sub.appendChild(o);
        });
      }
    });
    // auto-submit when selecting filters
    var form = top && top.closest('form');
    if (form){
      top.addEventListener('change', function(){ form.submit(); });
      sub.addEventListener('change', function(){ form.submit(); });
    }
  })();
</script>
