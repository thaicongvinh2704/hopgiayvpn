import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';
fs.mkdirSync('tmp/christmas-20261001',{recursive:true});
const products=JSON.parse(fs.readFileSync('wp-content/themes/custom-box-theme/inc/product-content/christmas-gift-boxes-20261001/products.json','utf8'));
const browser=await chromium.launch({headless:true,channel:"chrome"});
const page=await browser.newPage({viewport:{width:390,height:844}});
const results=[];
for(const p of products){
 const url='http://localhost/hopgiayvpn/product/'+p.slug+'/';
 const res=await page.goto(url,{waitUntil:'networkidle'});
 await page.locator('.product-inline-figure img, .christmas-factory-proof-figure img').evaluateAll(imgs=>imgs.forEach(i=>i.loading='eager'));
 await page.waitForFunction(()=>[...document.querySelectorAll('.product-inline-figure img, .christmas-factory-proof-figure img')].every(i=>i.complete&&i.naturalWidth>0),{timeout:10000});
 const r=await page.evaluate(()=>({title:document.title,h1:[...document.querySelectorAll('h1')].map(e=>e.textContent.trim()),description:document.querySelector('meta[name="description"]')?.content,canonical:document.querySelector('link[rel="canonical"]')?.href,robots:document.querySelector('meta[name="robots"]')?.content,overflow:document.documentElement.scrollWidth>innerWidth,schema:[...document.querySelectorAll('script[type="application/ld+json"]')].map(s=>JSON.parse(s.textContent)),inline:[...document.querySelectorAll('.product-inline-figure img, .christmas-factory-proof-figure img')].map(i=>({src:i.src,alt:i.alt,loaded:i.complete&&i.naturalWidth>0})),links:[...document.querySelectorAll('.product-detail-description a')].map(a=>a.href).filter(u=>u.includes('/product/custom-')||u.includes('/products/christmas-packaging/')||u.includes('/christmas-packaging-ideas/')||u.includes('/contact/')||u.includes('/about/')||u.includes('/about-us/'))}));
 results.push({slug:p.slug,status:res.status(),...r});
 if(p===products[0]){await page.screenshot({path:'tmp/christmas-20261001/mobile-top.png'}); await page.locator('.product-inline-figure').first().scrollIntoViewIfNeeded(); await page.screenshot({path:'tmp/christmas-20261001/mobile-description.png'}); await page.locator('.christmas-factory-proof-figure').first().scrollIntoViewIfNeeded(); await page.screenshot({path:'tmp/christmas-20261001/mobile-factory.png'});}
}
await page.setViewportSize({width:1440,height:1000});
await page.goto('http://localhost/hopgiayvpn/product/'+products[0].slug+'/',{waitUntil:'networkidle'});
await page.screenshot({path:'tmp/christmas-20261001/desktop.png',fullPage:false});
const unique=[...new Set(results.flatMap(r=>r.links))];
const links=[];
await Promise.all(unique.map(async url=>{try{const res=await page.request.get(url,{timeout:15000});links.push({url,status:res.status()});}catch(e){links.push({url,error:e.message});}}));
for(const r of results){assert.equal(r.status,200);assert.equal(r.h1.length,1);assert.equal(r.overflow,false);assert.equal(r.inline.length,5);assert.ok(r.inline.every(i=>i.loaded&&i.alt));assert.ok(r.description);const graph=r.schema.flatMap(s=>s['@graph']??[s]);assert.equal(graph.filter(n=>n['@type']==='Product').length,1);}
assert.ok(links.every(l=>l.status===200));
fs.writeFileSync('tmp/christmas-20261001/frontend-qa.json',JSON.stringify({results,links},null,2));
console.log(JSON.stringify({products:results.map(r=>({slug:r.slug,status:r.status,h1:r.h1.length,description:!!r.description,canonical:r.canonical,robots:r.robots,overflow:r.overflow,inline:r.inline.length,brokenImages:r.inline.filter(i=>!i.loaded).length,schema:r.schema.length})),links},null,2));
await browser.close();
