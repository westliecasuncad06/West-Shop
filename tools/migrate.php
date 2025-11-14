<?php
/**
 * Migration runner for ecommerce_db.
 * Usage (CLI): php tools/migrate.php [--reset]
 * Usage (Browser): /Ecommerce-Website/tools/migrate.php?reset=1
 *
 * --reset : Performs a full rebuild using sql/ecommerce_db.sql then runs migrations.
 * Default  : Only runs migration files under sql/migrations (sorted) against existing DB.
 *
 * Supports custom DELIMITER blocks (procedures) used in 002 migration.
 * Designed for MariaDB/MySQL 10.x (tested with MariaDB 10.4).
 */

ini_set('max_execution_time', '120');
ini_set('memory_limit', '256M');

// Basic DB credentials (match includes/db.php). If you change them there, update here or require that file.
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'ecommerce_db';

$isCli   = (php_sapi_name() === 'cli');
$doReset = ($isCli && in_array('--reset', $argv, true)) || (!$isCli && isset($_GET['reset']));

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Failed to resolve project root.\n");
    exit(1);
}

$sqlDir       = $root . DIRECTORY_SEPARATOR . 'sql';
$migrationsDir = $sqlDir . DIRECTORY_SEPARATOR . 'migrations';

if (!is_dir($sqlDir)) {
    fwrite(STDERR, "SQL directory missing: $sqlDir\n");
    exit(1);
}

// Gather files
$files = [];
if ($doReset) {
    $baseFile = $sqlDir . DIRECTORY_SEPARATOR . 'ecommerce_db.sql';
    if (!is_file($baseFile)) {
        fwrite(STDERR, "Base schema file missing: ecommerce_db.sql\n");
        exit(1);
    }
    $files[] = $baseFile;
}
if (is_dir($migrationsDir)) {
    $migFiles = glob($migrationsDir . DIRECTORY_SEPARATOR . '*.sql');
    natsort($migFiles);
    foreach ($migFiles as $mf) {
        $files[] = $mf;
    }
}
if (!$files) {
    fwrite(STDERR, "No SQL files found to run.\n");
    exit(1);
}

// Connect without selecting DB (base file may create DB)
$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connection failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

// When not doing a full reset, ensure the target database is selected up front
if (!$doReset) {
    if (!$mysqli->select_db(DB_NAME)) {
        fwrite(STDERR, "Database '" . DB_NAME . "' not found or not accessible. Run with --reset to initialize.\n");
        if ($isCli) {
            exit(1);
        } else {
            die('Database not selected.');
        }
    }
}

function out($msg, $isCli) {
    if ($isCli) {
        echo $msg . "\n";
    } else {
        echo htmlspecialchars($msg) . "<br>";
        @flush();
    }
}

function run_sql_file(mysqli $mysqli, string $path, bool $isCli): bool {
    out("== Running: " . basename($path), $isCli);
    $sql = file_get_contents($path);
    if ($sql === false) {
        out("Failed to read file: $path", $isCli);
        return false;
    }
    // Normalize line endings
    $sql = str_replace(["\r\n", "\r"], "\n", $sql);

    $delimiter = ';';
    $buffer = '';
    $lineNum = 0;

    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $lineNum++;
        $trimmed = trim($line);
        // Handle DELIMITER change
        if (preg_match('/^DELIMITER\s+(.*)$/i', $trimmed, $m)) {
            $delimiter = $m[1];
            out("-- Switched delimiter to '$delimiter'", $isCli);
            continue;
        }
        $buffer .= $line . "\n"; // preserve internal semicolons for procedures
        // If current buffer ends with the delimiter (followed by optional whitespace)
        if (substr(rtrim($buffer), -strlen($delimiter)) === $delimiter) {
            $statement = substr(rtrim($buffer), 0, -strlen($delimiter));
            $statement = trim($statement);
            $buffer = '';
            if ($statement === '' || preg_match('/^--/m', $statement)) {
                continue; // skip pure comment block
            }
            // Execute statement
            if (!$mysqli->query($statement)) {
                out("ERROR at file: " . basename($path) . " line ~" . $lineNum . ": (" . $mysqli->errno . ") " . $mysqli->error, $isCli);
                return false;
            }
        }
    }
    // Any trailing statement without delimiter
    $tail = trim($buffer);
    if ($tail !== '') {
        if (!$mysqli->query($tail)) {
            out("ERROR at file tail: " . basename($path) . " (" . $mysqli->errno . ") " . $mysqli->error, $isCli);
            return false;
        }
    }
    out("== Completed: " . basename($path), $isCli);
    return true;
}

out(($doReset ? 'Full reset + migrations' : 'Migrations only') . ' starting...', $isCli);

foreach ($files as $file) {
    if (!run_sql_file($mysqli, $file, $isCli)) {
        out('Migration aborted.', $isCli);
        exit(1);
    }
    // Re-select DB after potential CREATE/DROP
    $mysqli->select_db(DB_NAME);
}

out('All SQL files applied successfully.', $isCli);

if (!$isCli) {
    echo '<hr><strong>Done.</strong>'; 
    echo '<p>Re-run with ?reset=1 for a full rebuild.</p>'; 
}

?>