<?php
require_once __DIR__ . '/config.php';

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    $u = current_user();
    if (!$u || $u['role'] !== $role) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function login(string $email, string $password): bool {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) return false;

    $stored = $user['password'];
    $ok = false;

    // If bcrypt/argon hash
    if (preg_match('/^\$2y\$|^\$argon2/', $stored)) {
        $ok = password_verify($password, $stored);
    } elseif (strlen($stored) === 32) {
        // Legacy MD5 support for seed; upgrade on success
        $ok = (md5($password) === strtolower($stored));
        if ($ok) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $up = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $up->execute([$newHash, $user['user_id']]);
            $user['password'] = $newHash;
        }
    }

    if (!$ok) return false;

    // Sellers must be approved
    if ($user['role'] === 'seller' && $user['status'] !== 'approved') {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Seller account pending approval.'];
        return false;
    }

    $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'role' => $user['role'],
        'name' => $user['name'],
        'email' => $user['email'],
    ];
    return true;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function register_user(string $role, string $name, string $email, string $password): array {
    global $pdo;
    $role = in_array($role, ['buyer','seller']) ? $role : 'buyer';

    // Check unique email
    $exists = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
    $exists->execute([$email]);
    if ($exists->fetchColumn()) {
        return ['ok' => false, 'error' => 'Email already registered'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $status = ($role === 'seller') ? 'pending' : 'approved';
    $stmt = $pdo->prepare('INSERT INTO users(role, name, email, password, status) VALUES (?,?,?,?,?)');
    $stmt->execute([$role, $name, $email, $hash, $status]);

    if ($role === 'seller') {
        // Prepare empty seller profile
        $sellerId = (int)$pdo->lastInsertId();
        $shop = $pdo->prepare('INSERT INTO seller_profiles(seller_id, shop_name, description) VALUES (?,?,?)');
        $shop->execute([$sellerId, $name . ' Shop', '']);
    }

    return ['ok' => true];
}
