import {createRequire} from 'node:module';
import {readFile,writeFile} from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
const require=createRequire(import.meta.url),{chromium}=require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),passed=[],samples={};
const check=(v,n)=>{if(!v)throw Error(n);passed.push(n);console.log('PASS '+n);};
const markup=execFileSync('C:/xampp/php/php.exe',['tests/live-chat/inbox-delete-markup.php','manager'],{encoding:'utf8'});
const css=await readFile('wp-content/plugins/vpn-live-chat/assets/admin.css','utf8');
const ids=['11111111-1111-4111-8111-111111111111','22222222-2222-4222-8222-222222222222'];
const row=(id)=>({id,name:id===ids[0]?'Customer A':'Customer B',customer_id:id===ids[0]?6:7,email:'buyer@example.invalid',status:'unassigned',owner_id:0,version:1,guest_seq:1,read_seq:1,updated_at:'2026-10-07 01:00:00',created_at:'2026-10-07 01:00:00',preview:'Hello'});
const message=(seq,sender,body)=>({seq,sender,body,created_at:'2026-10-07 01:00:00',profile:{name:'Test Sales'}});
async function fixture(before=false){
 const js=await readFile(before?'tests/live-chat/runtime/admin-before.js':'wp-content/plugins/vpn-live-chat/assets/admin.js','utf8');
 const context=await browser.newContext({viewport:{width:1366,height:900}}),p=await context.newPage();
 const s={requests:[],holdNext:false,held:false,sendSlowSync:false,failSync:false,failSend:false,rows:[message(1,'guest','Hello')],saved:null,claimed:false,errors:[]};
 p.on('pageerror',e=>s.errors.push(e.message));
 await context.addInitScript(()=>{const original=fetch;window.fetch=(u,o)=>{if(String(u).includes('agent/sync'))o={...o,signal:undefined};return original(u,o);};});
 await context.route('**/*',async r=>{
  const path=new URL(r.request().url()).pathname,body=r.request().postDataJSON();
  if(path==='/admin.js')return r.fulfill({body:js,contentType:'text/javascript'});
  if(path==='/admin.css')return r.fulfill({body:css,contentType:'text/css'});
  if(path.startsWith('/api/')){
   s.requests.push({path,body});
   if(path.endsWith('agent/sync')){
    const result={list:ids.map(row),has_more:false,canned:[]};
    if(body.id){result.selected={...row(body.id),id:6,public_id:body.id,needs:'',label:'',source_path:'',history:[],seq:s.rows.length,...(s.claimed?{owner_id:1,status:'waiting_customer',version:3}:{})};result.delta={messages:body.id===ids[0]?structuredClone(s.rows):[message(1,'guest','Second customer')],cursor:s.rows.length,more:false};}
    if(s.holdNext){s.holdNext=false;s.held=true;await new Promise(resolve=>setTimeout(resolve,1200));s.held=false;}
    else if(s.sendSlowSync)await new Promise(resolve=>setTimeout(resolve,1000));
    if(s.failSync)return r.fulfill({status:503,json:{code:'service_unavailable'}});
    return r.fulfill({json:result});
   }
   if(path.endsWith('agent/update')){s.claimed=true;return r.fulfill({json:{...row(body.id),id:6,public_id:body.id,owner_id:1,status:'assigned',version:2,read_seq:body.read_seq||1}});}
   if(path.endsWith('agent/send')){
    await new Promise(resolve=>setTimeout(resolve,80));s.claimed=true;s.sendSlowSync=true;
    if(!s.saved){s.rows.push(message(2,'guest','Intervening guest'));s.saved={...message(3,body.note?'note':'agent',body.message)};s.rows.push(s.saved);}
    if(s.failSend){s.failSend=false;return r.fulfill({status:503,json:{code:'service_unavailable'}});}
    return r.fulfill({json:{saved:true,id:body.id,seq:3,message:s.saved,conversation:{...row(body.id),id:6,public_id:body.id,owner_id:1,status:'waiting_customer',version:3,seq:3,read_seq:1}}});
   }
   return r.abort();
  }
  if(path==='/')return r.fulfill({contentType:'text/html',body:`<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="/admin.css"><div id="wpbody-content">${markup}</div><script>window.VPNChatAdmin={api:'http://localhost/api/',nonce:'test',manager:true,user:1,agents:[]}</script><script src="/admin.js"></script>`});
  return r.abort();
 });
 await p.goto('http://localhost/');await p.locator('.vpn-row').first().waitFor();
 await p.locator('.vpn-row').first().click();await p.locator('#vpn-reply').waitFor({state:'visible'});
 return {context,p,s};
}
try{
 for(const before of [true,false]){
  const f=await fixture(before);try{
   f.s.holdNext=true;await f.p.evaluate(()=>dispatchEvent(new Event('focus')));
   while(!f.s.held)await f.p.waitForTimeout(10);
   const start=Date.now();await f.p.locator('.vpn-row').nth(1).click();await f.p.locator('#vpn-reply').waitFor({state:'visible'});
   await f.p.waitForFunction(()=>document.getElementById('vpn-customer').textContent.includes('Customer B')&&document.getElementById('vpn-transcript').textContent.includes('Second customer'));
   samples[before?'select_before_ms':'select_after_ms']=Date.now()-start;
   if(!before){check(samples.select_after_ms<600,'selection bypasses a 1.2-second old poll');check(f.s.requests.some(r=>r.body?.detail_only),'selection requests lightweight detail');await f.p.waitForTimeout(1300);check((await f.p.locator('#vpn-customer').textContent()).includes('Customer B'),'late old sync cannot replace the selected customer');}
  }finally{await f.context.close();}
  const send=await fixture(before);try{
   await send.p.locator('#vpn-message').fill('Fast sales reply');const start=Date.now();await send.p.locator('#vpn-agent-send').click();
   await send.p.locator('.vpn-msg[data-sender=agent]').filter({hasText:'Fast sales reply'}).waitFor();
   samples[before?'send_before_ms':'send_after_ms']=Date.now()-start;
   if(!before){check(samples.send_after_ms<650,'stored sales receipt displays without a one-second refresh');check(await send.p.locator('#vpn-agent-send').isEnabled(),'send button unlocks while background refresh is slow');check(!send.s.requests.some(r=>r.path.endsWith('agent/update')&&r.body.action==='claim'),'first reply avoids a separate claim request');
    await send.p.waitForFunction(()=>document.getElementById('vpn-transcript').textContent.includes('Intervening guest'));
    check((await send.p.locator('.vpn-msg').allTextContents()).map(t=>t.includes('Hello')?1:t.includes('Intervening')?2:3).join(',')==='1,2,3','receipt and subsequent delta remain in server sequence order');check(await send.p.locator('.vpn-msg[data-sender=agent]').count()===1,'receipt and delta do not duplicate the sales message');}
  }finally{await send.context.close();}
 }
 check(samples.select_before_ms>samples.select_after_ms+700,'measured selection improves with the same simulated latency');
 check(samples.send_before_ms>samples.send_after_ms+700,'measured reply display improves with the same simulated latency');
 const stable=await fixture();try{
  await stable.p.evaluate(()=>{window.firstRow=document.querySelector('.vpn-row');window.firstHeader=document.querySelector('#vpn-customer h2');window.firstCanned=document.querySelector('#vpn-canned option');});
  await stable.p.locator('#vpn-message').fill('Draft stays');await stable.p.locator('#vpn-search-button').click();await stable.p.waitForTimeout(150);
  check(await stable.p.evaluate(()=>firstRow===document.querySelector('.vpn-row')&&firstHeader===document.querySelector('#vpn-customer h2')&&firstCanned===document.querySelector('#vpn-canned option')),'unchanged sync preserves list/header/canned DOM nodes');
  check(await stable.p.locator('#vpn-message').inputValue()==='Draft stays','unchanged sync keeps the draft');check(stable.s.errors.length===0,'optimized inbox has no uncaught errors');
 }finally{await stable.context.close();}
 const failedSync=await fixture();try{
  failedSync.s.failSync=true;await failedSync.p.locator('#vpn-message').fill('Receipt survives sync failure');await failedSync.p.locator('#vpn-agent-send').click();
  await failedSync.p.locator('.vpn-msg[data-sender=agent]').waitFor();check((await failedSync.p.locator('#vpn-transcript').textContent()).includes('Receipt survives sync failure'),'successful send stays visible when sync returns 503');
 }finally{await failedSync.context.close();}
 const retry=await fixture();try{
  retry.s.failSend=true;await retry.p.locator('#vpn-message').fill('Lost response retry');await retry.p.locator('#vpn-agent-send').click();
  await retry.p.waitForFunction(()=>document.getElementById('vpn-send-status').textContent.includes('Chưa xác nhận'));
  check(await retry.p.locator('#vpn-message').inputValue()==='Lost response retry','unconfirmed send keeps the draft');await retry.p.locator('.vpn-row').nth(1).click();check((await retry.p.locator('#vpn-customer').textContent()).includes('Customer A'),'unconfirmed send prevents switching customers');
  await retry.p.locator('#vpn-agent-send').click();await retry.p.locator('.vpn-msg[data-sender=agent]').waitFor();const sends=retry.s.requests.filter(r=>r.path.endsWith('agent/send'));
  check(sends.length===2&&sends[0].body.client_message_id===sends[1].body.client_message_id,'retry reuses the original message identifier');check(await retry.p.locator('.vpn-msg[data-sender=agent]').count()===1,'retry displays one canonical message');
 }finally{await retry.context.close();}
}finally{
 await browser.close();await writeFile('artifacts/vpn-live-chat/evidence/admin-performance-ui.json',JSON.stringify({version:'1.8.4',checks:passed.length,passed,samples,mode:'real PHP markup and assets; controlled HTTP delays, not production latency'},null,2));
}
