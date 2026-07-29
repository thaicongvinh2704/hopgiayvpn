import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { chromium } from "@playwright/test";
import sharp from "sharp";
import {
  chromeExecutable,
  makeURL,
  repoRoot,
  representativePages,
  requiredViewports,
} from "../site-fixtures.mjs";

const phase = process.argv[2];
const resultFile = process.env.CAPTURE_RESULT_FILE || "metrics.json";
const concurrency = Math.max(
  1,
  Number.parseInt(process.env.CAPTURE_CONCURRENCY || "1", 10) || 1,
);

if (!["baseline", "after", "qc-pass-1", "qc-pass-2"].includes(phase)) {
  throw new Error(
    "Pass one output phase: baseline, after, qc-pass-1, or qc-pass-2.",
  );
}

const selectedPageKeys = new Set(
  (process.env.CAPTURE_PAGES || "")
    .split(",")
    .map((value) => value.trim())
    .filter(Boolean),
);
const selectedViewportKeys = new Set(
  (process.env.CAPTURE_VIEWPORTS || "")
    .split(",")
    .map((value) => value.trim())
    .filter(Boolean),
);

const pages = selectedPageKeys.size
  ? representativePages.filter(({ key }) => selectedPageKeys.has(key))
  : representativePages;
const viewports = selectedViewportKeys.size
  ? requiredViewports.filter(({ key }) => selectedViewportKeys.has(key))
  : requiredViewports;

if (!pages.length || !viewports.length) {
  throw new Error("The page or viewport filter did not match any fixture.");
}

const outputRoot = path.join(
  repoRoot,
  "artifacts",
  "mobile-ux-20260728",
  phase,
);
const screenshotRoot = path.join(outputRoot, "screenshots");
await fs.mkdir(screenshotRoot, { recursive: true });

const browser = await chromium.launch({
  executablePath: chromeExecutable,
  headless: true,
  args: [
    "--disable-background-networking",
    "--disable-component-update",
    "--disable-default-apps",
    "--disable-extensions",
    "--disable-features=Translate,MediaRouter",
    "--no-default-browser-check",
    "--no-first-run",
  ],
});

const results = [];

