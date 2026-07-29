import { expect, test } from "@playwright/test";
import { representativePages } from "../site-fixtures.mjs";
import {
  SELECTORS,
  controlledPanel,
  exerciseDisclosure,
  expectCompactStickyHeader,
  expectExactlyOneH1,
  expectHealthyImages,
  expectNoHorizontalOverflow,
  expectTouchTargets,
  firstVisibleLocator,
  gotoFixture,
  installNetworkGuards,
  isMobileProject,
  namedDisclosure,
  settleLazyMedia,
  viewportWidth,
  visibleLocatorCount,
} from "./helpers/site.mjs";

const fixtureByKey = new Map(
  representativePages.map((fixture) => [fixture.key, fixture]),
);

test.beforeEach(async ({ page }) => {
  await installNetworkGuards(page);
});

async function expectBottomBarContract(page, fixture, testInfo) {
  const bars = page.locator(SELECTORS.bottomBar);
  const visibleBars = await visibleLocatorCount(bars);

  if (!isMobileProject(testInfo) || fixture.key === "contact") {
    expect(
      visibleBars,
      fixture.key === "contact"
        ? "The contact route must not render the shared mobile bottom bar"
        : "The mobile conversion bar must not be visible on desktop",
    ).toBe(0);
    return;
  }

  expect(
    visibleBars,
    `${fixture.key} must show exactly one shared mobile conversion bar`,
  ).toBe(1);
  const bar = await firstVisibleLocator(page, SELECTORS.bottomBar);
  const requestQuote = bar.getByRole("link", { name: /^Request Quote$/i });
  const whatsapp = bar.getByRole("link", { name: /^WhatsApp$/i });
  await expect(requestQuote).toHaveCount(1);
  await expect(requestQuote).toHaveAttribute(
    "href",
    /\/contact\/?#quote$/i,
  );
  await expect(whatsapp).toHaveCount(1);
  await expect(whatsapp).toHaveAttribute(
    "href",
    /^https:\/\/wa\.me\/84933102653\/?$/i,
  );

  const geometry = await bar.evaluate((element) => {
    const rect = element.getBoundingClientRect();
    const style = getComputedStyle(element);
    return {
      bottom: Math.round(innerHeight - rect.bottom),
      height: Math.round(rect.height),
      position: style.position,
    };
  });
  expect(geometry.position).toBe("fixed");
  expect(geometry.bottom).toBeLessThanOrEqual(1);
  expect(geometry.height).toBeGreaterThanOrEqual(56);
  expect(geometry.height).toBeLessThanOrEqual(80);

  await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight));
  await page.waitForTimeout(100);
  const obstruction = await page.evaluate((selector) => {
    const barElement = Array.from(document.querySelectorAll(selector)).find(
      (element) => {
        const rect = element.getBoundingClientRect();
        const style = getComputedStyle(element);
        return (
          style.display !== "none" &&
          style.visibility !== "hidden" &&
          rect.width > 0 &&
          rect.height > 0
        );
      },
    );
    const footerTargets = Array.from(
      document.querySelectorAll("footer a[href], footer button, footer summary"),
    ).filter((element) => {
      const rect = element.getBoundingClientRect();
      const style = getComputedStyle(element);
      return (
        style.display !== "none" &&
        style.visibility !== "hidden" &&
        rect.width > 0 &&
        rect.height > 0
      );
    });
    const lastTarget = footerTargets.at(-1);

    if (!barElement || !lastTarget) {
      return null;
    }

    return {
      barTop: Math.round(barElement.getBoundingClientRect().top),
      targetBottom: Math.round(lastTarget.getBoundingClientRect().bottom),
    };
  }, SELECTORS.bottomBar);
  expect(
    obstruction === null ||
      obstruction.targetBottom <= obstruction.barTop + 1,
    obstruction
      ? `Bottom bar begins at ${obstruction.barTop}px but the last footer ` +
          `target ends at ${obstruction.targetBottom}px`
      : "Footer target obstruction could not be measured",
  ).toBeTruthy();
  await page.evaluate(() => scrollTo(0, 0));
}

for (const fixture of representativePages) {
  test(`${fixture.key} route contract${
    fixture.key === "home" ? " @smoke" : ""
  }`, async ({ page }, testInfo) => {
    await gotoFixture(page, fixture);

    await expectExactlyOneH1(page);
    await expectNoHorizontalOverflow(page, `${fixture.key} initial state`);

    if (isMobileProject(testInfo)) {
      await expectCompactStickyHeader(page);
      await expectTouchTargets(page);
    }

    await settleLazyMedia(page);
    await expectHealthyImages(page);
    await expectNoHorizontalOverflow(page, `${fixture.key} after lazy media`);
    await expectBottomBarContract(page, fixture, testInfo);
  });
}

