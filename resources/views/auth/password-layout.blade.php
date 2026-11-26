<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="referrer" content="no-referrer">
  <title>@yield('title') • Tarkam Market.ID</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
  <main>
    <div class="brand">Tarkam <span>Market.ID</span></div>
    <section class="card" aria-labelledby="page-title">
      <h1 id="page-title">@yield('title')</h1>
      <p class="intro">@yield('intro')</p>
      @if ($errors->any())
        <div class="errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
      @endif
      @if (session('status'))<p class="message" role="status">{{ session('status') }}</p>@endif
      @yield('form')
      <p class="notice"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
    </section>
    <footer>© {{ date('Y') }} Tarkam Market.ID</footer>
  </main>
</body>
</html>
