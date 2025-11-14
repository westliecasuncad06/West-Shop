<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$seller_id = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : 0;
if (!$seller_id) {
    echo '<div class="alert alert-danger">Store not found</div>';
    include __DIR__ . '/../templates/footer.php';
    exit;
}

$store = get_seller_profile($seller_id);
if (!$store) {
    echo '<div class="alert alert-danger">Store not found</div>';
    include __DIR__ . '/../templates/footer.php';
    exit;
}

// Handle follow / rate actions for buyers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $user = $_SESSION['user'] ?? null;

    if ($action === 'follow_store' && $user && ($user['role'] ?? '') === 'buyer') {
        $ins = $pdo->prepare('INSERT IGNORE INTO store_follows(buyer_id, seller_id) VALUES (?, ?)');
        $ins->execute([$user['user_id'], $seller_id]);
        set_flash('success', 'You are now following this store');
        header('Location: ' . base_url('seller/store_public.php?seller_id=' . $seller_id));
        exit;
    }

    if ($action === 'unfollow_store' && $user && ($user['role'] ?? '') === 'buyer') {
        $del = $pdo->prepare('DELETE FROM store_follows WHERE buyer_id = ? AND seller_id = ?');
        $del->execute([$user['user_id'], $seller_id]);
        set_flash('success', 'Unfollowed the store');
        header('Location: ' . base_url('seller/store_public.php?seller_id=' . $seller_id));
        exit;
    }

    if ($action === 'rate_store' && $user && ($user['role'] ?? '') === 'buyer') {
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $comment = trim($_POST['comment'] ?? '');
        $hasStoreId = store_reviews_has_column('store_id');
        $hasSellerId = store_reviews_has_column('seller_id');

        $columns = [];
        $params = [];

        if ($hasStoreId) {
            $sid = get_store_id_for_seller($seller_id);
            if (!$sid) {
                set_flash('danger', 'Store is not available for rating.');
                header('Location: ' . base_url('seller/store_public.php?seller_id=' . $seller_id));
                exit;
            }
            $columns[] = 'store_id';
            $params[] = $sid;
        }
        if ($hasSellerId) {
            $columns[] = 'seller_id';
            $params[] = $seller_id;
        }

        $columns = array_merge($columns, ['buyer_id', 'rating', 'comment']);
        $params = array_merge($params, [$user['user_id'], $rating, $comment]);

        $placeholders = rtrim(str_repeat('?,', count($columns)), ',');
        $sql = 'INSERT INTO store_reviews(' . implode(',', $columns) . ')
                              VALUES (' . $placeholders . ')
                              ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)';
        if (store_reviews_has_updated_at()) {
            $sql .= ', updated_at = NOW()';
        }
        $up = $pdo->prepare($sql);
        $up->execute($params);
        set_flash('success', 'Thanks for rating the store');
        header('Location: ' . base_url('seller/store_public.php?seller_id=' . $seller_id));
        exit;
    }
}

$rating = get_store_rating($seller_id);
$isBuyer = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'buyer';
$isFollowing = $isBuyer ? is_store_followed($_SESSION['user']['user_id'], $seller_id) : false;
$followers = count_store_followers($seller_id);

$p = $pdo->prepare('SELECT * FROM products WHERE seller_id = ? AND status = "active" ORDER BY created_at DESC');
$p->execute([$seller_id]);
$products = $p->fetchAll();

// Fetch active coupons for this store (robust to missing `stores` table)
$storeId = null;
try {
    $storeStmt = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
    $storeStmt->execute([$seller_id]);
    $storeId = $storeStmt->fetchColumn();
} catch (Exception $e) {
    $storeId = null;
}

$activeCoupons = [];
try {
    if ($storeId) {
        $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE store_id = ? ORDER BY created_at DESC');
        $couponStmt->execute([$storeId]);
        $allCoupons = $couponStmt->fetchAll();
    } else {
        $couponStmt = $pdo->prepare('SELECT * FROM coupons WHERE created_by = ? ORDER BY created_at DESC');
        $couponStmt->execute([$seller_id]);
        $allCoupons = $couponStmt->fetchAll();
    }

    $activeCoupons = array_filter($allCoupons, function ($c) {
        $notExpired = !$c['expires_at'] || strtotime($c['expires_at']) >= time();
        $notMaxed = ($c['max_uses'] ?? 0) == 0 || ($c['used_count'] ?? 0) < $c['max_uses'];
        return $notExpired && $notMaxed;
    });
} catch (Exception $e) {
    $activeCoupons = [];
}

$storeReviews = [];
try {
    $col = store_reviews_id_column();
    $ownerId = ($col === 'store_id') ? ($storeId ?: null) : $seller_id;
    if ($ownerId) {
        $revStmt = $pdo->prepare('SELECT sr.*, u.name AS buyer_name FROM store_reviews sr JOIN users u ON u.user_id = sr.buyer_id WHERE sr.' . $col . ' = ? ORDER BY sr.updated_at DESC, sr.created_at DESC');
        $revStmt->execute([$ownerId]);
        $storeReviews = $revStmt->fetchAll();
    } else {
        $storeReviews = [];
    }
} catch (Exception $e) {
    $storeReviews = [];
}

$existingStoreReview = null;
if ($isBuyer) {
    try {
        $col = store_reviews_id_column();
        $ownerId = ($col === 'store_id') ? ($storeId ?: null) : $seller_id;
        if ($ownerId) {
            $myStmt = $pdo->prepare('SELECT rating, comment FROM store_reviews WHERE ' . $col . ' = ? AND buyer_id = ? LIMIT 1');
            $myStmt->execute([$ownerId, $_SESSION['user']['user_id']]);
            $existingStoreReview = $myStmt->fetch();
        }
    } catch (Exception $e) { $existingStoreReview = null; }
}

include __DIR__ . '/../templates/header.php';

$storeViewMode = 'buyer';
$storeActionUrl = base_url('seller/store_public.php?seller_id=' . (int)$seller_id);
$previewExitUrl = null;

include __DIR__ . '/partials/storefront_view.php';

include __DIR__ . '/../templates/footer.php';