test.describe("mobile navigation", () => {
  test("drawer traps focus and closes through every required path", async ({
    page,
  }, testInfo) => {
    test.skip(!isMobileProject(testInfo), "The off-canvas drawer is mobile-only");
    await gotoFixture(page, fixtureByKey.get("home"));

    const toggle = await firstVisibleLocator(page, SELECTORS.menuToggle);
    await expect(toggle).toBeVisible();
    await expect(toggle).toHaveAttribute("aria-expanded", "false");
    const drawerId = await toggle.getAttribute("aria-controls");
    expect(drawerId, "Menu trigger needs aria-controls").toBeTruthy();
    const drawer = page.locator(
      `[id="${drawerId.replaceAll('"', '\\"')}"]`,
    );
    await expect(drawer).toHaveCount(1);

    await toggle.focus();
    await toggle.click();
    await expect(toggle).toHaveAttribute("aria-expanded", "true");
    await expect(drawer).toBeVisible();

    const closeButton = drawer.getByRole("button", {
      name: /close (navigation )?menu/i,
    });
    await expect(closeButton).toBeVisible();
    const closeBox = await closeButton.boundingBox();
    expect(closeBox?.width || 0).toBeGreaterThanOrEqual(44);
    expect(closeBox?.height || 0).toBeGreaterThanOrEqual(44);

    const focusEnteredDrawer = await page.evaluate(
      (id) =>
        document.getElementById(id)?.contains(document.activeElement) || false,
      drawerId,
    );
    expect(
      focusEnteredDrawer,
      "Opening the menu must move focus into the drawer",
    ).toBeTruthy();

    const focusables = drawer.locator(
      "a[href]:visible, button:not([disabled]):visible, " +
        "input:not([disabled]):not([type='hidden']):visible, " +
        "select:not([disabled]):visible, textarea:not([disabled]):visible, " +
        "[tabindex]:not([tabindex='-1']):visible",
    );
    expect(await focusables.count()).toBeGreaterThan(1);
    await focusables.last().focus();
    await page.keyboard.press("Tab");
    expect(
      await page.evaluate(
        (id) =>
          document.getElementById(id)?.contains(document.activeElement) || false,
        drawerId,
      ),
      "Tab from the last drawer control must remain in the drawer",
    ).toBeTruthy();
    await focusables.first().focus();
    await page.keyboard.press("Shift+Tab");
    expect(
      await page.evaluate(
        (id) =>
          document.getElementById(id)?.contains(document.activeElement) || false,
        drawerId,
      ),
      "Shift+Tab from the first drawer control must remain in the drawer",
    ).toBeTruthy();

    const scrollLocked = await page.evaluate(() =>
      [document.documentElement, document.body].some((element) =>
        ["hidden", "clip"].includes(getComputedStyle(element).overflowY),
      ),
    );
    expect(scrollLocked, "Opening the drawer must lock background scrolling").toBe(
      true,
    );
    const bottomBar = page.locator(SELECTORS.bottomBar);
    expect(
      await visibleLocatorCount(bottomBar),
      "The shared bottom bar must hide while the drawer is open",
    ).toBe(0);
    await expectNoHorizontalOverflow(page, "open mobile drawer");

    await page.keyboard.press("Escape");
    await expect(toggle).toHaveAttribute("aria-expanded", "false");
    await expect(drawer).toBeHidden();
    await expect(toggle).toBeFocused();

    await toggle.click();
    await expect(drawer).toBeVisible();
    const overlay = await firstVisibleLocator(page, SELECTORS.menuOverlay);
    await expect(overlay).toBeVisible();
    const drawerBox = await drawer.boundingBox();
    expect(drawerBox).not.toBeNull();
    const width = viewportWidth(testInfo);
    const clickX =
      drawerBox.x + drawerBox.width / 2 < width / 2
        ? width - 8
        : 8;
    const clickY = Math.min(
      (testInfo.project.use.viewport?.height || 800) - 8,
      Math.max(80, drawerBox.y + drawerBox.height / 2),
    );
    await page.mouse.click(clickX, clickY);
    await expect(drawer).toBeHidden();
    await expect(toggle).toBeFocused();
  });
});

