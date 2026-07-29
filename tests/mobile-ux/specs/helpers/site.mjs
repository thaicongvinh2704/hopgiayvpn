import { expect } from "@playwright/test";
import { baseURL, makeURL } from "../../site-fixtures.mjs";

export const SELECTORS = Object.freeze({
  bottomBar:
    "[data-mobile-conversion-bar], .mobile-conversion-bar, .mobile-bottom-cta, .sticky-mobile-cta",
  menuOverlay:
    "[data-mobile-menu-overlay], [data-menu-overlay], .mobile-menu-overlay, .menu-overlay",
  menuToggle:
    "[data-mobile-menu-toggle], .mobile-menu-icon, .vpn-lp-minimal-header__menu, .menu-toggle, button[aria-controls][aria-label*='menu' i]",
  quoteForm: "[data-primary-quote-form], form.quote-form",
  tableRegion:
    "[data-table-scroll-region], .blog-table-scroll, .table-scroll-region, [role='region']",
});

export function viewportWidth(testInfo) {
  return testInfo.project.use.viewport?.width || 0;
}

export function isMobileProject(testInfo) {
  return viewportWidth(testInfo) < 768;
}

export async function installNetworkGuards(page) {
  const localOrigin = new URL(baseURL).origin;

  await page.route("**/*", async (route) => {
    const request = route.request();
    const method = request.method().toUpperCase();

    if (!["GET", "HEAD"].includes(method)) {
      await route.fulfill({
        status: 200,
        contentType: "text/html; charset=utf-8",
        headers: {
          "x-playwright-mocked-submission": "1",
        },
        body:
          "<!doctype html><html><body>" +
          "Playwright intercepted this non-GET request." +
          "</body></html>",
      });
      return;
    }

    const url = new URL(request.url());
    if (
      ["http:", "https:"].includes(url.protocol) &&
      url.origin !== localOrigin
    ) {
      await route.abort("blockedbyclient");
      return;
    }

    await route.continue();
  });
}

export async function gotoFixture(page, fixture) {
  let renderedPath = fixture.path;
  let usedLocalFallback = false;
  let response = await page.goto(makeURL(renderedPath), {
    waitUntil: "domcontentloaded",
  });

  if (
    fixture.localFallbackPath &&
    (!response || response.status() >= 400)
  ) {
    renderedPath = fixture.localFallbackPath;
    usedLocalFallback = true;
    response = await page.goto(makeURL(renderedPath), {
      waitUntil: "domcontentloaded",
    });
  }

  expect(response, `No navigation response for ${fixture.key}`).not.toBeNull();
  expect(
    response.status(),
    `${fixture.key} returned HTTP ${response.status()} at ${renderedPath}`,
  ).toBeLessThan(400);

  await page
    .waitForLoadState("networkidle", { timeout: 10_000 })
    .catch(() => {});
  await page.evaluate(() => document.fonts?.ready).catch(() => {});
  await page.waitForTimeout(150);
  await page.evaluate(() => scrollTo(0, 0));

  return {
    renderedPath,
    status: response.status(),
    usedLocalFallback,
  };
}

export async function settleLazyMedia(page) {
  await page.evaluate(async () => {
    document.querySelectorAll("img[loading='lazy']").forEach((image) => {
      image.loading = "eager";
    });

    const maximum = Math.max(
      0,
      document.documentElement.scrollHeight - innerHeight,
    );
    const stops = 6;

    for (let index = 1; index <= stops; index += 1) {
      scrollTo(0, Math.round((maximum * index) / stops));
      await new Promise((resolve) => setTimeout(resolve, 45));
    }

    scrollTo(0, 0);
  });

  await page
    .waitForFunction(
      () =>
        Array.from(document.images)
          .filter((image) => {
            const style = getComputedStyle(image);
            const rect = image.getBoundingClientRect();
            return (
              style.display !== "none" &&
              style.visibility !== "hidden" &&
              rect.width > 0 &&
              rect.height > 0
            );
          })
          .every((image) => image.complete),
      { timeout: 6_000 },
    )
    .catch(() => {});
}

export async function expectExactlyOneH1(page) {
  const headings = page.locator("h1");
  await expect(headings, "Every representative page must have one H1").toHaveCount(
    1,
  );
  await expect(headings.first()).toBeVisible();
}

