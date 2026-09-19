# Sistem Tagihan Air - Water Billing System

Aplikasi web tagihan pembayaran air lengkap PHP + MySQL untuk XAMPP & ZimaOS.

## Fitur

- Dashboard admin & pelanggan
- Kelola pelanggan
- Input meter & generate tagihan
- Proses pembayaran
- Laporan keuangan

## Instalasi

### XAMPP
```bash
xcopy air C:\xampp\htdocs\air\ /E
```

### ZimaOS (Docker)
```bash
cd /media/HDD-Storage/air/
docker compose up -d
```

## Konfigurasi

### Environment Variables
Copy `.env.example` ke `.env` dan sesuaikan:

```bash
cp .env.example .env
nano .env
```

### Variabel yang Diharapkan
```
BASE_URL=http://localhost:2343  # URL aplikasi
DB_HOST=host.docker.internal   # MariaDB host
DB_NAME=air_bill
DB_USER=root
DB_PASS=your_password         # Ganti dengan password Anda
TARIFF=5000                   # Tarif per m3
```

### Database
- phpMyAdmin: http://192.168.3.24:8080
- Import file: `db.sql`
- Akses: http://localhost:2343

## Akun Demo
*Password di db.sql sudah dihash - bukan plaintext*
- Admin: `admin` / `admin123`
- Pelanggan: `budi` / `budi123`

## Security Notes
- Password di database sudah dihash (bcrypt)
- Tidak ada credential di kode sumber
- Gunakan `.env` untuk konfigurasi sensitif
- Jangan commit `.env` ke GitHub

## Struktur
```
air/
├── .env.example           # Contoh konfigurasi
├── README.md              # Dokumentasi
├── db.sql                 # Schema database (password hash)
├── docker-compose.yml     # Docker deployment
├── Dockerfile             # Build image PHP
├── login.php              # Halaman login
├── customer/              # Dashboard pelanggan
├── admin/                 # Dashboard admin
└── includes/              # Core files
    ├── config.php         # Konfigurasi (baca .env)
    ├── design.css         # Tampilan elegan
    └── animations.js      # Animasi lembut
```