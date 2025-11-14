<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $uid = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['approved','rejected'], true)) {
        $stmt = $pdo->prepare("UPDATE users SET status=? WHERE user_id=? AND role='seller'");
        $stmt->execute([$action, $uid]);
        set_flash('success', 'Seller status updated.');
    }
}

$sellers = $pdo->query("SELECT user_id, name, email, status, created_at FROM users WHERE role='seller' ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Sellers</h4>
<div class="table-responsive">
<table class="table align-middle">
  <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach($sellers as $s): ?>
      <tr>
        <td><?php echo e($s['name']); ?></td>
        <td><?php echo e($s['email']); ?></td>
        <td><?php echo e($s['status']); ?></td>
        <td><?php echo e($s['created_at']); ?></td>
        <td>
          <form method="post" class="d-inline">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="user_id" value="<?php echo (int)$s['user_id']; ?>">
            <button name="action" value="approved" class="btn btn-sm btn-success">Approve</button>
            <button name="action" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$sellers): ?><tr><td colspan="5" class="text-center text-muted">No sellers yet.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
