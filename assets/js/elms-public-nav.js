/* ============================================================
   Arandia College eLMS — phone/tablet menu for the public pages
   File: assets/js/elms-public-nav.js  (styles: assets/css/elms-public.css)
   Used by FAQ.php, contact.php, helpcenter.php, helpdesk.php, privacy.php.
   Adds a menu button to the top bar. It opens the page's own nav links
   as a slide-in sidebar from the same side as the menu button
   (same look as the eLMS sidebar).
   Close it with the X, the dimmed backdrop, Escape, or by picking a link.
   ============================================================ */
(function () {
  'use strict';
  var nav = document.querySelector('body > nav');
  var links = nav && nav.querySelector('.nav-links');
  if (!links || nav.querySelector('.pub-nav-toggle')) return;

  // ── Menu button (top bar) ──
  var btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'pub-nav-toggle';
  btn.setAttribute('aria-label', 'Open menu');
  btn.setAttribute('aria-controls', 'pubDrawer');
  btn.setAttribute('aria-expanded', 'false');
  btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>';
  nav.appendChild(btn);

  // ── Sidebar drawer, built from the same links ──
  var backdrop = document.createElement('div');
  backdrop.className = 'pub-drawer-backdrop';
  backdrop.setAttribute('aria-hidden', 'true');

  var drawer = document.createElement('div');
  drawer.className = 'pub-drawer';
  drawer.id = 'pubDrawer';
  drawer.setAttribute('aria-label', 'Menu');
  drawer.setAttribute('aria-hidden', 'true');

  var head = document.createElement('div');
  head.className = 'pub-drawer-head';
  var logo = nav.querySelector('.nav-brand img');
  if (logo) {
    var img = document.createElement('img');
    img.src = logo.getAttribute('src'); img.alt = ''; img.width = 44; img.height = 44;
    head.appendChild(img);
  }
  var name = document.createElement('strong');
  name.textContent = 'Arandia College';
  head.appendChild(name);
  var close = document.createElement('button');
  close.type = 'button';
  close.className = 'pub-drawer-close';
  close.setAttribute('aria-label', 'Close menu');
  close.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
  head.appendChild(close);
  drawer.appendChild(head);

  var list = document.createElement('div');
  list.setAttribute('role', 'navigation');
  list.className = 'pub-drawer-nav';
  var here = location.pathname.split('/').pop() || 'index.php';
  var login = null;
  links.querySelectorAll('a[href]').forEach(function (a) {
    var c = document.createElement('a');
    c.href = a.getAttribute('href');
    c.textContent = a.textContent.trim();
    if (a.classList.contains('btn-login')) { c.className = 'pub-drawer-login'; login = c; return; }
    if (c.getAttribute('href') === here) { c.className = 'is-current'; c.setAttribute('aria-current', 'page'); }
    list.appendChild(c);
  });
  drawer.appendChild(list);
  if (login) drawer.appendChild(login);

  document.body.appendChild(backdrop);
  document.body.appendChild(drawer);

  function isOpen() { return document.body.classList.contains('pub-nav-open'); }
  // The sidebar slides in from the same side of the screen as the menu button
  function placeOnButtonSide() {
    var r = btn.getBoundingClientRect();
    var right = (r.left + r.width / 2) > (window.innerWidth / 2);
    if (drawer.classList.contains('from-right') === right) return;
    drawer.style.transition = 'none';            // switch sides without a visible slide
    drawer.classList.toggle('from-right', right);
    void drawer.offsetWidth;
    drawer.style.transition = '';
    void drawer.offsetWidth;
  }
  function set(open) {
    if (open) placeOnButtonSide();
    document.body.classList.toggle('pub-nav-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) setTimeout(function () { close.focus({ preventScroll: true }); }, 60);
  }
  btn.addEventListener('click', function () { set(!isOpen()); });
  close.addEventListener('click', function () { set(false); btn.focus(); });
  backdrop.addEventListener('click', function () { set(false); });
  drawer.addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && isOpen()) { set(false); btn.focus(); } });
  window.matchMedia('(min-width: 901px)').addEventListener('change', function (m) { if (m.matches) set(false); });
  window.addEventListener('pageshow', function () { set(false); });
})();
