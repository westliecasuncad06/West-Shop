<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');

$u = current_user();

if (!function_exists('seller_normalize_image_input')) {
	function seller_normalize_image_input(?string $value): ?string {
		$value = trim((string)$value);
		if ($value === '') {
			return null;
		}
		if (preg_match('/^https?:\/\//i', $value)) {
			return $value;
		}
		if (preg_match('/^data:image\//i', $value)) {
			return $value;
		}
		if ($value[0] === '/') {
			return ltrim($value, '/');
		}
		if (stripos($value, 'assets/') === 0 || stripos($value, 'uploads/') === 0) {
			return ltrim($value, '/');
		}
		return null;
	}
}

// Ensure seller profile exists so catalog page can pull store data
$sp = $pdo->prepare('SELECT * FROM seller_profiles WHERE seller_id=? LIMIT 1');
$sp->execute([$u['user_id']]);
$profile = $sp->fetch();
if (!$profile) {
		$ins = $pdo->prepare('INSERT INTO seller_profiles(seller_id, shop_name) VALUES (?, ?)');
		$ins->execute([$u['user_id'], ($u['name'] . ' Shop')]);
		$sp->execute([$u['user_id']]);
		$profile = $sp->fetch();
}

// Mirror profile into stores table when possible so storefront previews stay in sync
try {
		$chkStore = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id=? LIMIT 1');
		$chkStore->execute([$u['user_id']]);
		$sid = $chkStore->fetchColumn();
		if (!$sid) {
				$insStore = $pdo->prepare('INSERT INTO stores (seller_id, store_name, logo, banner, description, shipping_policy, return_policy) VALUES (?,?,?,?,?,?,?)');
				$insStore->execute([
						$u['user_id'],
						($profile['shop_name'] ?: ($u['name'] . ' Shop')),
						$profile['logo'] ?? null,
						$profile['banner'] ?? null,
						$profile['description'] ?? null,
						$profile['shipping_policy'] ?? null,
						$profile['return_policy'] ?? null
				]);
		}
} catch (Exception $e) { /* stores table may not exist yet */ }

$q = trim($_GET['q'] ?? '');
$top_cat = (int)($_GET['top_cat'] ?? 0);
$sub_cat = (int)($_GET['sub_cat'] ?? 0);
$statusFilter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'recent';
$focusId = (int)($_GET['focus'] ?? 0);