async function collectPageMetrics(page, fixture, viewport, navigation) {
  return page.evaluate(
    ({ fixtureData, viewportData, navigationData }) => {
      const isVisible = (element) => {
        if (!(element instanceof Element)) {
          return false;
        }

        const style = getComputedStyle(element);
        const rect = element.getBoundingClientRect();

        return (
          style.display !== "none" &&
          style.visibility !== "hidden" &&
          Number.parseFloat(style.opacity || "1") > 0 &&
          rect.width > 0 &&
          rect.height > 0
        );
      };
      const visibleH1s = Array.from(document.querySelectorAll("h1")).filter(
        isVisible,
      );
      const allHeadings = Array.from(
        document.querySelectorAll("h1, h2, h3, h4, h5, h6"),
      );
      const visibleImages = Array.from(document.images).filter(isVisible);
      const brokenVisibleImages = visibleImages.filter(
        (image) => !image.complete || image.naturalWidth === 0,
      );
      const controls = Array.from(
        document.querySelectorAll(
          "button, input:not([type='hidden']), select, textarea, a[href], summary",
        ),
      ).filter(isVisible);
      const undersizedControls = controls
        .map((control) => {
          const rect = control.getBoundingClientRect();
          return {
            tag: control.tagName.toLowerCase(),
            id: control.id || "",
            className:
              typeof control.className === "string" ? control.className : "",
            text: (control.textContent || control.getAttribute("aria-label") || "")
              .trim()
              .replace(/\s+/g, " ")
              .slice(0, 90),
            width: Math.round(rect.width * 10) / 10,
            height: Math.round(rect.height * 10) / 10,
          };
        })
        .filter(({ width, height }) => width < 44 || height < 44)
        .slice(0, 100);
      const formControls = Array.from(
        document.querySelectorAll(
          "input:not([type='hidden']), select, textarea",
        ),
      ).filter(isVisible);
      const unnamedFormControls = formControls
        .filter((control) => {
          const labelledBy = control
            .getAttribute("aria-labelledby")
            ?.split(/\s+/)
            .some((id) => document.getElementById(id)?.textContent?.trim());
          const explicitLabel =
            control.id &&
            document.querySelector(
              `label[for="${CSS.escape(control.id)}"]`,
            )?.textContent?.trim();
          const wrappingLabel = control.closest("label")?.textContent?.trim();
          const ariaLabel = control.getAttribute("aria-label")?.trim();
          const title = control.getAttribute("title")?.trim();
          return !(
            labelledBy ||
            explicitLabel ||
            wrappingLabel ||
            ariaLabel ||
            title
          );
        })
        .map((control) => ({
          tag: control.tagName.toLowerCase(),
          type: control.getAttribute("type") || "",
          id: control.id || "",
          name: control.getAttribute("name") || "",
        }));
      const headerElements = Array.from(
        document.querySelectorAll(".top-bar, .main-header, .main-nav"),
      ).filter(isVisible);
      const visibleHeaderBottom = headerElements.reduce((bottom, element) => {
        const rect = element.getBoundingClientRect();
        return Math.max(bottom, rect.bottom);
      }, 0);
      const h1Rect = visibleH1s[0]?.getBoundingClientRect();
      const primaryQuote = Array.from(
        document.querySelectorAll(
          "main a[href*='/contact'][href*='#quote'], main button[type='submit'], main input[type='submit']",
        ),
      ).find(isVisible);
      const quoteRect = primaryQuote?.getBoundingClientRect();
      const visibleInteractiveHeaderNodes = Array.from(
        document.querySelectorAll(
          ".top-bar a, .top-bar button, .main-header a, .main-header button, .main-header input, .main-nav a, .main-nav button, .main-nav input",
        ),
      ).filter(isVisible);

      return {
        phase: navigationData.phase,
        page: fixtureData.key,
        requestedPath: fixtureData.path,
        renderedPath: navigationData.renderedPath,
        usedLocalFallback: navigationData.usedLocalFallback,
        status: navigationData.status,
        url: location.href,
        viewport: viewportData,
        document: {
          scrollWidth: document.documentElement.scrollWidth,
          clientWidth: document.documentElement.clientWidth,
          scrollHeight: document.documentElement.scrollHeight,
          horizontalOverflow:
            document.documentElement.scrollWidth >
            document.documentElement.clientWidth + 1,
        },
        structure: {
          visibleH1Count: visibleH1s.length,
          headingCount: allHeadings.length,
          sectionCount: document.querySelectorAll("main section").length,
          mainImageCount: document.querySelectorAll("main img").length,
          visibleHeaderInteractiveCount: visibleInteractiveHeaderNodes.length,
          formControlCount: formControls.length,
        },
        firstViewport: {
          visibleHeaderBottom: Math.round(visibleHeaderBottom),
          h1Top: h1Rect ? Math.round(h1Rect.top) : null,
          h1Bottom: h1Rect ? Math.round(h1Rect.bottom) : null,
          h1InViewport: Boolean(
            h1Rect && h1Rect.top < innerHeight && h1Rect.bottom > 0,
          ),
          quoteTop: quoteRect ? Math.round(quoteRect.top) : null,
          quoteInViewport: Boolean(
            quoteRect && quoteRect.top < innerHeight && quoteRect.bottom > 0,
          ),
        },
        accessibility: {
          undersizedControlCount: undersizedControls.length,
          undersizedControls,
          unnamedFormControlCount: unnamedFormControls.length,
          unnamedFormControls,
        },
        media: {
          visibleImageCount: visibleImages.length,
          brokenVisibleImageCount: brokenVisibleImages.length,
          brokenVisibleImages: brokenVisibleImages.slice(0, 50).map((image) => ({
            src: image.currentSrc || image.src,
            alt: image.alt,
          })),
          missingIntrinsicDimensionCount: visibleImages.filter(
            (image) => !image.hasAttribute("width") || !image.hasAttribute("height"),
          ).length,
        },
      };
    },
    {
      fixtureData: fixture,
      viewportData: viewport,
      navigationData: navigation,
    },
  );
}

async function scrollForLazyMedia(page) {
  await page.evaluate(async () => {
    const distance = Math.max(400, Math.floor(innerHeight * 0.8));
    const maximum = document.documentElement.scrollHeight - innerHeight;

    for (let position = 0; position < maximum; position += distance) {
      scrollTo(0, position);
      await new Promise((resolve) => setTimeout(resolve, 35));
    }

    scrollTo(0, maximum);
    await new Promise((resolve) => setTimeout(resolve, 300));
    scrollTo(0, 0);
  });
}

