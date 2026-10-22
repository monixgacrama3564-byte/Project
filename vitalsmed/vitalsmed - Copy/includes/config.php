<?php
// ============================================================
// includes/config.php  — Database & App Configuration
// ============================================================

define('DB_HOST',    'localhost');
define('DB_USER',    'root');         // Change if your MySQL user differs
define('DB_PASS',    '');             // Change to your MySQL password
define('DB_NAME',    'vitalsmed_db');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',   'VitalsMed');

// ── AUTO-DETECT BASE_URL ───────────────────────────────────
// This works regardless of what you named your project folder.
// It finds the project root (the folder containing /includes/config.php)
// and builds the URL from there automatically.
if (!defined('BASE_URL')) {
    $scriptPath  = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']); // e.g. /vitalsmed/pages/dashboard.php
    $projectRoot = str_replace('\\', '/', dirname(dirname(__FILE__))); // abs path to project root
    $docRoot     = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);  // abs path to htdocs

    // Get the web path of the project root relative to docroot
    $webPath = str_replace($docRoot, '', $projectRoot);
    $webPath = rtrim($webPath, '/') . '/';

    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'];

    define('BASE_URL', $scheme . '://' . $host . $webPath);
}

// Vital-sign safe-zone thresholds (M3)
define('SYSTOLIC_MAX',   140);
define('HEART_RATE_MAX', 100);
define('TEMP_MAX',        37.5);
define('TEMP_MIN',        35.0);
define('O2_MIN',          94.0);

// Medication compliance window in minutes (M4)
define('MED_GRACE_MINUTES', 120);

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// ── PDO connection (singleton) ─────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DB Connection Failed: " . $e->getMessage());
            die('<div style="font-family:sans-serif;padding:40px;color:#dc2626;">
                <h2>Database Connection Failed</h2>
                <p>Please check your <code>includes/config.php</code> settings and ensure MySQL is running.</p>
                <p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>
            </div>');
        }
    }
    return $pdo;
}