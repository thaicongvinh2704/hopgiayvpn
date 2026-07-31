const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawn } = require('child_process');

const ROOT = path.resolve(__dirname, '..', '..');
const RELEASE_DIR = path.join(ROOT, 'artifacts', 'product-seo-release-checkpoint-v1');
const SCREENSHOT_DIR = path.join(RELEASE_DIR, 'screenshots');
const MANIFEST_PATH = path.join(ROOT, 'seo-content', 'product-rewrite-v1', 'content-manifest.json');
const CHROME_PATH = process.argv[2];
const DEBUG_PORT = 9333;
const MAX_SCREENSHOT_HEIGHT = 30000;

if (!CHROME_PATH || !fs.existsSync(CHROME_PATH)) {
  throw new Error('Pass an installed Chrome or Edge executable path.');
}

fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function waitForJson(url, attempts = 100) {
  let lastError;
  for (let i = 0; i < attempts; i += 1) {
    try {
      const response = await fetch(url);
      if (response.ok) {
        return await response.json();
      }
    } catch (error) {
      lastError = error;
    }
    await delay(100);
  }
  throw lastError || new Error(`Timed out waiting for ${url}`);
}

class CdpClient {
  constructor(url) {
    this.url = url;
    this.socket = null;
    this.nextId = 1;
    this.pending = new Map();
    this.listeners = new Map();
  }

  async connect() {
    this.socket = new WebSocket(this.url);
    await new Promise((resolve, reject) => {
      const timeout = setTimeout(() => reject(new Error('CDP connection timeout')), 10000);
      this.socket.addEventListener('open', () => {
        clearTimeout(timeout);
        resolve();
      }, { once: true });
      this.socket.addEventListener('error', (event) => {
        clearTimeout(timeout);
        reject(event.error || new Error('CDP WebSocket error'));
      }, { once: true });
    });
    this.socket.addEventListener('message', (event) => {
      const message = JSON.parse(String(event.data));
      if (message.id && this.pending.has(message.id)) {
        const { resolve, reject } = this.pending.get(message.id);
        this.pending.delete(message.id);
        if (message.error) {
          reject(new Error(`${message.error.message}: ${JSON.stringify(message.error.data || {})}`));
        } else {
          resolve(message.result || {});
        }
        return;
      }
      const handlers = this.listeners.get(message.method) || [];
      handlers.slice().forEach((handler) => handler(message.params || {}));
    });
  }

  send(method, params = {}) {
    const id = this.nextId;
    this.nextId += 1;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.socket.send(JSON.stringify({ id, method, params }));
    });
  }

  waitFor(method, timeoutMs = 45000) {
    return new Promise((resolve, reject) => {
      const handler = (params) => {
        clearTimeout(timeout);
        const handlers = this.listeners.get(method) || [];
        this.listeners.set(method, handlers.filter((candidate) => candidate !== handler));
        resolve(params);
      };
      const timeout = setTimeout(() => {
        const handlers = this.listeners.get(method) || [];
        this.listeners.set(method, handlers.filter((candidate) => candidate !== handler));
        reject(new Error(`Timed out waiting for ${method}`));
      }, timeoutMs);
      const handlers = this.listeners.get(method) || [];
      handlers.push(handler);
      this.listeners.set(method, handlers);
    });
  }

  close() {
    if (this.socket && this.socket.readyState <= 1) {
      this.socket.close();
    }
  }
}

function csvEscape(value) {
  const text = Array.isArray(value) ? value.join(' | ') : String(value ?? '');
  return `"${text.replaceAll('"', '""')}"`;
}

function writeCsv(filePath, rows) {
  if (!rows.length) return;
  const headers = Object.keys(rows[0]);
  const content = [
    headers.map(csvEscape).join(','),
    ...rows.map((row) => headers.map((header) => csvEscape(row[header])).join(',')),
  ].join('\r\n') + '\r\n';
  fs.writeFileSync(filePath, content, 'utf8');
}

