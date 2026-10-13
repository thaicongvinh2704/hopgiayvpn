import {createRequire} from 'node:module';
import {writeFile} from 'node:fs/promises';
const require=createRequire(import.meta.url);
const {chromium}=require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const base='http://127.0.0.1:8091',passed=[],errors=[];
const check=(ok,name)=>{if(!ok)throw Error(name);passed.push(name);};
try{
 for(const width of [1366,390,320]){
  const context=await browser.newContext({viewport:{width,height:850}});
  await context.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());
  const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base+'/',{waitUntil:'domcontentloaded'});await page.locator('#vpn-chat-launch').click();
  await page.waitForFunction(()=>!document.getElementById('vpn-chat-start-button').disabled);
  let syncRequests=0;
  await page.route('**/vpn-chat/v1/guest/sync',r=>{syncRequests++;return r.fulfill({status:503,json:{code:'service_unavailable'}});});
  await page.locator('#vpn-chat-first').fill('What is MOQ for custom boxes?');
  const result=page.waitForResponse(r=>r.url().endsWith('/guest/start')&&r.request().method()==='POST');
  await page.locator('#vpn-chat-start-button').click();const receipt=await (await result).json();
  await page.locator('.vpn-chat-message[data-sender=bot]').waitFor({state:'visible'});
  check(receipt.auto_reply?.sender==='bot','real HTTP start returns bot receipt '+width);
  check((await page.locator('.vpn-chat-message[data-sender=bot]').innerText()).includes('1,000'),'MOQ answer visible with sync unavailable '+width);
  check((await page.locator('.vpn-chat-message[data-sender=bot] .vpn-chat-message-name').innerText())==='VPN Automated Assistant','automation clearly identified '+width);
  check(await page.locator('.vpn-chat-message[data-sender=guest]').count()===1,'one stored guest message '+width);
  await page.locator('#vpn-chat-message').fill('5000 boxes');await page.locator('#vpn-chat-send-button').click();
  await page.waitForFunction(()=>document.querySelectorAll('.vpn-chat-message[data-sender=bot]').length===2);
  check((await page.locator('.vpn-chat-message[data-sender=bot]').last().innerText()).includes('dimensions'),'follow-up asks missing size '+width);
  check(!((await page.locator('.vpn-chat-message[data-sender=bot]').last().innerText()).includes('how many')),'known quantity not asked again '+width);
  const panel=await page.locator('#vpn-chat-panel').boundingBox(),composer=await page.locator('#vpn-chat-message').boundingBox();
  check(panel.x>=-1&&panel.x+panel.width<=width+1&&composer.y+composer.height<=850,'panel/composer stay inside viewport '+width);
  await page.locator('#vpn-chat-panel').screenshot({path:`artifacts/vpn-live-chat/evidence/keyword-reply-${width}.png`});
  check(syncRequests>0,'receipt display tested while polling fails '+width);
  await page.unroute('**/vpn-chat/v1/guest/sync');
  await page.reload();await page.locator('#vpn-chat-launch').click();
  await page.waitForFunction(()=>document.querySelectorAll('.vpn-chat-message[data-sender=bot]').length===2);
  check(await page.locator('.vpn-chat-message').count()===4,'reload restores transcript without duplicates '+width);
  await page.locator('#vpn-chat-message').fill('Can I speak to a human?');await page.locator('#vpn-chat-send-button').click();
  await page.locator('.vpn-chat-message[data-sender=bot]').filter({hasText:'stop automated'}).waitFor();
  await page.waitForTimeout(1150);await page.locator('#vpn-chat-message').fill('Hello, anyone there?');await page.locator('#vpn-chat-send-button').click();
  await page.locator('.vpn-chat-message[data-sender=guest]').filter({hasText:'Hello, anyone there?'}).waitFor();
  check(await page.locator('.vpn-chat-message[data-sender=bot]').count()===3,'handoff pauses later automatic replies '+width);
  await context.close();
 }
 check(errors.length===0,'no JavaScript errors');
 const report={version:'1.9.0',checks:passed.length,passed,errors,environment:'real HTTP/WordPress, isolated vpn_chat_test:3311'};
 await writeFile('artifacts/vpn-live-chat/evidence/keyword-browser-report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report));
}finally{await browser.close();}
