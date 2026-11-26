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

## Lupa kata sandi

Klik **Lupa kata sandi?** pada halaman login atau buka `/forgot-password`. Tautan reset berlaku 60 menit, hanya dapat dipakai sekali, dan dikirim melalui notifikasi email berbahasa Indonesia. Kata sandi baru minimal 12 karakter serta mengandung huruf dan angka. Permintaan dibatasi per IP dan pengiriman ulang per akun memiliki jeda 60 detik. Pesan permintaan tidak mengungkap apakah email terdaftar.

Jalankan `php artisan migrate` setelah memperbarui kode. Untuk pengembangan lokal, `MAIL_MAILER=log` mencatat email beserta tautannya ke `storage/logs/laravel.log`; mode ini tidak mengirim email ke kotak masuk.

Untuk pengiriman nyata, isi konfigurasi berikut di `.env` menggunakan penyedia SMTP Anda:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.penyedia-anda.com
MAIL_PORT=587
MAIL_USERNAME=akun-smtp-anda
MAIL_PASSWORD=kata-sandi-smtp-anda
MAIL_FROM_ADDRESS=noreply@domain-anda.com
MAIL_FROM_NAME="Tarkam Market.ID"
```

Sesuaikan `APP_URL` dengan alamat aplikasi yang dapat diakses penerima. Gunakan `MAIL_SCHEME=smtps` jika penyedia mensyaratkan TLS langsung pada port 465; port 587 memakai STARTTLS sesuai dukungan server. Jalankan `php artisan config:clear` setelah mengubah konfigurasi. Jangan gunakan mailer log untuk email reset di produksi.

Pengujian mencakup pengiriman dan render email, token salah/kedaluwarsa/sekali pakai, kecocokan akun, validasi kata sandi, pembatasan permintaan, serta login setelah reset.

## Pengamanan pemulihan akun

- Setiap login menyimpan sidik kata sandi di sesi. Setelah reset, sesi dengan sidik lama ditolak pada permintaan berikutnya. Sesi lama sebelum pembaruan ini juga harus masuk kembali. Token Ingat saya dirotasi saat reset.
- Permintaan reset dibatasi 5 per menit per IP dan 3 per jam per email (termasuk email tidak terdaftar), dengan jeda pengiriman ulang 60 detik. Respons tetap sama saat batas akun tercapai atau pengiriman SMTP gagal agar keberadaan akun tidak terungkap.
- Setelah reset berhasil, aplikasi mengirim email pemberitahuan perubahan kata sandi. Kegagalan SMTP pemberitahuan dicatat tanpa membatalkan perubahan atau mengaktifkan ulang token. Pemberitahuan yang gagal tidak otomatis dikirim ulang.
- Log audit mencatat permintaan, pembatasan, reset ditolak/berhasil, dan kegagalan SMTP. Identitas email pada log audit menggunakan HMAC; token dan kata sandi tidak dicatat. Mailer `log` lokal tetap menyimpan isi email: batasi akses `storage/logs` dan gunakan SMTP di produksi.
- Tautan reset selalu memakai `APP_URL`, bukan Host permintaan. Pastikan alamat ini benar dan menggunakan HTTPS di produksi.

Sebelum produksi, atur `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda.com`, `SESSION_SECURE_COOKIE=true`, serta SMTP dan pengirim terverifikasi. Cookie Secure aktif secara default untuk lingkungan production. Mailer bawaan production adalah SMTP; ubah `MAIL_MAILER=log` yang berasal dari contoh lokal menjadi `smtp`.

```powershell
php artisan config:clear
php artisan security:check
```

Perintah pemeriksaan mengembalikan kode 1 bila konfigurasi dasar belum siap. Pemeriksaan ini tidak menguji kredensial/pengiriman SMTP, sertifikat TLS, izin file, atau reverse proxy. Document root server harus menunjuk ke `public`; batasi akses `.env`, file sesi, dan log melalui konfigurasi server dan izin sistem operasi. Pada beberapa server, gunakan penyimpanan cache bersama (misalnya Redis) agar batas permintaan berlaku lintas server.

2FA ditunda ke tahap berikutnya; proyek ini belum memiliki pengaturan peran admin atau pendaftaran faktor kedua.