async function captureFullPage(page, outputPath, viewport) {
  try {
    await page.screenshot({
      path: outputPath,
      animations: "disabled",
      fullPage: true,
    });
    return { method: "native", tiles: 1 };
  } catch (nativeError) {
    const dimensions = await page.evaluate(() => ({
      width: Math.max(
        document.documentElement.scrollWidth,
        document.body?.scrollWidth || 0,
        innerWidth,
      ),
      height: Math.max(
        document.documentElement.scrollHeight,
        document.body?.scrollHeight || 0,
        innerHeight,
      ),
    }));
    const width = Math.min(dimensions.width, viewport.width);
    const tileHeight = 6_000;
    const tileDirectory = await fs.mkdtemp(
      path.join(os.tmpdir(), "vpn-mobile-ux-tiles-"),
    );
    const composites = [];

    try {
      for (let top = 0, index = 0; top < dimensions.height; index += 1) {
        const height = Math.min(tileHeight, dimensions.height - top);
        const tilePath = path.join(
          tileDirectory,
          `${String(index).padStart(3, "0")}.png`,
        );
        await page.screenshot({
          path: tilePath,
          animations: "disabled",
          clip: {
            x: 0,
            y: top,
            width,
            height,
          },
        });
        composites.push({ input: tilePath, left: 0, top });
        top += height;
      }

      await sharp({
        create: {
          width,
          height: dimensions.height,
          channels: 4,
          background: "#ffffff",
        },
        limitInputPixels: false,
      })
        .composite(composites)
        .png()
        .toFile(outputPath);

      return {
        method: "stitched",
        tiles: composites.length,
        nativeError: nativeError.message,
      };
    } finally {
      await fs.rm(tileDirectory, { force: true, recursive: true });
    }
  }
}

async function captureFixture(context, fixture, viewport) {
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];

  page.on("console", (message) => {
    if (message.type() === "error") {
      consoleErrors.push(message.text());
    }
  });
  page.on("pageerror", (error) => pageErrors.push(error.message));
  await page.route(
    /(?:googletagmanager|google-analytics|clarity\.ms|doubleclick|recaptcha)/,
    (route) =>
      route.fulfill({
        status: 204,
        contentType: "application/javascript",
        body: "",
      }),
  );

  try {
    let renderedPath = fixture.path;
    let usedLocalFallback = false;
    let response = await page.goto(makeURL(fixture.path), {
      waitUntil: "domcontentloaded",
      timeout: 45_000,
    });

    if (
      fixture.localFallbackPath &&
      (!response || response.status() >= 400)
    ) {
      renderedPath = fixture.localFallbackPath;
      usedLocalFallback = true;
      response = await page.goto(makeURL(renderedPath), {
        waitUntil: "domcontentloaded",
        timeout: 45_000,
      });
    }

    await page
      .waitForLoadState("networkidle", { timeout: 10_000 })
      .catch(() => {});
    await page.evaluate(() => document.fonts?.ready).catch(() => {});
    await page.waitForTimeout(400);
    await page.evaluate(() => scrollTo(0, 0));

    const stem = `${fixture.key}-${viewport.key}`;
    await page.screenshot({
      path: path.join(screenshotRoot, `${stem}-first.png`),
      animations: "disabled",
    });

    const navigation = {
      phase,
      renderedPath,
      usedLocalFallback,
      status: response?.status() ?? null,
    };
    const firstViewportMetrics = await collectPageMetrics(
      page,
      fixture,
      viewport,
      navigation,
    );

    await scrollForLazyMedia(page);
    await page
      .waitForFunction(
        () => Array.from(document.images).every((image) => image.complete),
        { timeout: 5_000 },
      )
      .catch(() => {});
    await page.waitForTimeout(500);
    const fullPageCapture = await captureFullPage(
      page,
      path.join(screenshotRoot, `${stem}-full.png`),
      viewport,
    );

    const finalMetrics = await collectPageMetrics(
      page,
      fixture,
      viewport,
      navigation,
    );
    results.push({
      ...finalMetrics,
      firstViewport: firstViewportMetrics.firstViewport,
      consoleErrors: [...new Set(consoleErrors)],
      pageErrors: [...new Set(pageErrors)],
      fullPageCapture,
    });

    process.stdout.write(
      `${phase}: ${fixture.key} ${viewport.key}` +
        `${usedLocalFallback ? " (local article fallback)" : ""}\n`,
    );
  } finally {
    await page.close();
  }
}

for (const viewport of viewports) {
  const context = await browser.newContext({
    viewport: { width: viewport.width, height: viewport.height },
    deviceScaleFactor: 1,
    reducedMotion: "reduce",
    locale: "en-US",
    serviceWorkers: "block",
  });

  for (let index = 0; index < pages.length; index += concurrency) {
    await Promise.all(
      pages
        .slice(index, index + concurrency)
        .map((fixture) => captureFixture(context, fixture, viewport)),
    );
  }

  await context.close();
}

await browser.close();
results.sort(
  (left, right) =>
    viewports.findIndex(({ key }) => key === left.viewport.key) -
      viewports.findIndex(({ key }) => key === right.viewport.key) ||
    pages.findIndex(({ key }) => key === left.page) -
      pages.findIndex(({ key }) => key === right.page),
);
await fs.writeFile(
  path.join(outputRoot, resultFile),
  `${JSON.stringify(results, null, 2)}\n`,
  "utf8",
);

process.stdout.write(
  `Captured ${results.length} page/viewport combinations in ${outputRoot}\n`,
);
