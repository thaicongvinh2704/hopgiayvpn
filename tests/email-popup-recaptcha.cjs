// No real network, database writes, email or production tokens. Actual PHP render + popup/global JS.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { chromium } = require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const root = path.resolve(__dirname, '..');
const fixture = path.join(__dirname, 'email-popup-recaptcha-fixture.php');
const php = (action, mode = '') => execFileSync('C:/xampp/php/php.exe', [fixture, action, mode], { encoding: 'utf8' });
const script = fs.readFileSync(path.join(root, 'wp-content/mu-plugins/vpn-email-quote-popup/popup.js'), 'utf8');
const checks = [];
const check = (name, value) => { assert.ok(value, name); checks.push(name); };
(async () => {
  const browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
  try {
    const session = php('session', 'suppressed');
    check('Fresh public key matches backend even if theme frontend is suppressed', JSON.parse(session).data.recaptcha_site_key === 'test-public-key');
    check('Session never returns the secret key', !session.includes('test-secret-never-exposed'));
    for (const scenario of ['success', 'suppressed', 'off', 'captcha-rejected', 'load-retry', 'execute-retry', 'timeout', 'duplicate-click', 'legacy-form']) {
      const context = await browser.newContext();
      const page = await context.newPage();
      const mode = ['suppressed', 'off'].includes(scenario) ? scenario : '';
      let posts = [], loads = 0;
      await page.route('**/*', async route => {
        const url = new URL(route.request().url());
        if (url.pathname === '/popup.js') return route.fulfill({ contentType: 'application/javascript', body: script });
        if (url.pathname === '/session') return route.fulfill({ contentType: 'application/json', body: php('session', mode) });
        if (url.hostname === 'www.google.com') {
          loads++;
          if (scenario === 'load-retry' && loads === 1) return route.abort();
          return route.fulfill({ contentType: 'application/javascript', body: `window.grecaptcha={ready:fn=>fn(),execute:(key,opts)=>{window.calls=(window.calls||0)+1;window.lastAction=opts.action;${scenario === 'timeout' ? 'return new Promise(()=>{});' : scenario === 'execute-retry' ? 'if(window.calls===1)return Promise.reject(new Error("test failure"));' : ''}return Promise.resolve('verified-token-'+window.calls);}};` });
        }
        if (url.pathname === '/wp-admin/admin-post.php') {
          // FormData sends multipart; the captured wire payload must contain the fresh token.
          posts.push(route.request().postData());
          const rejected = scenario === 'captcha-rejected';
          return route.fulfill({ status: rejected ? 400 : 200, contentType: 'application/json', body: JSON.stringify(rejected ? { success: false, data: { status: 'captcha' } } : { success: true, data: { receipt: 'vpn-quote-123', status: 'success' } }) });
        }
        if (url.pathname === '/') return route.fulfill({ contentType: 'text/html', body: php('render', mode) });
        return route.fulfill({ status: 204, body: '' });
      });
      await page.goto('https://popup.test/');
      await page.waitForFunction(() => document.querySelector('#vpn-email-popup').open);
      check(scenario + ': email autofill remains and no checkbox', await page.locator('#vep-email').getAttribute('autocomplete') === 'email' && await page.locator('.vep-form input[type=checkbox]').count() === 0);
      await page.locator('#vep-email').fill('test@example.invalid');
      if (scenario === 'timeout') await page.clock.install();
      const submit = () => page.locator('.vep-form').evaluate(form => form.requestSubmit());
      await submit();
      if (scenario === 'duplicate-click') await submit();
      if (scenario === 'timeout') {
        await page.waitForFunction(() => window.calls === 1);
        await page.clock.runFor(26000);
      }
      if (['load-retry', 'execute-retry', 'timeout', 'captcha-rejected'].includes(scenario)) {
        await page.waitForFunction(() => !document.querySelector('.vep-status').hidden && !document.querySelector('.vep-submit').disabled);
        check(scenario + ': specific security error', /Security verification/.test(await page.locator('.vep-status').innerText()));
        check(scenario + ': draft is preserved', await page.locator('#vep-email').inputValue() === 'test@example.invalid');
        check(scenario + ': failure does not mark received', !await page.evaluate(() => JSON.parse(localStorage.getItem('vpn-email-popup-regression')).captured));
        if (scenario !== 'captcha-rejected') check(scenario + ': no POST without verification', posts.length === 0);
        if (scenario === 'load-retry' || scenario === 'execute-retry') await submit();
      }
      if (!['timeout', 'captcha-rejected'].includes(scenario)) {
        await page.waitForFunction(() => document.querySelector('#vpn-email-popup').classList.contains('vep-saved'));
        check(scenario + ': exactly one request reaches handler', posts.length === 1);
        check(scenario + ': fresh session nonce sent', posts[0].includes('fresh-nonce'));
        if (scenario !== 'off') {
          check(scenario + ': verification sent in payload', posts[0].includes('g-recaptcha-response') && posts[0].includes('verified-token-'));
          check(scenario + ': correct server action', await page.evaluate(() => window.lastAction === 'quote_submit'));
          check(scenario + ': no duplicate legacy verification', await page.evaluate(() => window.calls) === (scenario === 'execute-retry' ? 2 : 1));
        } else check('Unconfigured sites make no Google request', loads === 0);
      }
      if (scenario === 'legacy-form') {
        await page.locator('#vpn-email-popup').evaluate(dialog => dialog.close());
        await page.evaluate(() => {
          const form = document.querySelector('#other-quote');
          form.addEventListener('submit', event => { event.preventDefault(); window.otherToken = form.querySelector('[name="g-recaptcha-response"]')?.value; });
          form.requestSubmit();
        });
        await page.waitForFunction(() => !!window.otherToken);
        check('Other forms retain their legacy verification', await page.evaluate(() => window.calls === 2 && window.otherToken === 'verified-token-2'));
      }
      await context.close();
    }
  } finally { await browser.close(); }
  console.log(JSON.stringify({ passed: checks.length, checks }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
