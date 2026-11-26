<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard • Tarkam Market.ID</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
@php
    $user = auth()->user();
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));
    $icons = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'bag' => '<path d="M5 8h14l1 13H4L5 8Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
        'box' => '<path d="m12 3 9 5v9l-9 5-9-5V8l9-5Z"/><path d="m3 8 9 5 9-5M12 13v9M7.5 5.5l9 5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'wallet' => '<rect x="3" y="5" width="18" height="15" rx="3"/><path d="M3 9h18m-5 4h5v4h-5z"/>',
    ];
@endphp
<a class="skip-link" href="#overview">Lewati navigasi</a>
<aside class="sidebar" aria-label="Menu utama">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">t.</span><span>Tarkam <small>MARKET.ID</small></span></a>
    <div class="workspace"><span class="workspace-icon">TM</span><div>Workspace Anda<small>Tarkam Market.ID</small></div></div>
    <p class="nav-label">MENU UTAMA</p>
    <nav>
        @foreach (['overview' => ['grid', 'Ringkasan'], 'orders' => ['bag', 'Pesanan'], 'products' => ['box', 'Produk'], 'account' => ['user', 'Akun saya']] as $anchor => [$icon, $label])
        <a class="nav-link {{ $loop->first ? 'active' : '' }}" href="#{{ $anchor }}" @if($loop->first) aria-current="location" @endif><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="sidebar-bottom"><div class="community-note"><small>DARI LAPANGAN, UNTUK TIM.</small><p>Kebutuhan tim dan komunitas, dalam satu tempat.</p><span></span></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Keluar akun</button></form></div>
</aside>
<div class="app-shell">
    <header class="topbar"><div class="breadcrumb">Workspace <span>/</span> <strong>Dashboard</strong></div><a class="user-chip" href="#account"><span class="avatar">{{ $initial }}</span><span>{{ $user->name }}</span></a></header>
    <main id="overview">
        <div class="page-heading"><div><p class="eyebrow">WORKSPACE / RINGKASAN</p><h1>Dashboard</h1><p>Selamat datang kembali, {{ $user->name }}.</p></div><time class="date-pill" datetime="{{ now()->toDateString() }}">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</time></div>
        <section class="welcome-banner" aria-labelledby="welcome-title"><div class="welcome-copy"><span class="banner-label">SATU TIM. SATU TUJUAN.</span><h2 id="welcome-title">Langkah besar dimulai<br>dari tim yang siap.</h2><p>Pantau aktivitas dan kelola kebutuhan komunitas Anda dari satu dashboard.</p><a class="light-button" href="#workspace-status">Lihat ringkasan workspace</a></div><div class="pitch-art" aria-hidden="true"><div class="pitch"><div class="pitch-center"></div><div class="goal goal-left"></div><div class="goal goal-right"></div><i class="player one"></i><i class="player two"></i><i class="player three"></i></div><span>BUILT FOR YOUR TEAM</span></div></section>
        <section id="workspace-status" class="metrics" aria-label="Ringkasan workspace">
            @foreach ([['bag', 'Total pesanan'], ['wallet', 'Total transaksi'], ['box', 'Produk tersedia'], ['grid', 'Aktivitas hari ini']] as [$icon, $label])
            <article class="metric"><div class="metric-top"><span>{{ $label }}</span><span class="metric-icon"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$icon] !!}</svg></span></div><strong>—</strong><p>Data belum tersedia</p></article>
            @endforeach
        </section>
        <div class="content-grid">
            <section class="panel orders-panel" id="orders" aria-labelledby="orders-title"><div class="panel-heading"><div><h2 id="orders-title">Pesanan terbaru</h2><p>Ringkasan pesanan tim Anda.</p></div><span class="tag">Pesanan</span></div><div class="table-scroll"><table><thead><tr><th scope="col">Pesanan</th><th scope="col">Tanggal</th><th scope="col">Total</th><th scope="col">Status</th></tr></thead><tbody><tr><td colspan="4"><div class="empty-state"><span class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons['bag'] !!}</svg></span><h3>Belum ada pesanan untuk ditampilkan</h3><p>Ringkasan pesanan akan tersedia setelah fitur transaksi diaktifkan.</p></div></td></tr></tbody></table></div></section>
            <section class="panel account-panel" id="account" aria-labelledby="account-title"><div class="panel-heading"><h2 id="account-title">Akun saya</h2><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icons['user'] !!}</svg></div><div class="profile"><span class="avatar profile-avatar">{{ $initial }}</span><h3>{{ $user->name }}</h3><p>{{ $user->email }}</p></div><dl><div><dt>Terdaftar sejak</dt><dd>{{ $user->created_at?->locale('id')->translatedFormat('d M Y') ?? '—' }}</dd></div><div><dt>Akses akun</dt><dd>Sudah masuk</dd></div></dl><p class="security-note">Gunakan kata sandi unik dan keluar dari akun setelah memakai perangkat bersama.</p><form class="mobile-logout" method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Keluar akun</button></form></section>
            <section class="panel products-panel" id="products" aria-labelledby="products-title"><div class="panel-heading"><div><h2 id="products-title">Katalog tim</h2><p>Ruang untuk kebutuhan tim dan komunitas Anda.</p></div><span class="tag">Produk</span></div><div class="catalog-empty"><span class="catalog-icon" aria-hidden="true"><svg viewBox="0 0 24 24">{!! $icons['box'] !!}</svg></span><div><h3>Katalog Anda dimulai di sini</h3><p>Belum ada produk untuk ditampilkan. Pengelolaan katalog akan tersedia pada pengembangan berikutnya.</p></div></div></section>
        </div>
        <footer><span>© {{ date('Y') }} Tarkam Market.ID</span><span>Untuk tim. Untuk komunitas.</span></footer>
    </main>
</div>
<script src="{{ asset('js/dashboard.js') }}" defer></script>
</body>
</html>
