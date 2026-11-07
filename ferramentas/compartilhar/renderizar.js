/* Renderiza as artes de compartilhamento em PNG.
   Uso: node ferramentas/compartilhar/renderizar.js   (precisa do Playwright + Chromium) */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const aqui = __dirname;
const destino = path.resolve(aqui, '../../proposta-jogos/img');
(async () => {
  const browser = await chromium.launch();
  async function render(html, sel, w, h, out, opts = {}) {
    const ctx = await browser.newContext({ viewport: { width: w, height: h }, deviceScaleFactor: opts.scale || 1 });
    const page = await ctx.newPage();
    await page.goto('file://' + path.join(aqui, html), { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);
    await page.waitForTimeout(300);
    await page.locator(sel).screenshot({ path: out, omitBackground: !!opts.transparent, type: opts.type || 'png', quality: opts.quality });
    await ctx.close();
    console.log(path.relative(process.cwd(), out), Math.round(fs.statSync(out).size / 1024) + ' KB');
  }
  fs.mkdirSync(path.join(destino, 'compartilhar'), { recursive: true });
  fs.mkdirSync(path.join(destino, 'icones'), { recursive: true });
  // JPEG para as prévias (WhatsApp/Facebook limitam o tamanho do arquivo); PNG para postar
  await render('og.html', '.arte', 1200, 630, path.join(destino, 'compartilhar/og-1200x630.jpg'), { type: 'jpeg', quality: 90 });
  await render('og.html', '.arte', 1200, 630, path.join(destino, 'compartilhar/og-1200x630.png'));
  await render('quadrado.html', '.arte', 1080, 1080, path.join(destino, 'compartilhar/quadrado-1080.jpg'), { type: 'jpeg', quality: 90 });
  await render('quadrado.html', '.arte', 1080, 1080, path.join(destino, 'compartilhar/quadrado-1080.png'));
  await render('icone.html', '.icone', 512, 512, path.join(destino, 'icones/favicon-512.png'), { transparent: true });
  await browser.close();
})();
