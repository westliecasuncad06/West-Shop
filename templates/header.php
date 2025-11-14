<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
$u = current_user();
$notifCount = $u ? unread_notifications_count((int)$u['user_id']) : 0;
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
        <a href="<?php echo e(base_url('seller/chat_admin.php')); ?>" class="text-decoration-none">Support</a>
        <a href="<?php echo e(base_url('buyer/chat.php')); ?>" class="text-decoration-none">Buyer Chat</a>
        <a href="mailto:support@example.com" class="text-decoration-none">support@example.com</a>
      </div>
    </div>
  </div>
  <nav class="navbar navbar-expand-lg nav-glass sticky-top">
    <div class="container">
      <div class="d-flex align-items-center gap-2">
        <a class="navbar-brand" href="<?php echo e(base_url('index.php')); ?>"><?php echo e(APP_NAME); ?></a>
        <span class="nav-sep"></span>
        <span class="text-muted small d-none d-md-inline">Curated marketplace for modern buyers</span>
      </div>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample" aria-controls="navbarsExample" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarsExample">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('index.php')); ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('public/product.php?id=1')); ?>">Discover</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('buyer/cart.php')); ?>">Cart</a></li>
          <?php if($u): ?>
            <li class="nav-item">
              <a class="nav-link nav-pill" href="<?php echo e(base_url('seller/index.php')); ?>">Seller Portal</a>
            </li>
            <li class="nav-item position-relative">
              <a class="nav-link" href="<?php echo e(base_url('notifications.php')); ?>">
                <i class="bi bi-bell"></i>
                <?php if($notifCount>0): ?><span class="notif-dot"><?php echo (int)$notifCount; ?></span><?php endif; ?>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                <span class="avatar initials"><?php echo strtoupper(substr($u['name'],0,1)); ?></span>
                <span><?php echo e($u['name']); ?></span>
              </a>
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
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo e(base_url('logout.php')); ?>">Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="btn btn-light nav-pill" href="<?php echo e(base_url('login.php')); ?>">Login</a></li>
            <li class="nav-item"><a class="btn btn-primary nav-pill" href="<?php echo e(base_url('register.php')); ?>">Sign Up</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
</header>
<main class="page-shell container py-4">
<?php if($f = get_flash()): ?>
  <div class="alert alert-<?php echo e($f['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo e($f['msg']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
