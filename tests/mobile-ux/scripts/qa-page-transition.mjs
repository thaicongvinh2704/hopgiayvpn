import { chromium } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';
import assert from 'node:assert/strict';
import { repoRoot, chromeExecutable } from '../site-fixtures.mjs';

const output = path.join(repoRoot, 'artifacts/page-transition-print-studio');
await fs.mkdir(output, { recursive: true });
const browser = await chromium.launch({ executablePath: chromeExecutable, headless: true });
try {
    const context = await browser.newContext();
    await context.route('**/*', route => new URL(route.request().url()).hostname === 'localhost'
        ? route.continue() : route.abort());
    const page = await context.newPage();
    await page.goto('http://localhost/hopgiayvpn/', { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForSelector('[data-page-transition]', { state: 'attached' });
    await page.waitForFunction(() => Array.from(document.scripts).some(s => s.src.includes('page-transition.js')));
    await page.evaluate(() => sessionStorage.removeItem('vpnInitialHomeLoaderSeenV1'));
    let delayInitialScript = true;
    await page.route('**/assets/js/page-transition.js*', async route => {
        if (delayInitialScript) {
            delayInitialScript = false;
            await new Promise(resolve => setTimeout(resolve, 450));
        }
        await route.continue();
    });
    const initialReload = page.reload({waitUntil:'domcontentloaded'});
    await page.waitForFunction(() => document.documentElement.classList.contains('vpn-initial-page-loading'));
    await page.screenshot({path:path.join(output,'initial-home-visit.png')});
    await initialReload;
    await page.waitForFunction(() => document.querySelector('[data-page-transition]').hidden, null, {timeout:15000});
    await page.reload({waitUntil:'domcontentloaded'});
    assert.ok(await page.locator('[data-page-transition]').evaluate(o => o.hidden), 'Initial home loader runs once per tab session');
    const markup = await page.locator('[data-page-transition]').evaluate(el => el.outerHTML);
    assert.ok(markup.includes('data-nosnippet'), 'Loader copy is excluded from search snippets');
    assert.ok(!markup.includes('fetchpriority="high"'), 'Loader logo does not compete at high priority');
    await page.setViewportSize({width:1440,height:900});
    await page.locator('[data-page-transition]').evaluate(o => {
        o.hidden=false; o.classList.add('is-active');
        const status=o.querySelector('[data-transition-status]'); status.textContent=status.dataset.loadingText;
    });
    await page.locator('.vpn-page-transition__logo').evaluate(image => image.decode());
    await page.waitForTimeout(200);
    await page.screenshot({path:path.join(output,'actual-wordpress-desktop.png')});
    const css = await fs.readFile(path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/css/page-transition.css'), 'utf8');
    const js = await fs.readFile(path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/js/page-transition.js'), 'utf8');
    const fixture = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0} ${css}</style></head><body>
        <a id="next" href="/hopgiayvpn/qa-next/">Next page</a><a id="hash" href="#section">Section</a>
        <a id="ajax" href="/hopgiayvpn/qa-next/">Gallery</a><a id="file" href="/catalog.pdf">Download</a>
        ${markup}<script>${js}</script><script>document.getElementById('ajax').addEventListener('click', e => e.preventDefault());</script></body></html>`;
    await page.route('**/qa-transition/', route => route.fulfill({ contentType: 'text/html', body: fixture }));
    await page.goto('http://localhost/hopgiayvpn/qa-transition/');
    await page.locator('.vpn-page-transition__logo').evaluate(async image => image.decode());
    const measurements = [];
    for (const [width, height] of [[1920,900],[1440,900],[1366,768],[375,812],[320,568],[844,390]]) {
        await page.setViewportSize({ width, height });
        await page.evaluate(() => {
            const o = document.querySelector('[data-page-transition]');
            o.hidden = false; o.classList.add('is-active'); o.setAttribute('aria-hidden', 'false');
            const status=o.querySelector('[data-transition-status]'); status.textContent=status.dataset.loadingText;
        });
        await page.waitForTimeout(250);
        const dimensions = await page.locator('[data-page-transition]').evaluate(o => ({
            width: o.clientWidth, scrollWidth: o.scrollWidth, height: o.clientHeight, scrollHeight: o.scrollHeight,
            logoLoaded: o.querySelector('img').naturalWidth > 0,
            logoWidth: o.querySelector('img').naturalWidth
        }));
        assert.equal(dimensions.width, dimensions.scrollWidth, 'No horizontal overflow');
        assert.ok(dimensions.logoLoaded);
        assert.ok(dimensions.logoWidth >= 700, 'Loader uses the crisp high-resolution logo');
        measurements.push({width, height, ...dimensions});
        await page.screenshot({ path: path.join(output, `${width}x${height}.png`) });
    }
    await page.emulateMedia({ reducedMotion: 'reduce' });
    const animations = await page.locator('[data-page-transition]').evaluate(o => o.getAnimations({subtree:true}).filter(a => a.playState === 'running').length);
    assert.equal(animations, 0, 'Reduced motion disables animations');
    await page.setViewportSize({width: 375, height: 812});
    await page.addStyleTag({content: '.vpn-page-transition__title{font-size:52px}.vpn-page-transition__loading{font-size:24px}'});
    assert.ok(await page.locator('[data-page-transition]').evaluate(o => o.scrollWidth === o.clientWidth), 'Enlarged text remains in viewport');
    await page.goto('http://localhost/hopgiayvpn/qa-transition/');
    await page.locator('#hash').click();
    assert.ok(await page.locator('[data-page-transition]').evaluate(o => o.hidden), 'Hash link bypasses loader');
    await page.locator('#ajax').click();
    await page.waitForTimeout(80);
    assert.ok(await page.locator('[data-page-transition]').evaluate(o => o.hidden), 'Cancelled gallery navigation bypasses loader');
    let requested = false;
    const states = [];
    page.on('console', message => {
        if (message.text().startsWith('qa-overlay:')) states.push(message.text());
    });
    await page.evaluate(() => {
        const overlay = document.querySelector('[data-page-transition]');
        new MutationObserver(() => {
            const active = !overlay.hidden && overlay.classList.contains('is-active');
            const rootOverflow = getComputedStyle(document.documentElement).overflow;
            const layer = getComputedStyle(overlay).zIndex;
            console.log(`qa-overlay:${active}|${rootOverflow}|${layer}`);
        }).observe(overlay, {attributes:true});
    });
    await page.route('**/qa-next/', async route => {
        requested = true;
        await new Promise(resolve => setTimeout(resolve, 1200));
        await route.fulfill({contentType:'text/html',body:'<h1>Arrived</h1>'});
    });
    await page.locator('#next').click({noWaitAfter:true});
    await page.waitForURL('**/qa-next/');
    assert.ok(requested, 'Native navigation requested the next document');
    const activeState = states.find(state => state.startsWith('qa-overlay:true|')) || '';
    const [, rootOverflow, layer] = activeState.split('|');
    assert.ok(activeState, 'Loader is visible during slow native navigation');
    assert.equal(rootOverflow, 'hidden', 'Loader locks background scrolling');
    assert.ok(Number.parseInt(layer, 10) >= 2147483646, 'Loader stays above third-party widgets');
    await page.goBack();
    assert.ok(await page.locator('[data-page-transition]').evaluate(o => o.hidden), 'Back restores a usable page');
    await page.evaluate(() => { const o=document.querySelector('[data-page-transition]'); o.hidden=false; o.classList.add('is-active'); });
    await page.keyboard.press('Escape');
    assert.ok(await page.locator('[data-page-transition]').evaluate(o => o.hidden), 'Escape recovers the underlying page');
    console.log(JSON.stringify({result:'PASS', measurements, reducedMotion:'PASS', largeText:'PASS', nativeNavigation:'PASS', scrollLock:'PASS', topLayer:'PASS', anchor:'PASS', cancelledNavigation:'PASS', escape:'PASS', back:'PASS'},null,2));
} finally {
    await browser.close();
}
