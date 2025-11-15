<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$statusFilter = $_GET['status'] ?? 'all';
$searchTerm = trim($_GET['q'] ?? '');
$allowedStatuses = ['approved','pending','rejected'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $uid = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $returnStatus = $_POST['status_filter'] ?? 'all';
    $returnSearch = trim($_POST['search_filter'] ?? '');
    if (in_array($action, ['approved','rejected'], true)) {
        $stmt = $pdo->prepare("UPDATE users SET status=? WHERE user_id=? AND role='seller'");
        $stmt->execute([$action, $uid]);
        set_flash('success', 'Seller status updated.');
    }
    $query = [];
    if ($returnStatus && $returnStatus !== 'all') {
        $query['status'] = $returnStatus;
    }
    if ($returnSearch !== '') {
        $query['q'] = $returnSearch;
    }
    $redirect = 'admin/sellers.php';
    if ($query) {
        $redirect .= '?' . http_build_query($query);
    }
    redirect($redirect);
}

$countsStmt = $pdo->query("SELECT status, COUNT(*) AS total FROM users WHERE role='seller' GROUP BY status");
$statusCounts = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
$totalSellers = 0;
foreach ($countsStmt->fetchAll() as $row) {
    $statusKey = $row['status'] ?? 'pending';
    $total = (int)$row['total'];
    $totalSellers += $total;
    if (!array_key_exists($statusKey, $statusCounts)) {
        $statusCounts[$statusKey] = 0;
    }
    $statusCounts[$statusKey] = $total;
}

