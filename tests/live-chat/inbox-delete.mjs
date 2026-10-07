import {createRequire} from 'node:module';
import {readFile,writeFile} from 'node:fs/promises';
import {execFileSync} from 'node:child_process';
const require=createRequire(import.meta.url);
const {chromium}=require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),passed=[];
const check=(v,n)=>{if(!v)throw Error(n);passed.push(n);console.log('PASS '+n);};
const js=await readFile('wp-content/plugins/vpn-live-chat/assets/admin.js','utf8'),css=await readFile('wp-content/plugins/vpn-live-chat/assets/admin.css','utf8');
const markup=role=>execFileSync('C:/xampp/php/php.exe',['tests/live-chat/inbox-delete-markup.php',role],{encoding:'utf8'});
const id='11111111-1111-4111-8111-111111111111',other='22222222-2222-4222-8222-222222222222';
const row=(public_id=id)=>({id:public_id,name:public_id===id?'Khách #000006':'Khách #000007',customer_id:public_id===id?6:7,email:'buyer@example.invalid',status:'assigned',owner_id:1,version:1,guest_seq:1,read_seq:1,updated_at:'2026-10-07 01:00:00',created_at:'2026-10-07 01:00:00',preview:'Hello'});
const conv=public_id=>({...row(public_id),id:public_id===id?6:7,public_id,seq:1,needs:'',label:'',source_path:'',history:[]});
async function fixture(width=1366,role='manager'){
 const context=await browser.newContext({viewport:{width,height:900}}),p=await context.newPage();
 const s={deleted:false,deletes:[],failure:false,holdNext:false,release:null,delayDelete:0,syncs:0,errors:[]};
 p.on('pageerror',e=>s.errors.push(e.message));
 // Ignore AbortSignal on sync only, so a pre-delete response really can arrive late.
 await context.addInitScript(()=>{const fetch=window.fetch;window.fetch=(url,options)=>{if(String(url).includes('agent/sync'))options={...options,signal:undefined};return fetch(url,options);};});
 await context.route('**/*',async route=>{
  const u=new URL(route.request().url()),body=route.request().postDataJSON();
  if(u.pathname==='/admin.css')return route.fulfill({body:css,contentType:'text/css'});
  if(u.pathname==='/admin.js')return route.fulfill({body:js,contentType:'text/javascript'});
  if(u.pathname==='/api/agent/sync'){
   s.syncs++;const result={list:s.deleted?[row(other)]:[row(),row(other)],has_more:false,canned:[]};
   if(body.id&&(body.id!==id||!s.deleted)){result.selected=conv(body.id);result.delta={messages:[{seq:1,sender:'guest',body:'Hello',created_at:'2026-10-07 01:00:00'}],cursor:1,more:false};}
   if(s.holdNext){s.holdNext=false;await new Promise(resolve=>s.release=resolve);s.release=null;}
   return route.fulfill({json:result});
  }
  if(u.pathname==='/api/manager/privacy'){
   s.deletes.push(body);if(s.delayDelete)await new Promise(r=>setTimeout(r,s.delayDelete));
   if(s.failure)return route.fulfill({status:503,json:{code:'service_unavailable'}});
   s.deleted=true;return route.fulfill({json:{deleted:true}});
  }
  if(u.pathname==='/')return route.fulfill({contentType:'text/html',body:`<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><link rel="stylesheet" href="/admin.css"></head><body><div id="wpbody-content">${markup(role)}</div><script>window.VPNChatAdmin=${JSON.stringify({api:'http://localhost/api/',nonce:'test',manager:role==='manager',user:1,agents:[]})}</script><script src="/admin.js"></script></body></html>`});
  return route.abort();
 });
 await p.goto('http://localhost/');await p.locator('.vpn-row').first().waitFor();
 return {context,p,s,select:async()=>{await p.locator('.vpn-row').first().click();await p.locator('#vpn-reply').waitFor({state:'visible'});}};
}
try{
 for(const width of [1366,390,320]){
  const f=await fixture(width);try{
   await f.select();check(await f.p.locator('#vpn-delete').isVisible(),`${width}: delete visible with details closed`);
   const rect=await f.p.locator('#vpn-delete').boundingBox();check(rect.x>=0&&rect.x+rect.width<=width,`${width}: delete fits viewport`);
   check(await f.p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${width}: no horizontal overflow`);
   await f.p.screenshot({path:`artifacts/vpn-live-chat/evidence/inbox-delete-${width}.png`});
   f.p.once('dialog',d=>d.dismiss());await f.p.locator('#vpn-delete').click();check(f.s.deletes.length===0,`${width}: cancel does not call deletion API`);
   check(await f.p.locator('#vpn-detail').isVisible(),`${width}: cancel preserves selected chat`);
   f.p.once('dialog',d=>d.accept());await f.p.locator('#vpn-delete').click();await f.p.locator('#vpn-detail').waitFor({state:'hidden'});
   await f.p.waitForFunction(()=>document.getElementById('vpn-list').textContent.includes('000007')&&!document.getElementById('vpn-list').textContent.includes('000006'));
   check(f.s.deletes.length===1&&f.s.deletes[0].id===id&&f.s.deletes[0].confirmed_delete===true,`${width}: confirmed deletion targets selected conversation`);
   check(await f.p.locator('#vpn-transcript').textContent()==='',`${width}: deleted transcript cleared`);
   check(await f.p.locator('#vpn-admin-error').textContent()==='Đã xóa hội thoại.',`${width}: success survives refresh`);
   check(f.s.errors.length===0,`${width}: no JavaScript errors`);
  }finally{await f.context.close();}
 }
 const failure=await fixture();try{
  await failure.select();await failure.p.locator('#vpn-message').fill('Keep this draft');failure.s.failure=true;
  failure.p.once('dialog',d=>d.accept());await failure.p.locator('#vpn-delete').click();await failure.p.waitForFunction(()=>document.getElementById('vpn-admin-error').textContent.includes('Không xóa được'));
  check(await failure.p.locator('#vpn-detail').isVisible(),'failed delete preserves selected chat');
  check(await failure.p.locator('#vpn-message').inputValue()==='Keep this draft','failed delete preserves draft');
  check(await failure.p.locator('#vpn-delete').isEnabled(),'failed delete allows retry');
  failure.s.failure=false;failure.p.once('dialog',d=>d.accept());await failure.p.locator('#vpn-delete').click();await failure.p.locator('#vpn-empty').waitFor({state:'visible'});
  await failure.select();check(await failure.p.locator('#vpn-message').inputValue()==='','successful delete clears draft before choosing another customer');
 }finally{await failure.context.close();}
 const stale=await fixture();try{
  await stale.select();stale.s.holdNext=true;await stale.p.locator('#vpn-search-button').click();
  while(!stale.s.release)await stale.p.waitForTimeout(20);
  stale.s.delayDelete=300;stale.p.once('dialog',d=>d.accept());await stale.p.locator('#vpn-delete').click();
  check(await stale.p.locator('#vpn-delete').isDisabled(),'in-flight deletion prevents duplicate clicks');
  await stale.p.locator('#vpn-empty').waitFor({state:'visible'});stale.s.release();
  await stale.p.waitForFunction(()=>document.querySelectorAll('#vpn-list .vpn-row').length===1);
  check(await stale.p.locator('#vpn-detail').isHidden(),'stale sync cannot reopen deleted conversation');
  check(!(await stale.p.locator('#vpn-list').textContent()).includes('000006'),'stale sync cannot restore deleted list row');
  check(stale.s.deletes.length===1,'one delete request during delayed sync');
 }finally{await stale.context.close();}
 const sales=await fixture(1366,'sales');try{await sales.select();check(await sales.p.locator('#vpn-delete').count()===0,'sales markup omits manager delete control');check(sales.s.errors.length===0,'sales inbox has no JavaScript errors');}finally{await sales.context.close();}
}finally{
 await browser.close();await writeFile('artifacts/vpn-live-chat/evidence/inbox-delete-report.json',JSON.stringify({version:'1.8.4',checks:passed.length,passed,mode:'real PHP inbox markup and plugin JS/CSS; mocked HTTP'},null,2));
}
