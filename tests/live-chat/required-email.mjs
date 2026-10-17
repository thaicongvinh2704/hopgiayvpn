import { createRequire } from 'node:module';
import { readFile, writeFile, mkdir } from 'node:fs/promises';
const require = createRequire(import.meta.url);
const { chromium } = require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const output = 'outputs/live-chat-required-email-20261017'; await mkdir(output, { recursive: true });
const files = new Map(); for (const name of ['launcher.js','launcher.css','widget.js','chat.css','tho-nguyen.png']) files.set(name, await readFile('wp-content/plugins/vpn-live-chat/assets/' + name));
const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
const checks = []; const check = (ok, name) => { checks.push({ name, passed: !!ok }); if (!ok) throw new Error(name); };
async function fixture(width, legacy = false) {
  const context = await browser.newContext({ viewport: { width, height: 844 }, reducedMotion: 'reduce' });
  const page = await context.newPage(), errors = [], id = '11111111-1111-4111-8111-111111111111';
  const store = { email: '', id: legacy ? id : null, rows: [], starts: 0, sends: 0, contacts: 0, fail: false };
  const config = { accepting: true, presence: 'online', support: { name: 'Tho Nguyen', initials: 'TN', avatar: 'http://localhost/assets/tho-nguyen.png' }, site_key: '', max_chars: 2000 };
  page.on('pageerror', error => errors.push(error.message));
  await context.route('**/*', async route => {
    const url = new URL(route.request().url());
    if (url.pathname.startsWith('/assets/')) { const name = url.pathname.slice(8); return route.fulfill({ body: files.get(name), contentType: name.endsWith('.css') ? 'text/css' : name.endsWith('.png') ? 'image/png' : 'text/javascript' }); }
    if (url.pathname.startsWith('/api/')) {
      const action = url.pathname.slice(5), data = route.request().method() === 'POST' ? route.request().postDataJSON() : {}, summary = { id, email: store.email, version: 3, status: 'unassigned', unread: 0, guest_read_seq: store.rows.length, agent_read_seq: 0 };
      let json = {};
      if (action === 'bootstrap') json = { csrf: 'qa', config, conversation: store.id, customer: { code: 'Guest QA' }, history: [] };
      if (action === 'guest/sync') json = { config, conversation: summary, messages: store.rows, cursor: store.rows.length, more: false };
      if (action === 'guest/read') json = summary;
      if (action === 'guest/start' || action === 'guest/send') {
        if (action === 'guest/start') { store.starts++; store.first = data; } else store.sends++;
        if (store.fail) return route.fulfill({ status: 503, json: { code: 'request_failed' } });
        if (action === 'guest/start') { store.id = id; store.email = data.email; }
        const message = { seq: store.rows.length + 1, sender: 'guest', body: data.message, created_at: '2026-10-17 01:00:00' }; store.rows.push(message);
        json = { id, saved: true, seq: message.seq, message, contact_email: store.email, contact_version: 3 };
      }
      if (action === 'guest/contact') { store.contacts++; store.email = data.email; json = { saved: true, email: data.email, contact_version: 4 }; }
      return route.fulfill({ json });
    }
    return route.fulfill({ contentType: 'text/html; charset=utf-8', body: `<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/launcher.css"><link rel="stylesheet" href="/assets/chat.css"><style>body{font:16px Arial;background:#f5f9fc}</style><h1>VPN Packaging</h1><script>window.VPNChatLauncher=${JSON.stringify({ api:'http://localhost/api/',bundle:'http://localhost/assets/widget.js',css:'http://localhost/assets/chat.css',support:config.support,bottom:40,greeting_enabled:false,auto_reply:true,greeting_text:'Hi! How can we help with your packaging project?' })}</script><script src="/assets/widget.js"></script><script src="/assets/launcher.js"></script>` });
  });
  await page.goto('http://localhost/test'); await page.locator('#vpn-chat-launch').click();
  await page.waitForFunction(() => document.querySelector('#vpn-chat-panel').dataset.presence === 'online' && !document.querySelector('#vpn-chat-start-button').disabled);
  return { page, context, store, errors };
}
async function centered(page, selector, label) {
  const result = await page.locator(selector).evaluate(button => { const b = button.getBoundingClientRect(), s = button.querySelector('svg').getBoundingClientRect(); return Math.abs(b.x+b.width/2-s.x-s.width/2)<1 && Math.abs(b.y+b.height/2-s.y-s.height/2)<1; }); check(result, label);
}
try {
  for (const width of [1366,390,320]) {
    const { page, context, store, errors } = await fixture(width), tag = ' ' + width;
    check(await page.locator('#vpn-chat-email').isVisible(), 'Email visible before first message' + tag);
    check(await page.locator('#vpn-chat-email').evaluate(input => input.required && input.type === 'email' && input.autocomplete === 'email'), 'Email required with native autofill' + tag);
    check(!await page.locator('#vpn-chat-name').isVisible(), 'No name requested' + tag);
    check(!await page.locator('#vpn-chat-add-email').isVisible(), 'No optional email action before first message' + tag);
    await centered(page, '#vpn-chat-start-button', 'Initial send icon centered' + tag);
    await page.locator('#vpn-chat-first').fill('I need 10000 paper bags.'); await page.locator('#vpn-chat-start-button').click();
    check(store.starts === 0, 'Empty email blocks request' + tag);
    check(await page.locator('#vpn-chat-first').inputValue() === 'I need 10000 paper bags.', 'Draft retained after empty email' + tag);
    await page.locator('#vpn-chat-email').fill('bad-email'); await page.locator('#vpn-chat-first').press('Enter'); check(store.starts === 0, 'Enter also blocks invalid email' + tag);
    await page.locator('#vpn-chat-email').fill('buyer@gmail.com');
    await page.locator('#vpn-chat-panel').screenshot({ path: `${output}/initial-${width}.png` });
    store.fail = true; await page.locator('#vpn-chat-start-button').click(); await page.waitForFunction(() => document.querySelector('#vpn-chat-state').textContent.includes('not confirmed'));
    check(await page.locator('#vpn-chat-email').inputValue() === 'buyer@gmail.com' && await page.locator('#vpn-chat-first').inputValue() === 'I need 10000 paper bags.', 'Server failure retains email and draft' + tag);
    store.fail = false; await page.locator('#vpn-chat-start-button').click(); await page.locator('#vpn-chat-reply').waitFor({ state:'visible' });
    check(store.first.email === 'buyer@gmail.com' && store.first.name === '', 'First request includes email without name' + tag);
    check(!await page.locator('#vpn-chat-email').isVisible(), 'Email entry hidden after saved receipt' + tag);
    check(await page.locator('#vpn-chat-add-email').innerText() === 'Edit email', 'Saved contact can be edited' + tag);
    await centered(page, '#vpn-chat-send-button', 'Reply send icon centered' + tag);
    await page.locator('#vpn-chat-message').fill('Please quote delivery to Australia.'); await page.locator('#vpn-chat-send-button').click(); await page.waitForFunction(() => document.querySelectorAll('.vpn-chat-message').length === 2);
    check(store.sends === 1, 'Further message uses saved email' + tag);
    await page.locator('#vpn-chat-add-email').click(); await page.locator('#vpn-chat-contact-email').fill(''); await page.locator('#vpn-chat-contact-save').click(); check(store.contacts === 0, 'Cannot clear required contact in UI' + tag);
    await page.locator('#vpn-chat-contact-email').fill('updated@example.invalid'); await page.locator('#vpn-chat-contact-save').click(); await page.locator('#vpn-chat-contact').waitFor({ state:'hidden' }); check(store.email === 'updated@example.invalid', 'Valid email update saved' + tag);
    await page.locator('#vpn-chat-panel').screenshot({ path: `${output}/conversation-${width}.png` });
    check(!errors.length, 'No browser errors' + tag); await context.close();
  }
  const legacy = await fixture(390, true); await legacy.page.locator('#vpn-chat-contact').waitFor({ state:'visible' });
  check(!await legacy.page.locator('#vpn-chat-contact-dismiss').isVisible(), 'Legacy required-email card cannot be dismissed');
  await legacy.page.locator('#vpn-chat-message').fill('A legacy draft'); await legacy.page.locator('#vpn-chat-send-button').click(); check(legacy.store.sends === 0, 'Legacy anonymous reply blocked');
  check(await legacy.page.locator('#vpn-chat-message').inputValue() === 'A legacy draft', 'Legacy draft retained');
  await legacy.page.locator('#vpn-chat-contact-email').fill('legacy@example.invalid'); await legacy.page.locator('#vpn-chat-contact-save').click(); await legacy.page.locator('#vpn-chat-contact').waitFor({ state:'hidden' });
  await legacy.page.locator('#vpn-chat-send-button').click(); await legacy.page.waitForFunction(() => document.querySelectorAll('.vpn-chat-message').length === 1); check(legacy.store.sends === 1, 'Legacy visitor resumes after email saved'); await legacy.context.close();
  console.log(JSON.stringify({ passed: checks.length }));
} finally { await writeFile(`${output}/browser-checks.json`, JSON.stringify(checks,null,2)); await browser.close(); }
