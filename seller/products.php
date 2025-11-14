<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
      // Prefer subcategory if provided, otherwise use top-level category
      $subcat = (int)($_POST['subcategory_id'] ?? 0);
      $cat = $subcat ?: (int)($_POST['category_id'] ?? 0);
        $desc = trim($_POST['description'] ?? '');
      // Image handling: uploaded file > image_url > gdrive_url
      $imagePath = null;
      $uploadDirRel = 'assets/images/products';
      $uploadDir = __DIR__ . '/../' . $uploadDirRel;
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
      }
      if (!empty($_FILES['image']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['image'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        if (isset($allowed[$mime])) {
          $ext = $allowed[$mime];
          $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
          $dest = $uploadDir . DIRECTORY_SEPARATOR . $name;
          if (move_uploaded_file($f['tmp_name'], $dest)) {
            $imagePath = $uploadDirRel . '/' . $name;
          }
        }
      }
      if (!$imagePath) {
        $imgUrl = trim($_POST['image_url'] ?? '');
        if ($imgUrl && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
          $imagePath = $imgUrl;
        }
      }
      if (!$imagePath) {
        $gdrive = trim($_POST['gdrive_url'] ?? '');
        if ($gdrive && filter_var($gdrive, FILTER_VALIDATE_URL)) {
          $imagePath = $gdrive;
        }
      }
        if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO products(seller_id,category_id,name,description,price,stock,image,status) VALUES (?,?,?,?,?,?,?,"active")');
        $stmt->execute([$u['user_id'], $cat ?: null, $name, $desc, $price, $stock, $imagePath]);
            set_flash('success', 'Product added');
        }
    } elseif ($action === 'delete') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM products WHERE product_id=? AND seller_id=?');
        $stmt->execute([$pid, $u['user_id']]);
        set_flash('success', 'Product deleted');
    }
}

