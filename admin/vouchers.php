<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'fixed';
        $value = (float)($_POST['value'] ?? 0);
        $expires = $_POST['expires_at'] ?: null;
        $max = (int)($_POST['max_uses'] ?? 0);
        if ($code !== '') {
            $s = $pdo->prepare('INSERT INTO coupons(code,type,value,expires_at,max_uses) VALUES (?,?,?,?,?)');
            $s->execute([$code, $type, $value, $expires, $max]);
            set_flash('success', 'Coupon created.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['coupon_id'] ?? 0);
        $pdo->prepare('DELETE FROM coupons WHERE coupon_id=?')->execute([$id]);
        set_flash('success', 'Coupon deleted.');
    }
}

$coupons = $pdo->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();
include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Vouchers / Coupons</h4>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6>Create Coupon</h6>
      <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <div class="mb-2"><input name="code" class="form-control" placeholder="Code e.g. WELCOME10" required></div>
        <div class="mb-2 d-flex gap-2">
          <select name="type" class="form-select"><option value="fixed">Fixed</option><option value="percent">Percent</option></select>
          <input name="value" type="number" step="0.01" class="form-control" placeholder="Value" required>
        </div>
        <div class="mb-2"><input name="expires_at" type="date" class="form-control"></div>
        <div class="mb-2"><input name="max_uses" type="number" class="form-control" placeholder="Max uses (0 = unlimited)"></div>
        <button class="btn btn-primary">Create</button>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <h6>Existing Coupons</h6>
      <ul class="list-group list-group-flush">
        <?php foreach($coupons as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong><?php echo e($c['code']); ?></strong> — <?php echo e($c['type']); ?> <?php echo e($c['value']); ?>
              <div class="small text-muted">Expires: <?php echo e($c['expires_at'] ?? 'Never'); ?> • Used: <?php echo (int)$c['used_count']; ?>/<?php echo (int)$c['max_uses']; ?></div>
            </div>
            <form method="post" class="mb-0">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="coupon_id" value="<?php echo (int)$c['coupon_id']; ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
        <?php if(!$coupons): ?><li class="list-group-item text-muted">No coupons yet.</li><?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
