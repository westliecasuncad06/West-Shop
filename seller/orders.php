<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('seller');
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify()) { http_response_code(400); exit('Bad CSRF'); }
  $action = $_POST['action'] ?? '';
  $orderId = (int)($_POST['order_id'] ?? 0);
  $isCancel = ($action === 'Cancelled');
  if (in_array($action, ['Confirmed','Shipped','Delivered','Cancelled'], true) && $orderId > 0) {
    $stmt = $pdo->prepare('UPDATE orders o
      SET o.status = ?, o.cancel_reason = NULL
      WHERE o.order_id = ? AND EXISTS (
        SELECT 1 FROM order_items oi JOIN products p ON oi.product_id=p.product_id
        WHERE oi.order_id=o.order_id AND p.seller_id=?
      )');
    $stmt->execute([$action, $orderId, $u['user_id']]);
    if ($stmt->rowCount() > 0) {
      $buyerStmt = $pdo->prepare('SELECT buyer_id FROM orders WHERE order_id = ? LIMIT 1');
      $buyerStmt->execute([$orderId]);
      $buyerId = (int)($buyerStmt->fetchColumn() ?: 0);
      if ($buyerId) {
        if ($isCancel) {
          create_notification($buyerId, sprintf('Order #%d was cancelled by the seller.', $orderId));
          $profile = get_seller_profile((int)$u['user_id']);
          $storeName = trim($profile['shop_name'] ?? '') ?: $u['name'];
          $msg = sprintf('%s cancelled order #%d. Reach out anytime if you have questions.', $storeName, $orderId);
          send_chat_message((int)$u['user_id'], $buyerId, $msg);
        } else {
          create_notification($buyerId, sprintf('Order #%d status updated to %s.', $orderId, $action));
          if ($action === 'Confirmed') {
            $profile = get_seller_profile((int)$u['user_id']);
            $storeName = trim($profile['shop_name'] ?? '') ?: $u['name'];
            $msg = sprintf('%s confirmed your order #%d. We will notify you once it ships.', $storeName, $orderId);
            send_chat_message((int)$u['user_id'], $buyerId, $msg);
          }
        }
      }
      set_flash('success', sprintf('Order #%d updated to %s.', $orderId, $action));
    } else {
      set_flash('warning', 'No matching order to update.');
    }
  }
}

$ordersStmt = $pdo->prepare('SELECT o.*, u.name AS buyer_name,
    GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ", ") AS items_summary,
    SUM(oi.quantity) AS total_items
  FROM orders o
  JOIN order_items oi ON o.order_id = oi.order_id
  JOIN products p ON oi.product_id = p.product_id
  JOIN users u ON o.buyer_id = u.user_id
  WHERE p.seller_id = ?
  GROUP BY o.order_id
  ORDER BY o.created_at DESC');