function buildPlan(products) {
  const groups = new Map();
  products.forEach((product) => {
    const bucket = groups.get(product.batch_id) || [];
    bucket.push(product);
    groups.set(product.batch_id, bucket);
  });
  const plan = [];
  Array.from(groups.keys()).sort().forEach((batchId) => {
    const bucket = groups.get(batchId).slice().sort((a, b) => {
      if (a.batch_order !== b.batch_order) return a.batch_order - b.batch_order;
      return a.product_id - b.product_id;
    });
    const selected = [bucket[0], bucket[bucket.length - 1]];
    selected.forEach((product, index) => {
      plan.push({
        batch_id: batchId,
        sample_index: index + 1,
        product_id: product.product_id,
        title: product.title,
        slug: product.slug,
        url: product.url,
        cluster: product.cluster,
      });
    });
  });
  if (plan.length !== 34) {
    throw new Error(`Expected 34 planned products, got ${plan.length}`);
  }
  return plan;
}

function pageAuditExpression(expectedTitle) {
  return `(() => {
    const isVisible = (element) => {
      if (!element) return false;
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
    };
    const main = document.querySelector('main.product-detail-page');
    const root = document.documentElement;
    const body = document.body;
    const h1s = [...document.querySelectorAll('main.product-detail-page h1')];
    const headings = [...document.querySelectorAll('main.product-detail-page h1, main.product-detail-page h2, main.product-detail-page h3')];
    let previousLevel = 0;
    let headingHierarchyValid = true;
    const headingOutline = headings.map((heading) => {
      const level = Number(heading.tagName.substring(1));
      if (previousLevel && level > previousLevel + 1) headingHierarchyValid = false;
      previousLevel = level;
      return level + ':' + heading.textContent.replace(/\\s+/g, ' ').trim();
    });
    const images = [...document.querySelectorAll('main.product-detail-page img')];
    const brokenImages = images.filter((image) => image.complete && image.naturalWidth === 0);
    const quoteForm = document.querySelector('form.quote-form[data-primary-quote-form]');
    const formFieldNames = quoteForm ? [...quoteForm.querySelectorAll('input[name],select[name],textarea[name]')].map((field) => field.name) : [];
    const ctas = [...document.querySelectorAll('main.product-detail-page .product-detail-actions a, main.product-detail-page .quote-submit-button, main.product-detail-page .footer-cta a')]
      .filter(isVisible)
      .map((element) => element.textContent.replace(/\\s+/g, ' ').trim());
    const contentLinks = [...document.querySelectorAll('main.product-detail-page .product-detail-description a[href]')];
    const pageText = (main ? main.textContent : document.body.textContent).toLowerCase().replace(/\\s+/g, ' ');
    const bannedPhrases = [
      'verified packaging client',
      'frequently asked questions about custom packaging',
      'request free sample'
    ].filter((phrase) => pageText.includes(phrase));
    const viewportWidth = window.innerWidth;
    const documentScrollWidth = Math.max(root.scrollWidth, body ? body.scrollWidth : 0);
    const horizontalOverflow = documentScrollWidth > viewportWidth + 2;
    const overflowingElements = horizontalOverflow ? [...document.querySelectorAll('main.product-detail-page *')].filter((element) => {
      if (!isVisible(element)) return false;
      const rect = element.getBoundingClientRect();
      return rect.left < -2 || rect.right > viewportWidth + 2;
    }).slice(0, 20).map((element) => {
      const classes = typeof element.className === 'string' ? element.className.trim().replace(/\\s+/g, '.') : '';
      return element.tagName.toLowerCase() + (element.id ? '#' + element.id : '') + (classes ? '.' + classes : '');
    }) : [];
    const navigationEntry = performance.getEntriesByType('navigation')[0];
    return {
      documentReadyState: document.readyState,
      responseStatus: navigationEntry && 'responseStatus' in navigationEntry ? navigationEntry.responseStatus : 0,
      viewportWidth,
      viewportHeight: window.innerHeight,
      documentScrollWidth,
      documentScrollHeight: Math.max(root.scrollHeight, body ? body.scrollHeight : 0),
      horizontalOverflow,
      overflowingElements,
      mainPresent: Boolean(main),
      h1Count: h1s.length,
      h1Text: h1s.map((node) => node.textContent.replace(/\\s+/g, ' ').trim()).join(' | '),
      h1Preserved: h1s.length === 1 && h1s[0].textContent.replace(/\\s+/g, ' ').trim() === ${JSON.stringify(expectedTitle)},
      headingHierarchyValid,
      headingOutline,
      imageCount: images.length,
      brokenImageCount: brokenImages.length,
      quoteFormPresent: isVisible(quoteForm),
      quoteFormFieldsValid: ['product_name','full_name','email'].every((name) => formFieldNames.includes(name)),
      ctaCount: ctas.length,
      ctaTexts: ctas,
      internalLinkCount: contentLinks.length,
      bannedPhrases,
      fakeTestimonialAbsent: !pageText.includes('verified packaging client'),
      globalFaqAbsent: !pageText.includes('frequently asked questions about custom packaging'),
      freeSampleClaimAbsent: !pageText.includes('request free sample')
    };
  })()`;
}