$sql = "SELECT user_id, name, email, status, created_at FROM users WHERE role='seller'";
$conditions = [];
$params = [];
if ($statusFilter !== 'all' && in_array($statusFilter, $allowedStatuses, true)) {
    $conditions[] = 'status = ?';
    $params[] = $statusFilter;
}
if ($searchTerm !== '') {
    $conditions[] = '(name LIKE ? OR email LIKE ?)';
    $like = '%' . $searchTerm . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($conditions) {
    $sql .= ' AND ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sellers = $stmt->fetchAll();
$filteredCount = count($sellers);

function seller_tenure_label(string $date): string {
    try {
        $created = new DateTime($date);
        $now = new DateTime();
        $diff = $created->diff($now);
        if ($diff->y > 0) {
            return sprintf('%dyr %dmo', $diff->y, $diff->m);
        }
        if ($diff->m > 0) {
            return sprintf('%dmo %dd', $diff->m, $diff->d);
        }
        return sprintf('%dd', max(1, $diff->d));
    } catch (Exception $e) {
        return '—';
    }
}

function seller_status_class(string $status): string {
  $status = strtolower($status);
    $map = [
        'approved' => 'status-pill--success',
        'rejected' => 'status-pill--danger',
        'pending' => 'status-pill--warning',
    ];
    return $map[$status] ?? 'status-pill--neutral';
}

include __DIR__ . '/../templates/header.php';
?>
<section class="section-shell">
  <div class="section-heading pb-0">
    <div>
      <p class="section-heading__eyebrow mb-1">Community</p>
      <h1 class="section-heading__title mb-0">Seller directory</h1>
      <p class="page-subtitle mt-2">Approve new partners, monitor statuses, and keep communication crisp.</p>
    </div>
  </div>

  <div class="seller-stats-grid">
    <div class="seller-stat card">
      <p class="seller-stat__label">Total sellers</p>
      <div class="seller-stat__value"><?php echo number_format($totalSellers); ?></div>
      <span class="seller-stat__hint text-muted">All time</span>
    </div>
    <div class="seller-stat card">
      <p class="seller-stat__label">Approved</p>
      <div class="seller-stat__value text-success"><?php echo number_format($statusCounts['approved'] ?? 0); ?></div>
      <span class="seller-stat__hint text-muted">Ready to sell</span>
    </div>
    <div class="seller-stat card">
      <p class="seller-stat__label">Pending</p>
      <div class="seller-stat__value text-warning"><?php echo number_format($statusCounts['pending'] ?? 0); ?></div>
      <span class="seller-stat__hint text-muted">Awaiting review</span>
    </div>
    <div class="seller-stat card">
      <p class="seller-stat__label">Rejected</p>
      <div class="seller-stat__value text-danger"><?php echo number_format($statusCounts['rejected'] ?? 0); ?></div>
      <span class="seller-stat__hint text-muted">Need follow-up</span>
    </div>
  </div>

  <div class="seller-filters card">
    <form class="row g-3 align-items-center" method="get">
      <div class="col-lg-6">
        <label class="form-label small text-muted mb-1" for="sellerSearch">Search sellers</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input id="sellerSearch" class="form-control" type="text" name="q" placeholder="Name or email" value="<?php echo e($searchTerm); ?>">
        </div>
      </div>
      <div class="col-lg-3">
        <label class="form-label small text-muted mb-1" for="sellerStatus">Status</label>
        <select id="sellerStatus" class="form-select" name="status">
          <option value="all" <?php echo $statusFilter==='all'?'selected':''; ?>>All statuses</option>
          <?php foreach ($allowedStatuses as $statusOpt): ?>
            <option value="<?php echo e($statusOpt); ?>" <?php echo $statusFilter===$statusOpt?'selected':''; ?>><?php echo ucfirst($statusOpt); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100">Apply</button>
        <a class="btn btn-outline-secondary" href="<?php echo e(base_url('admin/sellers.php')); ?>">Reset</a>
      </div>
    </form>
  </div>

  <div class="seller-directory-grid">
    <?php foreach ($sellers as $s): ?>
      <?php $statusLabel = $s['status'] ?: 'pending'; ?>
      <div class="seller-card card h-100">
        <div class="seller-card__top">
          <div class="d-flex align-items-center gap-3">
            <div class="seller-avatar"><?php echo strtoupper(substr($s['name'], 0, 2)); ?></div>
            <div>
              <h5 class="mb-1"><?php echo e($s['name']); ?></h5>
              <p class="text-muted small mb-0"><?php echo e($s['email']); ?></p>
            </div>
          </div>
          <span class="status-pill <?php echo seller_status_class((string)$statusLabel); ?>"><?php echo ucfirst($statusLabel); ?></span>
        </div>
        <div class="seller-card__meta">
          <div>
            <p class="seller-meta__label">Joined</p>
            <p class="seller-meta__value"><?php echo date('M d, Y', strtotime($s['created_at'])); ?></p>
          </div>
          <div>
            <p class="seller-meta__label">Tenure</p>
            <p class="seller-meta__value"><?php echo seller_tenure_label($s['created_at']); ?></p>
          </div>
          <div>
            <p class="seller-meta__label">Status</p>
            <p class="seller-meta__value text-capitalize"><?php echo e($statusLabel); ?></p>
          </div>
        </div>
        <div class="seller-card__actions">
          <form method="post" class="d-flex flex-wrap gap-2">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="user_id" value="<?php echo (int)$s['user_id']; ?>">
            <input type="hidden" name="status_filter" value="<?php echo e($statusFilter); ?>">
            <input type="hidden" name="search_filter" value="<?php echo e($searchTerm); ?>">
            <button name="action" value="approved" class="btn btn-success flex-fill">Approve</button>
            <button name="action" value="rejected" class="btn btn-outline-danger flex-fill">Reject</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$sellers): ?>
      <div class="empty-panel text-center text-muted">
        <i class="bi bi-people fs-1 d-block mb-2"></i>
        <p class="mb-0">No sellers match this view. Try adjusting filters.</p>
      </div>
    <?php endif; ?>
  </div>
  <p class="text-muted small mt-3">Showing <?php echo $filteredCount; ?> of <?php echo $totalSellers; ?> sellers.</p>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
