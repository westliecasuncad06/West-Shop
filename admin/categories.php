<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Create / Update / Delete categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories(name, description) VALUES (?,?)');
            $stmt->execute([$name, $desc]);
            set_flash('success', 'Category created.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM categories WHERE category_id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Category deleted.');
    }
}

$cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<h4 class="mb-3">Categories</h4>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6>Add New</h6>
      <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <div class="mb-2"><input class="form-control" name="name" placeholder="Name" required></div>
        <div class="mb-2"><textarea class="form-control" name="description" placeholder="Description"></textarea></div>
        <button class="btn btn-primary">Create</button>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <h6>All Categories</h6>
      <ul class="list-group list-group-flush">
        <?php foreach($cats as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?php echo e($c['name']); ?></span>
            <form method="post" class="mb-0">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="category_id" value="<?php echo (int)$c['category_id']; ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </li>
        <?php endforeach; ?>
        <?php if(!$cats): ?><li class="list-group-item text-muted">No categories yet.</li><?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
