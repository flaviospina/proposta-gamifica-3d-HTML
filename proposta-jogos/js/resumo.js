/* =====================================================================
   Gamifica 3D — Proposta · js/resumo.js
   Painel resumo (diálogo): abrir/fechar, atualizar após cada observação
   salva (api/resumo.php) e botão de impressão.
   ===================================================================== */
(function () {
  'use strict';
  const dlg = document.getElementById('resumo');
  const btn = document.getElementById('abrir-resumo');
  if (!dlg || !btn) return;

  function esc(s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function abrir() {
    if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
    document.body.classList.add('sem-rolagem');
  }
  function fechar() {
    if (dlg.open) dlg.close(); else dlg.removeAttribute('open');
    document.body.classList.remove('sem-rolagem');
  }

  btn.addEventListener('click', abrir);
  dlg.querySelector('.resumo__fechar').addEventListener('click', fechar);
  dlg.addEventListener('close', () => document.body.classList.remove('sem-rolagem'));
  // clique fora do painel fecha
  dlg.addEventListener('click', (ev) => { if (ev.target === dlg) fechar(); });
  // "Ir à seção": fecha e rola
  dlg.addEventListener('click', (ev) => {
    const a = ev.target.closest('a.resumo__ir');
    if (!a) return;
    ev.preventDefault();
    fechar();
    const alvo = document.querySelector(a.getAttribute('href'));
    if (alvo) alvo.scrollIntoView({ behavior: 'smooth', block: 'start' });
    history.replaceState(null, '', a.getAttribute('href'));
  });

  const imprimir = document.getElementById('imprimir');
  if (imprimir) imprimir.addEventListener('click', () => { fechar(); setTimeout(() => window.print(), 150); });

  /* ---------- atualização ---------- */
  function linhaHtml(r) {
    const selo = r.ultimo_parecer
      ? '<span class="selo selo--' + esc(r.ultimo_parecer) + '">' + esc(r.ultimo_parecer_rotulo) + '</span>'
      : '<span class="selo selo--vazio">Sem parecer</span>';
    return '<tr data-slug="' + esc(r.slug) + '"><th scope="row">' + esc(r.nome) + '</th><td>' + selo + '</td>'
      + '<td class="num">' + r.total + '</td><td>' + (r.ultima_em ? esc(r.ultima_em) : '<span class="texto-mudo">—</span>') + '</td>'
      + '<td class="acao"><a href="#' + esc(r.slug) + '" class="resumo__ir">Ir à seção</a></td></tr>';
  }

  async function atualizar() {
    try {
      const resp = await fetch((window.PJ ? window.PJ.api : 'api/') + 'resumo.php', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const json = await resp.json();
      if (!json.ok) return;
      const linhas = json.data.jogos.map(linhaHtml).join('');
      const total = json.data.jogos.reduce((s, j) => s + j.total, 0);
      document.querySelectorAll('[data-resumo-linhas]').forEach((tb) => { tb.innerHTML = linhas; });
      document.querySelectorAll('[data-resumo-total]').forEach((td) => { td.textContent = total; });
    } catch (e) { /* silencioso: o resumo é só leitura */ }
  }
  document.addEventListener('pj:observacoes-atualizadas', atualizar);
})();
