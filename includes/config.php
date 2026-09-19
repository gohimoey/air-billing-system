<?php
// ===== Konfigurasi Aplikasi Tagihan Air =====
// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Auto-detect BASE_URL
$host = $_SERVER['HTTP_HOST'] ?? $_ENV['BASE_URL'] ?? 'localhost';
$port = $_ENV['PORT'] ?? ($_SERVER['SERVER_PORT'] ?? '');

// Extract host without port from HTTP_HOST
if (strpos($host, ':') !== false) {
    $hostParts = explode(':', $host);
    $hostName = $hostParts[0];
    $hostPort = $hostParts[1] ?? $port;
} else {
    $hostName = $host;
    $hostPort = $port ?: '80';
}

// Determine protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// BASE_URL
define('BASE_URL', $protocol . '://' . $hostName . ($hostPort && $hostPort !== '80' ? ':' . $hostPort : ''));

define('TARIFF', (int)($_ENV['TARIFF'] ?? 5000)); // tarif per m3 Rupiah

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'air_bill');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Timezone
date_default_timezone_set($_ENV['TZ'] ?? 'Asia/Jakarta');

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_name('air_billing');
    session_start();
}

// Error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);