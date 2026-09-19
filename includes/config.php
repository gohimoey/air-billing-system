<?php
// ===== Konfigurasi Aplikasi Tagihan Air =====
// Xampp: http://localhost/air
// ZimaOS Docker: http://localhost:2343

// Database configuration - ganti sesuai kebutuhan
define('BASE_URL', 'http://localhost:2343');  // Pastikan Cocok dengan port docker
define('TARIFF', 5000);  // Tarif per m3 Rupiah

// DATABASE CONFIGURATION
define('DB_HOST', 'host.docker.internal');   // Untuk Docker - konek ke host MariaDB
// Jika MariaDB di host lokal: define('DB_HOST', '192.168.3.24');
// Jika MariaDB di container lain: gunakan nama service docker-compose

define('DB_NAME', 'air_bill');
define('DB_USER', 'root');
define('DB_PASS', 'casaos');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_name('air_billing');
    session_start();
}

// Error reporting - matikan di production
ini_set('display_errors', 0);
error_reporting(E_ALL);