import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { chromium } from '@playwright/test';
import { chromeExecutable, repoRoot } from '../site-fixtures.mjs';

const php = process.env.PHP_EXECUTABLE || (process.platform === 'win32' ? 'C:/xampp/php/php.exe' : 'php');
const render = spawnSync(php, [path.join(repoRoot, 'tests/pizza-boxes-quote-form.php'), 'render-captcha'], { encoding: 'utf8' });
assert.equal(render.status, 0, render.stderr);
const captchaHandler = render.stdout;
assert.ok(captchaHandler.includes('custom-box-recaptcha-v3-submit-handler'));
const browser = await chromium.launch({ executablePath: chromeExecutable, headless: true });
const results = [];
try {
  for (const name of ['stale-session', 'recaptcha-success', 'session-failure', 'recaptcha-failure']) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 }, serviceWorkers: 'block' });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    let sessionRequests = 0;
    let submitted = null;
    const captcha = name.startsWith('recaptcha-');
    if (captcha) {
      await page.addInitScript(({ fail }) => {
        window.captchaExecutions = 0;
        window.grecaptcha = {
          ready: callback => callback(),
          execute: () => { window.captchaExecutions++; return fail ? Promise.reject(new Error('Simulated captcha failure')) : Promise.resolve('qa-captcha-token'); },
        };
      }, { fail: name === 'recaptcha-failure' });
      await page.route('**/custom-pizza-boxes-manufacturer/', async route => {
        const response = await route.fetch();
        let html = await response.text();
        if (!html.includes('id="custom-box-recaptcha-v3-submit-handler"')) {
          html = html.replace(/(<script id="pizza-boxes-quote-session"[\s\S]*?<\/script>)/, '$1' + captchaHandler);
        }
        await route.fulfill({ response, body: html });
      });
    }
    await page.route('**/wp-admin/admin-ajax.php', async route => {
      if (!route.request().postData()?.includes('action=custom_box_pizza_quote_session')) return route.continue();
      sessionRequests++;
      if (name === 'session-failure') return route.fulfill({ status: 503, body: 'Unavailable' });
      await route.continue();
    });
    // Never deliver a test enquiry to a recipient. PHP checks the actual handler separately.
    await page.route('**/wp-admin/admin-post.php', async route => {
      submitted = Object.fromEntries(new URLSearchParams(route.request().postData()));
      await route.fulfill({ status: 200, contentType: 'text/html', body: '<p>Test submission intercepted.</p>' });
    });
    await page.goto('http://localhost/hopgiayvpn/custom-pizza-boxes-manufacturer/', { waitUntil: 'load' });
    assert.equal(sessionRequests, 0, 'No form API request should delay initial page load.');
    await page.locator('.vpb-form').evaluate(form => {
      form.elements.custom_box_quote_nonce.value = 'expired-cached-nonce';
      form.elements.custom_box_form_started_at.value = '1';
      form.elements.custom_box_form_signature.value = 'expired-cached-signature';
    });
    await page.locator('[name="full_name"]').fill('Pizza Landing QA');
    await page.locator('[name="email"]').fill('pizza-qa@example.invalid');
    await page.locator('[name="privacy_consent"]').check();
    await page.locator('.vpb-submit').click();
    if (name.endsWith('failure')) {
      await page.waitForFunction(() => {
        const element = document.querySelector('.vpb-form-status');
        return !element.hidden && /could not/i.test(element.textContent);
      });
      assert.equal(submitted, null);
      assert.equal(await page.locator('.vpb-submit').isEnabled(), true);
      assert.equal(await page.locator('[name="email"]').inputValue(), 'pizza-qa@example.invalid');
    } else {
      await page.waitForFunction(() => document.body.textContent.includes('Test submission intercepted.'));
      assert.ok(submitted);
      assert.notEqual(submitted.custom_box_quote_nonce, 'expired-cached-nonce');
      assert.match(submitted.custom_box_form_signature, /^[a-f0-9]{64}$/);
      assert.ok(Date.now() / 1000 - Number(submitted.custom_box_form_started_at) >= 1);
      assert.equal(submitted.quote_source, 'custom_pizza_boxes_manufacturer');
      assert.equal(submitted.form_anchor, 'vpb-quote');
      assert.equal(submitted.privacy_consent, 'yes');
      if (captcha) assert.equal(submitted['g-recaptcha-response'], 'qa-captcha-token');
    }
    assert.deepEqual(errors, []);
    results.push({ case: name, passed: true, sessionRequests, submitted: !!submitted });
    await context.close();
  }
  const context = await browser.newContext();
  const endpoint = 'http://localhost/hopgiayvpn/wp-admin/admin-ajax.php';
  const session = await context.request.post(endpoint, { form: { action: 'custom_box_pizza_quote_session' } });
  assert.equal(session.status(), 200);
  assert.match(session.headers()['cache-control'], /no-store|no-cache/);
  const data = (await session.json()).data;
  assert.equal(data.context, 'quote');
  assert.match(data.signature, /^[a-f0-9]{64}$/);
  assert.ok(data.nonce);
  const get = await context.request.get(endpoint + '?action=custom_box_pizza_quote_session');
  assert.equal(get.status(), 405);
  const statusPage = await context.request.get('http://localhost/hopgiayvpn/custom-pizza-boxes-manufacturer/?quote_status=received');
  assert.equal(statusPage.status(), 200);
  assert.match(statusPage.headers()['cache-control'], /no-store|no-cache/);
  assert.ok((await statusPage.text()).includes('We have received your pizza-box enquiry'));
  results.push({ case: 'session-endpoint-and-status-cache', passed: true });
  await context.close();
} finally { await browser.close(); }
const output = path.join(repoRoot, 'artifacts/pizza-boxes-landing-20260928/form');
await fs.mkdir(output, { recursive: true });
await fs.writeFile(path.join(output, 'report.json'), JSON.stringify(results, null, 2));
console.log(JSON.stringify(results, null, 2));
