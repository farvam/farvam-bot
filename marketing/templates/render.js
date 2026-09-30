// ساخت تصاویر PNG از social.html:  NODE_PATH=$(npm root -g) node marketing/templates/render.js
const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1400, height: 2000 } });
  await page.goto('file://' + path.resolve(__dirname, 'social.html'));
  await page.waitForLoadState("networkidle");
  await page.evaluate(async () => { for (const w of [400,500,700,800]) await document.fonts.load(w + " 40px Vazirmatn", "فروم"); await document.fonts.load("700 40px \"Reem Kufi\"", "فروم"); await document.fonts.ready; });
  await page.waitForTimeout(800);
  const ids = await page.$$eval('.card', els => els.map(e => e.id));
  for (const id of ids) {
    await page.locator('#' + id).screenshot({ path: path.resolve(__dirname, '../social', id + '.png') });
    console.log('ok', id);
  }
  await browser.close();
})();