// Prepare relative return path for post-actions so we preserve filters
$returnFilters = [];
if ($q !== '') { $returnFilters['q'] = $q; }
if ($top_cat) { $returnFilters['top_cat'] = $top_cat; }
if ($sub_cat) { $returnFilters['sub_cat'] = $sub_cat; }
if (in_array($statusFilter, ['active', 'inactive'], true)) { $returnFilters['status'] = $statusFilter; }
if ($sort && $sort !== 'recent') { $returnFilters['sort'] = $sort; }
$currentPath = 'seller/products.php' . ($returnFilters ? ('?' . http_build_query($returnFilters)) : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
		$action = $_POST['action'] ?? '';
		$returnTo = trim($_POST['return_to'] ?? 'seller/products.php');
		if (strpos($returnTo, 'seller/products.php') !== 0) {
				$returnTo = 'seller/products.php';
		}

		if ($action === 'update_product') {
			$pid = (int)($_POST['product_id'] ?? 0);
			$name = trim($_POST['name'] ?? '');
			$price = (float)($_POST['price'] ?? 0);
			$stock = (int)($_POST['stock'] ?? 0);
			$status = in_array(($_POST['status'] ?? 'active'), ['active','inactive'], true) ? $_POST['status'] : 'active';
			$topCategory = (int)($_POST['category_id'] ?? 0);
			$subCategory = (int)($_POST['subcategory_id'] ?? 0);
			$categoryId = $subCategory ?: ($topCategory ?: null);
			$description = trim($_POST['description'] ?? '');
			$imageMethod = $_POST['image_method'] ?? 'current';
			$existingImage = trim($_POST['existing_image'] ?? '');
			$imagePath = null;
			$uploadRel = 'assets/images/products';
			$uploadDir = __DIR__ . '/../' . $uploadRel;
			if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

			switch ($imageMethod) {
				case 'remove':
					$imagePath = null;
					break;
				case 'upload':
					$imagePath = $existingImage !== '' ? $existingImage : null;
					if (!empty($_FILES['image']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
						$finfo = new finfo(FILEINFO_MIME_TYPE);
						$mime = $finfo->file($_FILES['image']['tmp_name']);
						$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
						if (isset($allowed[$mime])) {
							$ext = $allowed[$mime];
							$fname = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
							$dest = $uploadDir . DIRECTORY_SEPARATOR . $fname;
							if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
								$imagePath = $uploadRel . '/' . $fname;
							}
						}
					}
					break;
				case 'url':
					$imgUrlRaw = $_POST['image_url'] ?? '';
					$imagePath = seller_normalize_image_input($imgUrlRaw);
					if ($imagePath === null) {
						if (trim($imgUrlRaw) !== '') {
							set_flash('warning', 'Provide a valid image URL (http/https, data URI, or relative path).');
							redirect($returnTo);
						}
						$imagePath = $existingImage !== '' ? $existingImage : null;
					}
					break;
				case 'gdrive':
					$gdriveRaw = $_POST['gdrive_url'] ?? '';
					$imagePath = seller_normalize_image_input($gdriveRaw);
					if ($imagePath === null) {
						if (trim($gdriveRaw) !== '') {
							set_flash('warning', 'Provide a valid Google Drive share link.');
							redirect($returnTo);
						}
						$imagePath = $existingImage !== '' ? $existingImage : null;
					}
					break;
				case 'current':
				default:
					$imagePath = $existingImage !== '' ? $existingImage : null;
					break;
			}

			if ($pid && $name !== '') {
				$up = $pdo->prepare('UPDATE products SET name=?, category_id=?, description=?, price=?, stock=?, image=?, status=? WHERE product_id=? AND seller_id=?');
				$up->execute([
					$name,
					$categoryId ?: null,
					$description !== '' ? $description : null,
					$price,
					$stock,
					$imagePath ?: null,
					$status,
					$pid,
					$u['user_id']
				]);
				set_flash('success', 'Product updated');
			} else {
				set_flash('warning', 'Product name is required');
			}
			redirect($returnTo);
		}

		if ($action === 'delete_product') {
				$pid = (int)($_POST['product_id'] ?? 0);
				if ($pid) {
						$stmt = $pdo->prepare('DELETE FROM products WHERE product_id=? AND seller_id=?');
						$stmt->execute([$pid, $u['user_id']]);
						set_flash('success', 'Product deleted');
				}
				redirect($returnTo);
		}
}

$topCategories = $pdo->query('SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name')->fetchAll();
$allCats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$subCategories = array_values(array_filter($allCats, function($c){ return !is_null($c['parent_id']); }));
$categoryNamesById = [];
foreach ($allCats as $cat) {
		$categoryNamesById[(int)$cat['category_id']] = $cat['name'];
}
$categoryParents = [];
foreach ($allCats as $cat) {
	$categoryParents[(int)$cat['category_id']] = is_null($cat['parent_id']) ? null : (int)$cat['parent_id'];
}

$statStmt = $pdo->prepare('SELECT COUNT(*) AS total,
		SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) AS active_total,
		SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) AS inactive_total,
		SUM(CASE WHEN stock <= 5 THEN 1 ELSE 0 END) AS low_stock_total
		FROM products WHERE seller_id = ?');
