<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error = null; $success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Invalid CSRF token'); }
    $role = $_POST['role'] ?? 'buyer';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $res = register_user($role, $name, $email, $password);
        if ($res['ok']) {
            $success = ($role === 'seller') ? 'Registration submitted. Await admin approval.' : 'Registration successful. You may now login.';
        } else {
            $error = $res['error'] ?? 'Registration failed.';
        }
    }
    if ($error) set_flash('danger', $error); else if ($success) set_flash('success', $success);
}

include __DIR__ . '/templates/header.php';
?>

<section class="auth-shell">
  <div class="auth-hero">
    <span class="badge-soft mb-3">Join West-Shop</span>
    <h2 class="auth-hero__title mb-3">Create an account crafted for modern buying & selling</h2>
    <p class="text-muted-soft mb-4">Unlock curated storefronts, transparent seller vetting, and joyful checkout experiences.</p>
    <ul class="auth-hero__list mb-4">
      <li><i class="bi bi-stars text-warning"></i> Curated categories & inspiration</li>
      <li><i class="bi bi-people text-primary"></i> Trusted community with verified sellers</li>
      <li><i class="bi bi-graph-up-arrow text-success"></i> Insights and dashboards for sellers</li>
    </ul>
    <div class="auth-badges">
      <span class="auth-badge"><i class="bi bi-patch-check"></i> Admin approved sellers</span>
      <span class="auth-badge"><i class="bi bi-cash-coin"></i> Secure transactions</span>
    </div>
  </div>
  <div class="auth-card">
    <div class="surface-card">
      <h4 class="mb-3">Create your account</h4>
      <form method="post" class="d-flex flex-column gap-3">
        <?php echo csrf_field(); ?>
        <div>
          <label class="form-label">I am a</label>
          <select name="role" class="form-select">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller (requires admin approval)</option>
          </select>
        </div>
        <div>
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="Jane Mercado" required>
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password2" class="form-control" required>
          </div>
        </div>
        <button class="btn btn-primary w-100">Sign Up</button>
      </form>
      <p class="mt-4 mb-0 text-center">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
