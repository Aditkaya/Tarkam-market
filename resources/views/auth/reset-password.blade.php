@extends('auth.password-layout')
@section('title', 'Buat kata sandi baru')
@section('intro', 'Gunakan minimal 12 karakter yang mengandung huruf dan angka. Tautan berlaku 60 menit dan hanya dapat digunakan sekali.')
@section('form')
<form method="POST" action="{{ route('password.update') }}">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <div class="field">
    <label for="email">Alamat email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="username" maxlength="255" required>
  </div>
  <div class="field">
    <label for="password">Kata sandi baru</label>
    <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="4096" required>
  </div>
  <div class="field">
    <label for="password_confirmation">Konfirmasi kata sandi baru</label>
    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="4096" required>
  </div>
  <button class="submit" type="submit">Simpan kata sandi</button>
</form>
<p class="notice"><a href="{{ route('password.request') }}">Minta tautan reset baru</a></p>
@endsection
