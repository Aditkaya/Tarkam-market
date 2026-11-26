<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#146b48">
  <title>Masuk • Tarkam Market.ID</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
  <main>
    <div class="brand"><div class="mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14l1 12H4L5 8Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></svg></div><div>Tarkam <span>Market.ID</span></div></div>
    <section class="card" aria-labelledby="login-title">
      <h1 id="login-title">Selamat datang kembali</h1>
      <p class="intro">Masuk untuk melanjutkan ke akun Anda.</p>
      <form id="login-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        @if ($errors->any())<div class="errors" role="alert">{{ $errors->first() }}</div>@endif
        @if (session('status'))<p class="message" role="status">{{ session('status') }}</p>@endif
        <div class="field"><label for="email">Alamat email</label><input id="email" name="email" type="email" placeholder="nama@email.com" autocomplete="username" value="{{ old('email') }}" required></div>
        <div class="field"><label for="password">Kata sandi</label><div class="password"><input id="password" name="password" type="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required><button class="toggle" type="button" aria-label="Tampilkan kata sandi" aria-pressed="false" aria-controls="password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
        <div class="options"><label class="remember"><input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>Ingat saya</label></div>
        <button class="submit" type="submit">Masuk</button>
      </form>
      <p class="notice">Satu tempat untuk kebutuhan tim dan komunitas Anda.</p>
    </section>
    <footer>© <span>{{ date('Y') }}</span> Tarkam Market.ID</footer>
  </main>
  <script src="{{ asset('js/login.js') }}" defer></script>
</body>
</html>
