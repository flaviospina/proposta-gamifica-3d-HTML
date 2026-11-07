/* =====================================================================
   Gamifica 3D — Proposta da nova linha de jogos
   js/app.js — comportamentos gerais da página: menu no celular, destaque
   da seção atual na navegação e lightbox das imagens modelo.
   ===================================================================== */
(function () {
  'use strict';

  /* ---------- Aviso rápido (toast) ---------- */
  const toast = document.getElementById('toast');
  let toastTimer = null;
  function avisar(mensagem) {
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('visivel');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('visivel'), 3200);
  }

  /* ---------- Menu no celular ---------- */
  const nav = document.getElementById('nav');
  const toggle = document.querySelector('.nav-toggle');
  if (nav && toggle) {
    toggle.addEventListener('click', () => {
      const aberto = nav.classList.toggle('aberto');
      toggle.setAttribute('aria-expanded', String(aberto));
      toggle.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');
      toggle.textContent = aberto ? '✕' : '☰';
    });
    nav.querySelectorAll('a').forEach((a) => {
      a.addEventListener('click', () => {
        nav.classList.remove('aberto');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.textContent = '☰';
      });
    });
  }

  /* ---------- Lightbox das imagens modelo ---------- */
  const lb = document.getElementById('lightbox');
  if (lb) {
    const img = lb.querySelector('.lightbox__img');
    const legenda = lb.querySelector('.lightbox__legenda');
    let ultimoFoco = null;
    function abrirLightbox(el) {
      img.src = el.currentSrc || el.src;
      img.alt = el.alt || '';
      const fig = el.closest('figure');
      const cap = fig && fig.querySelector('figcaption');
      legenda.textContent = (el.alt ? el.alt + ' · ' : '') + (cap ? cap.textContent : '');
      ultimoFoco = document.activeElement;
      lb.hidden = false;
      document.body.classList.add('sem-rolagem');
      lb.querySelector('.lightbox__fechar').focus();
    }
    function fecharLightbox() {
      lb.hidden = true;
      img.src = '';
      document.body.classList.remove('sem-rolagem');
      if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus();
    }
    document.querySelectorAll('img[data-lightbox]').forEach((el) => {
      el.setAttribute('tabindex', '0');
      el.setAttribute('role', 'button');
      el.setAttribute('aria-label', 'Ampliar imagem');
      el.addEventListener('click', () => abrirLightbox(el));
      el.addEventListener('keydown', (ev) => { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); abrirLightbox(el); } });
    });
    lb.addEventListener('click', (ev) => { if (ev.target === lb || ev.target.closest('.lightbox__fechar')) fecharLightbox(); });
    document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape' && !lb.hidden) fecharLightbox(); });
  }

  /* ---------- Seção atual destacada no menu ---------- */
  const linksNav = Array.from(document.querySelectorAll('.nav a[href^="#"]'));
  const secoes = linksNav
    .map((a) => document.querySelector(a.getAttribute('href')))
    .filter(Boolean);
  if ('IntersectionObserver' in window && secoes.length) {
    const obs = new IntersectionObserver(
      (entradas) => {
        entradas.forEach((e) => {
          if (!e.isIntersecting) return;
          linksNav.forEach((a) => a.classList.toggle('ativo', a.getAttribute('href') === '#' + e.target.id));
        });
      },
      { rootMargin: '-40% 0px -55% 0px' }
    );
    secoes.forEach((s) => obs.observe(s));
  }
})();
