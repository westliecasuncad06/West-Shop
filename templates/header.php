<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
$u = current_user();
$notifCount = $u ? unread_notifications_count((int)$u['user_id']) : 0;
$chatBadges = $u ? chat_unread_breakdown((int)$u['user_id'], $u['role']) : ['primary' => 0, 'support' => 0];
$primaryChatCount = (int)($chatBadges['primary'] ?? 0);
$supportChatCount = (int)($chatBadges['support'] ?? 0);
$supportLink = 'seller/chat_admin.php';
$chatHubLink = 'buyer/chat.php';
$chatMenu = [];
if ($u) {
  if ($u['role'] === 'seller') {
    $supportLink = 'seller/chat_admin.php';
    $chatHubLink = 'seller/chat.php';
    $chatMenu[] = ['label' => 'Buyer Messages', 'href' => 'seller/chat.php', 'count' => $primaryChatCount];
    $chatMenu[] = ['label' => 'Support Chat', 'href' => 'seller/chat_admin.php', 'count' => $supportChatCount];
  } elseif ($u['role'] === 'admin') {
    $supportLink = 'admin/chat.php';
    $chatHubLink = 'admin/chat.php';
    $chatMenu[] = ['label' => 'Support Chat', 'href' => 'admin/chat.php', 'count' => max($supportChatCount, $primaryChatCount)];
  } else {
    $supportLink = 'buyer/chat.php';
    $chatHubLink = 'buyer/chat.php';
    $chatMenu[] = ['label' => 'Buyer Chat', 'href' => 'buyer/chat.php', 'count' => max($primaryChatCount, $supportChatCount)];
  }
}
$globalSearchTerm = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$globalSearchType = isset($_GET['type']) && strtolower((string)$_GET['type']) === 'stores' ? 'stores' : 'products';
$mainNavItems = [
  ['label' => 'Home', 'href' => base_url('index.php')],
  ['label' => 'Discover', 'href' => base_url('public/product.php?id=1')]
];
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e(APP_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo e(base_url('assets/css/theme.css')); ?>" rel="stylesheet">
</head>
<body class="bg-soft">
<header class="site-header">
  <div class="nav-top">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2 small">
      <div class="d-flex align-items-center gap-2 text-muted">
        <i class="bi bi-lightning-charge-fill text-warning"></i>
        <span>Same-day support for premium sellers.</span>
      </div>
      <div class="d-flex align-items-center gap-3 nav-top-links">
        <a href="<?php echo e(base_url($supportLink)); ?>" class="text-decoration-none">Support</a>
        <a href="<?php echo e(base_url($chatHubLink)); ?>" class="text-decoration-none">Chat</a>
        <a href="mailto:support@example.com" class="text-decoration-none">support@example.com</a>
      </div>
    </div>
  </div>
  <nav class="navbar navbar-expand-lg nav-glass main-navbar sticky-top">
    <div class="container">
      <div class="navbar-left d-flex align-items-center gap-3">
        <a class="navbar-brand" href="<?php echo e(base_url('index.php')); ?>"><?php echo e(APP_NAME); ?></a>
        <ul class="navbar-nav primary-nav d-none d-lg-flex flex-row gap-1">
          <?php foreach ($mainNavItems as $item): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo e($item['href']); ?>"><?php echo e($item['label']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <div class="navbar-panels">
          <ul class="navbar-nav primary-nav flex-column gap-2 d-lg-none">
            <?php foreach ($mainNavItems as $item): ?>
              <li class="nav-item"><a class="nav-link" href="<?php echo e($item['href']); ?>"><?php echo e($item['label']); ?></a></li>
            <?php endforeach; ?>
          </ul>
          <div class="nav-utility">
            <div class="nav-search-shell">
              <form class="nav-search nav-search-inline nav-search-modern d-flex flex-column flex-lg-row align-items-stretch" action="<?php echo e(base_url('public/search.php')); ?>" method="get">
                <div class="nav-search-field nav-search-pill d-flex flex-column flex-md-row flex-grow-1">
                  <div class="nav-search-chip d-flex align-items-center gap-2">
                    <i class="bi bi-ui-checks-grid text-primary"></i>
                    <select name="type" class="form-select form-select-sm nav-search-type">
                      <option value="products" <?php echo $globalSearchType==='products'?'selected':''; ?>>Products</option>
                      <option value="stores" <?php echo $globalSearchType==='stores'?'selected':''; ?>>Stores</option>
                    </select>
                  </div>
                  <div class="nav-search-input-wrap flex-grow-1">
                    <i class="bi bi-search text-muted"></i>
                    <input type="search" name="q" class="form-control form-control-sm nav-search-input" placeholder="Find products, stores, or categories" value="<?php echo e($globalSearchTerm); ?>">
                  </div>
                </div>
              </form>
            </div>
            <div class="nav-quick-actions nav-action-group">
              <?php if(!$u || ($u && $u['role']==='buyer')): ?>
                <a class="btn-icon btn-icon-compact" href="<?php echo e(base_url('buyer/cart.php')); ?>" title="Cart">
                  <i class="bi bi-bag"></i>
                  <span class="label">Cart</span>
                </a>
              <?php endif; ?>
              <?php if($u): ?>
                <a class="btn-icon btn-icon-compact position-relative" href="<?php echo e(base_url('notifications.php')); ?>" title="Notifications">
                  <i class="bi bi-bell"></i>
                  <?php if($notifCount>0): ?><span class="icon-badge" data-notif-count><?php echo (int)$notifCount; ?></span><?php endif; ?>
                </a>
                <div class="dropdown nav-profile">
                  <button class="btn-icon nav-profile-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                    <span class="avatar initials"><?php echo strtoupper(substr($u['name'],0,1)); ?></span>
                    <span class="profile-label">
                      <span class="d-block small text-muted">Account</span>
                      <strong><?php echo e($u['name']); ?></strong>
                    </span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow">
                    <?php if($u['role']==='buyer'): ?>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('dashboards/buyer/index.php')); ?>">Buyer Dashboard</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('buyer/orders.php')); ?>">My Orders</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('buyer/chat.php')); ?>">Chat</a></li>
                    <?php elseif($u['role']==='seller'): ?>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('seller/index.php')); ?>">Seller Dashboard</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('seller/orders.php')); ?>">Orders</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('seller/chat.php')); ?>">Buyer Messages</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('seller/chat_admin.php')); ?>">Support Chat</a></li>
                    <?php else: ?>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('dashboards/admin/index.php')); ?>">Admin Dashboard</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('admin/sellers.php')); ?>">Sellers</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('admin/categories.php')); ?>">Categories</a></li>
                      <li><a class="dropdown-item" href="<?php echo e(base_url('admin/chat.php')); ?>">Support Chat</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo e(base_url('logout.php')); ?>">Logout</a></li>
                  </ul>
                </div>
              <?php else: ?>
                <div class="nav-auth-buttons">
                  <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(base_url('login.php')); ?>">Login</a>
                  <a class="btn btn-sm btn-primary" href="<?php echo e(base_url('register.php')); ?>">Sign Up</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
<?php if($u): ?>
  <div class="floating-chat dropup">
    <button class="floating-chat-btn shadow-lg" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Open messages">
      <i class="bi bi-chat-dots"></i>
      <span>Messages</span>
      <?php if($primaryChatCount>0): ?><span class="floating-badge" data-chat-count><?php echo $primaryChatCount; ?></span><?php endif; ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end floating-chat-menu shadow-lg">
      <li>
        <a class="dropdown-item" href="<?php echo e(base_url($chatHubLink)); ?>">
          <div class="d-flex flex-column">
            <strong>Buyer Messages</strong>
            <small class="text-muted">Talk to customers</small>
          </div>
        </a>
      </li>
      <li>
        <a class="dropdown-item" href="<?php echo e(base_url($supportLink)); ?>">
          <div class="d-flex flex-column">
            <strong>Support Chat</strong>
            <small class="text-muted">Reach the help desk</small>
          </div>
        </a>
      </li>
    </ul>
  </div>
<?php endif; ?>
<main class="page-shell container py-4">
<?php if($f = get_flash()): ?>
  <div class="alert alert-<?php echo e($f['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo e($f['msg']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
