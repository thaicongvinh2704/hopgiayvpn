import { chromium } from '@playwright/test';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import { repoRoot, chromeExecutable } from '../site-fixtures.mjs';

const baseURL = 'http://localhost/hopgiayvpn';
const outputDir = path.join(repoRoot, 'artifacts/page-transition-qa');
const edgeExecutable = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const cssPath = path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/css/page-transition.css');
const jsPath = path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/js/page-transition.js');
const logoPath = path.join(repoRoot, 'wp-content/themes/custom-box-theme/assets/images/logo-hop-giay-vpn-loader.webp');

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
const report = { result: 'PASS', browsers: {}, initialLoad: {}, behavior: {}, assets: {}, accessibility: {} };

await fs.mkdir(outputDir, { recursive: true });

const [css, js, cssStat, jsStat, logoStat] = await Promise.all([
    fs.readFile(cssPath, 'utf8'),
    fs.readFile(jsPath, 'utf8'),
    fs.stat(cssPath),
    fs.stat(jsPath),
    fs.stat(logoPath),
]);

report.assets = {
    cssBytes: cssStat.size,
    jsBytes: jsStat.size,
    logoBytes: logoStat.size,
    totalBytes: cssStat.size + jsStat.size + logoStat.size,
};
assert.ok(report.assets.totalBytes < 60000, 'Loader assets stay lightweight (under 60 KB before transfer compression)');
assert.match(css, /prefers-reduced-motion:\s*reduce/, 'Reduced-motion CSS is present');
const keyframeBlocks = [...css.matchAll(/@keyframes\s+[^{]+\{(?:[^{}]|\{[^{}]*\})*\}/g)].map(match => match[0]);
assert.ok(keyframeBlocks.length > 0, 'Loader animation keyframes exist');
for (const keyframes of keyframeBlocks) {
    assert.doesNotMatch(keyframes, /(?:top|left|width|height)\s*:/, 'Keyframes avoid layout-triggering properties');
}
assert.match(js, /recoveryTimer\s*=\s*window\.setTimeout\(hideTransition,\s*12000\)/, 'Recovery timeout remains enabled');

async function routeExternalRequests(context) {
    await context.route('**/*', route => {
        const requestURL = new URL(route.request().url());
        return requestURL.hostname === 'localhost' ? route.continue() : route.abort();
    });
}

async function getLiveMarkup(page) {
    await page.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForSelector('[data-page-transition]', { state: 'attached' });
    return page.locator('[data-page-transition]').evaluate(el => el.outerHTML);
}

function buildFixture(markup) {
    return `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><style>html,body{margin:0;min-height:200vh} ${css}</style></head><body>
        <a id="internal" href="${baseURL}/qa-next/">Internal</a>
        <a id="hash" href="#section">Hash</a>
        <a id="same" href="${baseURL}/qa-transition/">Same document</a>
        <a id="external" href="https://example.com/">External</a>
        <a id="blank" href="${baseURL}/qa-next/" target="_blank">New tab</a>
        <a id="download" href="${baseURL}/catalog.pdf" download>Download</a>
        <a id="file" href="${baseURL}/catalog.pdf">PDF</a>
        <a id="admin" href="${baseURL}/wp-admin/">Admin</a>
        <a id="cart" href="${baseURL}/?add-to-cart=1">Cart</a>
        <a id="role" role="button" href="${baseURL}/qa-next/">Role button</a>
        <span data-no-page-transition><a id="bypass" href="${baseURL}/qa-next/">Bypass</a></span>
        <a id="ajax" href="${baseURL}/qa-next/">AJAX cancelled</a>
        <div id="section">Section</div>${markup}<script>${js}</script>
        <script>document.getElementById('ajax').addEventListener('click', event => event.preventDefault());</script>
    </body></html>`;
}

async function installFixture(page, markup) {
    const fixture = buildFixture(markup);
    await page.route('**/qa-transition/', route => route.fulfill({ contentType: 'text/html', body: fixture }));
    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    await page.locator('.vpn-page-transition__logo').evaluate(image => image.decode());
}

async function forceVisible(page, stalled = false) {
    await page.evaluate(({ stalled }) => {
        const overlay = document.querySelector('[data-page-transition]');
        overlay.hidden = false;
        overlay.classList.add('is-active');
        overlay.classList.toggle('is-stalled', stalled);
        overlay.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('vpn-page-transition-active');
        document.body.classList.add('vpn-page-transitioning');
        const status = overlay.querySelector('[data-transition-status]');
        status.textContent = status.dataset.loadingText;
        const dismiss = overlay.querySelector('[data-transition-dismiss]');
        dismiss.hidden = !stalled;
    }, { stalled });
}

async function measureLayout(page, viewport) {
    await page.setViewportSize(viewport);
    await forceVisible(page, true);
    await page.waitForTimeout(80);
    return page.locator('[data-page-transition]').evaluate((overlay, viewport) => {
        const rect = overlay.getBoundingClientRect();
        const content = overlay.querySelector('.vpn-page-transition__content').getBoundingClientRect();
        const logo = overlay.querySelector('img');
        const dismiss = overlay.querySelector('[data-transition-dismiss]').getBoundingClientRect();
        const progress = overlay.querySelector('.vpn-page-transition__progress').getBoundingClientRect();
        return {
            viewport,
            overlay: { width: rect.width, height: rect.height },
            scroll: { width: overlay.scrollWidth, height: overlay.scrollHeight },
            content: { top: content.top, right: content.right, bottom: content.bottom, left: content.left },
            logoNaturalWidth: logo.naturalWidth,
            dismiss: { width: dismiss.width, height: dismiss.height },
            progress: { width: progress.width, height: progress.height },
            rootOverflow: getComputedStyle(document.documentElement).overflow,
            bodyOverflow: getComputedStyle(document.body).overflow,
            zIndex: Number.parseInt(getComputedStyle(overlay).zIndex, 10),
        };
    }, viewport);
}

function assertLayout(m) {
    assert.equal(m.overlay.width, m.viewport.width, `Overlay width at ${m.viewport.width}x${m.viewport.height}`);
    assert.equal(m.overlay.height, m.viewport.height, `Overlay height at ${m.viewport.width}x${m.viewport.height}`);
    assert.equal(m.scroll.width, m.overlay.width, `No horizontal overflow at ${m.viewport.width}x${m.viewport.height}`);
    assert.equal(m.scroll.height, m.overlay.height, `No vertical overflow at ${m.viewport.width}x${m.viewport.height}`);
    assert.ok(m.content.left >= -1 && m.content.right <= m.viewport.width + 1, 'Content stays inside horizontal viewport');
    assert.ok(m.content.top >= -1 && m.content.bottom <= m.viewport.height + 1, 'Content stays inside vertical viewport');
    assert.ok(m.logoNaturalWidth >= 700, 'High-resolution logo loaded');
    assert.ok(m.dismiss.width >= 44 && m.dismiss.height >= 44, 'Dismiss touch target is at least 44x44');
    assert.ok(m.progress.width > 0 && m.progress.height >= 13, 'Progress indicator remains visible');
    assert.equal(m.rootOverflow, 'hidden', 'Root scrolling is locked');
    assert.equal(m.bodyOverflow, 'hidden', 'Body scrolling is locked');
    assert.ok(m.zIndex >= 2147483646, 'Overlay uses the top document layer');
}

async function testInitialHomeLoad(browser) {
    const delayedContext = await browser.newContext();
    await routeExternalRequests(delayedContext);
    await delayedContext.route('**/assets/js/page-transition.js*', async route => {
        await delay(600);
        await route.continue();
    });
    const delayedPage = await delayedContext.newPage();
    const navigation = delayedPage.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await delayedPage.waitForFunction(() => document.documentElement.classList.contains('vpn-initial-page-loading'));
    const shownState = await delayedPage.locator('[data-page-transition]').evaluate(overlay => ({
        display: getComputedStyle(overlay).display,
        opacity: getComputedStyle(overlay).opacity,
        ariaHidden: overlay.getAttribute('aria-hidden'),
    }));
    await navigation;
    await delayedPage.waitForFunction(() => document.querySelector('[data-page-transition]').hidden);
    await delayedPage.reload({ waitUntil: 'domcontentloaded' });
    const repeated = await delayedPage.locator('[data-page-transition]').evaluate(overlay => !overlay.hidden);
    assert.equal(shownState.display, 'grid', 'Delayed first visit displays the loader');
    assert.equal(shownState.opacity, '1', 'Delayed first visit loader is visible');
    assert.equal(repeated, false, 'Initial loader runs once per tab session');
    await delayedContext.close();

    const failSafeContext = await browser.newContext();
    await routeExternalRequests(failSafeContext);
    await failSafeContext.route('**/assets/js/page-transition.js*', route => route.abort());
    const failSafePage = await failSafeContext.newPage();
    await failSafePage.goto(`${baseURL}/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await failSafePage.waitForTimeout(350);
    assert.ok(await failSafePage.evaluate(() => document.documentElement.classList.contains('vpn-initial-page-loading')), 'Fallback shows while loader JavaScript is unavailable');
    await failSafePage.waitForTimeout(1350);
    assert.ok(await failSafePage.evaluate(() => !document.documentElement.classList.contains('vpn-initial-page-loading')), 'Inline hard limit releases a failed initial loader');
    await failSafeContext.close();

    const innerContext = await browser.newContext();
    await routeExternalRequests(innerContext);
    const innerPage = await innerContext.newPage();
    await innerPage.goto(`${baseURL}/blog/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    const innerInitialState = await innerPage.evaluate(() => document.documentElement.className.includes('vpn-initial-page-loading'));
    assert.equal(innerInitialState, false, 'Direct visits to inner pages do not show the first-home loader');
    await innerContext.close();

    return { delayedFirstVisit: 'PASS', oncePerSession: 'PASS', javascriptFailureSafety: 'PASS', innerPageDirectVisit: 'PASS' };
}

async function testLinkBehavior(page) {
    await page.route('**/qa-next/', route => route.abort('aborted'));
    await page.route('**/catalog.pdf', route => route.abort('aborted'));
    await page.route('**/wp-admin/**', route => route.abort('aborted'));
    await page.route('**/?add-to-cart=1', route => route.abort('aborted'));
    for (const selector of ['#hash', '#same', '#blank', '#download', '#file', '#admin', '#cart', '#role', '#bypass', '#ajax']) {
        await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
        const popupPromise = selector === '#blank' ? page.waitForEvent('popup', { timeout: 500 }).catch(() => null) : null;
        await page.locator(selector).click({ noWaitAfter: true });
        await page.waitForTimeout(80);
        assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), `${selector} bypasses loader`);
        if (popupPromise) {
            const popup = await popupPromise;
            if (popup) await popup.close();
        }
    }

    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    const modifiedPagePromise = page.context().waitForEvent('page', { timeout: 500 }).catch(() => null);
    await page.locator('#internal').click({ modifiers: ['Control'], noWaitAfter: true });
    await page.waitForTimeout(80);
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Ctrl-click bypasses loader');
    const modifiedPage = await modifiedPagePromise;
    if (modifiedPage) await modifiedPage.close();

    await page.unroute('**/qa-next/');
    await page.unroute('**/catalog.pdf');
    await page.unroute('**/wp-admin/**');
    await page.unroute('**/?add-to-cart=1');

    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    let navigationRequested = false;
    await page.route('**/qa-next/', async route => {
        navigationRequested = true;
        await delay(900);
        await route.fulfill({ contentType: 'text/html', body: '<h1>Arrived</h1>' });
    });
    const visibleDuringNavigation = new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('Loader was not observed during internal navigation')), 3000);
        const onConsole = message => {
            if (message.text() !== 'qa-transition-visible') return;
            clearTimeout(timeout);
            page.off('console', onConsole);
            resolve(true);
        };
        page.on('console', onConsole);
    });
    await page.evaluate(() => {
        const overlay = document.querySelector('[data-page-transition]');
        new MutationObserver(() => {
            if (!overlay.hidden && overlay.classList.contains('is-active')) console.log('qa-transition-visible');
        }).observe(overlay, { attributes: true });
    });
    await page.locator('#internal').click({ noWaitAfter: true });
    await visibleDuringNavigation;
    await page.waitForURL('**/qa-next/');
    assert.ok(navigationRequested, 'Internal navigation uses the native browser request');
    await page.unroute('**/qa-next/');
    await page.goBack({ waitUntil: 'domcontentloaded' });
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Back navigation restores a usable page');
    return { exclusions: 'PASS', modifiedClick: 'PASS', internalNavigation: 'PASS', backNavigation: 'PASS' };
}

