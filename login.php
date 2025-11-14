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

<section class="auth-shell">
  <div class="auth-hero">
    <span class="badge-soft mb-3">Marketplace essentials</span>
    <h2 class="auth-hero__title mb-3">Log in to keep shopping with confidence</h2>
    <p class="text-muted-soft mb-4">Track orders, message sellers, and enjoy secure checkout flows designed for modern buyers.</p>
    <ul class="auth-hero__list mb-4">
      <li><i class="bi bi-shield-check text-primary"></i> Buyer protection on every transaction</li>
      <li><i class="bi bi-lightning-charge text-warning"></i> Faster checkout with saved preferences</li>
      <li><i class="bi bi-chat-dots text-success"></i> Seamless chat for clarifications</li>
    </ul>
    <div class="auth-badges">
      <span class="auth-badge"><i class="bi bi-lock"></i> 2FA-ready</span>
      <span class="auth-badge"><i class="bi bi-emoji-smile"></i> Human support</span>
    </div>
  </div>
  <div class="auth-card">
    <div class="surface-card">
      <h4 class="mb-3">Welcome back</h4>
      <form method="post" class="d-flex flex-column gap-3">
        <?php echo csrf_field(); ?>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div>
          <div class="d-flex justify-content-between align-items-center">
            <label class="form-label mb-0">Password</label>
            <a href="#" class="small text-decoration-none">Forgot?</a>
          </div>
          <input type="password" name="password" class="form-control" placeholder="••••••" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
      </form>
      <p class="mt-4 mb-0 text-center">No account yet? <a href="register.php">Create one</a></p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