test.describe("homepage contracts", () => {
  test("keeps one primary quote form and a mobile category View All", async ({
    page,
  }, testInfo) => {
    await gotoFixture(page, fixtureByKey.get("home"));
    await expect(page.locator(SELECTORS.quoteForm)).toHaveCount(1);

    if (!isMobileProject(testInfo)) {
      return;
    }

    const viewAll = page
      .locator("a, button")
      .filter({ hasText: /^View All Packaging Categories$/i });
    await expect(viewAll).toHaveCount(1);
    await expect(viewAll).toBeVisible();

    const cards = page.locator(
      "[data-packaging-category-card], .categories-grid .category-card",
    );
    expect(
      await visibleLocatorCount(cards),
      "Mobile should initially show the six primary packaging categories",
    ).toBe(6);

    const tagName = await viewAll.evaluate((element) =>
      element.tagName.toLowerCase(),
    );
    if (tagName === "a") {
      await expect(viewAll).toHaveAttribute(
        "href",
        /\/products(?:\/|$)/i,
      );
      return;
    }

    const target = await controlledPanel(page, viewAll);
    await expect(viewAll).toHaveAttribute("aria-expanded", "false");
    await viewAll.click();
    await expect(viewAll).toHaveAttribute("aria-expanded", "true");
    await expect(target.panel).toBeVisible();
    expect(await visibleLocatorCount(cards)).toBeGreaterThan(6);
  });

  test("footer groups are semantic mobile disclosures", async ({
    page,
  }, testInfo) => {
    test.skip(!isMobileProject(testInfo), "Footer groups expand on mobile");
    await gotoFixture(page, fixtureByKey.get("home"));
    const footer = page.locator("footer");
    await expect(footer).toBeVisible();

    for (const name of [
      /^Quick Links$/i,
      /^Packaging Categories$/i,
      /^Factory Capabilities$/i,
    ]) {
      const disclosure = await namedDisclosure(footer, name);
      await exerciseDisclosure(page, disclosure);
    }
  });
});

test.describe("archive disclosures", () => {
  test("category sorting is labelled and taxonomy follows products on mobile", async ({
    page,
  }, testInfo) => {
    await gotoFixture(page, fixtureByKey.get("category"));
    const sort = page.locator("select.orderby, select[name='orderby']").first();
    await expect(sort).toBeVisible();
    await expect(sort).toHaveAccessibleName(/sort|order/i);
    expect(
      await sort.evaluate((element) => element.labels?.length || 0),
      "The product sort select needs a real associated label",
    ).toBeGreaterThan(0);

    if (!isMobileProject(testInfo)) {
      return;
    }

    const sortBox = await sort.boundingBox();
    expect(sortBox?.height || 0).toBeGreaterThanOrEqual(48);
    const disclosure = await namedDisclosure(
      page.locator("main"),
      /^Browse Categories$/i,
    );
    const target = await controlledPanel(page, disclosure);
    const firstProduct = page
      .locator(
        "[data-product-card], .product-grid .product-card, " +
          ".products-grid .product-card, ul.products > li.product",
      )
      .first();
    await expect(firstProduct).toBeVisible();
    const productPrecedesTaxonomy = await target.panel.evaluate(
      (panel, productSelector) => {
        const product = document.querySelector(productSelector);
        return Boolean(
          product &&
            product.compareDocumentPosition(panel) &
              Node.DOCUMENT_POSITION_FOLLOWING,
        );
      },
      "[data-product-card], .product-grid .product-card, " +
        ".products-grid .product-card, ul.products > li.product",
    );
    expect(
      productPrecedesTaxonomy,
      "Products must precede the full taxonomy list in mobile DOM order",
    ).toBe(true);
    await exerciseDisclosure(page, disclosure);
  });

  test("blog sidebar groups follow posts and collapse on mobile", async ({
    page,
  }, testInfo) => {
    test.skip(!isMobileProject(testInfo), "Blog sidebar is responsive on mobile");
    await gotoFixture(page, fixtureByKey.get("blog"));
    const main = page.locator("main");
    const firstPost = main
      .locator(".blog-grid article, .blog-card, article.post")
      .first();
    await expect(firstPost).toBeVisible();

    for (const name of [/^Packaging Topics$/i, /^Recent Guides$/i]) {
      const disclosure = await namedDisclosure(main, name);
      const target = await controlledPanel(page, disclosure);
      const postPrecedesPanel = await target.panel.evaluate(
        (panel, postSelector) => {
          const post = document.querySelector(postSelector);
          return Boolean(
            post &&
              post.compareDocumentPosition(panel) &
                Node.DOCUMENT_POSITION_FOLLOWING,
          );
        },
        ".blog-grid article, .blog-card, article.post",
      );
      expect(
        postPrecedesPanel,
        `${name} must follow the article list in mobile DOM order`,
      ).toBe(true);
      await exerciseDisclosure(page, disclosure);
    }
  });
});

