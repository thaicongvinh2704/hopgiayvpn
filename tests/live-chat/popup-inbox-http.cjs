// Real local HTTP popup -> committed database message -> existing admin inbox. Test sink blocks email.
const { chromium } = require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs = require('fs');
const path = require('path');
const base = 'http://127.0.0.1:8091';
const out = path.join(__dirname, 'results/popup-inbox'); fs.mkdirSync(out, { recursive: true });
const checks = [];
const marker = Date.now();
let firstEmail = '';
const timings = [];
const check = (value, name) => { if (!value) throw Error(name); checks.push(name); };
(async () => {
  const browser = await chromium.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
  try {
    for (const width of [1440, 390]) {
      const context = await browser.newContext({ viewport: { width, height: 900 } });
      await context.route('**/*', r => new URL(r.request().url()).hostname === '127.0.0.1' ? r.continue() : r.abort());
      await context.addInitScript(() => { let cfg; Object.defineProperty(window, 'vpnEmailPopup', { configurable: true, get: () => cfg, set: v => { cfg = { ...v, preview: '1' }; } }); });
      const page = await context.newPage();
      const postRequests = [];
      page.on('request', request => { if (request.method() === 'POST') postRequests.push(request.url()); });
      await page.goto(base + '/', { waitUntil: 'domcontentloaded' });
      await page.waitForFunction(() => document.querySelector('#vpn-email-popup')?.open);
      await page.waitForFunction(() => document.querySelector('#vpn-email-popup').dataset.vepInboxReady === '1');
      check(await page.locator('#vep-email').getAttribute('autocomplete') === 'email', width + ': real page preserves saved email suggestions');
      const email = 'popup-http-' + width + '-' + marker + '@example.invalid';
      if (width === 1440) firstEmail = email;
      await page.locator('#vep-email').fill(email);
      postRequests.length = 0; const clickedAt = Date.now();
      const responsePromise = page.waitForResponse(r => r.url().includes('/guest/email-quote') && r.request().method() === 'POST');
      await page.locator('.vep-submit').click(); const response = await responsePromise; const result = await response.json();
      check(response.status() === 200 && result.saved === true && result.receipt.startsWith('vpn-inbox-'), width + ': real HTTP saves inbox message');
      timings.push({ width, click_to_receipt_ms: Date.now() - clickedAt });
      check(postRequests[0]?.includes('/guest/email-quote'), width + ': first request after Send is the actual save, not session preparation');
      await page.locator('#vpn-email-popup.vep-saved').waitFor();
      check(await page.locator('.vep-status').innerText() === 'Your email has been received. Our team will contact you as soon as possible.', width + ': receipt success visible');
      await page.screenshot({ path: path.join(out, 'success-' + width + '.png') });
      await page.locator('.vep-not-now').click();
      await page.locator('#vpn-chat-launch').click();
      await page.locator('#vpn-chat-log').getByText('Packaging quote request (website popup)', { exact: false }).waitFor();
      check(await page.locator('#vpn-chat-reply').isVisible(), width + ': chat resumes the saved popup enquiry without asking for email again');
      await context.close();
    }
    const admin = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const page = await admin.newPage(); await page.goto(base + '/wp-login.php');
    await page.locator('#user_login').fill('chat-test-manager'); await page.locator('#user_pass').fill('test-password-local-only');
    await page.locator('#wp-submit').click(); await page.waitForURL('**/wp-admin/**');
    await page.goto(base + '/wp-admin/admin.php?page=vpn-live-chat');
    await page.locator('#vpn-search').fill(firstEmail); await page.locator('#vpn-search').press('Enter');
    const row = page.locator('#vpn-list .vpn-row').filter({ hasText: firstEmail });
    await row.waitFor(); await row.click();
    await page.locator('#vpn-transcript').getByText('Packaging quote request (website popup)', { exact: false }).waitFor();
    check(true, 'Existing admin inbox visibly displays the popup enquiry');
    check((await page.locator('#vpn-customer').innerText()).includes(firstEmail), 'Admin sees the saved email beside enquiry');
    await page.screenshot({ path: path.join(out, 'customer-inbox.png') });
    await admin.close();
  } finally { await browser.close(); }
  fs.writeFileSync(path.join(out, 'checks.json'), JSON.stringify(checks, null, 2));
  console.log(JSON.stringify({ passed: checks.length, timings, checks }, null, 2));
})().catch(error => { console.error(error); process.exitCode = 1; });
