<?php
// Local seeder: upserts a few users into the current database.
// Run locally: php tools/seed.php

require_once __DIR__ . '/../includes/config.php';

if (php_sapi_name() !== 'cli') {
    echo "This script is intended to be run from the command line.\n";
}

/** @var PDO $pdo */
$pdo = get_pdo();

$users = [
    [
        'role' => 'admin',
        'name' => 'Westlie Casuncad',
        'email' => 'westragma@gmail.com',
        'password' => '123456789',
        'status' => 'approved',
    ],
    [
        'role' => 'seller',
        'name' => 'West Ragma',
        'email' => 'west@gmail.com',
        'password' => '123456789',
        'status' => 'pending',
    ],
    [
        'role' => 'buyer',
        'name' => 'Danhil Baluyot',
        'email' => 'dbaluyot@gmail.com',
        'password' => '123456789',
        'status' => 'approved',
    ],
];

try {
    $pdo->beginTransaction();
    $select = $pdo->prepare('SELECT user_id FROM users WHERE email = :email LIMIT 1');
    $insert = $pdo->prepare('INSERT INTO users (role, name, email, password, phone, address, status) VALUES (:role, :name, :email, :password, NULL, NULL, :status)');
    $update = $pdo->prepare('UPDATE users SET role = :role, name = :name, password = :password, status = :status WHERE email = :email');

    foreach ($users as $u) {
        $select->execute([':email' => $u['email']]);
        $found = $select->fetchColumn();
        // Use password_hash for local seeding (stronger than MD5 used in old SQL)
        $pwHash = password_hash($u['password'], PASSWORD_DEFAULT);
        if ($found) {
            $update->execute([
                ':role' => $u['role'],
                ':name' => $u['name'],
                ':password' => $pwHash,
                ':status' => $u['status'],
                ':email' => $u['email'],
            ]);
            echo "Updated user: {$u['email']}\n";
        } else {
            $insert->execute([
                ':role' => $u['role'],
                ':name' => $u['name'],
                ':email' => $u['email'],
                ':password' => $pwHash,
                ':status' => $u['status'],
            ]);
            echo "Inserted user: {$u['email']}\n";
        }
    }

    $pdo->commit();
    echo "\nSeeding complete.\n";
    echo "Note: passwords are stored using password_hash(). Use the password '123456789' to login.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Seeding failed: " . $e->getMessage() . "\n";
}

?>
<?php
// Secure seeder for sample accounts using password_hash()
// Run once after importing sql/ecommerce_db.sql
// php tools/seed.php (from project root)

require_once __DIR__ . '/../includes/db.php';

$pdo = get_pdo();

function upsert_user(PDO $pdo, $role, $name, $email, $plainPassword, $status = 'approved', $phone = null, $address = null) {
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    if ($existing) {
        $stmt = $pdo->prepare('UPDATE users SET role=?, name=?, password=?, phone=?, address=?, status=? WHERE user_id=?');
        $stmt->execute([$role, $name, $hash, $phone, $address, $status, $existing['user_id']]);
        return (int)$existing['user_id'];
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (role,name,email,password,phone,address,status) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$role, $name, $email, $hash, $phone, $address, $status]);
        return (int)$pdo->lastInsertId();
    }
}

try {
    // Admin
    upsert_user(
        $pdo,
        'admin',
        'Admin User',
        'westragna@gmail.com',
        '123456789',
        'approved'
    );

    // Seller (pending approval initially; admin can approve after)
    $sellerId = upsert_user(
        $pdo,
        'seller',
        'West Seller',
        'west@gmail.com',
        '123456789',
        'pending'
    );

    // Create seller profile stub if missing
    $stmt = $pdo->prepare('SELECT profile_id FROM seller_profiles WHERE seller_id = ?');
    $stmt->execute([$sellerId]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare('INSERT INTO seller_profiles (seller_id, shop_name, description) VALUES (?,?,?)');
        $ins->execute([$sellerId, 'West Shop', 'Welcome to West Shop!']);
    }

    // Buyer
    upsert_user(
        $pdo,
        'buyer',
        'Dexter Baluyot',
        'dbaluyot@gmail.com',
        '123456789',
        'approved'
    );

    // Sample coupon
    $exists = $pdo->prepare('SELECT 1 FROM coupons WHERE code = ?');
    $exists->execute(['WELCOME10']);
    if (!$exists->fetchColumn()) {
        $ins = $pdo->prepare('INSERT INTO coupons(code,type,value,expires_at,max_uses) VALUES (?,?,?,?,?)');
        $ins->execute(['WELCOME10','percent',10.00, date('Y-m-d', strtotime('+365 days')) . ' 23:59:59', 1000]);
    }

    echo "Seed completed.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Seeding failed: ' . $e->getMessage();
}

?>
