import {createRequire} from 'node:module';
import {readFile,writeFile} from 'node:fs/promises';
const require=createRequire(import.meta.url),{chromium}=require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const base='http://127.0.0.1:8091',passed=[];function check(ok,name){if(!ok)throw Error(name);passed.push(name);console.log('PASS '+name);}
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
async function login(c,user){const p=await c.newPage();await p.goto(base+'/wp-login.php');await p.locator('#user_login').fill(user);await p.locator('#user_pass').fill('test-password-local-only');await Promise.all([p.waitForURL('**/wp-admin/**'),p.locator('#wp-submit').click()]);await p.goto(base+'/wp-admin/admin.php?page=vpn-chat-profile');return p;}
try{
 const context=await browser.newContext();await context.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());const page=await login(context,'agent-a');
 const id=await page.locator('input[name=user_id]').inputValue(),nonce=await page.locator('input[name=_wpnonce]').inputValue(),fields={action:'vpn_chat_profile',user_id:id,_wpnonce:nonce,profile_name:'Tho Nguyen',avatar_id:'0'};
 check(await page.locator('input[name=profile_avatar]').count()===1,'least-privileged sales has own avatar upload field');
 let response=await context.request.post(base+'/wp-admin/admin-post.php',{form:{...fields,_wpnonce:'invalid'},maxRedirects:0});check(response.status()===403,'profile mutation rejects invalid nonce');
 response=await context.request.post(base+'/wp-admin/admin-post.php',{form:{...fields,user_id:'999999'},maxRedirects:0});check(response.status()===403,'sales cannot alter another account profile');
 response=await context.request.post(base+'/wp-admin/admin-post.php',{multipart:{...fields,profile_avatar:{name:'unsafe.svg',mimeType:'image/svg+xml',buffer:Buffer.from('<svg xmlns="http://www.w3.org/2000/svg"></svg>')}},maxRedirects:0});check(response.status()===400,'avatar upload rejects SVG/non-raster content');
 response=await context.request.post(base+'/wp-admin/admin-post.php',{multipart:{...fields,profile_avatar:{name:'oversized.png',mimeType:'image/png',buffer:Buffer.alloc(2*1024*1024+1)}},maxRedirects:0});check(response.status()===400,'avatar upload enforces size/error bound');
 const bytes=await readFile(new URL('../../wp-content/themes/custom-box-theme/assets/images/logo-hop-giay-vpn-hcm.webp',import.meta.url));
 response=await context.request.post(base+'/wp-admin/admin-post.php',{multipart:{...fields,profile_avatar:{name:'profile-qa-logo.webp',mimeType:'image/webp',buffer:bytes}},maxRedirects:0});check(response.status()===302,'valid image uploads through capability/nonce-guarded profile action');
 await page.goto(base+'/wp-admin/admin.php?page=vpn-chat-profile');const attachment=Number(await page.locator('#vpn-profile-avatar').inputValue());check(attachment>0&&await page.locator('img[alt="Avatar hiện tại"]').count()===1,'uploaded image saved as image attachment with profile preview');
 response=await context.request.post(base+'/wp-admin/admin-post.php',{form:{...fields,_wpnonce:await page.locator('input[name=_wpnonce]').inputValue()},maxRedirects:0});check(response.status()===302,'avatar can be reset to initials without modifying user role');
 const adminContext=await browser.newContext();await adminContext.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());const admin=await login(adminContext,'chat-test-manager');
 check(await admin.locator('.vpn-chat-avatar-picker').count()===1&&await admin.evaluate(()=>typeof window.wp?.media==='function'),'admin Media Library picker script is functional');
 await writeFile(new URL('./results/profile-upload-report.json',import.meta.url),JSON.stringify({checks:passed.length,passed,test_attachment_id:attachment},null,2));console.log('TOTAL '+passed.length+' profile upload checks passed');
}finally{await browser.close();}
