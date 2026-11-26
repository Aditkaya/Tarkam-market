<?php

// Run from the terminal: php database/setup-local.php
// Optional connection overrides: DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$pdo->exec('CREATE DATABASE IF NOT EXISTS `tarkam_market` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE `tarkam_market`');
$pdo->exec('CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
$pdo->exec('CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$pdo->beginTransaction();
try {
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute(['admin@tarkam.id']);
    if (! $check->fetchColumn()) {
        $insert = $pdo->prepare('INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $insert->execute(['Admin Tarkam', 'admin@tarkam.id', password_hash('Tarkam123!', PASSWORD_BCRYPT, ['cost' => 12])]);
    }
    // This schema is identical to the included Laravel migration.
    $migration = '0001_01_01_000000_create_users_table';
    $check = $pdo->prepare('SELECT id FROM migrations WHERE migration = ?');
    $check->execute([$migration]);
    if (! $check->fetchColumn()) {
        $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, 1)')->execute([$migration]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

if (! file_exists($root.'/.env')) {
    $env = file_get_contents($root.'/.env.example');
    $env = str_replace('APP_KEY=', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), $env);
    foreach (['DB_HOST' => $host, 'DB_PORT' => $port, 'DB_USERNAME' => $username, 'DB_PASSWORD' => $password] as $key => $value) {
        $quoted = '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
        $env = preg_replace_callback('/^'.$key.'=.*$/m', fn () => $key.'='.$quoted, $env);
    }
    file_put_contents($root.'/.env', $env);
    echo ".env dibuat dengan koneksi MySQL dan APP_KEY.\n";
}

$check = $pdo->prepare('SELECT password FROM users WHERE email = ?');
$check->execute(['admin@tarkam.id']);
$valid = password_verify('Tarkam123!', $check->fetchColumn());
echo 'Database: '.$pdo->query('SELECT DATABASE()')->fetchColumn().PHP_EOL;
echo 'Jumlah pengguna: '.$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn().PHP_EOL;
echo 'Verifikasi kata sandi akun demo: '.($valid ? 'OK' : 'akun sudah memiliki kata sandi berbeda').PHP_EOL;
echo 'Tabel: '.implode(', ', $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN)).PHP_EOL;
