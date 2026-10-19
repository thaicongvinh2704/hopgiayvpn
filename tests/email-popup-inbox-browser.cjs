const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { chromium } = require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const root = path.resolve(__dirname, '..');
const php = (action, mode = '') => execFileSync('C:/xampp/php/php.exe', [path.join(__dirname, 'email-popup-recaptcha-fixture.php'), action, mode], { encoding: 'utf8' });
const js = fs.readFileSync(path.join(root, 'wp-content/mu-plugins/vpn-email-quote-popup/popup.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'wp-content/mu-plugins/vpn-email-quote-popup/popup.css'), 'utf8');
const checks = [];
const check = (name, result) => { assert.ok(result, name); checks.push(name); };
(async () => {
  const browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
  try {
    for (const scenario of ['desktop', 'mobile', 'cached-config', 'expired-session', 'double-click', 'network-retry', 'server-retry', 'malformed-receipt', 'boot-error', 'timeout']) {
      const context = await browser.newContext({ viewport: scenario === 'mobile' ? { width: 390, height: 844 } : { width: 1440, height: 1000 } });
      const page = await context.newPage(); const payloads = []; let bootstrapCalls = 0, tokenCalls = 0, external = 0, oldPosts = 0;
      let reachedPost;
      const postReached = new Promise(resolve => { reachedPost = resolve; });
      await page.route('**/*', async route => {
        const url = new URL(route.request().url());
        if (url.origin !== 'https://popup.test') { external++; return route.abort(); }
        if (url.pathname === '/popup.js') return route.fulfill({ contentType: 'application/javascript', body: js });
        if (url.pathname === '/popup.css') return route.fulfill({ contentType: 'text/css', body: css });
        if (url.pathname.endsWith('/packaging-banner-20261019.webp')) return route.fulfill({ contentType: 'image/webp', body: fs.readFileSync(path.join(root, 'wp-content/mu-plugins/vpn-email-quote-popup/packaging-banner-20261019.webp')) });
        if (url.pathname === '/session') { tokenCalls++; return route.fulfill({ contentType: 'application/json', body: php('session') }); }
        if (url.pathname === '/wp-json/vpn-chat/v1/bootstrap') {
          bootstrapCalls++;
          return route.fulfill({ status: scenario === 'boot-error' ? 503 : 200, contentType: 'application/json', body: JSON.stringify(scenario === 'boot-error' ? { code: 'new_chat_unavailable' } : { csrf: 'session-csrf' }) });
        }
        if (url.pathname === '/wp-json/vpn-chat/v1/guest/email-quote') {
          payloads.push(route.request().postDataJSON());
          check(scenario + ': protected by session CSRF', route.request().headers()['x-vpn-csrf'] === 'session-csrf');
          reachedPost();
          if (scenario === 'timeout') return; // Browser aborts at its deadline.
          if (scenario === 'network-retry' && payloads.length === 1) return route.abort();
          if (scenario === 'expired-session' && payloads.length === 1) return route.fulfill({ status: 401, contentType: 'application/json', body: '{"code":"session_expired"}' });
          if (scenario === 'server-retry' && payloads.length === 1) return route.fulfill({ status: 503, contentType: 'application/json', body: '{"code":"storage_unavailable"}' });
          const receipt = scenario === 'malformed-receipt' ? 'wrong' : 'vpn-inbox-12345678-1234-4234-8234-123456789abc-1';
          return route.fulfill({ contentType: 'application/json', body: JSON.stringify({ saved: true, id: '12345678-1234-4234-8234-123456789abc', seq: 1, receipt }) });
        }
        if (url.pathname === '/wp-admin/admin-post.php') { oldPosts++; return route.abort(); }
        if (url.pathname === '/') return route.fulfill({ contentType: 'text/html', body: php('render', scenario).replace('<head>', '<head><link rel="stylesheet" href="/popup.css">') });
        if (url.pathname.endsWith('packaging.webp')) return route.fulfill({ contentType: 'image/webp', body: fs.readFileSync(path.join(root, 'wp-content/mu-plugins/vpn-email-quote-popup/packaging.webp')) });
        return route.fulfill({ status: 204, body: '' });
      });
      await page.goto('https://popup.test/?utm_source=google&utm_campaign=bulk-bags');
      await page.waitForFunction(() => document.querySelector('#vpn-email-popup').open);
      if (scenario !== 'boot-error') await page.waitForFunction(() => document.querySelector('#vpn-email-popup').dataset.vepInboxReady === '1');
      const requestsBeforeClick = bootstrapCalls + tokenCalls;
      check(scenario + ': email autocomplete and no name requirement', await page.locator('#vep-email').getAttribute('autocomplete') === 'email' && await page.locator('.vep-form [name="full_name"]').count() === 0);
      check(scenario + ': no checkbox or autofill trap', await page.locator('.vep-form input[type=checkbox],.vep-form [name="website_url"],.vep-form [name="vpn_quote_guard"]').count() === 0);
      await page.locator('#vep-email').fill('buyer@example.invalid');
      if (scenario === 'timeout') await page.clock.install();
      const submit = () => page.locator('.vep-form').evaluate(form => form.requestSubmit());
      await submit();
      if (scenario === 'double-click') await submit();
      if (scenario === 'timeout') { await postReached; await page.clock.runFor(26000); }
      if (['network-retry', 'server-retry', 'malformed-receipt', 'boot-error', 'timeout'].includes(scenario)) {
        await page.waitForFunction(() => !document.querySelector('.vep-status').hidden && !document.querySelector('.vep-submit').disabled);
        check(scenario + ': draft remains after failure', await page.locator('#vep-email').inputValue() === 'buyer@example.invalid');
        check(scenario + ': failure does not mark captured', !await page.evaluate(() => JSON.parse(localStorage.getItem('vpn-email-popup-regression')).captured));
        if (scenario.endsWith('retry')) await submit();
      }
      if (!['malformed-receipt', 'boot-error', 'timeout'].includes(scenario)) {
        await page.waitForFunction(() => document.querySelector('#vpn-email-popup').classList.contains('vep-saved'));
        check(scenario + ': success waits for inbox receipt', await page.locator('.vep-status').innerText() === 'Your email has been received. Our team will contact you as soon as possible.');
        check(scenario + ': only intended attempts submitted', payloads.length === (scenario.endsWith('retry') || scenario === 'expired-session' ? 2 : 1));
        check(scenario + ': email-only enquiry has optional empty details', payloads[0].email === 'buyer@example.invalid' && payloads[0].message === '');
        check(scenario + ': attribution reaches inbox', payloads[0].metadata.utm_source === 'google' && payloads[0].metadata.utm_campaign === 'bulk-bags');
        if (scenario.endsWith('retry')) check(scenario + ': retry preserves request identity', payloads[0].client_message_id === payloads[1].client_message_id);
        if (scenario === 'expired-session') check('Expired session refresh is transparent and idempotent', bootstrapCalls === 2 && payloads[0].client_message_id === payloads[1].client_message_id);
        else check(scenario + ': Send performs no extra preparation requests', bootstrapCalls + tokenCalls === requestsBeforeClick);
        check(scenario + ': only cached HTML needs endpoint discovery', tokenCalls === (scenario === 'cached-config' ? 1 : 0));
      }
      check(scenario + ': no Google/external API requests', external === 0);
      check(scenario + ': no company-email form submission', oldPosts === 0);
      await context.close();
    }
  } finally { await browser.close(); }
  console.log(JSON.stringify({ passed: checks.length, checks }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