async function loadAllLazyImages(client) {
  const expression = `(async () => {
    const max = Math.max(document.documentElement.scrollHeight, document.body ? document.body.scrollHeight : 0);
    for (let y = 0; y < max; y += 700) {
      window.scrollTo(0, y);
      await new Promise((resolve) => setTimeout(resolve, 20));
    }
    window.scrollTo(0, 0);
    await new Promise((resolve) => setTimeout(resolve, 250));
    return true;
  })()`;
  await client.send('Runtime.evaluate', {
    expression,
    awaitPromise: true,
    returnByValue: true,
  });
}

async function captureViewport(client, sample, viewport) {
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: viewport.width,
    height: viewport.height,
    deviceScaleFactor: 1,
    mobile: viewport.mobile,
    screenWidth: viewport.width,
    screenHeight: viewport.height,
    positionX: 0,
    positionY: 0,
    dontSetVisibleSize: false,
  });
  await client.send('Network.clearBrowserCache');
  const loaded = client.waitFor('Page.loadEventFired');
  const navigation = await client.send('Page.navigate', { url: sample.url });
  if (navigation.errorText) {
    throw new Error(`Navigation error for ${sample.url}: ${navigation.errorText}`);
  }
  await loaded;
  await delay(500);
  const batchDir = path.join(SCREENSHOT_DIR, sample.batch_id);
  fs.mkdirSync(batchDir, { recursive: true });
  const viewportScreenshotName = `${sample.product_id}-${sample.slug}-${viewport.name}-viewport.png`;
  const viewportScreenshotPath = path.join(batchDir, viewportScreenshotName);
  const viewportScreenshotResult = await client.send('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: false,
    fromSurface: true,
    optimizeForSpeed: true,
  });
  fs.writeFileSync(viewportScreenshotPath, Buffer.from(viewportScreenshotResult.data, 'base64'));

  await loadAllLazyImages(client);
  const auditResult = await client.send('Runtime.evaluate', {
    expression: pageAuditExpression(sample.title),
    returnByValue: true,
  });
  const audit = auditResult.result ? auditResult.result.value : null;
  if (!audit) {
    throw new Error(`No audit result for ${sample.url}`);
  }
  const metrics = await client.send('Page.getLayoutMetrics');
  const contentHeight = Math.ceil(metrics.cssContentSize.height);
  const screenshotHeight = Math.max(viewport.height, Math.min(contentHeight, MAX_SCREENSHOT_HEIGHT));
  const screenshotResult = await client.send('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: true,
    fromSurface: true,
    optimizeForSpeed: true,
    clip: {
      x: 0,
      y: 0,
      width: viewport.width,
      height: screenshotHeight,
      scale: 1,
    },
  });
  const screenshotName = `${sample.product_id}-${sample.slug}-${viewport.name}.png`;
  const screenshotPath = path.join(batchDir, screenshotName);
  fs.writeFileSync(screenshotPath, Buffer.from(screenshotResult.data, 'base64'));

  const checks = {
    http_200: audit.responseStatus === 200,
    page_loaded: audit.documentReadyState === 'complete',
    no_horizontal_overflow: audit.horizontalOverflow === false,
    one_preserved_h1: audit.h1Count === 1 && audit.h1Preserved === true,
    valid_heading_hierarchy: audit.headingHierarchyValid === true,
    images_present_and_loaded: audit.imageCount > 0 && audit.brokenImageCount === 0,
    quote_form_present: audit.quoteFormPresent === true && audit.quoteFormFieldsValid === true,
    cta_present: audit.ctaCount > 0,
    internal_links_present: audit.internalLinkCount > 0,
    banned_template_copy_absent: audit.bannedPhrases.length === 0
      && audit.fakeTestimonialAbsent
      && audit.globalFaqAbsent
      && audit.freeSampleClaimAbsent,
    full_page_captured: contentHeight <= MAX_SCREENSHOT_HEIGHT,
  };
  const failures = Object.entries(checks).filter(([, passed]) => !passed).map(([name]) => name);
  return {
    ...sample,
    viewport: viewport.name,
    viewport_width: viewport.width,
    viewport_height: viewport.height,
    response_status: audit.responseStatus,
    document_scroll_width: audit.documentScrollWidth,
    document_scroll_height: audit.documentScrollHeight,
    horizontal_overflow: audit.horizontalOverflow,
    overflowing_elements: audit.overflowingElements,
    h1_count: audit.h1Count,
    h1_text: audit.h1Text,
    heading_hierarchy_valid: audit.headingHierarchyValid,
    image_count: audit.imageCount,
    broken_image_count: audit.brokenImageCount,
    quote_form_present: audit.quoteFormPresent,
    cta_count: audit.ctaCount,
    cta_texts: audit.ctaTexts,
    internal_link_count: audit.internalLinkCount,
    banned_phrases: audit.bannedPhrases,
    content_height: contentHeight,
    screenshot_height: screenshotHeight,
    screenshot_truncated: contentHeight > MAX_SCREENSHOT_HEIGHT,
    screenshot_path: path.relative(ROOT, screenshotPath).replaceAll('\\', '/'),
    screenshot_bytes: fs.statSync(screenshotPath).size,
    viewport_screenshot_path: path.relative(ROOT, viewportScreenshotPath).replaceAll('\\', '/'),
    viewport_screenshot_bytes: fs.statSync(viewportScreenshotPath).size,
    checks,
    qa_status: failures.length ? 'FAIL' : 'PASS',
    qa_failures: failures,
  };
}

