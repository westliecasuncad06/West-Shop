<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Invalid CSRF token'); }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($email, $password)) {
        $u = current_user();
        if ($u['role'] === 'admin') redirect('dashboards/admin/index.php');
        if ($u['role'] === 'seller') redirect('dashboards/seller/index.php');
        redirect('dashboards/buyer/index.php');
    } else {
        set_flash('danger', 'Invalid credentials or seller not approved.');
    }
}

include __DIR__ . '/templates/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card p-4">
      <h4 class="mb-3">Welcome back</h4>
      <form method="post">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
      </form>
      <p class="mt-3 mb-0">No account? <a href="register.php">Create one</a></p>
    </div>
  </div>
  <div class="col-12 text-center mt-3">
    <span class="badge badge-accent px-3 py-2">Safe, simple, and joyful shopping ✨</span>
  </div>
  </div>
<?php include __DIR__ . '/templates/footer.php'; ?>
