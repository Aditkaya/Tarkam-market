# Tarkam Market.ID

Halaman login Laravel 12 dengan Blade, database MySQL, autentikasi session, fitur ingat saya, pembatasan percobaan login, dashboard terlindungi, dan logout.

## Menjalankan di Windows PowerShell

PHP 8.3+, Composer, MySQL lokal pada port 3306, dan ekstensi PDO MySQL diperlukan. Database `tarkam_market`, tabel `users` dan `migrations`, serta akun demo sudah dibuat di MySQL lokal. File `.env` sudah dikonfigurasi. Jalankan dari folder proyek:

```powershell
composer install
php artisan migrate --seed
php artisan serve
```

Buka http://127.0.0.1:8000/login.

Akun demo lokal:

- Email: `admin@tarkam.id`
- Kata sandi: `Tarkam123!`

Seeder hanya membuat akun demo saat `APP_ENV=local`. Tidak ada proses build frontend atau Node.js yang diperlukan. Konfigurasikan document root Apache ke direktori `public` jika menggunakan XAMPP.

Untuk menyiapkan database di komputer lokal lain, jalankan `php database/setup-local.php`. Skrip memakai koneksi lokal `root` tanpa kata sandi secara default; koneksi dapat diubah melalui variabel lingkungan `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD`. Skrip tidak menimpa `.env` atau akun yang sudah ada. Pengujian otomatis tetap memakai SQLite dalam memori.

## Pemeriksaan

```powershell
php artisan test
```

Pengujian mencakup proteksi dashboard, login, logout, penolakan kata sandi salah, dan pembatasan percobaan login.

`index.html` adalah prototipe statis sebelumnya. Aplikasi Laravel dijalankan melalui `public/index.php` atau `php artisan serve`.

## Status penyiapan

Dependensi belum terpasang karena akses Packagist tidak tersedia di lingkungan pembuatan. Sintaks PHP diperiksa, tetapi pengujian aplikasi perlu dijalankan setelah `composer install` berhasil.
