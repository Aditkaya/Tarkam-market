<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard • Tarkam Market.ID</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <main>
        <div class="brand">Tarkam <span>Market.ID</span></div>
        <section class="card">
            <h1>Halo, {{ auth()->user()->name }}!</h1>
            <p class="intro">Anda berhasil masuk ke Tarkam Market.ID.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="submit">Keluar</button>
            </form>
        </section>
    </main>
</body>
</html>
