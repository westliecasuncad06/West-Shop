<?php
require_once __DIR__ . '/config.php';

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function set_flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function get_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function redirect(string $path): void { header('Location: ' . base_url($path)); exit; }

function get_categories(): array {
    global $pdo; $q = $pdo->query('SELECT * FROM categories ORDER BY name'); return $q->fetchAll();
}

function get_top_categories(): array {
    global $pdo; $stmt = $pdo->query('SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name'); return $stmt->fetchAll();
}

function get_subcategories(int $parentId): array {
    global $pdo; $stmt = $pdo->prepare('SELECT * FROM categories WHERE parent_id = ? ORDER BY name'); $stmt->execute([$parentId]); return $stmt->fetchAll();
}

function search_products(?string $term, ?int $topCategoryId, ?int $subCategoryId, int $limit = 48): array {
    global $pdo;
    $sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.status = "active"';
    $params = [];
    if ($subCategoryId && $subCategoryId > 0) {
        $sql .= ' AND p.category_id = ?';
        $params[] = $subCategoryId;
    } elseif ($topCategoryId && $topCategoryId > 0) {
        // Match products directly in top category or any child of it
        $sql .= ' AND (c.category_id = ? OR c.parent_id = ?)';
        $params[] = $topCategoryId;
        $params[] = $topCategoryId;
    }
    if ($term) {
        $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
        $like = '%'.$term.'%';
        $params[] = $like;
        $params[] = $like;
    }
    $sql .= ' ORDER BY p.created_at DESC LIMIT ?';
    $params[] = $limit;
    $stmt = $pdo->prepare($sql);
    // Bind parameters with appropriate types
    $idx = 1;
    foreach ($params as $pval) {
        $type = PDO::PARAM_STR;
        if (is_int($pval)) { $type = PDO::PARAM_INT; }
        $stmt->bindValue($idx++, $pval, $type);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_featured_products(int $limit = 8): array {
    global $pdo; $stmt = $pdo->prepare('SELECT p.*, u.name AS seller_name, c.name AS category_name
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.user_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = "active" ORDER BY p.created_at DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function product_image_src(?string $image, string $placeholder = 'https://via.placeholder.com/400x300?text=Product'): string {
    if (!$image || trim($image) === '') return $placeholder;
    if (preg_match('/^https?:\/\//i', $image)) return $image;
    return base_url(ltrim($image, '/'));
}

function create_notification(int $userId, string $message): void {
    global $pdo; $stmt = $pdo->prepare('INSERT INTO notifications(user_id, message) VALUES (?,?)'); $stmt->execute([$userId, $message]);
}

function unread_notifications_count(int $userId): int {
    global $pdo; $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0'); $stmt->execute([$userId]); return (int)$stmt->fetchColumn();
}

function mark_notifications_read(int $userId): void {
    global $pdo; $stmt = $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?'); $stmt->execute([$userId]);
}

// Cart helpers (session-based)
function cart_get(): array { return $_SESSION['cart'] ?? []; }
function cart_add(int $productId, int $qty): void {
    if ($qty < 1) $qty = 1;
    $cart = cart_get();
    $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
    $_SESSION['cart'] = $cart;
}
function cart_set(int $productId, int $qty): void {
    $cart = cart_get();
    if ($qty < 1) unset($cart[$productId]); else $cart[$productId] = $qty;
    $_SESSION['cart'] = $cart;
}
function cart_remove(int $productId): void { $cart = cart_get(); unset($cart[$productId]); $_SESSION['cart'] = $cart; }
function cart_clear(): void { unset($_SESSION['cart']); }

function cart_items_with_products(): array {
    global $pdo; $cart = cart_get(); if (!$cart) return [];
    $ids = array_map('intval', array_keys($cart));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($in)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    $map = [];
    foreach ($products as $p) { $map[$p['product_id']] = $p; }
    $out = [];
    foreach ($cart as $pid => $qty) {
        if (isset($map[$pid])) { $p = $map[$pid]; $p['cart_qty'] = $qty; $p['line_total'] = $qty * (float)$p['price']; $out[] = $p; }
    }
    return $out;
}

function order_create_from_cart(int $buyerId, string $paymentMethod, ?array $coupon = null): ?int {
    global $pdo; $items = cart_items_with_products(); if (!$items) return null;
    $total = 0; foreach ($items as $it) { $total += $it['line_total']; }
    $discountAmount = 0.00;
    $couponCode = null;
    if ($coupon) {
        $couponCode = $coupon['code'] ?? null;
        $discountAmount = (float)($coupon['discount_amount'] ?? 0.00);
        $total = max(0, $total - $discountAmount);
    }
    try {
        $pdo->beginTransaction();
        $o = $pdo->prepare('INSERT INTO orders(buyer_id,total_amount,payment_method,coupon_code,discount_amount) VALUES (?,?,?,?,?)');
        $o->execute([$buyerId, $total, $paymentMethod, $couponCode, $discountAmount]);
        $orderId = (int)$pdo->lastInsertId();
        $oi = $pdo->prepare('INSERT INTO order_items(order_id, product_id, quantity, price) VALUES (?,?,?,?)');
        $dec = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?');
        foreach ($items as $it) {
            $oi->execute([$orderId, $it['product_id'], $it['cart_qty'], $it['price']]);
            $dec->execute([$it['cart_qty'], $it['product_id'], $it['cart_qty']]);
        }
        // If coupon used, increment used_count
        if ($couponCode) {
            $up = $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE code = ?');
            $up->execute([$couponCode]);
        }
        $pdo->commit();
        cart_clear();
        return $orderId;
    } catch (Exception $e) {
        $pdo->rollBack();
        return null;
    }
}

// Reviews
function get_reviews(int $productId): array {
    global $pdo; $s = $pdo->prepare('SELECT r.*, u.name AS buyer_name FROM reviews r LEFT JOIN users u ON r.buyer_id=u.user_id WHERE r.product_id=? ORDER BY r.created_at DESC'); $s->execute([$productId]); return $s->fetchAll();
}
function get_product_rating(int $productId): float {
    global $pdo; $s = $pdo->prepare('SELECT AVG(rating) FROM reviews WHERE product_id=?'); $s->execute([$productId]); $avg = $s->fetchColumn(); return $avg ? (float)$avg : 0.0;
}

// Coupons
function get_valid_coupon(string $code): ?array {
    global $pdo;
    $s = $pdo->prepare('SELECT * FROM coupons WHERE code = ? LIMIT 1');
    $s->execute([trim($code)]);
    $c = $s->fetch();
    if (!$c) return null;
    if ($c['expires_at'] && strtotime($c['expires_at']) < time()) return null;
    if ($c['max_uses'] > 0 && $c['used_count'] >= $c['max_uses']) return null;
    return $c;
}

function calc_coupon_discount(array $coupon, float $subtotal): float {
    if (!$coupon) return 0.0;
    if ($coupon['type'] === 'percent') {
        return round($subtotal * ((float)$coupon['value'] / 100.0), 2);
    }
    return min((float)$coupon['value'], $subtotal);
}

// Store helpers (seller profiles, follows, ratings)
function get_seller_profile(int $sellerId): ?array {
    global $pdo;
    $s = $pdo->prepare('SELECT sp.*, u.name AS seller_name, u.user_id AS seller_id
                        FROM seller_profiles sp
                        JOIN users u ON u.user_id = sp.seller_id
                        WHERE sp.seller_id = ? LIMIT 1');
    $s->execute([$sellerId]);
    $row = $s->fetch();
    if (!$row) {
        // Create a minimal virtual profile using users table
        $u = $pdo->prepare('SELECT user_id AS seller_id, name AS seller_name FROM users WHERE user_id=? LIMIT 1');
        $u->execute([$sellerId]);
        $usr = $u->fetch();
        if (!$usr) return null;
        return [
            'seller_id' => (int)$usr['seller_id'],
            'shop_name' => ($usr['seller_name'] . ' Shop'),
            'logo' => null,
            'banner' => null,
            'description' => '',
            'shipping_policy' => null,
            'return_policy' => null,
            'seller_name' => $usr['seller_name'],
        ];
    }
    return $row;
}

function get_store_rating(int $sellerId): array {
    global $pdo;
    $col = store_reviews_id_column();
    $ownerId = store_reviews_owner_value($sellerId);
    if (!$ownerId) {
        return ['avg' => 0.0, 'cnt' => 0];
    }
    $sql = 'SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS total FROM store_reviews WHERE ' . $col . ' = ?';
    $r = $pdo->prepare($sql);
    $r->execute([$ownerId]);
    $row = $r->fetch();
    return [
        'avg' => isset($row['avg_rating']) ? (float)$row['avg_rating'] : 0.0,
        'cnt' => isset($row['total']) ? (int)$row['total'] : 0,
    ];
}

function is_store_followed(int $buyerId, int $sellerId): bool {
    global $pdo;
    $s = $pdo->prepare('SELECT 1 FROM store_follows WHERE buyer_id=? AND seller_id=? LIMIT 1');
    $s->execute([$buyerId, $sellerId]);
    return (bool)$s->fetchColumn();
}

function count_store_followers(int $sellerId): int {
    global $pdo;
    $s = $pdo->prepare('SELECT COUNT(*) FROM store_follows WHERE seller_id=?');
    $s->execute([$sellerId]);
    return (int)$s->fetchColumn();
}

function store_reviews_has_updated_at(): bool {
    static $has = null;
    if ($has !== null) {
        return $has;
    }
    global $pdo;
    try {
        $q = $pdo->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='store_reviews' AND column_name='updated_at' LIMIT 1");
        $has = (bool)$q->fetchColumn();
    } catch (Exception $e) {
        $has = false;
    }
    return $has;
}

// Generic column presence check for store_reviews
function store_reviews_has_column(string $name): bool {
    static $cache = [];
    if (array_key_exists($name, $cache)) return $cache[$name];
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='store_reviews' AND column_name = ? LIMIT 1");
        $stmt->execute([$name]);
        $cache[$name] = (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        $cache[$name] = false;
    }
    return $cache[$name];
}

// Detect whether store reviews reference a store record or the legacy seller id
function store_reviews_id_column(): string {
    static $col = null;
    if ($col !== null) return $col;
    global $pdo;
    try {
        $q = $pdo->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name='store_reviews' AND column_name='store_id' LIMIT 1");
        $col = $q->fetchColumn() ? 'store_id' : 'seller_id';
    } catch (Exception $e) {
        $col = 'seller_id';
    }
    return $col;
}

// Map a seller_id to the correct owner id for store_reviews (store_id or seller_id)
function store_reviews_owner_value(int $sellerId): ?int {
    $col = store_reviews_id_column();
    if ($col === 'seller_id') {
        return $sellerId;
    }
    // When using stores, resolve the seller's store_id
    global $pdo;
    try {
        $s = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
        $s->execute([$sellerId]);
        $sid = $s->fetchColumn();
        return $sid ? (int)$sid : null;
    } catch (Exception $e) {
        return null;
    }
}

// Always resolve store_id for a given seller when available
function get_store_id_for_seller(int $sellerId): ?int {
    global $pdo;
    try {
        $s = $pdo->prepare('SELECT store_id FROM stores WHERE seller_id = ? LIMIT 1');
        $s->execute([$sellerId]);
        $sid = $s->fetchColumn();
        return $sid ? (int)$sid : null;
    } catch (Exception $e) {
        return null;
    }
}