export async function expectNoHorizontalOverflow(page, context = "") {
  const metrics = await page.evaluate(() => {
    const root = document.documentElement;
    const body = document.body;
    const masks = [root, body]
      .filter(Boolean)
      .map((element) => ({
        element: element === root ? "html" : "body",
        overflowX: getComputedStyle(element).overflowX,
      }))
      .filter(({ overflowX }) => ["hidden", "clip"].includes(overflowX));

    return {
      clientWidth: root.clientWidth,
      scrollWidth: Math.max(
        root.scrollWidth,
        body?.scrollWidth || 0,
      ),
      masks,
    };
  });

  expect(
    metrics.masks,
    `${context} must not conceal page overflow on html/body`,
  ).toEqual([]);
  expect(
    metrics.scrollWidth,
    `${context} scroll width ${metrics.scrollWidth}px exceeds the ` +
      `${metrics.clientWidth}px viewport`,
  ).toBeLessThanOrEqual(metrics.clientWidth + 1);
}

export async function expectCompactStickyHeader(page) {
  const metrics = await page.evaluate(() => {
    const selectors = [
      "[data-mobile-header]",
      ".site-mobile-header",
      ".mobile-masthead",
      ".vpn-lp-minimal-header",
      ".top-bar",
      ".main-header",
      ".main-nav",
      "body > header",
    ];
    const elements = Array.from(
      new Set(selectors.flatMap((selector) => Array.from(document.querySelectorAll(selector)))),
    );
    const surfaces = elements
      .map((element) => {
        const style = getComputedStyle(element);
        const rect = element.getBoundingClientRect();
        return {
          bottom: rect.bottom,
          height: rect.height,
          position: style.position,
          top: rect.top,
          visible:
            style.display !== "none" &&
            style.visibility !== "hidden" &&
            rect.width > 0 &&
            rect.height > 0,
        };
      })
      .filter(
        ({ bottom, position, top, visible }) =>
          visible &&
          ["fixed", "sticky"].includes(position) &&
          top < 65 &&
          bottom > 0,
      );

    return {
      count: surfaces.length,
      height: surfaces.length
        ? Math.max(...surfaces.map(({ bottom }) => bottom)) -
          Math.min(...surfaces.map(({ top }) => Math.max(0, top)))
        : 0,
      surfaces,
    };
  });

  expect(
    metrics.count,
    "A mobile sticky header surface must be present",
  ).toBeGreaterThan(0);
  expect(
    metrics.height,
    `Sticky header stack is ${Math.round(metrics.height)}px tall`,
  ).toBeLessThanOrEqual(64);
}

export async function expectTouchTargets(page) {
  const issues = await page.evaluate(() => {
    const selector = [
      "button",
      "input:not([type='hidden'])",
      "select",
      "textarea",
      "summary",
      "[role='button']",
      "header a[href]",
      "nav a[href]",
      "footer a[href]",
      ".pagination a[href]",
      ".page-numbers",
      "a.btn",
      "a[class*='button']",
      "a[class*='cta']",
      "a[class*='menu']",
    ].join(",");
    const rendered = (element) => {
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return (
        style.display !== "none" &&
        style.visibility !== "hidden" &&
        Number.parseFloat(style.opacity || "1") > 0 &&
        style.pointerEvents !== "none" &&
        rect.width > 0 &&
        rect.height > 0
      );
    };
    const describe = (element) => {
      const label =
        element.getAttribute("aria-label") ||
        element.textContent ||
        element.getAttribute("name") ||
        "";
      return {
        className:
          typeof element.className === "string" ? element.className : "",
        id: element.id || "",
        label: label.trim().replace(/\s+/g, " ").slice(0, 80),
        tag: element.tagName.toLowerCase(),
      };
    };

    return Array.from(new Set(document.querySelectorAll(selector)))
      .filter(rendered)
      .filter(
        (element) =>
          !element.matches("[data-touch-target-exempt]") &&
          !element.closest("[data-touch-target-exempt]"),
      )
      .map((element) => {
        const type = (element.getAttribute("type") || "").toLowerCase();
        let target = element;

        if (["checkbox", "radio"].includes(type)) {
          target =
            element.closest("label") ||
            (element.id
              ? document.querySelector(`label[for="${CSS.escape(element.id)}"]`)
              : null) ||
            element;
        }

        const rect = target.getBoundingClientRect();
        const minimumHeight = element.closest(".quote-form") ? 48 : 44;
        return {
          ...describe(element),
          height: Math.round(rect.height * 10) / 10,
          minimumHeight,
          width: Math.round(rect.width * 10) / 10,
        };
      })
      .filter(
        ({ height, minimumHeight, width }) =>
          height < minimumHeight || width < 44,
      )
      .slice(0, 60);
  });

  expect(
    issues,
    "Important standalone controls must be at least 44×44px; " +
      "quote-form controls must be at least 48px high",
  ).toEqual([]);
}

