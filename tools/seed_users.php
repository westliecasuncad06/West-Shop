<?php
// Seeder: upsert a small set of users (idempotent)
// Run: php tools/seed_users.php

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
    echo "\nUser seeding complete. Use password '123456789' to login for these accounts.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Seeding failed: " . $e->getMessage() . "\n";
}

?>
