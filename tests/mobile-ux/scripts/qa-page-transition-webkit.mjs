import { devices, webkit } from '@playwright/test';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import { repoRoot } from '../site-fixtures.mjs';

const baseURL = 'http://localhost/hopgiayvpn';
const outputDir = path.join(repoRoot, 'artifacts/page-transition-qa');
const css = await fs.readFile(path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/css/page-transition.css'), 'utf8');
const js = await fs.readFile(path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/js/page-transition.js'), 'utf8');
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
const report = { result: 'PASS', engine: 'WebKit', initialLoad: {}, navigation: {}, layouts: [], accessibility: {} };

await fs.mkdir(outputDir, { recursive: true });

async function blockExternal(context) {
    await context.route('**/*', route => new URL(route.request().url()).hostname === 'localhost'
        ? route.continue()
        : route.abort());
}

async function getMarkup(browser) {
    const context = await browser.newContext({ ...devices['iPhone 13'] });
    await blockExternal(context);
    const page = await context.newPage();
    await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForSelector('[data-page-transition]', { state: 'attached' });
    const markup = await page.locator('[data-page-transition]').evaluate(element => element.outerHTML);
    await context.close();
    return markup;
}

function fixture(markup) {
    return `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><style>html,body{margin:0;min-height:200vh}${css}</style></head><body>
        <a id="next" href="${baseURL}/qa-webkit-next/">Next page</a>
        <a id="hash" href="#section">Hash</a>
        <a id="ajax" href="${baseURL}/qa-webkit-next/">AJAX</a>
        <div id="section">Section</div>${markup}<script>${js}</script>
        <script>document.getElementById('ajax').addEventListener('click', event => event.preventDefault());</script>
    </body></html>`;
}

async function testInitialLoad(browser) {
    const context = await browser.newContext({ ...devices['iPhone 13'] });
    await blockExternal(context);
    await context.route('**/assets/js/page-transition.js*', async route => {
        await delay(600);
        await route.continue();
    });
    const page = await context.newPage();
    const navigation = page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForFunction(() => document.documentElement.classList.contains('vpn-initial-page-loading'));
    const visibleAt = Date.now();
    const visible = await page.locator('[data-page-transition]').evaluate(overlay => ({
        display: getComputedStyle(overlay).display,
        opacity: getComputedStyle(overlay).opacity,
        loadingText: overlay.querySelector('[data-transition-status]').textContent.trim(),
    }));
    assert.equal(visible.display, 'grid', 'WebKit displays the delayed initial loader');
    assert.equal(visible.opacity, '1', 'WebKit initial loader is opaque');
    assert.equal(visible.loadingText, 'LOADING...', 'Initial loading copy exists before deferred JavaScript runs');
    await navigation;
    await page.waitForFunction(() => {
        const root = document.documentElement;
        const overlay = document.querySelector('[data-page-transition]');
        return !root.classList.contains('vpn-initial-page-loading')
            && !root.classList.contains('vpn-initial-page-loading-pending')
            && getComputedStyle(overlay).display === 'none';
    });
    const visibleDuration = Date.now() - visibleAt;
    assert.ok(visibleDuration >= 500, `WebKit initial loader remains perceptible (${visibleDuration}ms)`);

    await page.reload({ waitUntil: 'domcontentloaded' });
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Initial loader runs once per Safari tab');
    await context.close();

    const failSafeContext = await browser.newContext({ ...devices['iPhone 13'] });
    await blockExternal(failSafeContext);
    await failSafeContext.route('**/assets/js/page-transition.js*', route => route.abort());
    const failSafePage = await failSafeContext.newPage();
    await failSafePage.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await failSafePage.waitForTimeout(350);
    assert.ok(await failSafePage.evaluate(() => document.documentElement.classList.contains('vpn-initial-page-loading')), 'WebKit no-JavaScript fallback becomes visible');
    await failSafePage.waitForTimeout(1600);
    assert.ok(await failSafePage.evaluate(() => !document.documentElement.classList.contains('vpn-initial-page-loading')), 'WebKit no-JavaScript fallback releases the page');
    await failSafeContext.close();

    return { visibleDuration, minimumVisibleTime: 'PASS', oncePerSession: 'PASS', javascriptFailureSafety: 'PASS' };
}

async function testNavigationAndLayout(browser, markup) {
    const context = await browser.newContext({ ...devices['iPhone 13'] });
    await blockExternal(context);
    const page = await context.newPage();
    const runtimeErrors = [];
    page.on('pageerror', error => runtimeErrors.push(error.message));
    await page.route('**/qa-webkit/', route => route.fulfill({ contentType: 'text/html', body: fixture(markup) }));
    await page.goto(`${baseURL}/qa-webkit/`, { waitUntil: 'load' });

    await page.locator('#hash').click();
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Hash navigation bypasses loader in WebKit');
    await page.locator('#ajax').click();
    await page.waitForTimeout(80);
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Cancelled AJAX navigation bypasses loader in WebKit');

    let requestAt = 0;
    await page.route('**/qa-webkit-next/', async route => {
        requestAt = Date.now();
        await delay(900);
        await route.fulfill({ contentType: 'text/html', body: '<h1>WebKit arrived</h1>' });
    });

    let activeAt = 0;
    let paintFrameAt = 0;
    const activePromise = new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('WebKit never exposed the active loader')), 2000);
        const listener = message => {
            if (!message.text().startsWith('qa-webkit-active:')) return;
            activeAt = Date.now();
            clearTimeout(timer);
            page.off('console', listener);
            resolve(message.text());
        };
        page.on('console', listener);
    });
    const paintPromise = new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error('WebKit never reached a loader paint frame')), 2000);
        const listener = message => {
            if (message.text() !== 'qa-webkit-paint-frame') return;
            paintFrameAt = Date.now();
            clearTimeout(timer);
            page.off('console', listener);
            resolve(true);
        };
        page.on('console', listener);
    });
    await page.evaluate(() => {
        const overlay = document.querySelector('[data-page-transition]');
        new MutationObserver(() => {
            if (!overlay.hidden && overlay.classList.contains('is-active')) {
                console.log(`qa-webkit-active:${getComputedStyle(overlay).opacity}`);
            }
        }).observe(overlay, { attributes: true });
        window.addEventListener('click', event => {
            if (event.target.closest('#next')) {
                requestAnimationFrame(() => console.log('qa-webkit-paint-frame'));
            }
        });
    });
    await page.locator('#next').click({ noWaitAfter: true });
    const activeState = await activePromise;
    await paintPromise;
    assert.equal(activeState, 'qa-webkit-active:1', 'WebKit loader is fully opaque before navigation');
    await page.waitForURL('**/qa-webkit-next/');
    assert.ok(requestAt >= paintFrameAt && paintFrameAt >= activeAt, 'WebKit receives a loader frame before requesting the next document');
    await page.goBack({ waitUntil: 'domcontentloaded' });
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Safari back navigation restores a usable page');
    await page.unroute('**/qa-webkit-next/');

    const viewports = [
        { width: 320, height: 568 },
        { width: 375, height: 667 },
        { width: 390, height: 664 },
        { width: 844, height: 390 },
        { width: 768, height: 1024 },
        { width: 1366, height: 768 },
    ];
    for (const viewport of viewports) {
        await page.setViewportSize(viewport);
        const measurement = await page.locator('[data-page-transition]').evaluate((overlay, expected) => {
            overlay.hidden = false;
            overlay.classList.add('is-active');
            overlay.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('vpn-page-transition-active');
            document.body.classList.add('vpn-page-transitioning');
            const rect = overlay.getBoundingClientRect();
            const content = overlay.querySelector('.vpn-page-transition__content').getBoundingClientRect();
            return {
                expected,
                overlay: { width: rect.width, height: rect.height },
                scroll: { width: overlay.scrollWidth, height: overlay.scrollHeight },
                content: { left: content.left, right: content.right, top: content.top, bottom: content.bottom },
                logoWidth: overlay.querySelector('img').naturalWidth,
            };
        }, viewport);
        assert.equal(measurement.overlay.width, viewport.width, `WebKit overlay width at ${viewport.width}x${viewport.height}`);
        assert.equal(measurement.overlay.height, viewport.height, `WebKit overlay height at ${viewport.width}x${viewport.height}`);
        assert.equal(measurement.scroll.width, viewport.width, 'No WebKit horizontal overflow');
        assert.equal(measurement.scroll.height, viewport.height, 'No WebKit vertical overflow');
        assert.ok(measurement.content.left >= -1 && measurement.content.right <= viewport.width + 1, 'WebKit content stays horizontally visible');
        assert.ok(measurement.content.top >= -1 && measurement.content.bottom <= viewport.height + 1, 'WebKit content stays vertically visible');
        assert.ok(measurement.logoWidth >= 700, 'WebKit loads the high-resolution WebP logo');
        if ((viewport.width === 390 && viewport.height === 664) || (viewport.width === 844 && viewport.height === 390)) {
            await page.screenshot({ path: path.join(outputDir, `webkit-${viewport.width}x${viewport.height}.png`) });
        }
        report.layouts.push(`${viewport.width}x${viewport.height}`);
    }

    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.waitForTimeout(250);
    const runningAnimations = await page.locator('[data-page-transition]').evaluate(overlay => overlay.getAnimations({ subtree: true }).filter(animation => animation.playState === 'running').length);
    assert.equal(runningAnimations, 0, 'WebKit reduced-motion mode disables animations');
    assert.deepEqual(runtimeErrors, [], 'WebKit produced no uncaught JavaScript errors');
    await context.close();

    return {
        internalNavigation: 'PASS',
        paintedBeforeRequest: 'PASS',
        exclusions: 'PASS',
        backNavigation: 'PASS',
        runtimeErrors: 'PASS',
    };
}

const browser = await webkit.launch({ headless: true });
try {
    const markup = await getMarkup(browser);
    report.initialLoad = await testInitialLoad(browser);
    report.navigation = await testNavigationAndLayout(browser, markup);
    report.accessibility = { reducedMotion: 'PASS', responsiveLayout: 'PASS' };
    await fs.writeFile(path.join(outputDir, 'webkit-report.json'), `${JSON.stringify(report, null, 2)}\n`);
    console.log(JSON.stringify(report, null, 2));
} finally {
    await browser.close();
}
