import { createRequire } from 'node:module';
import { readFile, mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
const require=createRequire(import.meta.url);
const {chromium}=require(process.env.PLAYWRIGHT_PATH||'C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const base='http://localhost/hopgiayvpn';
const credentials=JSON.parse(await readFile(new URL('./runtime/local-demo-access.json',import.meta.url),'utf8'));
const output=new URL('./results/',import.meta.url).pathname.replace(/^\/(\w:)/,'$1');await mkdir(output,{recursive:true});
const browser=await chromium.launch({headless:true,executablePath:process.env.CHROME_PATH||'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const passed=[];function check(value,name){if(!value)throw new Error('FAIL '+name);passed.push(name);console.log('PASS '+name);}
async function blockExternal(context){await context.route('**/*',route=>{const host=new URL(route.request().url()).hostname;return ['localhost','127.0.0.1','::1'].includes(host)?route.continue():route.abort();});}
try{
 const guestContext=await browser.newContext({viewport:{width:1366,height:900}});await blockExternal(guestContext);const guest=await guestContext.newPage();const errors=[];guest.on('pageerror',e=>errors.push(e.message));
 await guest.goto(base+'/');await guest.locator('#vpn-chat-launch').waitFor();check(true,'widget installed on existing local homepage');
 await guest.locator('#vpn-chat-launch').click();await guest.locator('#vpn-chat-name').waitFor();await guest.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent==='Ready to send.');check(true,'local demo challenge works without external service');
 const name='Local installation test '+Date.now();await guest.locator('#vpn-chat-name').fill(name);await guest.locator('#vpn-chat-email').fill('local-preview@example.invalid');await guest.locator('#vpn-chat-first').fill('Can your sales team reply in this local preview?');await guest.locator('#vpn-chat-start-button').click();await guest.locator('#vpn-chat-reply').waitFor({state:'visible'});await guest.locator('.vpn-chat-message').first().waitFor();check(true,'local guest first message committed');
 const salesContext=await browser.newContext({viewport:{width:1366,height:1000}});await blockExternal(salesContext);const sales=await salesContext.newPage();sales.on('pageerror',e=>errors.push(e.message));
 await sales.goto(base+'/wp-login.php');await sales.locator('#user_login').fill(credentials.login);await sales.locator('#user_pass').fill(credentials.password);await Promise.all([sales.waitForURL('**/wp-admin/**'),sales.locator('#wp-submit').click()]);await sales.goto(credentials.inbox);
 await sales.locator('#vpn-presence').selectOption('available');await sales.locator('.vpn-row').filter({hasText:name}).click();await sales.locator('#vpn-reply').waitFor({state:'visible'});check(true,'local demo sales opens chat without manual claim');
 await sales.locator('#vpn-message').fill('Local preview reply received successfully.');await sales.locator('#vpn-reply button').click();await guest.locator('.vpn-chat-message').filter({hasText:'Local preview reply received successfully.'}).waitFor({timeout:25000});check(true,'local sales reply reaches visitor');
 await guest.reload();await guest.locator('#vpn-chat-launch').click();await guest.locator('.vpn-chat-message').filter({hasText:'Local preview reply received successfully.'}).waitFor();check(true,'local reload restores persisted transcript');
 check(errors.length===0,'existing local theme and chat have no uncaught JS errors');
 const denied=await guestContext.request.get(base+'/tests/live-chat/runtime/local-demo-access.json');check(denied.status()===403,'demo credentials and test runtime protected from HTTP');
 await guest.screenshot({path:path.join(output,'local-installed-widget.png'),fullPage:false});await guest.locator('#vpn-chat-panel').screenshot({path:path.join(output,'local-chat-panel.png')});await sales.screenshot({path:path.join(output,'local-installed-inbox.png'),fullPage:false});
 await sales.locator('#vpn-presence').selectOption('offline');await sales.waitForResponse(response=>decodeURIComponent(response.url()).includes('/vpn-chat/v1/agent/sync'),{timeout:15000}).catch(()=>{});
 await guestContext.close();await salesContext.close();await writeFile(path.join(output,'local-preview-report.json'),JSON.stringify({checks:passed.length,passed,widget:base+'/',inbox:credentials.inbox},null,2));console.log('TOTAL '+passed.length+' local install checks passed');
}finally{await browser.close();}
