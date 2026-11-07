/* =====================================================================
   Gamifica 3D — Proposta da nova linha de jogos
   js/app.js — comportamentos gerais da página: menu no celular, destaque
   da seção atual na navegação e avisos de recursos ainda não disponíveis.
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

  /* ---------- Recursos das próximas fases ---------- */
  // Botão "Resumo" (painel resumo — Fase 4)
  document.querySelectorAll('[data-fase="4"]').forEach((btn) => {
    btn.addEventListener('click', () => avisar('O painel resumo chega na Fase 4 (acabamento).'));
  });
})();
