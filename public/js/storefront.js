(() => {
'use strict';
const themeToggles = document.querySelectorAll('[data-theme-toggle]');
let themePreference = document.documentElement.dataset.themePreference || 'light';
const applyTheme = () => {
 const dark = themePreference === 'dark';
 document.documentElement.dataset.theme = dark ? 'dark' : 'light';
 document.documentElement.dataset.themePreference = themePreference;
 themeToggles.forEach(toggle => {
  toggle.setAttribute('aria-checked', String(dark));
  toggle.title = dark ? 'Chuyển sang giao diện sáng' : 'Chuyển sang giao diện tối';
 });
 document.querySelector('meta[name="theme-color"]')?.setAttribute('content', dark ? '#17251d' : '#deeee5');
};
themeToggles.forEach(toggle => toggle.addEventListener('click', () => {
 themePreference = themePreference === 'dark' ? 'light' : 'dark';
 try { localStorage.setItem('techshop-theme', themePreference); } catch (_) {}
 applyTheme();
}));
window.addEventListener('storage', event => {
 if (event.key !== 'techshop-theme') return;
 themePreference = ['light', 'dark'].includes(event.newValue) ? event.newValue : 'light';
 applyTheme();
});
applyTheme();
document.querySelectorAll('form[data-confirm]').forEach(form => {
 form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); });
});
document.querySelectorAll('[data-carousel]').forEach(carousel => {
 const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
 const dots = Array.from(carousel.querySelectorAll('[data-slide-to]'));
 const pause = carousel.querySelector('[data-slide-pause]');
 const media = window.matchMedia('(prefers-reduced-motion: reduce)');
 let index = 0, timer, paused = media.matches, hovered = false, focused = false;
 const schedule = () => {
  clearInterval(timer);
  carousel.classList.toggle('is-paused', paused || hovered || focused || document.hidden);
  if (!paused && !hovered && !focused && !document.hidden) timer = setInterval(() => show(index + 1), 7000);
 };
 const show = next => {
  index = (next + slides.length) % slides.length;
  slides.forEach((slide, i) => {
   const active = i === index;
   slide.hidden = !active; slide.inert = !active; slide.classList.toggle('is-active', active);
  });
  dots.forEach((dot, i) => { dot.classList.toggle('is-active', i === index); dot.setAttribute('aria-pressed', String(i === index)); });
  carousel.querySelector('.slide-count').textContent = String(index + 1).padStart(2, '0') + ' / 03';
 };
 const updatePause = () => {
  pause.setAttribute('aria-pressed', String(paused));
  pause.setAttribute('aria-label', paused ? pause.dataset.resumeLabel : pause.dataset.pauseLabel);
  pause.innerHTML = paused ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m8 4 12 8-12 8V4Z"/></svg>' : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 5v14M15 5v14"/></svg>';
  schedule();
 };
 dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); schedule(); }));
 carousel.querySelector('[data-slide-prev]').addEventListener('click', () => { show(index - 1); schedule(); });
 carousel.querySelector('[data-slide-next]').addEventListener('click', () => { show(index + 1); schedule(); });
 pause.addEventListener('click', () => { paused = !paused; updatePause(); });
 carousel.addEventListener('mouseenter', () => { hovered = true; schedule(); });
 carousel.addEventListener('mouseleave', () => { hovered = false; schedule(); });
 carousel.addEventListener('focusin', () => { focused = true; schedule(); });
 carousel.addEventListener('focusout', event => { if (!carousel.contains(event.relatedTarget)) { focused = false; schedule(); } });
 carousel.addEventListener('keydown', event => {
  if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') { event.preventDefault(); show(index + (event.key === 'ArrowRight' ? 1 : -1)); schedule(); }
 });
 let startX = 0, startY = 0;
 carousel.addEventListener('touchstart', e => { startX = e.changedTouches[0].clientX; startY = e.changedTouches[0].clientY; }, {passive:true});
 carousel.addEventListener('touchend', e => {
  const dx = e.changedTouches[0].clientX - startX, dy = e.changedTouches[0].clientY - startY;
  if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy)) { show(index + (dx < 0 ? 1 : -1)); schedule(); }
 }, {passive:true});
 document.addEventListener('visibilitychange', schedule);
 media.addEventListener('change', () => { paused = media.matches; updatePause(); });
 updatePause();
});
document.querySelectorAll('[data-quantity]').forEach(control => {
 const input = control.querySelector('input');
 control.querySelectorAll('[data-step]').forEach(button => button.addEventListener('click', () => {
  const min = Number(input.min || 1), max = Number(input.max || 999);
  input.value = Math.min(max, Math.max(min, (Number(input.value) || min) + Number(button.dataset.step)));
  input.dispatchEvent(new Event('change', {bubbles:true}));
 }));
});
const accountButton = document.querySelector('[data-account-toggle]');
if (accountButton) {
 const menu = document.getElementById(accountButton.getAttribute('aria-controls'));
 const close = () => { menu.hidden = true; accountButton.setAttribute('aria-expanded', 'false'); };
 accountButton.addEventListener('click', () => { menu.hidden = !menu.hidden; accountButton.setAttribute('aria-expanded', String(!menu.hidden)); });
 document.addEventListener('click', e => { if (!e.target.closest('.account-menu')) close(); });
 document.addEventListener('keydown', e => { if (e.key === 'Escape' && !menu.hidden) { close(); accountButton.focus(); } });
}
document.querySelectorAll('[data-filter-panel]').forEach(panel => {
 const mobile = window.matchMedia('(max-width: 600px)');
 const update = () => { panel.open = !mobile.matches; };
 update(); mobile.addEventListener('change', update);
});
})();
