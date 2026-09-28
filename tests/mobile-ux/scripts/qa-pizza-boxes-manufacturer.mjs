import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from '@playwright/test';
import { chromeExecutable, repoRoot } from '../site-fixtures.mjs';

const phase = process.argv[2] || 'after';
const outputDir = path.join(repoRoot, 'artifacts', 'pizza-boxes-landing-20260928', phase);
await fs.mkdir(outputDir, { recursive: true });
const browser = await chromium.launch({ executablePath: chromeExecutable, headless: true });
const results = [];
try {
  for (const viewport of [
    { name: 'desktop', width: 1440, height: 900, throttle: false },
    { name: 'mobile', width: 390, height: 844, throttle: true },
    { name: 'small-mobile', width: 320, height: 568, throttle: false },
    { name: 'tablet', width: 768, height: 1024, throttle: false },
  ]) {
    const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height }, reducedMotion: 'reduce', serviceWorkers: 'block' });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => {
      window.pizzaMetrics = { lcp: 0, cls: 0 };
      new PerformanceObserver(list => {
        for (const entry of list.getEntries()) window.pizzaMetrics.lcp = entry.startTime;
      }).observe({ type: 'largest-contentful-paint', buffered: true });
      new PerformanceObserver(list => {
        for (const entry of list.getEntries()) {
          if (!entry.hadRecentInput) window.pizzaMetrics.cls += entry.value;
        }
      }).observe({ type: 'layout-shift', buffered: true });
    });
    const cdp = await context.newCDPSession(page);
    await cdp.send('Network.enable');
    await cdp.send('Network.setCacheDisabled', { cacheDisabled: true });
    if (viewport.throttle) {
      await cdp.send('Network.emulateNetworkConditions', {
        offline: false, latency: 150, downloadThroughput: 200000, uploadThroughput: 93750,
      });
      await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
    }
    const response = await page.goto('http://localhost/hopgiayvpn/custom-pizza-boxes-manufacturer/', {
      waitUntil: 'load', timeout: 45000,
    });
    await page.waitForTimeout(1200);
    const initial = await page.evaluate(() => {
      const nav = performance.getEntriesByType('navigation')[0];
      const resources = performance.getEntriesByType('resource');
      return {
        ttfbMs: Math.round(nav.responseStart),
        fcpMs: Math.round(performance.getEntriesByName('first-contentful-paint')[0]?.startTime || 0),
        lcpMs: Math.round(window.pizzaMetrics.lcp),
        cls: Number(window.pizzaMetrics.cls.toFixed(4)),
        bytes: resources.reduce((sum, resource) => sum + resource.encodedBodySize, nav.encodedBodySize),
        resourceCount: resources.length,
        css: [...document.querySelectorAll('link[rel="stylesheet"]')].map(link => ({ id: link.id, href: link.href })),
        scripts: [...document.querySelectorAll('script[src]')].map(script => ({ id: script.id, src: script.src })),
        resources: resources.map(resource => ({ url: resource.name, bytes: resource.encodedBodySize, duration: Math.round(resource.duration) })),
      };
    });
    await page.screenshot({ path: path.join(outputDir, viewport.name + '-top.png') });
    await page.evaluate(async () => {
      for (let y = 0; y < document.documentElement.scrollHeight; y += 600) {
        scrollTo(0, y);
        await new Promise(resolve => setTimeout(resolve, 70));
      }
    });
    await page.waitForFunction(() => [...document.querySelectorAll('main img')].every(image => image.complete), null, { timeout: 15000 });
    await page.evaluate(() => scrollTo(0, 0));
    const rendered = await page.evaluate(() => {
      const main = document.querySelector('.vpn-pizza-page');
      const form = main.querySelector('.vpb-form');
      const images = [...main.querySelectorAll('img')];
      const jsonErrors = [];
      const entities = [];
      for (const block of document.querySelectorAll('script[type="application/ld+json"]')) {
        try { const data = JSON.parse(block.textContent); entities.push(...(data['@graph'] || [data])); }
        catch (error) { jsonErrors.push(error.message); }
      }
      return {
        title: document.title,
        canonical: document.querySelector('link[rel="canonical"]')?.href,
        h1Count: main.querySelectorAll('h1').length,
        horizontalOverflow: document.documentElement.scrollWidth > innerWidth + 1,
        products: main.querySelectorAll('.vpb-product-card').length,
        factoryImages: main.querySelectorAll('[data-factory-image]').length,
        brokenImages: images.filter(image => image.naturalWidth === 0).map(image => image.src),
        images: images.map(image => ({ src: image.currentSrc, width: image.width, height: image.height, lazy: image.loading, sizes: image.sizes })),
        hasNonce: !!form.querySelector('[name="custom_box_quote_nonce"]')?.value,
        formAction: form.action,
        stickyQuoteTarget: document.querySelector('.mobile-conversion-bar__quote')?.href,
        faqCount: main.querySelectorAll('.vpb-accordion details').length,
        schemaFaqCount: entities.find(entity => entity['@type'] === 'FAQPage')?.mainEntity?.length || 0,
        schemaErrors: jsonErrors,
        schemaTypes: entities.map(entity => entity['@type']),
      };
    });
    const faq = page.locator('.vpb-accordion details').first();
    await faq.locator('summary').click();
    let menuWorks = true;
    if (viewport.width < 768) {
      const menu = page.locator('[data-mobile-menu-toggle]');
      if (await menu.count()) {
        await menu.click();
        menuWorks = await menu.getAttribute('aria-expanded') === 'true' && await page.locator('#mobile-site-menu').isVisible();
        await page.keyboard.press('Escape');
      }
    }
    if (rendered.factoryImages) {
      await page.locator('.vpb-factory').screenshot({ path: path.join(outputDir, viewport.name + '-factory.png') });
    }
    const faqWorks = await faq.getAttribute('open') !== null;
    await faq.locator('summary').click();
    await page.evaluate(() => scrollTo(0, 0));
    await page.screenshot({ path: path.join(outputDir, viewport.name + '-full.png'), fullPage: true });
    results.push({ viewport, status: response.status(), initial, rendered, faqWorks, menuWorks, errors });
    await context.close();
  }
} finally { await browser.close(); }
await fs.writeFile(path.join(outputDir, 'report.json'), JSON.stringify(results, null, 2));
console.log(JSON.stringify(results.map(({ viewport, status, initial, rendered, faqWorks, menuWorks, errors }) => ({
  viewport: viewport.name, status, ttfbMs: initial.ttfbMs, fcpMs: initial.fcpMs, lcpMs: initial.lcpMs,
  cls: initial.cls, initialKB: Math.round(initial.bytes / 1024), requests: initial.resourceCount,
  products: rendered.products, factoryImages: rendered.factoryImages,
  overflow: rendered.horizontalOverflow, brokenImages: rendered.brokenImages.length,
  faqWorks, menuWorks, schemaErrors: rendered.schemaErrors, errors,
})), null, 2));
const failures = results.filter(result => result.status !== 200 || result.errors.length || result.rendered.horizontalOverflow
  || result.rendered.brokenImages.length || result.rendered.schemaErrors.length || result.rendered.h1Count !== 1
  || !result.rendered.hasNonce || !result.faqWorks || !result.menuWorks
  || result.rendered.faqCount !== result.rendered.schemaFaqCount
  || (phase !== 'before' && (result.rendered.factoryImages !== 3
    || !result.initial.css.some(css => css.href.includes('pizza-landing-shell.css'))
    || !result.initial.css.some(css => css.href.includes('pizza-boxes-manufacturer.css')))));
if (failures.length) throw new Error(`Landing QA failed: ${failures.map(result => result.viewport.name).join(', ')}`);
