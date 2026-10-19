// Actual PHP markup and production assets, isolated inbox responses; no real leads or mail.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const {chromium} = require('C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const root = path.resolve(__dirname, '..');
const out = path.join(root, 'outputs/email-popup-minimal-20261019');
fs.mkdirSync(out,{recursive:true});
const markup = execFileSync('C:/xampp/php/php.exe', [path.join(__dirname,'email-popup-recaptcha-fixture.php'),'render'], {encoding:'utf8'})
 .replace('<head>', '<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>body{font-family:Arial,sans-serif;background:#f5f8fa;color:#17313b;padding:24px}</style><link rel="stylesheet" href="/popup.css">');
const checks=[], views=[];
function check(label,value){assert.ok(value,label);checks.push(label);}
async function bounds(page,label){
 const g=await page.locator('#vpn-email-popup').evaluate(d=>{const r=d.getBoundingClientRect();return{left:r.left,right:r.right,top:r.top,bottom:r.bottom,width:d.clientWidth,scrollWidth:d.scrollWidth,scrollHeight:d.scrollHeight,height:d.clientHeight,viewportWidth:innerWidth,viewportHeight:innerHeight};});
 check(label+': no horizontal overflow',g.scrollWidth<=g.width+1);
 check(label+': within viewport',g.left>=0&&g.right<=g.viewportWidth+1&&g.top>=0&&g.bottom<=g.viewportHeight+1);
 return g;
}
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 try{
  for(const [width,height] of [[1440,1000],[600,900],[430,932],[390,844],[360,740],[320,568]]){
   const context=await browser.newContext({viewport:{width,height},reducedMotion:'reduce'});
   let saves=0, fail=false, external=0;
   await context.route('**/*',route=>{
    const u=new URL(route.request().url());
    if(u.origin!=='https://popup.test'){external++;return route.abort();}
    if(u.pathname==='/popup.js'||u.pathname==='/popup.css')return route.fulfill({contentType:u.pathname.endsWith('.js')?'application/javascript':'text/css',body:fs.readFileSync(path.join(root,'wp-content/mu-plugins/vpn-email-quote-popup',u.pathname.slice(1)))});
    if(u.pathname.endsWith('packaging.webp'))return route.fulfill({contentType:'image/webp',body:fs.readFileSync(path.join(root,'wp-content/mu-plugins/vpn-email-quote-popup/packaging.webp'))});
    if(u.pathname.endsWith('packaging-banner-20261019.webp'))return route.fulfill({contentType:'image/webp',body:fs.readFileSync(path.join(root,'wp-content/mu-plugins/vpn-email-quote-popup/packaging-banner-20261019.webp'))});
    if(u.pathname.endsWith('/bootstrap'))return route.fulfill({json:{csrf:'mobile-ui-csrf'}});
    if(u.pathname.endsWith('/email-quote')){saves++;return route.fulfill(fail?{status:503,json:{code:'storage_unavailable'}}:{json:{saved:true,id:'12345678-1234-4234-8234-123456789abc',receipt:'vpn-inbox-12345678-1234-4234-8234-123456789abc-1'}});}
    return route.fulfill({contentType:'text/html',body:markup});
   });
   const page=await context.newPage();
   try{
    await page.goto('https://popup.test/');await page.locator('#vpn-email-popup[open]').waitFor();
    const label=width+'x'+height;
    const initial=await bounds(page,label+' initial');
    check(label+': photo visible',await page.locator('.vep-photo').isVisible());
    await page.locator('.vep-photo img').evaluate(img=>img.decode());
    check(label+': image loaded',await page.locator('.vep-photo img').evaluate(img=>img.naturalWidth>0));
    if(width<=600){
     check(label+': landscape photo selected',await page.locator('.vep-photo img').evaluate(i=>i.currentSrc.endsWith('packaging-banner-20261019.webp')));
     check(label+': no decorative headline',await page.locator('.vep-photo-caption').count()===0);
     const close=await page.locator('.vep-close').boundingBox();check(label+': 44px close target',close.width>=44&&close.height>=44);
     check(label+': discreet close glyph',await page.locator('.vep-close').evaluate(i=>parseFloat(getComputedStyle(i).fontSize)<=22&&getComputedStyle(i).backgroundColor==='rgba(0, 0, 0, 0)'));
     check(label+': input avoids iOS text zoom',await page.locator('#vep-email').evaluate(i=>parseFloat(getComputedStyle(i).fontSize)>=16));
    }else check(label+': desktop photo column retained',await page.locator('.vep-photo').evaluate(i=>i.getBoundingClientRect().height>=465&&i.getBoundingClientRect().width>250));
    await page.screenshot({path:path.join(out,'entry-'+width+'.png')});
    check(label+': email-only form',await page.locator('.vep-form input:visible').count()===1&&await page.locator('.vep-details,.vep-form textarea').count()===0);
    check(label+': no redundant dismiss link',!(await page.locator('.vep-not-now').isVisible()));
    check(label+': heading receives initial focus',await page.evaluate(()=>document.activeElement.id==='vep-title'));
    const email=width===320?'packaging.orders.'+'a'.repeat(40)+'@international-brand-example.invalid':'buyer@example.invalid';
    await page.locator('#vep-email').fill(email);
    if(width===390){
     await page.setViewportSize({width:390,height:420});
     await page.locator('#vep-email').focus();
     check(label+': compact input mode hides decorative header',!(await page.locator('.vep-photo').isVisible()));
     await bounds(page,label+' compact input viewport');
     await page.locator('.vep-submit').scrollIntoViewIfNeeded();
     check(label+': submit reachable in compact viewport',await page.locator('.vep-submit').isVisible());
     await page.screenshot({path:path.join(out,'compact-input-390.png')});
     await page.setViewportSize({width,height});
     fail=true;await page.locator('.vep-submit').click();
     await page.waitForFunction(()=>!document.querySelector('.vep-submit').disabled&&!document.querySelector('.vep-status').hidden);
     check(label+': failed save retains email',await page.locator('#vep-email').inputValue()===email);
     await bounds(page,label+' retry');
     fail=false;
    }
    await page.locator('.vep-submit').click();await page.locator('.vep-saved').waitFor();
    check(label+': correct receipt email',await page.locator('.vep-success-email strong').innerText()===email);
    check(label+': acknowledgement heading',await page.locator('#vep-title').innerText()==="We've received your email.");
    const success=await bounds(page,label+' saved');
    await page.locator('#vpn-email-popup').evaluate(d=>{d.scrollTop=0;});
    await page.screenshot({path:path.join(out,'success-'+width+'.png')});
    await page.locator('.vep-not-now').click();
    check(label+': close still works',!(await page.locator('#vpn-email-popup').evaluate(d=>d.open)));
    check(label+': no duplicate save',saves===(width===390?2:1));
    check(label+': no external requests',external===0);
    views.push({width,height,initial,success});
   }catch(error){await page.screenshot({path:path.join(out,'failure-'+width+'.png')});throw error;}
   finally{await context.close();}
  }
  fs.writeFileSync(path.join(out,'review.json'),JSON.stringify({passed:checks.length,checks,views},null,2));
  console.log(JSON.stringify({passed:checks.length,views},null,2));
 }finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
