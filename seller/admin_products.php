<?php
require_once __DIR__ . '/../templates/header.php';
require_role('seller');
global $pdo; $user = current_user();

// Basic CRUD: add product
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='add') {
    if (!csrf_verify()) { set_flash('danger','Invalid token'); redirect('seller/admin_products.php'); }
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $discount = (float)($_POST['discount_price'] ?? 0);
    $category = (int)($_POST['category_id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $sku = 'SKU'.strtoupper(bin2hex(random_bytes(3)));

    // image handling - single main image
    $imgPath = null;
    if (!empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        $ok = ['image/jpeg','image/png','image/webp'];
        if ($f['error']===0 && in_array(mime_content_type($f['tmp_name']), $ok) && $f['size'] <= 2*1024*1024) {
            $dir = __DIR__.'/../uploads/store/'; if (!is_dir($dir)) mkdir($dir,0755,true);
            $ext = pathinfo($f['name'],PATHINFO_EXTENSION);
            $fn = 'prod_'.$user['user_id'].'_'.time().'.'.$ext;
            if (move_uploaded_file($f['tmp_name'],$dir.$fn)) { $imgPath = 'uploads/store/'.$fn; }
        } else { set_flash('danger','Invalid product image (JPG/PNG/WEBP <=2MB)'); }
    }

    $ins = $pdo->prepare('INSERT INTO products (seller_id, name, description, price, discount_price, category_id, stock, sku, image, status) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $ins->execute([$user['user_id'],$name,$description,$price,$discount,$category,$stock,$sku,$imgPath,'active']);
    set_flash('success','Product added'); redirect('seller/admin_products.php');
}

// fetch products
$q = $pdo->prepare('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.category_id=p.category_id WHERE p.seller_id = ?');
$q->execute([$user['user_id']]); $prods = $q->fetchAll();

?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Products (Admin)</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProduct">Add Product</button>
</div>
<div class="row g-3">
  <?php foreach($prods as $p): ?>
    <div class="col-md-4">
      <div class="card">
        <img src="<?php echo e($p['image'] ? base_url($p['image']) : base_url('assets/images/products/placeholder.png')); ?>" class="w-100" style="height:160px;object-fit:cover">
        <div class="card-body">
          <h6><?php echo e($p['name']); ?></h6>
          <div class="small text-muted">Stock: <?php echo (int)$p['stock']; ?> | SKU: <?php echo e($p['sku']); ?></div>
          <div class="mt-2 d-flex justify-content-between">
            <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProduct" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="add">
        <div class="modal-header"><h5 class="modal-title">Add Product</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
          <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">Price</label><input class="form-control" name="price" type="number" step="0.01" required></div>
            <div class="col-md-4 mb-3"><label class="form-label">Discount Price</label><input class="form-control" name="discount_price" type="number" step="0.01"></div>
            <div class="col-md-4 mb-3"><label class="form-label">Stock</label><input class="form-control" name="stock" type="number" required></div>
          </div>
          <div class="mb-3"><label class="form-label">Category</label>
            <select name="category_id" class="form-select">
              <option value="0">Uncategorized</option>
              <?php foreach(get_categories() as $c): ?><option value="<?php echo $c['category_id']; ?>"><?php echo e($c['name']); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Main Image (JPG/PNG/WEBP)</label><input type="file" name="image" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Save</button></div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
