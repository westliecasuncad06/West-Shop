<?php
// Simple database connectivity test page
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h2>Database Connectivity Test</h2>';
try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        // Attempt to get PDO explicitly (this will exit with a friendly message from db.php on failure)
        $pdo = get_pdo();
    }
    $stmt = $pdo->query('SELECT 1');
    $res = $stmt->fetchColumn();
    echo '<p style="color:green;font-weight:600;">Connected to database "' . htmlspecialchars(DB_NAME) . '" successfully.</p>';
    echo '<p>Test query <code>SELECT 1</code> returned: <strong>' . htmlspecialchars((string)$res) . '</strong></p>';
    echo '<p>Host: ' . htmlspecialchars(DB_HOST) . ' | Charset: ' . htmlspecialchars(DB_CHARSET) . '</p>';
} catch (Exception $e) {
    http_response_code(500);
    echo '<p style="color:red;font-weight:600;">Database connection failed.</p>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<p>Check your database credentials in <code>includes/db.php</code> and make sure MySQL is running.</p>';
}

echo '<p><a href="' . htmlspecialchars(base_url('index.php')) . '">Back to site</a></p>';

?>
