/* =====================================================================
   Gamifica 3D — Proposta · js/observacoes.js
   Salva, edita e exclui observações sem recarregar a página (fetch → api/).
   Usa window.PJ = { csrf, usuario, api, pareceres } definido no index.php.
   ===================================================================== */
(function () {
  'use strict';
  if (!window.PJ) return;
  const PJ = window.PJ;

  /* ---------- utilidades ---------- */
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  async function chamar(endpoint, corpo, metodo) {
    metodo = metodo || 'POST';
    const opcoes = {
      method: metodo,
      headers: { 'Accept': 'application/json', 'X-CSRF-Token': PJ.csrf },
      credentials: 'same-origin',
    };
    if (metodo === 'POST') {
      opcoes.headers['Content-Type'] = 'application/json';
      opcoes.body = JSON.stringify(Object.assign({ csrf: PJ.csrf }, corpo || {}));
    }
    let resp, json;
    try {
      resp = await fetch(PJ.api + endpoint, opcoes);
    } catch (e) {
      return { ok: false, mensagem: 'Sem conexão com o servidor. Verifique a internet e tente de novo.' };
    }
    try {
      json = await resp.json();
    } catch (e) {
      return { ok: false, mensagem: 'Resposta inesperada do servidor (HTTP ' + resp.status + ').' };
    }
    if (resp.status === 401) {
      json.mensagem = (json.mensagem || 'Sessão expirada.') + ' ';
      json.relogar = true;
    }
    return json;
  }

  function mostrarStatus(form, texto, erro) {
    const el = form.querySelector('.obs__status');
    if (!el) return;
    el.textContent = texto;
    el.classList.toggle('erro', !!erro);
    clearTimeout(el._t);
    if (!erro) el._t = setTimeout(() => { el.textContent = ''; }, 4000);
  }

  /* ---------- renderização (mesma estrutura do inc/render.php) ---------- */
  function seloHtml(parecer, rotulo) {
    return parecer
      ? '<span class="selo selo--' + esc(parecer) + '">' + esc(rotulo) + '</span>'
      : '<span class="selo selo--vazio">Comentário</span>';
  }

  function itemHtml(o) {
    const editada = o.atualizado_em
      ? '<span class="obs-item__editada" title="Editada em ' + esc(o.atualizado_em) + '">editada</span>' : '';
    const acoes = o.propria
      ? '<div class="obs-item__acoes"><button type="button" class="editar">Editar</button><button type="button" class="excluir">Excluir</button></div>' : '';
    const texto = o.texto ? '<p class="obs-item__texto">' + esc(o.texto) + '</p>' : '';
    return '<article class="obs-item" data-id="' + o.id + '" data-parecer="' + esc(o.parecer || '') + '">'
      + '<div class="obs-item__meta"><span class="obs-item__autor">' + esc(o.autor) + '</span>'
      + seloHtml(o.parecer, o.parecer_rotulo)
      + '<span class="obs-item__data">' + esc(o.criado_em) + '</span>' + editada + '</div>'
      + texto + acoes + '</article>';
  }

  function atualizarBloco(data) {
    const slug = data.jogo;
    const hist = document.querySelector('[data-historico="' + slug + '"]');
    if (hist) {
      hist.innerHTML = data.observacoes.length
        ? data.observacoes.map(itemHtml).join('')
        : '<p class="obs__vazio">Nenhuma observação ainda. O histórico aparecerá aqui, com autor, data/hora e parecer.</p>';
    }
    const cont = document.querySelector('[data-contagem="' + slug + '"]');
    if (cont) cont.textContent = data.total + (data.total === 1 ? ' observação' : ' observações');
    const selo = document.querySelector('[data-selo="' + slug + '"]');
    if (selo) {
      selo.className = 'selo ' + (data.ultimo_parecer ? 'selo--' + data.ultimo_parecer : 'selo--vazio');
      selo.textContent = data.ultimo_parecer_rotulo;
    }
    document.dispatchEvent(new CustomEvent('pj:observacoes-atualizadas', { detail: data }));
  }

  /* ---------- formulário: criar / editar ---------- */
  function limparForm(form) {
    form.querySelector('input[name="id"]').value = '';
    form.querySelector('textarea').value = '';
    form.querySelectorAll('input[name="parecer"]').forEach((r) => { r.checked = false; });
    form.querySelector('button[type="submit"]').textContent = 'Salvar observação';
    form.querySelector('.obs__cancelar').hidden = true;
    form.classList.remove('editando');
  }

  function entrarEmEdicao(form, item) {
    const id = item.dataset.id;
    const parecer = item.dataset.parecer || '';
    const texto = item.querySelector('.obs-item__texto');
    form.querySelector('input[name="id"]').value = id;
    form.querySelector('textarea').value = texto ? texto.textContent : '';
    form.querySelectorAll('input[name="parecer"]').forEach((r) => { r.checked = (r.value === parecer); });
    form.querySelector('button[type="submit"]').textContent = 'Salvar alteração';
    form.querySelector('.obs__cancelar').hidden = false;
    form.classList.add('editando');
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    form.querySelector('textarea').focus();
  }

  document.querySelectorAll('form.obs__form').forEach((form) => {
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const botao = form.querySelector('button[type="submit"]');
      const id = form.querySelector('input[name="id"]').value;
      const parecerEl = form.querySelector('input[name="parecer"]:checked');
      const corpo = {
        jogo: form.dataset.jogo,
        parecer: parecerEl ? parecerEl.value : '',
        texto: form.querySelector('textarea').value.trim(),
      };
      if (!corpo.parecer && !corpo.texto) {
        mostrarStatus(form, 'Escolha um parecer ou escreva uma observação.', true);
        return;
      }
      botao.disabled = true;
      mostrarStatus(form, 'Salvando…');
      let resp;
      if (id) {
        corpo.id = Number(id);
        resp = await chamar('editar.php', corpo);
      } else {
        resp = await chamar('salvar.php', corpo);
      }
      botao.disabled = false;
      if (resp.ok) {
        atualizarBloco(resp.data);
        limparForm(form);
        mostrarStatus(form, resp.mensagem || 'Salvo ✓');
      } else {
        mostrarStatus(form, resp.mensagem || 'Não foi possível salvar.', true);
        if (resp.relogar) setTimeout(() => { location.href = 'login.php#' + form.dataset.jogo; }, 1500);
      }
    });

    form.querySelector('.obs__cancelar').addEventListener('click', () => {
      limparForm(form);
      mostrarStatus(form, 'Edição cancelada.');
    });
  });

  /* ---------- histórico: editar / excluir (delegação) ---------- */
  document.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('.obs-item__acoes button');
    if (!btn) return;
    const item = btn.closest('.obs-item');
    const bloco = btn.closest('.obs');
    const form = bloco && bloco.querySelector('form.obs__form');
    if (!item || !form) return;

    if (btn.classList.contains('editar')) {
      entrarEmEdicao(form, item);
      return;
    }
    if (btn.classList.contains('excluir')) {
      if (!confirm('Excluir esta observação? Ela sai da página, mas fica guardada no banco.')) return;
      btn.disabled = true;
      const resp = await chamar('excluir.php', { id: Number(item.dataset.id) });
      if (resp.ok) {
        atualizarBloco(resp.data);
        if (form.querySelector('input[name="id"]').value === item.dataset.id) limparForm(form);
        mostrarStatus(form, resp.mensagem || 'Observação excluída.');
      } else {
        btn.disabled = false;
        mostrarStatus(form, resp.mensagem || 'Não foi possível excluir.', true);
        if (resp.relogar) setTimeout(() => { location.href = 'login.php'; }, 1500);
      }
    }
  });

  /* ---------- atalho: Ctrl+Enter salva ---------- */
  document.querySelectorAll('form.obs__form textarea').forEach((ta) => {
    ta.addEventListener('keydown', (ev) => {
      if ((ev.ctrlKey || ev.metaKey) && ev.key === 'Enter') {
        ev.preventDefault();
        ta.form.requestSubmit();
      }
    });
  });
})();
