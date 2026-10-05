import { createRequire } from 'node:module';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
const require=createRequire(import.meta.url);const {chromium}=require(process.env.PLAYWRIGHT_PATH||'C:/Users/ADMIN/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const browser=await chromium.launch({headless:true,executablePath:process.env.CHROME_PATH||'C:/Program Files/Google/Chrome/Application/chrome.exe'});const base='http://127.0.0.1:8091';const passed=[];const check=(value,name)=>{if(!value)throw new Error(name);passed.push(name);console.log('PASS '+name);};
const output=new URL('./results/',import.meta.url).pathname.replace(/^\/(\w:)/,'$1');await mkdir(output,{recursive:true});
try{
  for(const [label,width,height]of [['desktop',1366,900],['mobile',390,844]]){
    const context=await browser.newContext({viewport:{width,height},isMobile:label==='mobile'});const page=await context.newPage();const errors=[],calls=[];page.on('pageerror',e=>errors.push(e.message));page.on('request',r=>calls.push(decodeURIComponent(r.url())));
    await page.route('**/*',route=>{const host=new URL(route.request().url()).hostname;if(host==='challenges.cloudflare.com')return route.fulfill({contentType:'application/javascript',body:`window.turnstile={render:(el,opts)=>{opts.callback('valid-theme-'+crypto.randomUUID());return 1;},reset:()=>{}};`});if(host!=='127.0.0.1')return route.abort();return route.continue();});
    await page.goto(base);await page.locator('#vpn-chat-launch').waitFor();check(await page.locator('.main-nav, .site-header nav, nav[aria-label]').count()>0,label+' theme navigation renders');
    check(await page.locator('a[href="https://wa.me/84933102653"]').count()>0,label+' existing WhatsApp link remains');
    check(await page.locator('meta[name="description"]').count()>0,label+' SEO description remains');
    check(await page.locator('script[type="application/ld+json"]').count()>0,label+' SEO schema remains');
    if(label==='mobile'){
      const launcher=await page.locator('#vpn-chat-launch').boundingBox();const cta=await page.locator('.mobile-conversion-bar').boundingBox();check(!cta || launcher.y+launcher.height<=cta.y,'mobile launcher avoids conversion CTA');
    }
    await page.locator('#vpn-chat-launch').click();await page.locator('#vpn-chat-name').waitFor();await page.locator('#vpn-chat-first').fill('Theme integration draft');await page.keyboard.press('Escape');check(await page.locator('.vpn-chat-panel').isHidden(),label+' scoped widget closes on theme');
    check(errors.length===0,label+' theme/chat have no uncaught JS errors');
    await page.screenshot({path:path.join(output,`theme-${label}.png`),fullPage:false});
    await page.goto(base+'/contact/');check(await page.locator('form[action*="admin-post.php"] input[name="action"][value="custom_box_quote_form"]').count()>0,label+' original quote form remains');
    await context.close();
  }
  const response=await fetch(base+'/wp-json/custom-box/v1/search-suggestions?q=box');check(response.status===200,'custom-box REST namespace remains available');
  await writeFile(path.join(output,'theme-report.json'),JSON.stringify({checks:passed.length,passed},null,2));console.log('TOTAL '+passed.length+' theme checks passed');
}finally{await browser.close();}