export async function collectImageIssues(page) {
  return page.evaluate(() => {
    const rendered = (image) => {
      const style = getComputedStyle(image);
      const rect = image.getBoundingClientRect();
      return (
        style.display !== "none" &&
        style.visibility !== "hidden" &&
        rect.width > 0 &&
        rect.height > 0
      );
    };

    return Array.from(document.images)
      .filter(rendered)
      .map((image) => {
        const width = Number.parseFloat(image.getAttribute("width") || "0");
        const height = Number.parseFloat(image.getAttribute("height") || "0");
        const style = getComputedStyle(image);
        const stableAspectRatio =
          style.aspectRatio && style.aspectRatio !== "auto";
        const problems = [];

        if (!image.complete || image.naturalWidth === 0) {
          problems.push("broken");
        }
        if (!(width > 0 && height > 0) && !stableAspectRatio) {
          problems.push("missing width/height or CSS aspect-ratio");
        }

        return {
          alt: image.alt,
          problems,
          src: image.currentSrc || image.src,
        };
      })
      .filter(({ problems }) => problems.length)
      .slice(0, 80);
  });
}

export async function expectHealthyImages(page) {
  const issues = await collectImageIssues(page);

  expect(
    issues,
    "Rendered images must load and reserve stable intrinsic space",
  ).toEqual([]);
}

export async function firstVisibleLocator(page, selector) {
  const candidates = page.locator(selector);
  const count = await candidates.count();

  for (let index = 0; index < count; index += 1) {
    const candidate = candidates.nth(index);
    if (await candidate.isVisible()) {
      return candidate;
    }
  }

  return candidates.first();
}

export async function visibleLocatorCount(locator) {
  let visible = 0;
  const count = await locator.count();

  for (let index = 0; index < count; index += 1) {
    if (await locator.nth(index).isVisible()) {
      visible += 1;
    }
  }

  return visible;
}

export async function controlledPanel(page, control) {
  const tagName = await control.evaluate((element) =>
    element.tagName.toLowerCase(),
  );

  if (tagName === "summary") {
    const details = control.locator("xpath=parent::details");
    await expect(details, "A summary must belong to a details element").toHaveCount(
      1,
    );
    return {
      kind: "details",
      panel: details,
    };
  }

  const id = await control.getAttribute("aria-controls");
  expect(id, "Disclosure buttons need aria-controls").toBeTruthy();
  const panel = page.locator(`[id="${id.replaceAll('"', '\\"')}"]`);
  await expect(panel, `Missing controlled panel #${id}`).toHaveCount(1);

  return {
    kind: "aria",
    panel,
  };
}

export async function exerciseDisclosure(page, control) {
  await expect(control).toBeVisible();
  const target = await controlledPanel(page, control);

  if (target.kind === "details") {
    await expect(target.panel).not.toHaveAttribute("open", "");
    await control.click();
    await expect(target.panel).toHaveAttribute("open", "");
    await control.click();
    await expect(target.panel).not.toHaveAttribute("open", "");
    return target.panel;
  }

  await expect(control).toHaveAttribute("aria-expanded", "false");
  await expect(target.panel).toBeHidden();
  await control.click();
  await expect(control).toHaveAttribute("aria-expanded", "true");
  await expect(target.panel).toBeVisible();
  await control.click();
  await expect(control).toHaveAttribute("aria-expanded", "false");
  await expect(target.panel).toBeHidden();
  return target.panel;
}

export async function namedDisclosure(scope, pattern) {
  const candidates = scope.locator("button, summary").filter({
    hasText: pattern,
  });
  const count = await candidates.count();

  for (let index = 0; index < count; index += 1) {
    if (await candidates.nth(index).isVisible()) {
      return candidates.nth(index);
    }
  }

  return candidates.first();
}