test.describe("product gallery", () => {
  test("uses named controls, semantic active state, and never autoplays", async ({
    page,
  }) => {
    await gotoFixture(page, fixtureByKey.get("product"));
    const gallery = page.locator("[data-product-gallery]");
    await expect(gallery).toHaveCount(1);
    const slides = gallery.locator(".product-gallery-slide");
    expect(await slides.count()).toBeGreaterThan(1);

    const previous = gallery.getByRole("button", {
      name: /previous product image/i,
    });
    const next = gallery.getByRole("button", {
      name: /next product image/i,
    });
    await expect(previous).toBeVisible();
    await expect(next).toBeVisible();

    const activeSignature = () =>
      slides.evaluateAll((elements) =>
        elements
          .map((element, index) => ({
            active:
              element.classList.contains("is-active") ||
              (!element.hidden &&
                element.getAttribute("aria-hidden") !== "true" &&
                getComputedStyle(element).display !== "none"),
            index,
          }))
          .filter(({ active }) => active)
          .map(({ index }) => index)
          .join(","),
      );
    const initial = await activeSignature();
    expect(initial).not.toBe("");
    await page.waitForTimeout(3_400);
    expect(
      await activeSignature(),
      "The product gallery must not advance automatically",
    ).toBe(initial);

    await next.click();
    const afterNext = await activeSignature();
    expect(afterNext).not.toBe(initial);
    await previous.focus();
    await page.keyboard.press("Enter");
    expect(await activeSignature()).toBe(initial);

    const thumbs = gallery.locator(".product-gallery-thumb");
    expect(await thumbs.count()).toBeGreaterThan(1);
    const thumbsWithState = await thumbs.evaluateAll((elements) =>
      elements.filter(
        (element) =>
          element.getAttribute("aria-current") === "true" ||
          element.getAttribute("aria-selected") === "true",
      ).length,
    );
    expect(
      thumbsWithState,
      "Exactly one gallery thumbnail must expose its active state",
    ).toBe(1);
  });
});

test.describe("article navigation and tables", () => {
  test("TOC is responsive and tables use labelled keyboard regions", async ({
    page,
  }, testInfo) => {
    await gotoFixture(page, fixtureByKey.get("article"));
    const toc = page.locator("[data-article-toc], .blog-toc").first();
    await expect(toc).toBeVisible();

    if (isMobileProject(testInfo)) {
      const toggle = toc.locator("button, summary").first();
      const box = await toggle.boundingBox();
      expect(box?.height || 0).toBeGreaterThanOrEqual(44);
      const target = await controlledPanel(page, toggle);

      if (target.kind === "details") {
        await expect(target.panel).not.toHaveAttribute("open", "");
      } else {
        await expect(toggle).toHaveAttribute("aria-expanded", "false");
        await expect(target.panel).toBeHidden();
      }

      await toggle.click();
      await expectNoHorizontalOverflow(page, "open article TOC");

      const focusablesInToc = toc.locator(
        "a[href]:visible, button:visible, summary:visible, " +
          "[tabindex]:not([tabindex='-1']):visible",
      );
      await focusablesInToc.first().focus();
      const attempts = (await focusablesInToc.count()) + 2;
      for (let index = 0; index < attempts; index += 1) {
        await page.keyboard.press("Tab");
      }
      expect(
        await toc.evaluate((element) =>
          element.contains(document.activeElement),
        ),
        "The article TOC must not trap keyboard focus",
      ).toBe(false);
    }

    const tables = page.locator(
      "article table, .blog-content table, .article-content table",
    );
    expect(
      await tables.count(),
      "The representative article must exercise the table wrapper",
    ).toBeGreaterThan(0);

    const tableIssues = await tables.evaluateAll((elements, regionSelector) =>
      elements.map((table, index) => {
        const region = table.closest(regionSelector);
        const style = region ? getComputedStyle(region) : null;
        const labelledBy = region?.getAttribute("aria-labelledby");
        const accessibleName =
          region?.getAttribute("aria-label") ||
          (labelledBy
            ? document.getElementById(labelledBy)?.textContent?.trim()
            : "");
        return {
          accessibleName,
          index,
          overflowX: style?.overflowX || "",
          regionFound: Boolean(region),
          tabIndex: region?.getAttribute("tabindex") || "",
          tableFontSize: Number.parseFloat(getComputedStyle(table).fontSize),
        };
      }),
      SELECTORS.tableRegion,
    );
    expect(
      tableIssues.filter(
        ({ accessibleName, overflowX, regionFound, tabIndex, tableFontSize }) =>
          !regionFound ||
          tabIndex !== "0" ||
          !accessibleName ||
          !["auto", "scroll"].includes(overflowX) ||
          tableFontSize < 14,
      ),
      "Each article table needs a named tabindex=0 horizontal-scroll region " +
        "with table text at least 14px",
    ).toEqual([]);
  });
});

