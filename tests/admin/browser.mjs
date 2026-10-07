import { createRequire } from 'node:module';
import { writeFile } from 'node:fs/promises';
const require=createRequire(import.meta.url);
const {chromium}=require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const base='http://127.0.0.1:8091';
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const context=await browser.newContext();
await context.route('**/*',route=>new URL(route.request().url()).hostname==='127.0.0.1'?route.continue():route.abort());
const page=await context.newPage(),passed=[],samples=[],errors=[];
page.on('pageerror',e=>errors.push(e.message));
const check=(v,n)=>{if(!v)throw Error(n);passed.push(n);console.log('PASS '+n);};
let postId=0,nonce='';
async function api(path,method,data){return page.evaluate(async({base,path,method,data,nonce})=>{
 const r=await fetch(base+'/wp-json/wp/v2/'+path,{method,headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},body:data?JSON.stringify(data):undefined});
 return {status:r.status,data:await r.json()};
},{base,path,method,data,nonce});}
try{
 await page.goto(base+'/wp-login.php');
 await page.locator('#user_login').fill('chat-test-manager');await page.locator('#user_pass').fill('test-password-local-only');
 await Promise.all([page.waitForURL('**/wp-admin/**'),page.locator('#wp-submit').click()]);
 for(const path of ['index.php','edit.php','upload.php?mode=list','edit.php?post_type=product','plugins.php','options-general.php']){
  const start=performance.now();const response=await page.goto(base+'/wp-admin/'+path,{waitUntil:'domcontentloaded'});
  check(response.status()===200&&await page.locator('body.wp-admin #wpbody-content').count()===1,'authenticated admin loads '+path+' (HTTP '+response.status()+')');
  samples.push({path,status:response.status(),ttfb_ms:Math.round(response.request().timing().responseStart),domcontentloaded_ms:Math.round(performance.now()-start)});
 }
 await page.goto(base+'/wp-admin/post-new.php',{waitUntil:'domcontentloaded'});
 await page.waitForFunction(()=>!!window.wpApiSettings?.nonce);
 nonce=await page.evaluate(()=>window.wpApiSettings.nonce);
 const title='Admin performance isolated draft '+Date.now();
 let r=await api('posts','POST',{title,status:'draft',content:'Initial test draft'});
 postId=r.data.id||0;check(r.status===201&&postId>0&&r.data.status==='draft','authenticated editor creates a real draft');
 r=await api('posts/'+postId,'POST',{title:title+' updated',content:'Saved test edit'});
 check(r.status===200&&r.data.title.raw===title+' updated','authenticated editor saves a real draft edit');
 r=await api('posts/'+postId+'?context=edit','GET');check(r.status===200&&r.data.content.raw==='Saved test edit','saved draft persists in the database');
 const front=await page.goto(base+'/',{waitUntil:'domcontentloaded'});check(front.status()===200&&await page.locator('body').count()===1,'actual theme frontend still renders');
 check(errors.length===0,'admin and frontend have no uncaught JavaScript errors');
}finally{
 if(postId&&nonce){const r=await api('posts/'+postId+'?force=true','DELETE');check(r.status===200,'isolated draft is removed after testing');}
 await browser.close();
 await writeFile('artifacts/admin-performance/browser-tests.json',JSON.stringify({checks:passed.length,passed,samples,errors,mode:'Actual WordPress HTTP and browser on isolated 8091/3311; local host only; timings are not production measurements'},null,2));
}