async function main() {
  const manifest = JSON.parse(fs.readFileSync(MANIFEST_PATH, 'utf8'));
  if (manifest.product_count !== 179) {
    throw new Error('Source manifest does not contain 179 products.');
  }
  const plan = buildPlan(manifest.products);
  writeCsv(path.join(RELEASE_DIR, 'visual-qa-plan.csv'), plan);
  fs.writeFileSync(path.join(RELEASE_DIR, 'visual-qa-plan.json'), JSON.stringify({
    created_at_utc: new Date().toISOString(),
    browser_executable: CHROME_PATH,
    product_count: plan.length,
    viewport_count: plan.length * 2,
    products: plan,
  }, null, 2) + '\n');

  const userDataDir = fs.mkdtempSync(path.join(os.tmpdir(), 'hopgiayvpn-headless-'));
  const chrome = spawn(CHROME_PATH, [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-background-networking',
    '--disable-component-update',
    '--disable-sync',
    '--metrics-recording-only',
    '--disable-default-apps',
    '--mute-audio',
    '--remote-debugging-address=127.0.0.1',
    `--remote-debugging-port=${DEBUG_PORT}`,
    `--user-data-dir=${userDataDir}`,
    '--host-resolver-rules=MAP * 0.0.0.0, EXCLUDE localhost, EXCLUDE 127.0.0.1',
    'about:blank',
  ], {
    stdio: ['ignore', 'ignore', 'ignore'],
    windowsHide: true,
  });

  let client;
  try {
    await waitForJson(`http://127.0.0.1:${DEBUG_PORT}/json/version`);
    const targetResponse = await fetch(`http://127.0.0.1:${DEBUG_PORT}/json/new?${encodeURIComponent('about:blank')}`, { method: 'PUT' });
    if (!targetResponse.ok) {
      throw new Error(`Cannot create CDP target: ${targetResponse.status}`);
    }
    const target = await targetResponse.json();
    client = new CdpClient(target.webSocketDebuggerUrl);
    await client.connect();
    await client.send('Page.enable');
    await client.send('Runtime.enable');
    await client.send('Network.enable');

    const viewports = [
      { name: 'desktop-1440', width: 1440, height: 1000, mobile: false },
      { name: 'mobile-390', width: 390, height: 844, mobile: true },
    ];
    const rows = [];
    for (const sample of plan) {
      for (const viewport of viewports) {
        try {
          rows.push(await captureViewport(client, sample, viewport));
        } catch (error) {
          rows.push({
            ...sample,
            viewport: viewport.name,
            viewport_width: viewport.width,
            viewport_height: viewport.height,
            qa_status: 'FAIL',
            qa_failures: ['capture_error'],
            error: error.stack || error.message,
          });
        }
      }
    }

    const csvRows = rows.map((row) => ({
      batch_id: row.batch_id,
      product_id: row.product_id,
      title: row.title,
      url: row.url,
      viewport: row.viewport,
      viewport_width: row.viewport_width,
      viewport_height: row.viewport_height,
      response_status: row.response_status || 0,
      document_scroll_width: row.document_scroll_width || 0,
      document_scroll_height: row.document_scroll_height || 0,
      horizontal_overflow: row.horizontal_overflow ?? '',
      h1_count: row.h1_count || 0,
      heading_hierarchy_valid: row.heading_hierarchy_valid ?? '',
      image_count: row.image_count || 0,
      broken_image_count: row.broken_image_count || 0,
      quote_form_present: row.quote_form_present ?? '',
      cta_count: row.cta_count || 0,
      internal_link_count: row.internal_link_count || 0,
      banned_phrases: row.banned_phrases || [],
      screenshot_truncated: row.screenshot_truncated ?? '',
      screenshot_path: row.screenshot_path || '',
      screenshot_bytes: row.screenshot_bytes || 0,
      viewport_screenshot_path: row.viewport_screenshot_path || '',
      viewport_screenshot_bytes: row.viewport_screenshot_bytes || 0,
      qa_status: row.qa_status,
      qa_failures: row.qa_failures || [],
      error: row.error || '',
    }));
    writeCsv(path.join(RELEASE_DIR, 'visual-qa-report.csv'), csvRows);
    const failures = rows.filter((row) => row.qa_status !== 'PASS');
    const report = {
      schema_version: 1,
      created_at_utc: new Date().toISOString(),
      browser: {
        executable: CHROME_PATH,
        debug_port: DEBUG_PORT,
        external_host_resolution_blocked: true,
      },
      plan_products: plan.length,
      batches: new Set(plan.map((row) => row.batch_id)).size,
      captures_expected: plan.length * 2,
      captures_created: rows.filter((row) => row.screenshot_path && fs.existsSync(path.join(ROOT, row.screenshot_path))).length,
      viewport_captures_created: rows.filter((row) => row.viewport_screenshot_path && fs.existsSync(path.join(ROOT, row.viewport_screenshot_path))).length,
      passed_captures: rows.length - failures.length,
      failed_captures: failures.length,
      failed_rows: failures.map((row) => ({
        batch_id: row.batch_id,
        product_id: row.product_id,
        viewport: row.viewport,
        failures: row.qa_failures,
        error: row.error || '',
      })),
      rows,
      production_requests: 0,
      production_writes: 0,
    };
    fs.writeFileSync(path.join(RELEASE_DIR, 'visual-qa-report.json'), JSON.stringify(report, null, 2) + '\n');
    process.stdout.write(JSON.stringify({
      ok: failures.length === 0,
      products: plan.length,
      batches: report.batches,
      captures_created: report.captures_created,
      viewport_captures_created: report.viewport_captures_created,
      passed_captures: report.passed_captures,
      failed_captures: report.failed_captures,
      report: path.join(RELEASE_DIR, 'visual-qa-report.json'),
    }, null, 2) + '\n');
    process.exitCode = failures.length ? 3 : 0;
  } finally {
    if (client) client.close();
    chrome.kill();
    await delay(300);
    try {
      fs.rmSync(userDataDir, { recursive: true, force: true });
    } catch {
      // The temporary browser profile is outside the project and may still be locked briefly.
    }
  }
}

// Keep the Windows CLI process alive until every CDP operation has settled.
// A pending Promise alone is not an event-loop handle when Chrome exits early.
const processKeepAlive = setInterval(() => {}, 1000);

main()
  .catch((error) => {
    console.error(error.stack || error.message);
    process.exitCode = 2;
  })
  .finally(() => {
    clearInterval(processKeepAlive);
  });
