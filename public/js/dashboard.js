const links = [...document.querySelectorAll('.nav-link')];
function updateNavigation() {
  links.forEach(link => {
    const active = link.getAttribute('href') === (location.hash || '#overview');
    link.classList.toggle('active', active);
    if (active) link.setAttribute('aria-current', 'location');
    else link.removeAttribute('aria-current');
  });
}
window.addEventListener('hashchange', updateNavigation);
updateNavigation();
