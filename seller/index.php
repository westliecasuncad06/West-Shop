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

// Ensure a corresponding row exists in `stores` for this seller (if table is present)
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
} catch (Exception $e) { /* stores table may not exist yet; ignore */ }

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
      $shop_name = trim($_POST['shop_name'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $shipping_policy = trim($_POST['shipping_policy'] ?? '');
      $return_policy = trim($_POST['return_policy'] ?? '');
      if ($shop_name !== '') {
        $up = $pdo->prepare('UPDATE seller_profiles SET shop_name=?, description=?, shipping_policy=?, return_policy=? WHERE seller_id=?');
        $up->execute([$shop_name, $description, $shipping_policy, $return_policy, $u['user_id']]);
        // Mirror into stores table if present (ignore failures silently)
        try {
          $chk = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id=? LIMIT 1');
          $chk->execute([$u['user_id']]);
          $sid = $chk->fetchColumn();
          if ($sid) {
            $up2 = $pdo->prepare('UPDATE stores SET store_name=?, description=?, shipping_policy=?, return_policy=? WHERE seller_id=?');
            $up2->execute([$shop_name, $description, $shipping_policy, $return_policy, $u['user_id']]);
          } else {
            // Create a store row if missing
            $crt = $pdo->prepare('INSERT INTO stores (seller_id, store_name, description, shipping_policy, return_policy) VALUES (?,?,?,?,?)');
            $crt->execute([$u['user_id'], $shop_name, $description, $shipping_policy, $return_policy]);
          }
        } catch (Exception $e) { /* noop */ }
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
            // Mirror to stores table if exists
            try {
              $chk = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id=? LIMIT 1');
              $chk->execute([$u['user_id']]);
              $existingStoreId = $chk->fetchColumn();
              if ($existingStoreId) {
                $up2 = $pdo->prepare("UPDATE stores SET {$field}=? WHERE seller_id=?");
                $up2->execute([$path, $u['user_id']]);
              } else {
                // Create store row and set image
                $crt = $pdo->prepare('INSERT INTO stores (seller_id, store_name, {$field}) VALUES (?,?,?)');
                $crt->execute([$u['user_id'], ($profile['shop_name'] ?: ($u['name'].' Shop')), $path]);
              }
            } catch (Exception $e) { /* ignore */ }
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

      if ($action === 'create_product') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $subcat = (int)($_POST['subcategory_id'] ?? 0);
        $cat = $subcat ?: (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        $imagePath = null;
        $uploadRel = 'assets/images/products';
        $uploadDir = __DIR__ . '/../' . $uploadRel;
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

        if (!empty($_FILES['image']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
          $f = $_FILES['image'];
          $finfo = new finfo(FILEINFO_MIME_TYPE);
          $mime = $finfo->file($f['tmp_name']);
          $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
          if (isset($allowed[$mime])) {
            $ext = $allowed[$mime];
            $fname = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadDir . DIRECTORY_SEPARATOR . $fname;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
              $imagePath = $uploadRel . '/' . $fname;
            }
          }
        }
        if (!$imagePath) {
          $imgUrl = trim($_POST['image_url'] ?? '');
          if ($imgUrl && filter_var($imgUrl, FILTER_VALIDATE_URL)) { $imagePath = $imgUrl; }
        }
        if (!$imagePath) {
          $gdrive = trim($_POST['gdrive_url'] ?? '');
          if ($gdrive && filter_var($gdrive, FILTER_VALIDATE_URL)) { $imagePath = $gdrive; }
        }

        if ($name !== '') {
          $stmt = $pdo->prepare('INSERT INTO products(seller_id, category_id, name, description, price, stock, image, status) VALUES (?,?,?,?,?,?,?,"active")');
          $stmt->execute([$u['user_id'], $cat ?: null, $name, $description, $price, $stock, $imagePath]);
          set_flash('success', 'Product added');
        } else {
          set_flash('warning', 'Product name is required');
        }
        redirect('seller/index.php#inventory');
      }

      if ($action === 'delete_product') {
        $pid = (int)($_POST['product_id'] ?? 0);
        if ($pid) {
          $stmt = $pdo->prepare('DELETE FROM products WHERE product_id=? AND seller_id=?');
          $stmt->execute([$pid, $u['user_id']]);
          set_flash('success', 'Product deleted');
        }
        redirect('seller/index.php#inventory');
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
        // Resolve store id if possible (non-fatal if stores table missing)
        $storeId = null;
        try {
          $storeCheck = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
          $storeCheck->execute([$u['user_id']]);
          $storeId = $storeCheck->fetchColumn() ?: null;
        } catch (Exception $e) { $storeId = null; }

        // Detect coupons schema columns to keep compatibility
        $hasStoreId = false; $hasShopId = false;
        try {
          $c1 = $pdo->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='coupons' AND column_name='store_id'");
          $hasStoreId = (bool)$c1->fetchColumn();
          $c2 = $pdo->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='coupons' AND column_name='shop_id'");
          $hasShopId = (bool)$c2->fetchColumn();
        } catch (Exception $e) { /* ignore, default false */ }

        // Attempt to map to legacy shops if needed
        $shopId = null;
        if ($hasShopId && !$hasStoreId && $storeId === null) {
          try {
            $s = $pdo->prepare('SELECT shop_id FROM shops WHERE seller_id=? LIMIT 1');
            $s->execute([$u['user_id']]);
            $shopId = $s->fetchColumn() ?: null;
          } catch (Exception $e) { $shopId = null; }
        }

        // Build INSERT based on available columns
        $cols = ['code','type','value','expires_at','max_uses','created_by'];
        $vals = [$code,$type,$value,($expires_at ?: null),$max_uses,$u['user_id']];
        if ($hasStoreId) { $cols[] = 'store_id'; $vals[] = $storeId; }
        elseif ($hasShopId) { $cols[] = 'shop_id'; $vals[] = $shopId; }

        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = 'INSERT INTO coupons(' . implode(',', $cols) . ') VALUES (' . $placeholders . ')';

        try {
          $ins = $pdo->prepare($sql);
          $ins->execute($vals);
          set_flash('success', 'Coupon created');
        } catch (Exception $e) {
          // Attempt to detect duplicate code; otherwise generic
          $msg = 'Failed to create coupon';
          $em = $e->getMessage();
          if (stripos($em, 'Duplicate') !== false || stripos($em, '1062') !== false) {
            $msg = 'Coupon code already exists';
          }
          set_flash('danger', $msg);
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

// Get seller's store_id and load coupons (fallback to created_by)
$storeId = null;
try {
  $storeStmt = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
  $storeStmt->execute([$u['user_id']]);
  $storeId = $storeStmt->fetchColumn() ?: null;
} catch (Exception $e) { $storeId = null; }

$coupons = [];
try {
  if ($storeId) {
    $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE store_id = ? ORDER BY created_at DESC');
    $couponStmt->execute([$storeId]);
  } else {
    $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE created_by = ? ORDER BY created_at DESC');
    $couponStmt->execute([$u['user_id']]);
  }
  $coupons = $couponStmt->fetchAll();
} catch (Exception $e) { $coupons = []; }

// Search / filter inputs (GET)
$q = trim($_GET['q'] ?? '');
$top_cat = (int)($_GET['top_cat'] ?? 0);
$sub_cat = (int)($_GET['sub_cat'] ?? 0);

// Load categories (top-level and all for JS mapping)
$topCatsStmt = $pdo->prepare('SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name');
$topCatsStmt->execute();
$topCategories = $topCatsStmt->fetchAll();
$allCats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$subCategories = array_values(array_filter($allCats, function($c){ return !is_null($c['parent_id']); }));
$categoryNamesById = [];
foreach ($allCats as $cat) {
  $categoryNamesById[(int)$cat['category_id']] = $cat['name'];
}

$sellerCurrentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$sellerSidebarLinks = [
  ['label' => 'Overview', 'href' => base_url('seller/index.php'), 'icon' => 'bi-speedometer2', 'match' => 'seller/index.php'],
  ['label' => 'Orders', 'href' => base_url('seller/orders.php'), 'icon' => 'bi-bag-check', 'badge' => $soldCount, 'match' => 'seller/orders.php'],
  ['label' => 'Products', 'href' => base_url('seller/products.php'), 'icon' => 'bi-box-seam', 'badge' => $prodCount, 'match' => 'seller/products.php'],
  // 'Store Profile' removed from seller dashboard menu per request
  ['label' => 'Buyer Messages', 'href' => base_url('seller/chat.php'), 'icon' => 'bi-chat-dots', 'match' => 'seller/chat.php'],
  ['label' => 'Support Chat', 'href' => base_url('seller/chat_admin.php'), 'icon' => 'bi-headset', 'match' => 'seller/chat_admin.php']
];

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

include __DIR__ . '/../templates/header.php';
?>

<?php if ($isPreview): ?>
  <?php
    $sellerIdForPreview = (int)$u['user_id'];
    $store = get_seller_profile($sellerIdForPreview);
    if (!$store) {
      $store = [
        'seller_id' => $sellerIdForPreview,
        'seller_name' => $u['name'],
        'shop_name' => $profile['shop_name'] ?? ($u['name'] . ' Shop'),
        'description' => $profile['description'] ?? '',
        'shipping_policy' => $profile['shipping_policy'] ?? '',
        'return_policy' => $profile['return_policy'] ?? '',
        'logo' => $profile['logo'] ?? null,
        'banner' => $profile['banner'] ?? null,
      ];
    }

    $rating = get_store_rating($sellerIdForPreview);
    $followers = count_store_followers($sellerIdForPreview);
    $isBuyer = false; // Seller preview should not expose buyer actions
    $isFollowing = false;

    $pstmt = $pdo->prepare('SELECT * FROM products WHERE seller_id = ? AND status = "active" ORDER BY created_at DESC');
    $pstmt->execute([$sellerIdForPreview]);
    $products = $pstmt->fetchAll();

    // Copy coupon logic from buyer storefront
    $storeId = null;
    try {
      $storeStmt = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
      $storeStmt->execute([$sellerIdForPreview]);
      $storeId = $storeStmt->fetchColumn();
    } catch (Exception $e) {
      $storeId = null;
    }

    $activeCoupons = [];
    try {
      if ($storeId) {
        $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE store_id = ? ORDER BY created_at DESC');
        $couponStmt->execute([$storeId]);
        $allCoupons = $couponStmt->fetchAll();
      } else {
        $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE created_by = ? ORDER BY created_at DESC');
        $couponStmt->execute([$sellerIdForPreview]);
        $allCoupons = $couponStmt->fetchAll();
      }

      $activeCoupons = array_filter($allCoupons, function($c) {
        $notExpired = !$c['expires_at'] || strtotime($c['expires_at']) >= time();
        $notMaxed = ($c['max_uses'] ?? 0) == 0 || ($c['used_count'] ?? 0) < $c['max_uses'];
        return $notExpired && $notMaxed;
      });
    } catch (Exception $e) {
      $activeCoupons = [];
    }

    $storeViewMode = 'seller_preview';
    $storeActionUrl = base_url('seller/index.php?preview=1');
    $previewExitUrl = base_url('seller/index.php');

    include __DIR__ . '/partials/storefront_view.php';
  ?>
<?php else: ?>
  <div class="dashboard-shell seller-dashboard-shell">
    <div class="dashboard-shell__sidebar">
      <div class="dashboard-shell__sidebar-trigger d-lg-none">
        <button class="btn btn-outline-primary w-100 mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sellerSidebarNav" aria-controls="sellerSidebarNav">
          <i class="bi bi-sliders me-2"></i> Seller menu
        </button>
      </div>
      <div class="offcanvas offcanvas-start offcanvas-lg dashboard-offcanvas" tabindex="-1" id="sellerSidebarNav" aria-labelledby="sellerSidebarNavLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="sellerSidebarNavLabel">Seller navigation</h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <nav class="dashboard-menu">
            <?php foreach ($sellerSidebarLinks as $item): ?>
              <?php
                $isActive = $sellerCurrentPath === trim($item['match'], '/');
                $linkClass = trim('dashboard-menu__link ' . ($isActive ? 'active' : ''));
              ?>
              <a href="<?php echo e($item['href']); ?>" class="<?php echo e($linkClass); ?>">
                <span><i class="bi <?php echo e($item['icon']); ?> me-2"></i><?php echo e($item['label']); ?></span>
                <?php if (array_key_exists('badge', $item)): ?><span class="badge bg-light text-dark"><?php echo (int)$item['badge']; ?></span><?php endif; ?>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>
      </div>
    </div>
    <div class="dashboard-shell__content">
      <div class="seller-shell">
    <section class="seller-hero card border-0 p-4 p-lg-5 dashboard-header">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="hero-icon shadow-sm">🏪</div>
            <div>
              <p class="text-muted mb-1 small">Welcome back, <?php echo e($u['name']); ?></p>
              <h2 class="mb-0"><?php echo e($profile['shop_name'] ?: 'Your Storefront'); ?></h2>
            </div>
          </div>
          <p class="text-muted mb-4">
            <?php echo e($profile['description'] ?: 'Add a short store story so buyers instantly get your vibe.'); ?>
          </p>
          <div class="hero-actions d-flex flex-wrap gap-2">
            <a class="pill-button pill-button--mint" href="<?php echo e(base_url('seller/index.php?preview=1')); ?>">Buyer View</a>
            <a class="pill-button pill-button--ghost" href="<?php echo e(base_url('seller/products.php')); ?>">Manage Products</a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="hero-metrics">
            <div class="hero-stat-card">
              <span class="hero-stat-label">Products live</span>
              <strong class="display-6 fs-2"><?php echo $prodCount; ?></strong>
            </div>
            <div class="hero-stat-card">
              <span class="hero-stat-label">Items sold</span>
              <strong class="display-6 fs-2"><?php echo $soldCount; ?></strong>
            </div>
            <div class="hero-stat-card">
              <span class="hero-stat-label">Last updated</span>
              <strong class="fs-5">Today</strong>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="seller-stats-grid">
      <a class="seller-stat-card" href="<?php echo e(base_url('seller/products.php')); ?>">
        <span class="seller-stat-icon">📦</span>
        <div>
          <p class="seller-stat-label mb-1">Catalog health</p>
          <strong class="seller-stat-value"><?php echo $prodCount; ?> items listed</strong>
          <span class="text-muted small">Review stock & pricing</span>
        </div>
      </a>
      <div class="seller-stat-card">
        <span class="seller-stat-icon">💰</span>
        <div>
          <p class="seller-stat-label mb-1">Order momentum</p>
          <strong class="seller-stat-value"><?php echo $soldCount; ?> orders fulfilled</strong>
          <span class="text-muted small">Keep products active</span>
        </div>
      </div>
      <a class="seller-stat-card" href="<?php echo e(base_url('seller/chat.php')); ?>">
        <span class="seller-stat-icon">📨</span>
        <div>
          <p class="seller-stat-label mb-1">Buyer inbox</p>
          <strong class="seller-stat-value">Reply faster</strong>
          <span class="text-muted small">Open messages</span>
        </div>
      </a>
      <a class="seller-stat-card" href="<?php echo e(base_url('seller/chat_admin.php')); ?>">
        <span class="seller-stat-icon">🆘</span>
        <div>
          <p class="seller-stat-label mb-1">Need help?</p>
          <strong class="seller-stat-value">Support chat</strong>
          <span class="text-muted small">Talk to the team</span>
        </div>
      </a>
    </div>

    <div class="seller-content-grid">
      <section class="seller-stack">
        <div class="card p-4 store-profile-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <p class="section-label mb-1">Your brand kit</p>
              <h5 class="mb-0">Store Profile</h5>
            </div>
            <span class="badge-chip">Keep info fresh</span>
          </div>

          <div class="store-banner mb-3">
            <?php if($profile['banner']): ?>
              <img src="<?php echo e(base_url($profile['banner'])); ?>" alt="Store banner" class="img-fluid">
            <?php else: ?>
              <div class="empty-banner">Add a wide hero banner</div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="store-banner__upload">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="upload_banner">
              <label class="btn btn-outline-primary w-100 mb-0">
                <input type="file" name="image" accept="image/*" required hidden class="auto-submit-upload">
                Upload banner
              </label>
            </form>
          </div>

          <div class="store-logo mb-4">
            <div class="store-logo__preview">
              <?php if($profile['logo']): ?>
                <img src="<?php echo e(base_url($profile['logo'])); ?>" alt="Store logo">
              <?php else: ?>
                <span>Logo</span>
              <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" class="w-100">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="upload_logo">
              <label class="btn btn-outline-primary w-100 mb-0">
                <input type="file" name="image" accept="image/*" required hidden class="auto-submit-upload">
                Upload logo
              </label>
            </form>
          </div>

          <form method="post" class="store-profile-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_profile">
            <label class="form-label">Store name</label>
            <input class="form-control mb-3" name="shop_name" value="<?php echo e($profile['shop_name']); ?>" required>

            <label class="form-label">Short bio</label>
            <textarea class="form-control mb-3" name="description" rows="3" placeholder="Describe your style and promise."><?php echo e($profile['description'] ?? ''); ?></textarea>

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Shipping policy</label>
                <textarea class="form-control" name="shipping_policy" rows="3"><?php echo e($profile['shipping_policy'] ?? ''); ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Return policy</label>
                <textarea class="form-control" name="return_policy" rows="3"><?php echo e($profile['return_policy'] ?? ''); ?></textarea>
              </div>
            </div>
            <button class="btn btn-primary w-100 mt-4">Save profile</button>
          </form>
        </div>
      </section>

      <section class="seller-stack" id="inventory">
        <div class="card p-4 mb-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
              <p class="section-label mb-1">Quick publish</p>
              <h5 class="mb-0">Add Product</h5>
            </div>
            <span class="badge-chip">Boost your catalog</span>
          </div>
          <form method="post" enctype="multipart/form-data" class="row g-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="create_product">
            <div class="col-md-6">
              <label class="form-label">Product name</label>
              <input class="form-control" name="name" placeholder="Premium sneakers" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Top category</label>
              <select name="category_id" id="product_category_id" class="form-select">
                <option value="">Pick one</option>
                <?php foreach($topCategories as $tc): ?>
                  <option value="<?php echo (int)$tc['category_id']; ?>"><?php echo e($tc['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Subcategory</label>
              <select name="subcategory_id" id="product_subcategory_id" class="form-select">
                <option value="">Optional</option>
                <?php foreach($subCategories as $sc): ?>
                  <option data-parent="<?php echo (int)$sc['parent_id']; ?>" value="<?php echo (int)$sc['category_id']; ?>"><?php echo e($sc['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Price</label>
              <input type="number" step="0.01" name="price" class="form-control" placeholder="100" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Stock</label>
              <input type="number" name="stock" class="form-control" placeholder="10" required>
            </div>
            <div class="col-md-7">
              <label class="form-label">Short description</label>
              <textarea name="description" class="form-control" rows="2" placeholder="Highlight materials, benefits, delivery promises"></textarea>
            </div>
            <div class="col-12">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label">Image source</label>
                  <select id="image_method_select" name="image_method" class="form-select">
                    <option value="upload">Upload</option>
                    <option value="url">Image URL</option>
                    <option value="gdrive">Google Drive URL</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <div id="img_block_upload" class="img-block">
                    <label class="form-label">Upload file</label>
                    <input type="file" name="image" id="image_input" class="form-control" accept="image/*">
                  </div>
                  <div id="img_block_url" class="img-block" style="display:none;">
                    <label class="form-label">Direct URL</label>
                    <input type="url" name="image_url" id="image_url_input" class="form-control" placeholder="https://...">
                  </div>
                  <div id="img_block_gdrive" class="img-block" style="display:none;">
                    <label class="form-label">Drive share link</label>
                    <input type="url" name="gdrive_url" id="gdrive_url_input" class="form-control" placeholder="https://drive.google.com/...">
                  </div>
                </div>
                <div class="col-md-4 text-center">
                  <label class="form-label">Preview</label>
                  <div class="product-preview">
                    <img id="image_preview" src="" alt="Preview" style="display:none;">
                    <div class="text-muted small" id="image_preview_placeholder">Upload or link an image</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
              <button class="btn btn-primary">Publish product</button>
              <button class="btn btn-outline-secondary" type="reset">Reset</button>
            </div>
          </form>
        </div>

        <div class="card p-4 h-100">
          <?php
            $fullTotal = count($products);
            $previewProducts = array_slice($products, 0, 5);
            $previewTotal = count($previewProducts);
            $extraCount = max(0, $fullTotal - $previewTotal);
          ?>
          <div class="seller-catalog-preview__header mb-4">
            <div>
              <p class="section-label mb-1">Catalog</p>
              <h5 class="mb-1">My Products</h5>
              <p class="text-muted small mb-0"><?php echo $fullTotal; ?> item<?php echo $fullTotal === 1 ? '' : 's'; ?> in your catalog</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <a href="<?php echo e(base_url('seller/products.php')); ?>" class="pill-button pill-button--ghost">Open manager</a>
              <a class="pill-button pill-button--mint" href="<?php echo e(base_url('seller/index.php#inventory')); ?>">Add listing</a>
            </div>
          </div>

          <form method="get" class="row g-2 mb-4">
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

          <?php if($previewProducts): ?>
            <div class="seller-catalog-grid seller-catalog-grid--preview">
              <?php foreach($previewProducts as $p):
                $imageSrc = ($p['image'] && strpos($p['image'], 'http') === 0)
                  ? $p['image']
                  : ($p['image'] ? base_url($p['image']) : base_url('assets/images/products/placeholder.png'));
                $statusClass = $p['status'] === 'active' ? 'badge bg-success' : 'badge bg-secondary';
                $categoryLabel = $categoryNamesById[$p['category_id'] ?? 0] ?? 'Uncategorized';
                $lowStock = (int)$p['stock'] <= 5;
                $timestamp = $p['updated_at'] ?? $p['created_at'] ?? null;
                $timestampLabel = $timestamp ? date('M j, Y', strtotime($timestamp)) : '—';
              ?>
                <article class="seller-catalog-card">
                  <div class="seller-catalog-card__thumb">
                    <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($p['name']); ?>">
                    <span class="seller-catalog-card__status <?php echo $statusClass; ?>"><?php echo e($p['status']); ?></span>
                  </div>
                  <div class="seller-catalog-card__body">
                    <div class="seller-catalog-card__header">
                      <h6 class="mb-0"><?php echo e($p['name']); ?></h6>
                      <span class="seller-catalog-card__price">$<?php echo number_format((float)$p['price'], 2); ?></span>
                    </div>
                    <p class="seller-catalog-card__meta text-muted mb-2"><?php echo e($categoryLabel); ?> · SKU #<?php echo (int)$p['product_id']; ?></p>
                    <div class="seller-catalog-card__stats">
                      <span class="seller-catalog-chip <?php echo $lowStock ? 'seller-catalog-chip--alert' : ''; ?>">
                        Stock <?php echo (int)$p['stock']; ?><?php echo $lowStock ? ' • Reorder soon' : ''; ?>
                      </span>
                      <span class="seller-catalog-chip">Updated <?php echo e($timestampLabel); ?></span>
                    </div>
                    <div class="seller-card-actions">
                      <a class="btn btn-sm btn-outline-primary" href="<?php echo e(base_url('seller/products.php?focus=' . (int)$p['product_id'])); ?>">Manage</a>
                      <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(base_url('public/product.php?id=' . (int)$p['product_id'])); ?>" target="_blank" rel="noopener">Preview</a>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
            <?php if($extraCount > 0): ?>
              <p class="text-muted small mt-3"><?php echo $extraCount; ?> more product<?php echo $extraCount === 1 ? '' : 's'; ?> in your catalog.</p>
            <?php endif; ?>
          <?php else: ?>
            <div class="seller-catalog-preview__empty empty-panel">
              <p class="fw-semibold mb-1">No products yet</p>
              <p class="text-muted mb-3">List at least one item to unlock your storefront preview.</p>
              <a class="btn btn-primary" href="<?php echo e(base_url('seller/index.php#inventory')); ?>">Add your first product</a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="seller-stack seller-stack--sidebar">
        <div class="card p-4 mb-4">
          <p class="section-label mb-1">Growth lever</p>
          <h5 class="mb-3">Add Coupon</h5>
          <form method="post" class="small">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add_coupon">
            <div class="mb-3">
              <label class="form-label">Code</label>
              <input class="form-control" name="code" placeholder="CODE2025" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Type & value</label>
              <div class="input-group">
                <select name="type" class="form-select" style="max-width:45%">
                  <option value="fixed">Fixed</option>
                  <option value="percent">Percent</option>
                </select>
                <input type="number" step="0.01" name="value" class="form-control" placeholder="Value" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Expires</label>
              <input type="datetime-local" name="expires_at" class="form-control">
            </div>
            <div class="mb-4">
              <label class="form-label">Max uses</label>
              <input type="number" name="max_uses" class="form-control" placeholder="0 = unlimited" value="0">
            </div>
            <button class="btn btn-primary w-100">Create coupon</button>
          </form>
        </div>

        <div class="card p-4">
          <p class="section-label mb-1">Active promos</p>
          <h5 class="mb-3">Store Coupons</h5>
          <?php if (count($coupons) > 0): ?>
            <div class="coupon-stack">
              <?php foreach ($coupons as $coupon): 
                $isExpired = $coupon['expires_at'] && strtotime($coupon['expires_at']) < time();
                $isMaxed = $coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses'];
              ?>
                <div class="coupon-card <?php echo ($isExpired ? 'coupon-card--muted' : ''); ?>">
                  <div class="coupon-code"><?php echo e($coupon['code']); ?></div>
                  <p class="mb-1 text-muted small">
                    <?php echo e($coupon['type']); ?> ·
                    <?php if ($coupon['type'] === 'percent'): ?>
                      <?php echo e($coupon['value']); ?>%
                    <?php else: ?>
                      $<?php echo e($coupon['value']); ?>
                    <?php endif; ?>
                  </p>
                  <p class="mb-1 text-muted small">
                    <?php if ($coupon['expires_at']): ?>
                      Expires <?php echo e(date('M j, Y', strtotime($coupon['expires_at']))); ?>
                      <?php if ($isExpired): ?><span class="text-danger">(Expired)</span><?php endif; ?>
                    <?php else: ?>
                      No expiration
                    <?php endif; ?>
                  </p>
                  <p class="mb-0 text-muted small">
                    Used <?php echo (int)$coupon['used_count']; ?>
                    <?php if ($coupon['max_uses'] > 0): ?>
                      / <?php echo (int)$coupon['max_uses']; ?>
                      <?php if ($isMaxed): ?><span class="text-warning">(Max reached)</span><?php endif; ?>
                    <?php else: ?>
                      (Unlimited)
                    <?php endif; ?>
                  </p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="empty-panel text-center">
              <p class="mb-1 fw-semibold">No coupons yet</p>
              <p class="text-muted small mb-0">Create a promo code to reward loyal buyers.</p>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div><!-- /.seller-content-grid -->
  </div><!-- /.seller-shell -->
    </div><!-- /.dashboard-shell__content -->
  </div><!-- /.dashboard-shell -->
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
    var top = document.getElementById('top_cat');
    var sub = document.getElementById('sub_cat');
    if (top && sub){
      top.addEventListener('change', function(){
        var val = parseInt(this.value) || 0;
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
      var form = top.closest('form');
      if (form){
        top.addEventListener('change', function(){ form.submit(); });
        sub.addEventListener('change', function(){ form.submit(); });
      }
    }

    var prodCat = document.getElementById('product_category_id');
    var prodSub = document.getElementById('product_subcategory_id');
    function filterProductSubs(){
      if (!prodCat || !prodSub) return;
      var selected = prodCat.value ? Number(prodCat.value) : 0;
      [].forEach.call(prodSub.options, function(opt){
        var parent = Number(opt.getAttribute('data-parent')) || 0;
        if (!parent) { opt.hidden = false; return; }
        opt.hidden = selected && parent !== selected;
        if (opt.hidden && opt.selected) { prodSub.value = ''; }
      });
    }
    if (prodCat) {
      prodCat.addEventListener('change', filterProductSubs);
      filterProductSubs();
    }

    var methodSelect = document.getElementById('image_method_select');
    var blockUpload = document.getElementById('img_block_upload');
    var blockUrl = document.getElementById('img_block_url');
    var blockGdrive = document.getElementById('img_block_gdrive');
    var inputFile = document.getElementById('image_input');
    var inputUrl = document.getElementById('image_url_input');
    var inputG = document.getElementById('gdrive_url_input');
    var preview = document.getElementById('image_preview');
    var previewPlaceholder = document.getElementById('image_preview_placeholder');

    function setPreview(src){
      if (preview && previewPlaceholder){
        if (src){
          preview.src = src;
          preview.style.display = 'block';
          previewPlaceholder.style.display = 'none';
        } else {
          preview.src = '';
          preview.style.display = 'none';
          previewPlaceholder.style.display = 'block';
        }
      }
    }

    function setMethod(val){
      if (!blockUpload) return;
      blockUpload.style.display = (val === 'upload') ? '' : 'none';
      blockUrl.style.display = (val === 'url') ? '' : 'none';
      blockGdrive.style.display = (val === 'gdrive') ? '' : 'none';
      if (val !== 'upload' && inputFile){ inputFile.value = ''; }
      if (val !== 'url' && inputUrl){ inputUrl.value = ''; }
      if (val !== 'gdrive' && inputG){ inputG.value = ''; }
      setPreview('');
    }

    if (methodSelect){
      methodSelect.addEventListener('change', function(){ setMethod(this.value); });
      setMethod(methodSelect.value || 'upload');
    }

    if (inputFile){
      inputFile.addEventListener('change', function(e){
        var file = e.target.files && e.target.files[0];
        if (!file){ setPreview(''); return; }
        var reader = new FileReader();
        reader.onload = function(ev){ setPreview(ev.target.result); };
        reader.readAsDataURL(file);
      });
    }
    function handleLinkPreview(evt){
      var val = evt.target.value.trim();
      if (val && /^https?:\/\//i.test(val)){ setPreview(val); }
      else { setPreview(''); }
    }
    if (inputUrl){ inputUrl.addEventListener('input', handleLinkPreview); }
    if (inputG){ inputG.addEventListener('input', handleLinkPreview); }

    // Auto-submit banner/logo upload forms as soon as a file is picked
    var autoUploadInputs = document.querySelectorAll('.auto-submit-upload');
    autoUploadInputs.forEach(function(inp){
      inp.addEventListener('change', function(){
        if (!inp.files || inp.files.length === 0) { return; }
        var form = inp.closest('form');
        if (form) { form.submit(); }
      });
    });
  })();
</script>
