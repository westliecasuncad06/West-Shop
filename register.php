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
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-6">
    <div class="card p-4">
      <h4 class="mb-3">Create your account</h4>
      <form method="post">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label">I am a</label>
          <select name="role" class="form-select">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller (requires admin approval)</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password2" class="form-control" required>
          </div>
        </div>
        <button class="btn btn-primary w-100">Sign Up</button>
      </form>
      <p class="mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