$statStmt->execute([$u['user_id']]);
$catalogStats = $statStmt->fetch() ?: [];
$totalProducts = (int)($catalogStats['total'] ?? 0);
$activeProducts = (int)($catalogStats['active_total'] ?? 0);
$inactiveProducts = (int)($catalogStats['inactive_total'] ?? max(0, $totalProducts - $activeProducts));
$lowStockProducts = (int)($catalogStats['low_stock_total'] ?? 0);

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
if (!in_array($statusFilter, ['active', 'inactive'], true)) {
		$statusFilter = '';
} else {
		$sql .= ' AND status = ?';
		$params[] = $statusFilter;
}
switch ($sort) {
		case 'stock_low':
				$sql .= ' ORDER BY stock ASC, name ASC';
				break;
		case 'stock_high':
				$sql .= ' ORDER BY stock DESC, name ASC';
				break;
		case 'price_low':
				$sql .= ' ORDER BY price ASC';
				break;
		case 'price_high':
				$sql .= ' ORDER BY price DESC';
				break;
		default:
				$sort = 'recent';
				$sql .= ' ORDER BY created_at DESC';
				break;
}
$pp = $pdo->prepare($sql);
$pp->execute($params);
$products = $pp->fetchAll();
$filteredCount = count($products);

include __DIR__ . '/../templates/header.php';
?>

