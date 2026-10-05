import { createRequire } from 'node:module';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
const require=createRequire(import.meta.url);
const {chromium}=require(process.env.PLAYWRIGHT_PATH || 'C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const browser=await chromium.launch({headless:true,executablePath:process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const base='http://127.0.0.1:8091';
const output=new URL('./results/',import.meta.url).pathname.replace(/^\/(\w:)/,'$1');await mkdir(output,{recursive:true});
const results=[];
const check=(value,name)=>{if(!value)throw new Error(`FAIL ${name}`);results.push(name);console.log(`PASS ${name}`);};
const wait=ms=>new Promise(resolve=>setTimeout(resolve,ms));
async function mockChallenge(page){
  await page.route('**/*',route=>new URL(route.request().url()).hostname==='127.0.0.1'?route.continue():route.abort());
  await page.route('https://challenges.cloudflare.com/turnstile/v0/api.js*',route=>route.fulfill({contentType:'application/javascript',body:`window.turnstile={render:(el,opts)=>{window.testTurnstile=opts;opts.callback('valid-browser-'+crypto.randomUUID());return 1;},reset:()=>{window.testTurnstile.callback('valid-browser-'+crypto.randomUUID());}};`}));
}
async function login(page,login){await page.route('**/*',route=>new URL(route.request().url()).hostname==='127.0.0.1'?route.continue():route.abort());await page.goto(base+'/wp-login.php');await page.locator('#user_login').fill(login);await page.locator('#user_pass').fill('test-password-local-only');await Promise.all([page.waitForURL('**/wp-admin/**'),page.locator('#wp-submit').click()]);await page.goto(base+'/wp-admin/admin.php?page=vpn-live-chat');}
try{
  const context=await browser.newContext({viewport:{width:1366,height:900}});const page=await context.newPage();await mockChallenge(page);
  const requests=[],errors=[];page.on('request',r=>requests.push(decodeURIComponent(r.url())));page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base);await page.locator('#vpn-chat-launch').waitFor();await wait(3500);
  check(!requests.some(url=>url.includes('/vpn-chat/v1/')||url.includes('assets/widget.js')||url.includes('assets/chat.css')),'passive reader loads no full widget, session or poll');
  await page.locator('#vpn-chat-launch').click();await page.locator('#vpn-chat-name').waitFor();await page.waitForFunction(()=>!!window.testTurnstile);
  const guestName='Browser Guest '+Date.now();await page.locator('#vpn-chat-name').fill(guestName);await page.locator('#vpn-chat-email').fill('browser@example.invalid');await page.locator('#vpn-chat-first').fill('Need packaging <script>window.xssRan=true</script> 😊');
  await page.evaluate(()=>window.testTurnstile['expired-callback']());await page.locator('#vpn-chat-start-button').click();
  check((await page.locator('#vpn-chat-first').inputValue()).includes('Need packaging'),'expired challenge preserves draft');
  await page.locator('#vpn-chat-start-button').click();await page.locator('#vpn-chat-reply').waitFor({state:'visible'});await page.locator('.vpn-chat-message').waitFor();
  check(!(await page.evaluate(()=>window.xssRan)),'visitor XSS rendered as text');
  const cookies=await context.cookies();const session=cookies.find(c=>c.name==='vpn_chat_session');check(session?.httpOnly&&session.sameSite==='Strict','guest cookie HttpOnly and SameSite Strict');
  const privateResponse=await context.request.get(base+'/wp-json/vpn-chat/v1/guest/sync?id=invalid');check(privateResponse.headers()['cache-control']?.includes('no-store'),'HTTP private error response no-store');
  check(!privateResponse.headers()['access-control-allow-origin'],'chat API removes reflected CORS header');
  let lost=false;await page.route(url=>decodeURIComponent(url.href).includes('/vpn-chat/v1/guest/send'),async route=>{if(!lost){lost=true;await route.fetch();await route.abort('failed');}else await route.continue();});
  await page.locator('#vpn-chat-message').fill('Retry after lost acknowledgement');await page.locator('#vpn-chat-send-button').click();await page.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent.includes('not confirmed'));
  check(await page.locator('#vpn-chat-message').inputValue()==='Retry after lost acknowledgement','lost acknowledgement keeps draft');
  await page.locator('#vpn-chat-send-button').click();await page.waitForFunction(()=>document.getElementById('vpn-chat-message').value==='');
  await page.waitForFunction(()=>Array.from(document.querySelectorAll('.vpn-chat-message')).some(el=>el.textContent.includes('Retry after lost acknowledgement')));
  check(await page.locator('.vpn-chat-message').filter({hasText:'Retry after lost acknowledgement'}).count()===1,'network retry shows one saved message');
  await page.keyboard.press('Escape');await wait(300);const count=requests.filter(url=>url.includes('guest/sync')).length;await wait(16000);check(requests.filter(url=>url.includes('guest/sync')).length>count,'minimized existing chat keeps slow unread sync');
  check(await page.locator('#vpn-chat-launch').evaluate(el=>el===document.activeElement),'Escape returns keyboard focus to launcher');
  await page.reload();await page.locator('#vpn-chat-launch').click();await page.locator('.vpn-chat-message').filter({hasText:'Retry after lost acknowledgement'}).waitFor();check(true,'reload restores session transcript without email lookup');
  const storage=await page.evaluate(()=>JSON.stringify({...localStorage,...sessionStorage}));check(!storage.includes('browser@example.invalid')&&!storage.includes('Retry after lost'),'no persistent email/body storage');
  await page.screenshot({path:path.join(output,'widget-desktop.png'),fullPage:false});
  const salesContext=await browser.newContext({viewport:{width:1366,height:1000}});const sales=await salesContext.newPage();await login(sales,'agent-a');
  await sales.locator('.vpn-row').filter({hasText:guestName}).click();await sales.locator('#vpn-claim').click();await sales.waitForFunction(()=>document.getElementById('vpn-reply').hidden===false);
  await sales.locator('#vpn-message').fill('BROWSER INTERNAL NOTE');await sales.locator('#vpn-note').check();await sales.locator('#vpn-reply button').click();await sales.locator('.vpn-msg').filter({hasText:'BROWSER INTERNAL NOTE'}).waitFor();
  await sales.locator('#vpn-note').uncheck();await sales.locator('#vpn-message').fill('We will review your packaging specifications.');await sales.locator('#vpn-reply button').click();
  await page.locator('.vpn-chat-message').filter({hasText:'We will review your packaging specifications.'}).waitFor({timeout:22000});check(true,'sales claim/reply reaches active visitor');
  check(await page.locator('.vpn-chat-message').filter({hasText:'BROWSER INTERNAL NOTE'}).count()===0,'internal note absent in real guest UI');
  await sales.locator('#vpn-label').selectOption('needs_quote');await sales.locator('#vpn-needs').fill('Need quote after dimensions confirmed');await sales.locator('#vpn-save').click();await sales.waitForFunction(()=>document.getElementById('vpn-admin-error').textContent.includes('đồng bộ'));
  await sales.screenshot({path:path.join(output,'inbox-desktop.png'),fullPage:false});
  const mobile=await browser.newContext({viewport:{width:390,height:844},isMobile:true,hasTouch:true});await mobile.addCookies(cookies);const mp=await mobile.newPage();await mockChallenge(mp);await mp.goto(base);await mp.locator('#vpn-chat-launch').click();await mp.locator('#vpn-chat-reply').waitFor({state:'visible'});
  const box=await mp.locator('.vpn-chat-panel').boundingBox();check(box.x>=0&&box.x+box.width<=391&&box.y>=0&&box.y+box.height<=845,'mobile widget fits viewport');
  await mp.locator('.vpn-chat-message').filter({hasText:'We will review your packaging specifications.'}).waitFor({timeout:22000});
  await mp.locator('#vpn-chat-message').focus();await mp.keyboard.type('Mobile draft');await mp.keyboard.press('Escape');await mp.locator('#vpn-chat-launch').click();check(await mp.locator('#vpn-chat-message').inputValue()==='Mobile draft','close/reopen keeps in-memory mobile draft');
  await mp.screenshot({path:path.join(output,'widget-mobile.png'),fullPage:false});
  await sales.setViewportSize({width:390,height:844});await sales.screenshot({path:path.join(output,'inbox-mobile.png'),fullPage:true});check(await sales.evaluate(()=>document.documentElement.scrollWidth<=document.documentElement.clientWidth+1),'mobile inbox has no horizontal overflow');
  // Hide/focus behavior: background tab stops guest sync (desktop headed browser policy differs).
  await page.evaluate(()=>{Object.defineProperty(document,'hidden',{configurable:true,value:true});document.dispatchEvent(new Event('visibilitychange'));});await wait(500);const beforeHidden=requests.filter(url=>url.includes('guest/sync')).length;await wait(4500);check(requests.filter(url=>url.includes('guest/sync')).length===beforeHidden,'hidden-tab event stops polling');
  await page.evaluate(()=>{Object.defineProperty(document,'hidden',{configurable:true,value:false});document.dispatchEvent(new Event('visibilitychange'));});await wait(1500);check(requests.filter(url=>url.includes('guest/sync')).length>beforeHidden,'focus visibility resumes sync');
  check(errors.length===0,'no uncaught visitor JS errors');
  const second=await context.newPage();await mockChallenge(second);await second.goto(base);await second.locator('#vpn-chat-launch').click();await second.locator('.vpn-chat-message').first().waitFor();
  await page.locator('#vpn-chat-message').fill('Same-browser tab coordination');await page.locator('#vpn-chat-send-button').click();await second.locator('.vpn-chat-message').filter({hasText:'Same-browser tab coordination'}).waitFor({timeout:22000});check(true,'same-browser second tab restores and receives new message');await second.close();
  const fresh=await browser.newContext();const fp=await fresh.newPage();await mockChallenge(fp);let firstLost=false;
  await fp.route(url=>decodeURIComponent(url.href).includes('/vpn-chat/v1/guest/start'),async route=>{if(!firstLost){firstLost=true;await route.fetch();await route.abort('failed');}else await route.continue();});
  await fp.goto(base);await fp.locator('#vpn-chat-launch').click();await fp.waitForFunction(()=>!!window.testTurnstile);await fp.locator('#vpn-chat-name').fill('Lost first ack');await fp.locator('#vpn-chat-email').fill('first-ack@example.invalid');await fp.locator('#vpn-chat-first').fill('First message saved before network loss');await fp.locator('#vpn-chat-start-button').click();await fp.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent.includes('not confirmed'));await fp.locator('#vpn-chat-start-button').click();await fp.locator('#vpn-chat-reply').waitFor({state:'visible'});await fp.locator('.vpn-chat-message').waitFor();check(await fp.locator('.vpn-chat-message').filter({hasText:'First message saved before network loss'}).count()===1,'lost first acknowledgement retries one lead and first message');await fresh.close();
  await context.close();await salesContext.close();await mobile.close();
  await writeFile(path.join(output,'browser-report.json'),JSON.stringify({checks:results.length,passed:results},null,2));console.log(`TOTAL ${results.length} browser checks passed`);
}finally{await browser.close();}