async function testRecovery(page) {
    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    const stalledVisible = new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('Stalled dismiss button did not appear')), 4700);
        const onConsole = message => {
            if (message.text() !== 'qa-transition-stalled') return;
            clearTimeout(timeout);
            page.off('console', onConsole);
            resolve(true);
        };
        page.on('console', onConsole);
    });
    await page.evaluate(() => {
        const overlay = document.querySelector('[data-page-transition]');
        new MutationObserver(() => {
            const dismiss = overlay.querySelector('[data-transition-dismiss]');
            if (overlay.classList.contains('is-stalled') && !dismiss.hidden) console.log('qa-transition-stalled');
        }).observe(overlay, { attributes: true, subtree: true });
        // Let the loader's window listener approve the original internal URL,
        // then turn the browser's default action into a harmless same-page hash.
        // This accurately exercises stalled/recovery timers without unloading.
        window.addEventListener('click', event => {
            if (event.target.closest('#internal')) event.target.href = '#qa-stalled';
        }, { once: true });
    });
    await page.locator('#internal').click({ noWaitAfter: true });
    await stalledVisible;
    const dismissSize = await page.evaluate(() => {
        const button = document.querySelector('[data-transition-dismiss]');
        const rect = button.getBoundingClientRect();
        return { width: rect.width, height: rect.height, label: button.getAttribute('aria-label') };
    });
    assert.ok(dismissSize.width >= 44 && dismissSize.height >= 44 && dismissSize.label, 'Stalled close button is accessible');
    await page.evaluate(() => document.querySelector('[data-transition-dismiss]').click());
    assert.ok(await page.evaluate(() => document.querySelector('[data-page-transition]').hidden), 'Dismiss releases a stalled page');
    assert.notEqual(await page.evaluate(() => getComputedStyle(document.documentElement).overflow), 'hidden', 'Dismiss unlocks page scrolling');

    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    await forceVisible(page);
    await page.keyboard.press('Escape');
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), 'Escape releases the loader');

    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => {
        window.addEventListener('click', event => {
            if (event.target.closest('#internal')) event.target.href = '#qa-timeout';
        }, { once: true });
    });
    await page.locator('#internal').click({ noWaitAfter: true });
    await page.waitForFunction(() => !document.querySelector('[data-page-transition]').hidden);
    await page.waitForTimeout(12200);
    assert.ok(await page.locator('[data-page-transition]').evaluate(overlay => overlay.hidden), '12-second recovery timeout releases a stranded loader');
    assert.notEqual(await page.evaluate(() => getComputedStyle(document.documentElement).overflow), 'hidden', 'Automatic recovery unlocks page scrolling');
    return { stalledDismiss: 'PASS', automaticRecovery: 'PASS', scrollUnlock: 'PASS', escape: 'PASS' };
}

