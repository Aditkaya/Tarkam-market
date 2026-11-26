const password = document.getElementById('password');
const toggle = document.querySelector('.toggle');
toggle.addEventListener('click', () => {
  const visible = password.type === 'password';
  password.type = visible ? 'text' : 'password';
  toggle.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
  toggle.setAttribute('aria-pressed', String(visible));
});