<div class="seller-shell seller-catalog-page">
	<section class="card seller-products-hero p-4 p-lg-5 mb-4">
		<div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
			<div class="d-flex gap-3 align-items-start">
				<div class="seller-products-hero__icon">🛍️</div>
				<div>
				<p class="section-label mb-1">Catalog manager</p>
				<h2 class="mb-2">My Products</h2>
				<p class="text-muted mb-0">Curate, edit, and launch listings without scrolling through endless tables.</p>
				</div>
			</div>
			<div class="d-flex flex-wrap gap-2">
				<a class="btn btn-primary" href="<?php echo e(base_url('seller/index.php#inventory')); ?>">Add new product</a>
				<a class="btn btn-outline-secondary" href="<?php echo e(base_url('seller/index.php')); ?>">Back to dashboard</a>
			</div>
		</div>
		<div class="seller-products-stats mt-4">
			<div class="seller-products-stat">
				<span class="seller-products-stat__label">Total listings</span>
				<strong class="seller-products-stat__value"><?php echo $totalProducts; ?></strong>
			</div>
			<div class="seller-products-stat">
				<span class="seller-products-stat__label">Active</span>
				<strong class="seller-products-stat__value"><?php echo $activeProducts; ?></strong>
			</div>
			<div class="seller-products-stat">
				<span class="seller-products-stat__label">Inactive</span>
				<strong class="seller-products-stat__value"><?php echo $inactiveProducts; ?></strong>
			</div>
			<div class="seller-products-stat <?php echo $lowStockProducts ? 'seller-products-stat--alert' : ''; ?>">
				<span class="seller-products-stat__label">Low stock (&le;5)</span>
				<strong class="seller-products-stat__value"><?php echo $lowStockProducts; ?></strong>
			</div>
		</div>
	</section>

	<section class="card p-4 mb-4">
		<div class="seller-products-toolbar mb-3">
			<div>
				<p class="section-label mb-1">Filters</p>
				<h5 class="mb-0">Tune your catalog</h5>
			</div>
			<span class="badge-chip">
				<?php echo $filteredCount === $totalProducts ? 'Showing all listings' : ($filteredCount . ' of ' . $totalProducts . ' listings'); ?>
			</span>
		</div>
		<form method="get" class="seller-filter-grid">
			<div>
				<label class="form-label">Search</label>
				<input type="search" name="q" class="form-control" placeholder="Product name or description" value="<?php echo e($q); ?>">
			</div>
			<div>
				<label class="form-label">Top category</label>
				<select name="top_cat" id="products_top_cat" class="form-select">
					<option value="">All</option>
					<?php foreach($topCategories as $tc): ?>
						<option value="<?php echo (int)$tc['category_id']; ?>" <?php echo ($top_cat==(int)$tc['category_id']?'selected':''); ?>><?php echo e($tc['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label class="form-label">Subcategory</label>
				<select name="sub_cat" id="products_sub_cat" class="form-select">
					<option value="">All</option>
					<?php if($top_cat):
						$subStmt = $pdo->prepare('SELECT * FROM categories WHERE parent_id = ? ORDER BY name');
						$subStmt->execute([$top_cat]);
						$subList = $subStmt->fetchAll();
						foreach($subList as $sc): ?>
							<option value="<?php echo (int)$sc['category_id']; ?>" <?php echo ($sub_cat==(int)$sc['category_id']?'selected':''); ?>><?php echo e($sc['name']); ?></option>
						<?php endforeach; endif; ?>
				</select>
			</div>
			<div>
				<label class="form-label">Status</label>
				<select name="status" class="form-select">
					<option value="">Any</option>
					<option value="active" <?php echo ($statusFilter==='active'?'selected':''); ?>>Active</option>
					<option value="inactive" <?php echo ($statusFilter==='inactive'?'selected':''); ?>>Inactive</option>
				</select>
			</div>
			<div>
				<label class="form-label">Sort</label>
				<select name="sort" class="form-select">
					<option value="recent" <?php echo ($sort==='recent'?'selected':''); ?>>Newest first</option>
					<option value="stock_low" <?php echo ($sort==='stock_low'?'selected':''); ?>>Stock (low &rarr; high)</option>
					<option value="stock_high" <?php echo ($sort==='stock_high'?'selected':''); ?>>Stock (high &rarr; low)</option>
					<option value="price_low" <?php echo ($sort==='price_low'?'selected':''); ?>>Price (low &rarr; high)</option>
					<option value="price_high" <?php echo ($sort==='price_high'?'selected':''); ?>>Price (high &rarr; low)</option>
				</select>
			</div>
			<div class="seller-filter-grid__actions">
				<button class="btn btn-primary">Apply filters</button>
				<a class="btn btn-outline-secondary" href="<?php echo e(base_url('seller/products.php')); ?>">Reset</a>
			</div>
		</form>
	</section>

	<?php if($products): ?>
		<div class="seller-catalog-grid seller-catalog-grid--full">
			<?php foreach($products as $p):
				$imageSrc = ($p['image'] && strpos($p['image'], 'http') === 0)
					? $p['image']
					: ($p['image'] ? base_url($p['image']) : base_url('assets/images/products/placeholder.png'));
				$statusClass = $p['status'] === 'active' ? 'badge bg-success' : 'badge bg-secondary';
				$categoryLabel = $categoryNamesById[$p['category_id'] ?? 0] ?? 'Uncategorized';
				$lowStock = (int)$p['stock'] <= 5;
				$timestamp = $p['created_at'] ?? null;
				$focusClass = ($focusId && $focusId === (int)$p['product_id']) ? 'seller-catalog-card--focus' : '';
				$currentCatId = (int)($p['category_id'] ?? 0);
				$currentParentId = $categoryParents[$currentCatId] ?? null;
				$editTopCategoryId = $currentParentId ?: ($currentCatId ?: 0);
				$editSubCategoryId = $currentParentId ? $currentCatId : 0;
			?>
				<article class="seller-catalog-card <?php echo $focusClass; ?>" id="product_<?php echo (int)$p['product_id']; ?>">
					<div class="seller-catalog-card__thumb">
						<img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($p['name']); ?>">
						<span class="seller-catalog-card__status <?php echo $statusClass; ?>"><?php echo e($p['status']); ?></span>
					</div>
					<div class="seller-catalog-card__body">
						<div class="seller-catalog-card__header">
							<h5 class="mb-0"><?php echo e($p['name']); ?></h5>
							<span class="seller-catalog-card__price">$<?php echo number_format((float)$p['price'], 2); ?></span>
						</div>
						<p class="seller-catalog-card__meta text-muted mb-2"><?php echo e($categoryLabel); ?> · SKU #<?php echo (int)$p['product_id']; ?></p>
						<div class="seller-catalog-card__stats">
							<span class="seller-catalog-chip <?php echo $lowStock ? 'seller-catalog-chip--alert' : ''; ?>">
								Stock <?php echo (int)$p['stock']; ?><?php echo $lowStock ? ' • Reorder soon' : ''; ?>
							</span>
							<span class="seller-catalog-chip">Created <?php echo $timestamp ? date('M j, Y', strtotime($timestamp)) : '—'; ?></span>
						</div>
						<div class="seller-card-actions">
							<button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#edit_product_modal_<?php echo (int)$p['product_id']; ?>">Edit</button>
							<a class="btn btn-sm btn-outline-secondary" href="<?php echo e(base_url('public/product.php?id=' . (int)$p['product_id'])); ?>" target="_blank" rel="noopener">Preview</a>
							<form method="post" onsubmit="return confirm('Delete this product?');">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="return_to" value="<?php echo e($currentPath); ?>">
								<input type="hidden" name="action" value="delete_product">
								<input type="hidden" name="product_id" value="<?php echo (int)$p['product_id']; ?>">
								<button class="btn btn-sm btn-outline-danger">Delete</button>
							</form>
						</div>
					</div>
				</article>
				<div class="modal fade seller-product-modal" id="edit_product_modal_<?php echo (int)$p['product_id']; ?>" tabindex="-1" aria-labelledby="edit_product_label_<?php echo (int)$p['product_id']; ?>" aria-hidden="true">
					<div class="modal-dialog modal-lg modal-dialog-scrollable">
						<div class="modal-content">
							<form method="post" enctype="multipart/form-data" class="seller-edit-modal-form">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="action" value="update_product">
								<input type="hidden" name="product_id" value="<?php echo (int)$p['product_id']; ?>">
								<input type="hidden" name="return_to" value="<?php echo e($currentPath); ?>">
								<input type="hidden" name="existing_image" value="<?php echo e($p['image'] ?? ''); ?>">
								<div class="modal-header">
									<h5 class="modal-title" id="edit_product_label_<?php echo (int)$p['product_id']; ?>">Edit product</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<div class="row g-3">
										<div class="col-md-7">
											<label class="form-label">Name</label>
											<input class="form-control" name="name" value="<?php echo e($p['name']); ?>" required>
										</div>
										<div class="col-md-3">
											<label class="form-label">Top category</label>
											<select class="form-select seller-edit-top-category" name="category_id" data-initial="<?php echo $editTopCategoryId; ?>">
												<option value="">Uncategorized</option>
												<?php foreach($topCategories as $tc): ?>
													<option value="<?php echo (int)$tc['category_id']; ?>" <?php echo ($editTopCategoryId === (int)$tc['category_id'] ? 'selected' : ''); ?>><?php echo e($tc['name']); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="col-md-2">
											<label class="form-label">Status</label>
											<select class="form-select" name="status">
												<option value="active" <?php echo ($p['status']==='active'?'selected':''); ?>>Active</option>
												<option value="inactive" <?php echo ($p['status']==='inactive'?'selected':''); ?>>Inactive</option>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Subcategory</label>
											<select class="form-select seller-edit-sub-category" name="subcategory_id" data-placeholder="Subcategory (optional)">
												<option value="">Optional</option>
												<?php foreach($subCategories as $sc): ?>
													<option value="<?php echo (int)$sc['category_id']; ?>" data-parent="<?php echo (int)$sc['parent_id']; ?>" <?php echo ($editSubCategoryId === (int)$sc['category_id'] ? 'selected' : ''); ?>><?php echo e($sc['name']); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Price</label>
											<input type="number" step="0.01" class="form-control" name="price" value="<?php echo e($p['price']); ?>" required>
										</div>
										<div class="col-md-4">
											<label class="form-label">Stock</label>
											<input type="number" class="form-control" name="stock" value="<?php echo (int)$p['stock']; ?>" required>
										</div>
										<div class="col-12">
											<label class="form-label">Description</label>
											<textarea name="description" class="form-control" rows="3" placeholder="Describe product features"><?php echo e($p['description'] ?? ''); ?></textarea>
										</div>
										<div class="col-12">
											<div class="row g-3 align-items-end seller-edit-image-controls">
												<div class="col-md-4">
													<label class="form-label">Image source</label>
													<select class="form-select seller-image-method" name="image_method">
														<option value="current" selected>Keep current</option>
														<option value="upload">Upload new image</option>
														<option value="url">Image URL</option>
														<option value="gdrive">Google Drive URL</option>
														<option value="remove">Remove image</option>
													</select>
												</div>
												<div class="col-md-4">
													<div class="seller-image-input seller-image-input--upload" data-method-block="upload" style="display:none;">
														<label class="form-label">Upload file</label>
														<input type="file" name="image" class="form-control" accept="image/*">
													</div>
													<div class="seller-image-input seller-image-input--url" data-method-block="url" style="display:none;">
														<label class="form-label">Direct URL</label>
														<input type="url" name="image_url" class="form-control" placeholder="https://..." value="<?php echo e((strpos((string)$p['image'], 'http') === 0) ? $p['image'] : ''); ?>">
													</div>
													<div class="seller-image-input seller-image-input--gdrive" data-method-block="gdrive" style="display:none;">
														<label class="form-label">Drive link</label>
														<input type="url" name="gdrive_url" class="form-control" placeholder="https://drive.google.com/...">
													</div>
												</div>
												<div class="col-md-4 text-center">
													<label class="form-label">Current preview</label>
													<div class="product-preview">
														<img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($p['name']); ?>">
													</div>
													<p class="text-muted small mb-0">Changes apply after saving.</p>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
									<button class="btn btn-primary">Save changes</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<div class="empty-panel text-center">
			<p class="fw-semibold mb-1">No products match your filters</p>
			<p class="text-muted mb-3">Adjust the filters or add a new listing to populate your catalog.</p>
			<div class="d-inline-flex gap-2">
				<a class="btn btn-outline-secondary" href="<?php echo e(base_url('seller/products.php')); ?>">Clear filters</a>
				<a class="btn btn-primary" href="<?php echo e(base_url('seller/index.php#inventory')); ?>">Add product</a>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
<script>
	(function(){
		var allCats = <?php echo json_encode($allCats ?: []); ?>;
		var map = {};
		allCats.forEach(function(c){
			if (c.parent_id === null) { return; }
			var pid = Number(c.parent_id);
			if (!map[pid]) { map[pid] = []; }
			map[pid].push(c);
		});
		var top = document.getElementById('products_top_cat');
		var sub = document.getElementById('products_sub_cat');
		if (top && sub) {
			top.addEventListener('change', function(){
				var choice = Number(top.value || 0);
				sub.innerHTML = '<option value="">All</option>';
				if (choice && map[choice]) {
					map[choice].forEach(function(child){
						var opt = document.createElement('option');
						opt.value = child.category_id;
						opt.textContent = child.name;
						sub.appendChild(opt);
					});
				}
			});
		}
		if (<?php echo $focusId ? 'true' : 'false'; ?>) {
			var focusEl = document.getElementById('product_<?php echo $focusId; ?>');
			if (focusEl) {
				focusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		}

		var editForms = document.querySelectorAll('.seller-edit-modal-form');
		editForms.forEach(function(form){
			var topSelect = form.querySelector('.seller-edit-top-category');
			var subSelect = form.querySelector('.seller-edit-sub-category');
			var syncSubOptions = function(){
				if (!topSelect || !subSelect) { return; }
				var parentId = Number(topSelect.value || 0);
				var options = subSelect.querySelectorAll('option[data-parent]');
				var hasMatch = false;
				options.forEach(function(opt){
					var matches = parentId && Number(opt.getAttribute('data-parent') || 0) === parentId;
					opt.hidden = !matches;
					opt.style.display = matches ? '' : 'none';
					if (!matches && opt.selected) {
						opt.selected = false;
					}
					if (matches) { hasMatch = true; }
				});
				subSelect.disabled = !parentId || !hasMatch;
				if (!parentId) {
					subSelect.value = '';
				}
			};
			if (topSelect && subSelect) {
				syncSubOptions();
				topSelect.addEventListener('change', syncSubOptions);
			}

			var imageControls = form.querySelector('.seller-edit-image-controls');
			if (imageControls) {
				var methodSelect = imageControls.querySelector('.seller-image-method');
				var blocks = imageControls.querySelectorAll('[data-method-block]');
				var syncBlocks = function(){
					var method = methodSelect ? methodSelect.value : 'current';
					blocks.forEach(function(block){
						var match = block.getAttribute('data-method-block') === method;
						block.style.display = match ? '' : 'none';
					});
				};
				if (methodSelect) {
					syncBlocks();
					methodSelect.addEventListener('change', syncBlocks);
				}
			}
		});
	})();
</script>
