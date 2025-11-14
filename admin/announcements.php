<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Ensure announcements table exists for installs that have not migrated yet.
$pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
  announcement_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  audience VARCHAR(20) NOT NULL DEFAULT 'all',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  author_id INT NULL,
  INDEX (audience)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $audience = $_POST['audience'] ?? 'all';
        if ($title !== '' && $body !== '') {
            $stmt = $pdo->prepare('INSERT INTO announcements(title, body, audience, author_id) VALUES (?,?,?,?)');
            $stmt->execute([$title, $body, $audience, current_user()['user_id'] ?? null]);
            set_flash('success', 'Announcement published.');
        } else {
            set_flash('danger', 'Title and message are required.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM announcements WHERE announcement_id = ?');
            $stmt->execute([$id]);
            set_flash('success', 'Announcement removed.');
        }
    }
}

$announcements = $pdo->query('SELECT a.*, u.name AS author_name FROM announcements a LEFT JOIN users u ON u.user_id = a.author_id ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>

<section class="section-shell">
  <div class="section-heading">
    <div>
      <p class="section-heading__eyebrow mb-1">Broadcast</p>
      <h1 class="section-heading__title mb-0">Announcements</h1>
      <p class="page-subtitle mt-2">Share product drops, maintenance windows, or incentives with your community.</p>
    </div>
  </div>
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="surface-card h-100">
        <h5 class="mb-3">Create announcement</h5>
        <form method="post" class="d-flex flex-column gap-3">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="create">
          <div>
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Flash sale" required>
          </div>
          <div>
            <label class="form-label">Audience</label>
            <select name="audience" class="form-select">
              <option value="all">All users</option>
              <option value="buyers">Buyers</option>
              <option value="sellers">Sellers</option>
            </select>
          </div>
          <div>
            <label class="form-label">Message</label>
            <textarea name="body" class="form-control" rows="4" placeholder="Write your announcement" required></textarea>
          </div>
          <button class="btn btn-primary">Publish</button>
        </form>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="surface-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Recent announcements</h5>
          <span class="badge-soft"><?php echo count($announcements); ?> live</span>
        </div>
        <?php if($announcements): ?>
          <ul class="stacked-list">
            <?php foreach($announcements as $a): ?>
              <li class="stacked-list__item">
                <div>
                  <div class="fw-semibold"><?php echo e($a['title']); ?></div>
                  <div class="text-muted-soft small mb-1">Audience: <?php echo ucfirst($a['audience']); ?> · <?php echo date('M d, Y g:i a', strtotime($a['created_at'])); ?></div>
                  <p class="mb-0 small"><?php echo e($a['body']); ?></p>
                </div>
                <div class="text-end">
                  <div class="small text-muted-soft mb-2">By <?php echo e($a['author_name'] ?? 'System'); ?></div>
                  <form method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="announcement_id" value="<?php echo (int)$a['announcement_id']; ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state-card text-center">
            <h6 class="mb-1">No announcements yet</h6>
            <p class="text-muted-soft mb-0">Publish your first broadcast to keep everyone in the loop.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
