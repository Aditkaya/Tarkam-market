<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('security:check', function () {
    $checks = [
        'APP_ENV menggunakan production' => app()->environment('production'),
        'APP_DEBUG dimatikan' => ! config('app.debug'),
        'APP_KEY tersedia' => (bool) config('app.key'),
        'APP_URL menggunakan HTTPS' => str_starts_with(config('app.url'), 'https://'),
        'Cookie sesi Secure aktif' => (bool) config('session.secure'),
        'Cookie sesi HttpOnly aktif' => (bool) config('session.http_only'),
        'SMTP dikonfigurasi' => config('mail.default') === 'smtp'
            && (bool) config('mail.mailers.smtp.host')
            && ! in_array(config('mail.mailers.smtp.host'), ['localhost', '127.0.0.1']),
        'Alamat pengirim email dikonfigurasi' => (bool) filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)
            && config('mail.from.address') !== 'noreply@tarkam.id',
    ];
    foreach ($checks as $label => $passed) {
        $passed ? $this->info('OK: '.$label) : $this->error('PERLU DIATUR: '.$label);
    }
    $this->comment('Pemeriksaan konfigurasi saja; tidak menguji pengiriman SMTP, TLS server, atau izin file/log.');

    return in_array(false, $checks, true) ? 1 : 0;
})->purpose('Memeriksa konfigurasi keamanan dasar sebelum produksi tanpa menampilkan rahasia.');
