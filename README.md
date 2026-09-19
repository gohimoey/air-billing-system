# Sistem Tagihan Air - Water Billing System

Aplikasi web tagihan pembayaran air lengkap PHP + MySQL untuk XAMPP & ZimaOS.

## Fitur

### Admin
- Dashboard statistik
- Kelola pelanggan
- Input meter & generate tagihan
- Proses pembayaran
- Laporan keuangan

### Pelanggan
- Lihat tagihan
- Cek status pembayaran
- Profil

## Instalasi

### XAMPP
```bash
# Salin folder ke htdocs
xcopy air C:\xampp\htdocs\air\ /E

# Buka http://localhost/air
# Import db.sql via phpMyAdmin
```

### ZimaOS (Docker)
```bash
# 1. Salin ke /media/HDD-Storage/air/
# 2. Akses http://localhost:2343

# MySQL Config (config.php):
define('BASE_URL', 'http://localhost:2343');
define('DB_HOST', '192.168.3.24');  // atau 'localhost'
```

### phpMyAdmin Access
- http://192.168.3.24:8080

### Database Access
- MariaDB: 192.168.3.24:3306

## Akun Default
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Pelanggan | budi | budi123 |

## Struktur Folder
```
air/
├── db.sql
├── login.php
├── index.php
├── logout.php
├── bills.php
├── customers.php
├── readings.php
├── payments.php
├── reports.php
├── profile.php
├── admin/
│   └── index.php
├── customer/
│   ├── index.php
│   ├── bills.php
│   └── profile.php
└── includes/
    ├── config.php
    ├── db.php
    ├── auth.php
    ├── functions.php
    ├── header.php
    ├── footer.php
    ├── design.css
    └── animations.js
```

## Design
- Gradient biru-teal
- Animasi halus (fade-in, ripple, scroll)
- Mobile responsive
- Glassmorphism cards

## Konfigurasi
Edit `includes/config.php`:
```php
define('BASE_URL', 'http://localhost:2343');
define('TARIFF', 5000);
define('DB_HOST', '192.168.3.24');
define('DB_NAME', 'air_bill');
define('DB_USER', 'root');
define('DB_PASS', 'casaos');
```

## Troubleshooting
- `Koneksi DB gagal`: Cek DB_HOST, DB_USER, DB_PASS
- `404 Not Found`: Pastikan BASE_URL sesuai folder
- `Permission denied`: File harus readable oleh web server