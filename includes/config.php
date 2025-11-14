<?php
// Global app bootstrap: sessions, DB, base helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Provide a global PDO for helpers expecting $pdo
/** @var PDO $pdo */
$pdo = get_pdo();

// Configure base URL helper
// If deploying under a subfolder, set BASE_PATH to '/subfolder'
// Project is served from XAMPP under the folder `/Ecommerce-Website`.
define('BASE_PATH', '/Ecommerce-Website');
define('APP_NAME', 'West Shop');

function base_url(string $path = ''): string {
    $base = rtrim(BASE_PATH, '/');
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

// Simple CSRF token utilities
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): bool {
    return isset($_POST['_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token']);
}

?>
