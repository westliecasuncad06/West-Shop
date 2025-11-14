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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo e(base_url('assets/css/theme.css')); ?>" rel="stylesheet">
  <style>body{font-family:'Poppins',sans-serif}</style>
</head>
<body class="bg-soft">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-semibold text-primary" href="<?php echo e(base_url('index.php')); ?>"><?php echo e(APP_NAME); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample" aria-controls="navbarsExample" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExample">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('index.php')); ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('buyer/cart.php')); ?>">Cart</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if($u): ?>
          <li class="nav-item me-3">
            <a class="nav-link position-relative" href="<?php echo e(base_url('notifications.php')); ?>">
              <i class="bi bi-bell"></i>
              <?php if($notifCount>0): ?><span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle"><?php echo (int)$notifCount; ?></span><?php endif; ?>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Hello, <?php echo e($u['name']); ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if($u['role']==='buyer'): ?>
                <li><a class="dropdown-item" href="<?php echo e(base_url('dashboards/buyer/index.php')); ?>">Buyer Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo e(base_url('buyer/orders.php')); ?>">My Orders</a></li>
                <li><a class="dropdown-item" href="<?php echo e(base_url('buyer/chat.php')); ?>">Chat</a></li>
              <?php elseif($u['role']==='seller'): ?>
                <li><a class="dropdown-item" href="<?php echo e(base_url('seller/index.php')); ?>">Seller Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo e(base_url('seller/products.php')); ?>">Products</a></li>
                <li><a class="dropdown-item" href="<?php echo e(base_url('seller/orders.php')); ?>">Orders</a></li>
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
          <li class="nav-item"><a class="btn btn-primary rounded-pill px-3" href="<?php echo e(base_url('login.php')); ?>">Login</a></li>
          <li class="nav-item ms-2"><a class="btn btn-outline-primary rounded-pill px-3" href="<?php echo e(base_url('register.php')); ?>">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4">
<?php if($f = get_flash()): ?>
  <div class="alert alert-<?php echo e($f['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo e($f['msg']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
