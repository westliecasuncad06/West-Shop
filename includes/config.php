<?php
// Global app bootstrap: sessions, DB, base helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Provide a global PDO for helpers expecting $pdo
/** @var PDO $pdo */
$pdo = get_pdo();

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        if ($needle === '') {
            return true;
        }
        if (strlen($needle) > strlen($haystack)) {
            return false;
        }
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('normalize_path_component')) {
    function normalize_path_component(?string $path): string {
        if (!$path) {
            return '';
        }
        $real = realpath($path);
        $normalized = $real ? $real : $path;
        $normalized = str_replace('\\', '/', $normalized);
        $normalized = preg_replace('#/{2,}#', '/', $normalized);
        return rtrim($normalized, '/');
    }
}

define('APP_NAME', 'West Shop');

// Determine the base path once so links stay portable across environments
if (!function_exists('detect_base_path')) {
    function detect_base_path(): string {
        $projectRoot = normalize_path_component(dirname(__DIR__));
        $docRoot = normalize_path_component($_SERVER['DOCUMENT_ROOT'] ?? '');

        if ($docRoot && $projectRoot && str_starts_with($projectRoot, $docRoot)) {
            $relative = substr($projectRoot, strlen($docRoot));
            return $relative ? '/' . ltrim($relative, '/') : '';
        }

        $scriptFile = normalize_path_component($_SERVER['SCRIPT_FILENAME'] ?? '');
        $scriptUrlPath = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '');
        $scriptUrlPath = str_replace('\\', '/', $scriptUrlPath);
        $scriptUrlPath = preg_replace('#/{2,}#', '/', $scriptUrlPath);

        if ($scriptFile && $projectRoot && $scriptUrlPath && str_starts_with($scriptFile, $projectRoot)) {
            $relative = trim(substr($scriptFile, strlen($projectRoot)), '/');
            if ($relative !== '') {
                $needle = '/' . $relative;
                if (str_ends_with($scriptUrlPath, $needle)) {
                    $base = substr($scriptUrlPath, 0, -strlen($needle));
                    $base = rtrim($base, '/');
                    return $base === '' ? '' : '/' . ltrim($base, '/');
                }
            }
        }

        $scriptDir = dirname($scriptUrlPath ?: '/') ?: '';
        if ($scriptDir && $scriptDir !== '.' && $scriptDir !== '/') {
            return '/' . trim($scriptDir, '/');
        }

        return '';
    }
}

$envBasePath = getenv('APP_BASE_PATH') ?: ($_SERVER['APP_BASE_PATH'] ?? '');
if ($envBasePath !== '') {
    $envBasePath = '/' . trim($envBasePath, '/');
}

define('BASE_PATH', $envBasePath !== '' ? $envBasePath : detect_base_path());

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