$ordersStmt->execute([$u['user_id']]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$statusMeta = [
  'Pending' => ['icon' => 'bi-hourglass-split', 'label' => 'Awaiting action', 'pill' => 'status-pill--warning'],
  'Confirmed' => ['icon' => 'bi-check2-circle', 'label' => 'Pack in progress', 'pill' => 'status-pill--neutral'],
  'Shipped' => ['icon' => 'bi-truck', 'label' => 'On the way', 'pill' => 'status-pill--success'],
  'Delivered' => ['icon' => 'bi-box-seam', 'label' => 'Completed drops', 'pill' => 'status-pill--success'],
  'Cancelled' => ['icon' => 'bi-x-octagon', 'label' => 'Voided', 'pill' => 'status-pill--danger'],
];

$statusCounts = array_fill_keys(array_keys($statusMeta), 0);
foreach ($orders as &$order) {
  $status = $order['status'] ?? 'Pending';
  if (!isset($statusCounts[$status])) {
    $statusCounts[$status] = 0;
  }
  $statusCounts[$status]++;
  $order['buyer_name'] = $order['buyer_name'] ?? 'Buyer';
  $order['items_summary'] = $order['items_summary'] ?? '';
  $order['total_items'] = (int)($order['total_items'] ?? 0);
}
unset($order);

$totalOrders = count($orders);
$openOrders = max(0, $totalOrders - (($statusCounts['Delivered'] ?? 0) + ($statusCounts['Cancelled'] ?? 0)));

include __DIR__ . '/../templates/header.php';
?>
<section class="section-shell">
  <div class="section-heading">
    <div>
      <p class="section-heading__eyebrow mb-1">Seller workspace</p>
      <h1 class="section-heading__title mb-0">Orders overview</h1>
      <p class="page-subtitle mt-2">Keep buyers in the loop and move orders through each milestone seamlessly.</p>
    </div>
    <div class="section-heading__actions flex-wrap">
      <span class="badge-chip"><i class="bi bi-bag-check"></i> <?php echo $totalOrders; ?> total</span>
      <span class="badge-chip badge-mint"><i class="bi bi-lightning-charge"></i> <?php echo $openOrders; ?> active</span>
      <a href="<?php echo e(base_url('seller/products.php')); ?>" class="pill-link"><i class="bi bi-grid"></i> Manage products</a>
    </div>
  </div>

  <div class="seller-stats-grid mb-4">
    <?php foreach ($statusMeta as $statusKey => $meta): ?>
      <div class="seller-stat-card">
        <div class="seller-stat-icon"><i class="bi <?php echo $meta['icon']; ?>"></i></div>
        <div>
          <div class="seller-stat-label"><?php echo e(strtoupper($statusKey)); ?></div>
          <div class="seller-stat__value fs-3 mb-1"><?php echo $statusCounts[$statusKey] ?? 0; ?></div>
          <small class="text-muted"><?php echo e($meta['label']); ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if($orders): ?>
    <div class="seller-orders-grid">
      <?php foreach($orders as $o):
        $statusKey = $o['status'] ?? 'Pending';
        $pillClass = $statusMeta[$statusKey]['pill'] ?? 'status-pill--neutral';
        $orderDate = $o['created_at'] ? date('M d, Y g:i A', strtotime($o['created_at'])) : '—';
        $isClosed = in_array($statusKey, ['Delivered','Cancelled'], true);
        $itemSummary = trim((string)$o['items_summary']);
      ?>
        <div class="seller-order-card">
          <div class="seller-order-card__top">
            <div>
              <span class="text-muted small">Order</span>
              <h5 class="mb-0">#<?php echo (int)$o['order_id']; ?></h5>
            </div>
            <span class="status-pill <?php echo $pillClass; ?>"><?php echo e($statusKey); ?></span>
          </div>
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
              <span class="text-muted small d-block">Buyer</span>
              <span class="seller-order-card__buyer"><?php echo e($o['buyer_name']); ?></span>
            </div>
            <div class="text-end">
              <span class="text-muted small d-block">Total</span>
              <span class="seller-order-card__total">$<?php echo number_format((float)$o['total_amount'], 2); ?></span>
            </div>
          </div>
          <?php if($itemSummary): ?>
            <div class="seller-order-card__items">
              <i class="bi bi-bag"></i>
              <span><?php echo e($itemSummary); ?></span>
            </div>
          <?php endif; ?>
          <div class="seller-order-card__meta">
            <div>
              <span class="seller-order-card__meta-label">Items</span>
              <span class="seller-order-card__meta-value"><?php echo $o['total_items'] ?: '—'; ?></span>
            </div>
            <div>
              <span class="seller-order-card__meta-label">Placed</span>
              <span class="seller-order-card__meta-value"><?php echo e($orderDate); ?></span>
            </div>
            <div>
              <span class="seller-order-card__meta-label">Payment</span>
              <span class="seller-order-card__meta-value"><?php echo e($o['payment_method'] ?: 'Not set'); ?></span>
            </div>
          </div>
          <?php if($isClosed): ?>
            <div class="text-muted-soft small">This order is closed. No further actions are available.</div>
          <?php else: ?>
            <div class="seller-order-card__actions">
              <form method="post" class="seller-order-card__status-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>">
                <button name="action" value="Confirmed" class="btn btn-sm btn-outline-secondary">Confirm</button>
                <button name="action" value="Shipped" class="btn btn-sm btn-outline-primary">Ship</button>
                <button name="action" value="Delivered" class="btn btn-sm btn-outline-success">Deliver</button>
              </form>
              <form method="post" class="seller-order-card__cancel-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="order_id" value="<?php echo (int)$o['order_id']; ?>">
                <input type="hidden" name="action" value="Cancelled">
                <button class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Cancel order #<?php echo (int)$o['order_id']; ?>?');">Cancel order</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state-card text-center">
      <i class="bi bi-clipboard-data fs-1 text-primary d-block mb-3"></i>
      <h5 class="mb-1">Nothing to fulfill yet</h5>
      <p class="text-muted-soft mb-0">Orders from your storefront will show up here the moment buyers check out.</p>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
