@extends('auth.password-layout')
@section('title', 'Lupa kata sandi?')
@section('intro', 'Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.')
@section('form')
<form method="POST" action="{{ route('password.email') }}">
  @csrf
  <div class="field">
    <label for="email">Alamat email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@email.com" maxlength="255" required autofocus>
  </div>
  <button class="submit" type="submit">Kirim tautan reset</button>
</form>
@endsection
