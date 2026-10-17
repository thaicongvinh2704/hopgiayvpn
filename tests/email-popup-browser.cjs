const { chromium } = require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs = require('fs');
const path = require('path');
const out = path.resolve(__dirname, '../outputs/email-popup-20261017');
fs.mkdirSync(out, { recursive: true });
const base = 'http://localhost/hopgiayvpn/';
const checks = [];
function check(name, ok) { checks.push({ name, passed: !!ok }); if (!ok) throw new Error(name); }
(async () => {
  const browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
  async function fresh(width = 1440, height = 1000, url = base, previewHarness = false) {
    const context = await browser.newContext({ viewport: { width, height }, reducedMotion: 'reduce' });
    if (previewHarness) await context.addInitScript(() => {
      let config; Object.defineProperty(window, 'vpnEmailPopup', { configurable: true, get: () => config, set: value => { config = { ...value, preview: '1' }; } });
    });
    const page = await context.newPage();
    await page.clock.install();
    const response = await page.goto(url); check('HTTP 200 ' + url, response.status() === 200);
    await page.waitForFunction(() => window.vpnEmailPopup && document.querySelector('#vpn-email-popup'));
    return { context, page };
  }
  async function advance(page, ms = 31000) { await page.clock.runFor(ms); }
  async function state(page) { return page.evaluate(() => ({ key: 'vpn-email-popup-' + vpnEmailPopup.scope, state: JSON.parse(localStorage.getItem('vpn-email-popup-' + vpnEmailPopup.scope)), visit: JSON.parse(sessionStorage.getItem('vpn-email-popup-' + vpnEmailPopup.scope + '-visit')) })); }
  try {
    const { page, context } = await fresh();
    await advance(page, 28000); check('Not shown before 30 seconds', !await page.locator('#vpn-email-popup').evaluate(el => el.open));
    await advance(page, 3000); await page.waitForFunction(() => document.querySelector('#vpn-email-popup').open);
    check('One appearance counted', (await state(page)).state.count === 1);
    check('Email native autofill', await page.locator('#vep-email').getAttribute('autocomplete') === 'email');
    check('No consent checkbox required', await page.locator('.vep-form input[type=checkbox]').count() === 0);
    check('Contact notice below submit', await page.locator('.vep-submit + .vep-contact-notice').isVisible());
    check('Email not autofocused', await page.evaluate(() => document.activeElement.id !== 'vep-email'));
    check('Desktop photo visible', await page.locator('.vep-photo').isVisible());
    check('No horizontal overflow desktop', await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
    await page.clock.runFor(300);
    await page.screenshot({ path: path.join(out, 'desktop.png'), animations: 'disabled' });
    await page.keyboard.press('Escape'); check('Escape closes', !await page.locator('#vpn-email-popup').evaluate(el => el.open));
    await page.goto(base + 'custom-paper-bags-manufacturer/'); await advance(page);
    check('No second popup in same visit on another page', !await page.locator('#vpn-email-popup').evaluate(el => el.open));
    await context.close();

    const mobile = await fresh(390, 844, base + 'products/paper-bags-with-logo/'); await advance(mobile.page);
    await mobile.page.waitForFunction(() => document.querySelector('#vpn-email-popup').open);
    await mobile.page.clock.runFor(300);
    check('Mobile photo hidden', !await mobile.page.locator('.vep-photo').isVisible());
    const rect = await mobile.page.locator('#vpn-email-popup').boundingBox();
    check('Mobile sheet at bottom', Math.abs(rect.y + rect.height - 844) < 2);
    check('Mobile fits width', rect.width <= 390);
    await mobile.page.locator('.vep-details summary').click();
    check('Optional details expands', await mobile.page.locator('#vep-message').isVisible());
    await mobile.page.screenshot({ path: path.join(out, 'mobile-details.png') });
    await mobile.page.locator('.vep-details summary').click();
    await mobile.page.screenshot({ path: path.join(out, 'mobile.png') });
    // Exercise error then confirmed receipt without sending a test email over HTTP.
    await mobile.page.route('**/wp-admin/admin-post.php', route => route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ success: false, data: { status: 'failed' } }) }));
    await mobile.page.locator('#vep-email').fill('popup-browser-qa@example.invalid');
    await mobile.page.locator('.vep-submit').click();
    await mobile.page.waitForFunction(() => !document.querySelector('.vep-status').hidden);
    check('Failure preserves email', await mobile.page.locator('#vep-email').inputValue() === 'popup-browser-qa@example.invalid');
    check('Failure does not mark captured', !(await state(mobile.page)).state.captured);
    await mobile.page.unroute('**/wp-admin/admin-post.php');
    let submitted;
    await mobile.page.route('**/wp-admin/admin-post.php', route => { submitted = route.request().postData(); return route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, data: { status: 'received', receipt: 'vpn-quote-999999' } }) }); });
    await mobile.page.locator('.vep-submit').click();
    await mobile.page.waitForFunction(() => document.querySelector('#vpn-email-popup').classList.contains('vep-saved'));
    check('Context sent with email', submitted.includes('website_email_popup') && submitted.includes('current_page_url'));
    check('Email-only submission has no consent flag', !submitted.includes('privacy_consent'));
    check('Contact notice hidden after receipt', !await mobile.page.locator('.vep-contact-notice').isVisible());
    check('Confirmed receipt suppresses future appearances', (await state(mobile.page)).state.captured);
    check('No PII in persistent state', !JSON.stringify(await state(mobile.page)).includes('example.invalid'));
    await mobile.page.goto(base); await advance(mobile.page);
    check('Captured visitor suppressed on homepage', !await mobile.page.locator('#vpn-email-popup').evaluate(el => el.open));
    await mobile.context.close();

    for (const scenario of ['limit', 'cooldown', 'second-visit', 'chat', 'form', 'cookie', 'storage-unavailable']) {
      const item = await fresh(1280, 900, base + 'custom-paper-bags-manufacturer/');
      const config = await state(item.page);
      if (scenario === 'limit' || scenario === 'cooldown' || scenario === 'second-visit') {
        await item.page.evaluate(({ config, scenario }) => {
          const now = Date.now(); localStorage.setItem(config.key, JSON.stringify({ start: now - 3 * 86400000, count: scenario === 'limit' ? 2 : 1, last: now - (scenario === 'cooldown' ? 3600000 : 2 * 86400000), captured: false })); sessionStorage.removeItem(config.key + '-visit');
        }, { config, scenario }); await item.page.reload();
      }
      if (scenario === 'chat') await item.page.evaluate(() => sessionStorage.setItem('vpn-chat-active', '1'));
      if (scenario === 'form') await item.page.locator('input[type=email]:not(#vep-email)').first().fill('engaged@example.invalid');
      if (scenario === 'cookie') await item.context.addCookies([{ name: 'vpn_quote_received_' + await item.page.evaluate(() => vpnEmailPopup.scope), value: '1', url: base }]);
      if (scenario === 'storage-unavailable') { await item.page.addInitScript(() => { Storage.prototype.setItem = () => { throw new Error('blocked'); }; }); await item.page.reload(); }
      await advance(item.page);
      check('Frequency/suppression ' + scenario, await item.page.locator('#vpn-email-popup').evaluate(el => el.open) === (scenario === 'second-visit'));
      await item.context.close();
    }
    const product = await fresh(1280, 900, base + 'product/custom-twisted-handle-kraft-paper-bags/'); await advance(product.page);
    await product.page.waitForFunction(() => document.querySelector('#vpn-email-popup').open);
    check('Popup available on product detail', await product.page.locator('#vpn-email-popup').evaluate(el => el.open)); await product.context.close();
    const preview = await fresh(1280, 900, base, true);
    await advance(preview.page, 1500); await preview.page.waitForFunction(() => document.querySelector('#vpn-email-popup').open);
    check('WordPress string preview flag opens immediately', await preview.page.locator('#vpn-email-popup').evaluate(el => el.open));
    check('Preview does not consume display allowance', (await state(preview.page)).state.count === 0 && !(await state(preview.page)).visit.shown);
    await preview.page.keyboard.press('Escape'); await advance(preview.page, 4000);
    check('Preview stays closed after dismissal', !await preview.page.locator('#vpn-email-popup').evaluate(el => el.open)); await preview.context.close();
    const guestPreview = await fresh(1280, 900, base + '?vpn_popup_preview=1');
    await advance(guestPreview.page, 5000);
    check('Guest cannot enable admin preview by URL', !await guestPreview.page.locator('#vpn-email-popup').evaluate(el => el.open) && !await guestPreview.page.evaluate(() => vpnEmailPopup.preview));
    await guestPreview.context.close();
  } finally {
    fs.writeFileSync(path.join(out, 'browser-checks.json'), JSON.stringify(checks, null, 2));
    await browser.close();
  }
  console.log(JSON.stringify({ passed: checks.length, failed: checks.filter(c => !c.passed) }));
})().catch(error => { console.error(error); process.exit(1); });
