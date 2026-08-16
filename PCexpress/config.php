<?php
/**
 * config.php
 * Central database configuration for PCexpress.
 * Place this file in: E:\Xampp\htdocs\PCexpress\config.php
 *
 * Edit the constants below to match your XAMPP MySQL setup.
 * Default XAMPP credentials are pre-filled.
 */

// ── Database credentials ────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'pcexpress');   // Your MySQL database name
define('DB_USER',     'root');        // XAMPP default username
define('DB_PASS',     '');            // XAMPP default password (empty)
define('DB_CHARSET',  'utf8mb4');

// ── Return a PDO connection (returns null on failure) ───────
function getDB(): ?PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo; // Reuse existing connection
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;

    } catch (PDOException $e) {
        // Log error privately — never expose credentials to the browser
        error_log('DB connection failed: ' . $e->getMessage());
        return null;
    }
}