$cats = get_categories();
// Build category maps: top-level and subcategories
$cats_map = [];
$top_cats = [];
$sub_cats = [];
foreach ($cats as $c) {
  $cats_map[(int)$c['category_id']] = $c;
  if (empty($c['parent_id'])) {
    $top_cats[] = $c;
  } else {
    $sub_cats[] = $c;
  }
}
$stmt = $pdo->prepare('SELECT * FROM products WHERE seller_id=? ORDER BY created_at DESC');
$stmt->execute([$u['user_id']]);
$products = $stmt->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">My Products</h4>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6>Add Product</h6>
      <form method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <div class="mb-2"><input class="form-control" name="name" placeholder="Name" required></div>
        <div class="mb-2">
          <select name="category_id" id="category_id" class="form-select">
            <option value="">Top-level Category</option>
            <?php foreach($top_cats as $c){ echo '<option value="'.(int)$c['category_id'].'">'.e($c['name']).'</option>'; } ?>
          </select>
        </div>
        <div class="mb-2">
          <select name="subcategory_id" id="subcategory_id" class="form-select">
            <option value="">Subcategory (optional)</option>
            <?php foreach($sub_cats as $s){ echo '<option data-parent="'.(int)$s['parent_id'].'" value="'.(int)$s['category_id'].'">'.e($s['name']).'</option>'; } ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Product Image</label>
          <div class="mb-2">
            <select id="image_method_select" name="image_method" class="form-select">
              <option value="upload">Upload file</option>
              <option value="url">Image URL</option>
              <option value="gdrive">Google Drive URL</option>
            </select>
          </div>

          <div id="img_block_upload" class="img-block">
            <input type="file" name="image" id="image_input" accept="image/*" class="form-control mb-1">
          </div>

          <div id="img_block_url" class="img-block" style="display:none;">
            <input type="url" name="image_url" id="image_url_input" placeholder="Image URL (https://...)" class="form-control mb-1">
          </div>

          <div id="img_block_gdrive" class="img-block" style="display:none;">
            <input type="url" name="gdrive_url" id="gdrive_url_input" placeholder="Google Drive share URL (optional)" class="form-control">
          </div>

          <div class="mt-2">
            <label class="form-label">Preview</label>
            <div>
              <img id="image_preview" src="" alt="Preview" style="max-width:150px;max-height:150px;display:none;border:1px solid #ddd;padding:4px;border-radius:4px;" />
            </div>
          </div>
        </div>
        <div class="mb-2"><input type="number" step="0.01" class="form-control" name="price" placeholder="Price" required></div>
        <div class="mb-2"><input type="number" class="form-control" name="stock" placeholder="Stock" required></div>
        <div class="mb-2"><textarea class="form-control" name="description" placeholder="Description"></textarea></div>
        <button class="btn btn-primary">Add</button>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <h6>All Products</h6>
      <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Name</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach($products as $p): ?>
            <tr>
              <td><?php echo e($p['name']); ?></td>
              <td>$<?php echo number_format((float)$p['price'],2); ?></td>
              <td><?php echo (int)$p['stock']; ?></td>
              <td>
                <?php
                  $catLabel = '';
                  if (!empty($p['category_id']) && isset($cats_map[(int)$p['category_id']])) {
                      $c = $cats_map[(int)$p['category_id']];
                      if (!empty($c['parent_id']) && isset($cats_map[(int)$c['parent_id']])) {
                          echo e($cats_map[(int)$c['parent_id']]['name']) . ' / ' . e($c['name']);
                      } else {
                          echo e($c['name']);
                      }
                  } else {
                      echo '<span class="text-muted">Uncategorized</span>';
                  }
                ?>
              </td>
              <td><?php echo e($p['status']); ?></td>
              <td>
                <form method="post" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?php echo (int)$p['product_id']; ?>">
                  <button class="btn btn-sm btn-outline-danger">Delete</button>
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
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
<script>
// Filter subcategory options to only show those belonging to selected top-level category
document.addEventListener('DOMContentLoaded', function(){
  var top = document.getElementById('category_id');
  var sub = document.getElementById('subcategory_id');
  if (!top || !sub) return;

  function filterSub() {
    var val = top.value || '';
    // Show only options whose data-parent matches val (or empty for placeholder)
    for (var i=0;i<sub.options.length;i++){
      var opt = sub.options[i];
      var parent = opt.getAttribute('data-parent');
      if (!parent) {
        // keep placeholder
        opt.style.display = '';
        continue;
      }
      if (val !== '' && parent === val) {
        opt.style.display = '';
      } else {
        opt.style.display = 'none';
      }
    }
    // If currently selected subcategory is hidden, reset selection
    var sel = sub.options[sub.selectedIndex];
    if (sel && sel.style.display === 'none') {
      sub.value = '';
    }
  }

  top.addEventListener('change', filterSub);
  // Run initially to set correct subset
  filterSub();
});
</script>
<script>
// Image method toggle and preview (dropdown)
document.addEventListener('DOMContentLoaded', function(){
  var methodSelect = document.getElementById('image_method_select');
  var blockUpload = document.getElementById('img_block_upload');
  var blockUrl = document.getElementById('img_block_url');
  var blockGdrive = document.getElementById('img_block_gdrive');
  var inputFile = document.getElementById('image_input');
  var inputUrl = document.getElementById('image_url_input');
  var inputG = document.getElementById('gdrive_url_input');
  var preview = document.getElementById('image_preview');

  function setMethod(m) {
    blockUpload.style.display = (m === 'upload') ? '' : 'none';
    blockUrl.style.display = (m === 'url') ? '' : 'none';
    blockGdrive.style.display = (m === 'gdrive') ? '' : 'none';
    if (m !== 'upload') {
      if (inputFile) inputFile.value = '';
      preview.style.display = 'none'; preview.src = '';
    }
  }

  function onSelectChange() {
    var m = methodSelect ? methodSelect.value : 'upload';
    setMethod(m);
  }

  if (methodSelect) methodSelect.addEventListener('change', onSelectChange);

  // File preview
  if (inputFile) {
    inputFile.addEventListener('change', function(e){
      var f = e.target.files && e.target.files[0];
      if (!f) { preview.style.display='none'; preview.src=''; return; }
      var reader = new FileReader();
      reader.onload = function(ev){ preview.src = ev.target.result; preview.style.display='inline-block'; };
      reader.readAsDataURL(f);
    });
  }

  // URL preview
  if (inputUrl) {
    inputUrl.addEventListener('input', function(e){
      var v = e.target.value.trim();
      if (v && (v.startsWith('http://') || v.startsWith('https://'))) {
        preview.src = v; preview.style.display='inline-block';
      } else {
        preview.style.display='none'; preview.src='';
      }
    });
  }

  // GDrive preview: show URL as-is (user must provide direct link)
  if (inputG) {
    inputG.addEventListener('input', function(e){
      var v = e.target.value.trim();
      if (v && (v.startsWith('http://') || v.startsWith('https://'))) {
        preview.src = v; preview.style.display='inline-block';
      } else {
        preview.style.display='none'; preview.src='';
      }
    });
  }

  // initialize
  onSelectChange();
});
</script>
</script>