async function testAccessibility(page) {
    await page.goto(`${baseURL}/qa-transition/`, { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('load');
    await page.waitForTimeout(50);
    await forceVisible(page, true);
    const semantics = await page.locator('[data-page-transition]').evaluate(overlay => {
        const status = overlay.querySelector('[data-transition-status]');
        const dismiss = overlay.querySelector('[data-transition-dismiss]');
        return {
            dataNoSnippet: overlay.hasAttribute('data-nosnippet'),
            ariaHidden: overlay.getAttribute('aria-hidden'),
            statusRole: status.getAttribute('role'),
            ariaLive: status.getAttribute('aria-live'),
            dismissLabel: dismiss.getAttribute('aria-label'),
            logoAlt: overlay.querySelector('img').getAttribute('alt'),
        };
    });
    assert.deepEqual(semantics, {
        dataNoSnippet: true,
        ariaHidden: 'false',
        statusRole: 'status',
        ariaLive: 'polite',
        dismissLabel: 'Hide loading screen',
        logoAlt: 'VPN',
    });

    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.waitForTimeout(50);
    const runningAnimations = await page.locator('[data-page-transition]').evaluate(overlay => overlay.getAnimations({ subtree: true }).filter(animation => animation.playState === 'running').length);
    assert.equal(runningAnimations, 0, 'Reduced motion disables loader animation');
    await page.emulateMedia({ reducedMotion: 'no-preference' });

    await page.setViewportSize({ width: 320, height: 568 });
    await page.addStyleTag({ content: '.vpn-page-transition__title{font-size:52px}.vpn-page-transition__loading{font-size:24px}.vpn-page-transition__tagline{font-size:16px;white-space:normal}' });
    const largeText = await page.locator('.vpn-page-transition__content').evaluate(content => {
        const rect = content.getBoundingClientRect();
        return { top: rect.top, right: rect.right, bottom: rect.bottom, left: rect.left, width: innerWidth, height: innerHeight };
    });
    assert.ok(largeText.left >= -1 && largeText.right <= largeText.width + 1, 'Large text stays within horizontal viewport');
    assert.ok(largeText.top >= -1 && largeText.bottom <= largeText.height + 1, 'Large text stays within vertical viewport');
    return { semantics: 'PASS', reducedMotion: 'PASS', largeText: 'PASS', touchTarget: 'PASS' };
}

async function runBrowser(name, executablePath, viewports, fullSuite) {
    const browser = await chromium.launch({ executablePath, headless: true });
    try {
        if (fullSuite) report.initialLoad = await testInitialHomeLoad(browser);
        const context = await browser.newContext({ hasTouch: true });
        await routeExternalRequests(context);
        const page = await context.newPage();
        const pageErrors = [];
        page.on('pageerror', error => pageErrors.push(error.message));
        const markup = await getLiveMarkup(page);
        assert.match(markup, /data-nosnippet/, 'Loader copy is excluded from search snippets');
        assert.doesNotMatch(markup, /fetchpriority="high"/, 'Loader logo does not compete with LCP at high priority');
        await installFixture(page, markup);

        const measurements = [];
        for (const viewport of viewports) {
            const measurement = await measureLayout(page, viewport);
            assertLayout(measurement);
            measurements.push(measurement);
        }
        await page.setViewportSize({ width: 1366, height: 768 });
        await forceVisible(page);
        await page.screenshot({ path: path.join(outputDir, `${name}-1366x768.png`) });
        await page.setViewportSize({ width: 390, height: 844 });
        await page.screenshot({ path: path.join(outputDir, `${name}-390x844.png`) });

        const browserReport = { layout: 'PASS', viewports: measurements.map(item => `${item.viewport.width}x${item.viewport.height}`) };
        if (fullSuite) {
            report.behavior = { ...(await testLinkBehavior(page)), ...(await testRecovery(page)) };
            report.accessibility = await testAccessibility(page);
            Object.assign(browserReport, { behavior: 'PASS', accessibility: 'PASS' });
        }
        assert.deepEqual(pageErrors, [], `${name} produced no uncaught JavaScript errors`);
        browserReport.runtimeErrors = 'PASS';
        report.browsers[name] = browserReport;
        await context.close();
    } finally {
        await browser.close();
    }
}

const chromeViewports = [
    { width: 320, height: 568 }, { width: 360, height: 640 }, { width: 375, height: 812 },
    { width: 390, height: 844 }, { width: 412, height: 915 }, { width: 568, height: 320 },
    { width: 844, height: 390 }, { width: 915, height: 412 }, { width: 768, height: 1024 },
    { width: 820, height: 1180 }, { width: 1024, height: 768 }, { width: 1280, height: 720 },
    { width: 1366, height: 768 }, { width: 1440, height: 900 }, { width: 1536, height: 864 },
    { width: 1920, height: 900 }, { width: 1920, height: 1080 }, { width: 2560, height: 1440 },
];
const edgeViewports = [
    { width: 320, height: 568 }, { width: 390, height: 844 }, { width: 844, height: 390 },
    { width: 1366, height: 768 }, { width: 1920, height: 1080 },
];

await runBrowser('chrome', chromeExecutable, chromeViewports, true);
try {
    await fs.access(edgeExecutable);
    await runBrowser('edge', edgeExecutable, edgeViewports, false);
} catch (error) {
    if (error?.code !== 'ENOENT') throw error;
    report.browsers.edge = { result: 'SKIPPED', reason: 'Edge executable not found' };
}

await fs.writeFile(path.join(outputDir, 'report.json'), `${JSON.stringify(report, null, 2)}\n`);
console.log(JSON.stringify(report, null, 2));