test.describe("quote form", () => {
  test("all visible controls have visible labels and semantic field groups", async ({
    page,
  }, testInfo) => {
    await gotoFixture(page, fixtureByKey.get("contact"));
    const form = page.locator(SELECTORS.quoteForm);
    await expect(form).toHaveCount(1);
    let optionalDisclosure = null;
    let optionalTarget = null;

    if (isMobileProject(testInfo)) {
      optionalDisclosure = await namedDisclosure(
        form,
        /More packaging specifications.*optional/i,
      );
      await expect(optionalDisclosure).toBeVisible();
      optionalTarget = await controlledPanel(page, optionalDisclosure);

      if (optionalTarget.kind === "details") {
        await expect(optionalTarget.panel).not.toHaveAttribute("open", "");
        await optionalDisclosure.click();
        await expect(optionalTarget.panel).toHaveAttribute("open", "");
      } else {
        await expect(optionalDisclosure).toHaveAttribute(
          "aria-expanded",
          "false",
        );
        await expect(optionalTarget.panel).toBeHidden();
        await optionalDisclosure.click();
        await expect(optionalDisclosure).toHaveAttribute(
          "aria-expanded",
          "true",
        );
        await expect(optionalTarget.panel).toBeVisible();
      }
    }

    const semantics = await form.evaluate((element) => {
      const rendered = (node) => {
        const style = getComputedStyle(node);
        const rect = node.getBoundingClientRect();
        return (
          style.display !== "none" &&
          style.visibility !== "hidden" &&
          rect.width > 0 &&
          rect.height > 0
        );
      };
      const visibleLabel = (label) => {
        if (!rendered(label)) {
          return false;
        }
        const rect = label.getBoundingClientRect();
        const style = getComputedStyle(label);
        return (
          rect.width > 1 &&
          rect.height > 1 &&
          style.clipPath === "none" &&
          (style.clip === "auto" || style.clip === "rect(auto, auto, auto, auto)")
        );
      };
      const controls = Array.from(
        element.querySelectorAll(
          "input:not([type='hidden']):not([type='submit']):not([type='button']), " +
            "select, textarea",
        ),
      ).filter(rendered);
      const labelIssues = controls
        .map((control) => {
          const labels = Array.from(control.labels || []).filter(visibleLabel);
          const wrappingLabel = control.closest("label");
          if (wrappingLabel && visibleLabel(wrappingLabel)) {
            labels.push(wrappingLabel);
          }
          const uniqueLabels = Array.from(new Set(labels)).filter((label) =>
            label.textContent?.trim(),
          );
          return {
            id: control.id || "",
            name: control.getAttribute("name") || "",
            tag: control.tagName.toLowerCase(),
            type: control.getAttribute("type") || "",
            visibleLabelCount: uniqueLabels.length,
          };
        })
        .filter(({ visibleLabelCount }) => visibleLabelCount === 0);
      const fieldsets = Array.from(element.querySelectorAll("fieldset"));
      const legends = fieldsets.map(
        (fieldset) =>
          fieldset.querySelector(":scope > legend")?.textContent?.trim() || "",
      );
      const ungroupedControls = controls
        .filter((control) => !control.closest("fieldset"))
        .map((control) => control.getAttribute("name") || control.id);

      return {
        fieldsetCount: fieldsets.length,
        labelIssues,
        legends,
        ungroupedControls,
      };
    });

    expect(
      semantics.labelIssues,
      "Every visible form control, including selects and file inputs, " +
        "needs a visible label",
    ).toEqual([]);
    expect(semantics.ungroupedControls).toEqual([]);
    expect(semantics.fieldsetCount).toBeGreaterThanOrEqual(5);
    for (const expectedLegend of [
      /project basics/i,
      /size and quantity/i,
      /material|printing|finishing/i,
      /contact details/i,
      /artwork|message/i,
    ]) {
      expect(semantics.legends.some((legend) => expectedLegend.test(legend))).toBe(
        true,
      );
    }

    if (optionalDisclosure && optionalTarget) {
      await optionalDisclosure.click();
      if (optionalTarget.kind === "details") {
        await expect(optionalTarget.panel).not.toHaveAttribute("open", "");
      } else {
        await expect(optionalDisclosure).toHaveAttribute(
          "aria-expanded",
          "false",
        );
        await expect(optionalTarget.panel).toBeHidden();
      }
    }
  });

  test("invalid submission exposes associated inline errors and focused summary", async ({
    page,
  }, testInfo) => {
    test.skip(
      testInfo.project.name !== "390x844",
      "Validation interaction has one canonical mobile viewport",
    );
    await gotoFixture(page, fixtureByKey.get("contact"));
    const form = page.locator(SELECTORS.quoteForm);
    const submissions = [];
    page.on("request", (request) => {
      if (!["GET", "HEAD"].includes(request.method())) {
        submissions.push(request.url());
      }
    });

    await form
      .getByRole("button", { name: /submit quote|request quote|send/i })
      .click();
    await page.waitForTimeout(250);

    const invalidControls = form.locator("[aria-invalid='true']");
    expect(
      await invalidControls.count(),
      "Empty submission must mark invalid controls with aria-invalid",
    ).toBeGreaterThan(0);
    const associationIssues = await invalidControls.evaluateAll((controls) =>
      controls
        .map((control) => ({
          describedBy: control.getAttribute("aria-describedby") || "",
          id: control.id,
        }))
        .filter(({ describedBy }) => {
          const ids = describedBy.split(/\s+/).filter(Boolean);
          return (
            !ids.length ||
            !ids.some((id) => {
              const message = document.getElementById(id);
              if (!message?.textContent?.trim()) {
                return false;
              }
              const style = getComputedStyle(message);
              const rect = message.getBoundingClientRect();
              return (
                style.display !== "none" &&
                style.visibility !== "hidden" &&
                rect.width > 0 &&
                rect.height > 0
              );
            })
          );
        }),
    );
    expect(
      associationIssues,
      "Each invalid control needs a visible aria-describedby error",
    ).toEqual([]);

    const summary = page
      .locator(
        "[data-form-error-summary], .form-error-summary, " +
          `:is(${SELECTORS.quoteForm}) [role='alert']`,
      )
      .first();
    await expect(summary).toBeVisible();
    const usefulFocus = await page.evaluate((formSelector) => {
      const active = document.activeElement;
      const formElement = document.querySelector(formSelector);
      return Boolean(
        active &&
          (active.matches("[data-form-error-summary], .form-error-summary") ||
            active.getAttribute("role") === "alert" ||
            (formElement?.contains(active) &&
              active.getAttribute("aria-invalid") === "true")),
      );
    }, SELECTORS.quoteForm);
    expect(
      usefulFocus,
      "Validation must focus the summary or first invalid control",
    ).toBe(true);
    expect(
      submissions,
      "Client validation must not submit an invalid form",
    ).toEqual([]);
  });

  test("rapid programmatic submits create only one network submission", async ({
    page,
  }, testInfo) => {
    test.skip(
      testInfo.project.name !== "390x844",
      "Duplicate-submit behavior has one canonical mobile viewport",
    );
    await gotoFixture(page, fixtureByKey.get("contact"));
    const form = page.locator(SELECTORS.quoteForm);
    const submissions = [];
    page.on("request", (request) => {
      if (!["GET", "HEAD"].includes(request.method())) {
        submissions.push({
          method: request.method(),
          url: request.url(),
        });
      }
    });

    await form.locator("[name='product_name']").fill("Custom rigid box");
    await form.locator("[name='full_name']").fill("Mobile UX Test");
    await form.locator("[name='email']").fill("mobile-test@example.com");
    await page.evaluate(() => {
      window.grecaptcha = {
        ready(callback) {
          callback();
        },
        execute() {
          return Promise.resolve("playwright-recaptcha-token");
        },
      };
    });

    await form.evaluate((element) => {
      element.requestSubmit();
      element.requestSubmit();
    });
    await expect
      .poll(() => submissions.length, {
        message: "A duplicate submit guard must allow only one form request",
        timeout: 5_000,
      })
      .toBe(1);
  });
});
