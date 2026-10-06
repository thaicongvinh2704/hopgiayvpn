import {createRequire} from 'node:module';
import {readFile,writeFile} from 'node:fs/promises';
const require=createRequire(import.meta.url);
const {chromium}=require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),passed=[];
const check=(value,label)=>{if(!value)throw Error(label);passed.push(label);console.log('PASS '+label);};
const files=new Map();for(const n of ['launcher.js','launcher.css','widget.js','chat.css','tho-nguyen.png'])files.set(n,await readFile('wp-content/plugins/vpn-live-chat/assets/'+n));
const id='11111111-1111-4111-8111-111111111111',asset='/assets/';
async function fixture(width,overrides={}){
 const c=await browser.newContext({viewport:{width,height:844}}),p=await c.newPage(),errors=[],store={email:'',conversation:null,rows:[],starts:0,contacts:0,failSave:false,closed:false};
 p.on('pageerror',e=>errors.push(e.message));
 const config={accepting:true,online:true,presence:'online',agent_available:width===1366,support:{name:'Tho Nguyen',initials:'TN',avatar:'http://localhost/assets/tho-nguyen.png'},max_chars:2000,site_key:'',...overrides};
 await c.route('**/*',async r=>{
  const u=new URL(r.request().url());if(u.pathname.startsWith(asset)){const n=u.pathname.slice(asset.length);return r.fulfill({body:files.get(n),contentType:n.endsWith('.css')?'text/css':n.endsWith('.png')?'image/png':'text/javascript'});}
  if(u.pathname.startsWith('/api/')){
   const path=u.pathname.slice(5),d=r.request().postDataJSON();let out={};
   if(path==='bootstrap')out={csrf:'test',conversation:store.conversation,config,customer:{code:'Customer #TEST'},history:store.conversation?[{id,updated_at:'2026-10-06 01:00:00',status:'unassigned'}]:[]};
   if(path==='guest/start'){store.starts++;if(store.rejectStart)return r.fulfill({status:store.rejectStart==='rate_limited'?429:503,headers:{'Retry-After':'2'},json:{code:store.rejectStart}});if(store.first?.client_message_id===d.client_message_id)return r.fulfill({json:{id,saved:true}});store.first=d;store.email=d.email;store.conversation=id;store.rows.push({seq:1,sender:'guest',body:d.message,created_at:'2026-10-06 01:00:00'});if(store.loseStart){store.loseStart=false;return r.abort();}out={id,saved:true};}
   if(path==='guest/send'){store.rows.push({seq:store.rows.length+1,sender:'guest',body:d.message,created_at:'2026-10-06 01:01:00'});out={id,saved:true};}
   if(path==='guest/sync')out={messages:store.rows,cursor:store.rows.length,more:false,config,conversation:{id,email:store.email,status:store.closed?'closed':'unassigned',unread:0,guest_read_seq:store.rows.length,agent_read_seq:0}};
   if(path==='guest/read')out={email:store.email,unread:0,guest_read_seq:store.rows.length};
   if(path==='guest/contact'){store.contacts++;if(store.failSave)return r.fulfill({status:500,json:{code:'request_failed'}});store.email=d.email;out={email:store.email,saved:true};}
   if(path==='guest/identity/request')out={request_id:'test-restore'};
   if(path==='guest/identity/verify')out={verified:true};
   return r.fulfill({json:out});
  }
  return r.fulfill({contentType:'text/html',body:`<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/launcher.css"><style>body{background:#f6fafc;font:16px Arial}.mobile-conversion-bar{position:fixed;bottom:0;right:0;width:100%;height:62px;background:white}</style><h1>VPN Packaging</h1><div class="mobile-conversion-bar">Request Quote / WhatsApp</div><script>window.VPNChatLauncher=${JSON.stringify({api:'http://localhost/api/',bundle:'http://localhost/assets/widget.js',css:'http://localhost/assets/chat.css',support:config.support,bottom:100,greeting_enabled:false})}</script><script src="/assets/launcher.js"></script>`});
 });return {c,p,store,errors};
}
try{
 for(const width of [1366,390,320]){
  const {c,p,store,errors}=await fixture(width),tag=' '+width;
  await p.goto('http://localhost/test');await p.locator('#vpn-chat-launch').click();await p.locator('#vpn-chat-welcome').waitFor({state:'visible'});
  check(!await p.locator('#vpn-chat-name').isVisible()&&!await p.locator('#vpn-chat-email').isVisible(),'no contact form before chatting'+tag);
  check(!await p.locator('#vpn-chat-contact').isVisible(),'follow-up waits for a message'+tag);
  check(!await p.locator('#vpn-chat-away-message').isVisible(),'no duplicate unattended message'+tag);
  check(!await p.locator('#vpn-chat-customer-code').isVisible(),'customer identity hidden in options'+tag);
  check(await p.locator('#vpn-chat-add-email').isVisible(),'email action always accessible'+tag);
  check(await p.locator('#vpn-chat-presence').evaluate(e=>getComputedStyle(e).position==='static'),'presence stays inside header'+tag);
  await p.locator('#vpn-chat-panel').screenshot({path:`artifacts/vpn-live-chat/evidence/gentle-followup-open-${width}.png`});
  await p.locator('#vpn-chat-options').click();check(await p.locator('#vpn-chat-customer-code').isVisible(),'options reveal identity'+tag);
  await p.locator('#vpn-chat-restore summary').click();await p.locator('#vpn-chat-identity-email').fill('restore@example.invalid');await p.locator('#vpn-chat-code-request').click();await p.locator('#vpn-chat-code-form').waitFor({state:'visible'});check(true,'history restoration remains available'+tag);
  await p.locator('#vpn-chat-code').fill('123456');await p.locator('#vpn-chat-code-verify').click();await p.locator('#vpn-chat-code-form').waitFor({state:'hidden'});check((await p.locator('#vpn-chat-state').innerText()).includes('verified'),'restore verification completes'+tag);
  await p.keyboard.press('Escape');check(await p.locator('#vpn-chat-panel').isVisible()&&!await p.locator('#vpn-chat-identity-menu').isVisible(),'Escape closes options first'+tag);
  await p.locator('#vpn-chat-first').fill('I need boxes for skincare.');await p.locator('#vpn-chat-start-button').click();await p.locator('#vpn-chat-reply').waitFor({state:'visible'});await p.locator('#vpn-chat-contact').waitFor({state:'visible'});
  check(store.first.email===''&&store.first.name==='','first message needs no name or email'+tag);
  check((await p.locator('#vpn-chat-log').innerText()).includes('I need boxes for skincare.'),'sent message is retained'+tag);
  check(await p.locator('#vpn-chat-contact').count()===1,'one inline follow-up card'+tag);
  check(!await p.locator('#vpn-chat-contact-email').evaluate(e=>e.required),'email remains optional'+tag);
  await p.locator('#vpn-chat-panel').screenshot({path:`artifacts/vpn-live-chat/evidence/gentle-followup-sent-${width}.png`});
  await p.locator('#vpn-chat-contact-dismiss').click();await p.locator('#vpn-chat-message').fill('Please show the options.');await p.locator('#vpn-chat-send-button').click();await p.waitForFunction(()=>document.querySelectorAll('.vpn-chat-message').length===2);
  check(!await p.locator('#vpn-chat-contact').isVisible(),'dismissed invitation stays closed while chatting'+tag);
  await p.locator('#vpn-chat-add-email').click();check(await p.locator('#vpn-chat-contact-email').evaluate(e=>e===document.activeElement),'persistent email action reopens and focuses field'+tag);
  await p.locator('#vpn-chat-contact-email').fill('not-an-email');await p.locator('#vpn-chat-contact-save').click();check(store.contacts===0,'invalid email does not reach API'+tag);
  store.failSave=true;await p.locator('#vpn-chat-contact-email').fill('buyer@example.invalid');await p.locator('#vpn-chat-contact-save').click();await p.locator('#vpn-chat-contact-status').filter({hasText:'Could not save'}).waitFor();check(await p.locator('#vpn-chat-contact-email').inputValue()==='buyer@example.invalid','failed save preserves email draft'+tag);
  store.failSave=false;await p.locator('#vpn-chat-contact-save').click();await p.locator('#vpn-chat-contact').waitFor({state:'hidden'});check(store.email==='buyer@example.invalid','email saved to conversation'+tag);
  check(await p.locator('#vpn-chat-add-email').innerText()==='Edit email','saved email remains editable'+tag);
  await p.reload();await p.locator('#vpn-chat-launch').click();await p.locator('#vpn-chat-log .vpn-chat-message').first().waitFor();check(!await p.locator('#vpn-chat-contact').isVisible(),'saved email suppresses invitation after reload'+tag);
  await p.locator('#vpn-chat-add-email').click();check(await p.locator('#vpn-chat-contact-email').inputValue()==='buyer@example.invalid','saved email is restored into editor'+tag);
  await p.locator('#vpn-chat-contact-email').fill('');await p.locator('#vpn-chat-contact-save').click();await p.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent.includes('removed'));check(store.email==='','customer may remove email'+tag);
  const bounds=await p.locator('#vpn-chat-message').boundingBox();check(bounds.x>=0&&bounds.x+bounds.width<=width&&bounds.y+bounds.height<782,'composer stays visible above bottom CTA'+tag);
  check(await p.locator('#vpn-chat-panel input[type=file]').count()===0,'text-only chat has no file upload'+tag);
  store.closed=true;await p.locator('#vpn-chat-close').click();await p.locator('#vpn-chat-launch').click();await p.locator('#vpn-chat-new').waitFor({state:'visible'});check(!await p.locator('#vpn-chat-contact').isVisible(),'closed conversation has no unsolicited email prompt'+tag);
  const oldCount=store.rows.length;await p.locator('#vpn-chat-message').fill('Closed draft');await p.locator('#vpn-chat-message').press('Enter');check(store.rows.length===oldCount,'Enter cannot submit to a closed conversation'+tag);
  await p.locator('#vpn-chat-new').click();await p.locator('#vpn-chat-first-composer').waitFor({state:'visible'});check(!await p.locator('#vpn-chat-contact').isVisible(),'new chat starts without email form'+tag);
  check(errors.length===0,'no JavaScript page errors'+tag);await c.close();
 }
 const {c,p,store}=await fixture(390);await p.goto('http://localhost/pre-email');await p.locator('#vpn-chat-launch').click();await p.locator('#vpn-chat-add-email').click();await p.locator('#vpn-chat-contact-email').fill('early@example.invalid');await p.locator('#vpn-chat-contact-save').click();check(store.starts===0&&store.contacts===0,'pre-chat email creates no conversation or API save');await p.locator('#vpn-chat-first').fill('Hello');await p.locator('#vpn-chat-start-button').click();await p.locator('#vpn-chat-reply').waitFor({state:'visible'});check(store.first.email==='early@example.invalid','optional pre-chat email accompanies first message');await c.close();
 const unavailable=await fixture(390,{accepting:false});await unavailable.p.goto('http://localhost/unavailable');await unavailable.p.locator('#vpn-chat-launch').click();await unavailable.p.locator('#vpn-chat-first').fill('TEST');await unavailable.p.locator('#vpn-chat-first').press('Enter');check(unavailable.store.starts===0,'Enter cannot bypass disabled new chat');check((await unavailable.p.locator('#vpn-chat-state').innerText()).includes('temporarily unavailable'),'unavailable status explains rejected action');check(await unavailable.p.locator('#vpn-chat-first').inputValue()==='TEST','unavailable chat retains draft');await unavailable.c.close();
 const limited=await fixture(390);limited.store.rejectStart='rate_limited';await limited.p.goto('http://localhost/rate-limit');await limited.p.locator('#vpn-chat-launch').click();await limited.p.locator('#vpn-chat-first').fill('Keep this message');await limited.p.locator('#vpn-chat-start-button').click();await limited.p.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent.includes('Please wait 2 seconds'));check(await limited.p.locator('#vpn-chat-first').inputValue()==='Keep this message','rate limit keeps draft');check(limited.store.rows.length===0,'rate limit creates no message');limited.store.rejectStart='';await limited.p.locator('#vpn-chat-start-button').click();await limited.p.locator('#vpn-chat-reply').waitFor({state:'visible'});check(limited.store.rows.length===1&&limited.store.rows[0].body==='Keep this message','rate limit retry sends once without CAPTCHA');await limited.c.close();
 const rejected=await fixture(390);rejected.store.rejectStart='new_chat_unavailable';await rejected.p.goto('http://localhost/server-unavailable');await rejected.p.locator('#vpn-chat-launch').click();await rejected.p.locator('#vpn-chat-first').fill('TEST');await rejected.p.locator('#vpn-chat-start-button').click();await rejected.p.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent.includes('temporarily unavailable'));check(await rejected.p.locator('#vpn-chat-first').inputValue()==='TEST','server rejection preserves draft');check(!await rejected.p.locator('#vpn-chat-state').innerText().then(t=>t.includes('not confirmed')),'server rejection uses specific explanation');await rejected.c.close();
 const retry=await fixture(390);retry.store.loseStart=true;await retry.p.goto('http://localhost/lost-response');await retry.p.locator('#vpn-chat-launch').click();await retry.p.locator('#vpn-chat-first').fill('Original message');await retry.p.locator('#vpn-chat-start-button').click();await retry.p.waitForFunction(()=>document.getElementById('vpn-chat-state').textContent.includes('not confirmed'));check(await retry.p.locator('#vpn-chat-first').inputValue()==='Original message','lost response retains first draft');await retry.p.locator('#vpn-chat-first').fill('New draft written before retry');await retry.p.locator('#vpn-chat-start-button').click();await retry.p.locator('#vpn-chat-reply').waitFor({state:'visible'});check(retry.store.rows.length===1&&retry.store.rows[0].body==='Original message','lost response retry confirms original without duplicate');check(await retry.p.locator('#vpn-chat-message').inputValue()==='New draft written before retry','edited draft survives first-message retry transition');await retry.c.close();
}finally{await browser.close();await writeFile('artifacts/vpn-live-chat/evidence/gentle-followup-report.json',JSON.stringify({version:'1.8.0',checks:passed.length,passed,mode:'actual plugin assets and mocked HTTP; no database fixtures'},null,2));}
