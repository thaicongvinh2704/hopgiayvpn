import fs from "node:fs/promises";
import path from "node:path";
import { chromium } from "@playwright/test";
import { chromeExecutable } from "../site-fixtures.mjs";

const url =
  process.env.CORRUGATED_MAILER_CATEGORY_URL ||
  "http://localhost/hopgiayvpn/products/corrugated-mailer-boxes/";
const screenshotDirectory = process.env.CATEGORY_QA_SCREENSHOT_DIR || "";
const expectedTitle =
  "Custom Corrugated Mailer Boxes | Vietnam Manufacturer";
const expectedDescription =
  "Custom corrugated mailer boxes made to size with E- or B-flute, inside/outside printing and inserts. Compare options and request a factory quote.";
const viewports = [
  { name: "desktop", width: 1440, height: 900 },
  { name: "mobile", width: 390, height: 844 },
];

if (screenshotDirectory) {
  await fs.mkdir(screenshotDirectory, { recursive: true });
}

const browser = await chromium.launch({
  executablePath: chromeExecutable,
  headless: true,
  args: ["--disable-extensions", "--no-first-run"],
});
const results = [];

try {
  for (const viewport of viewports) {
    const page = await browser.newPage({ viewport });
    const runtimeErrors = [];
    page.on("pageerror", (error) => runtimeErrors.push(error.message));

    const response = await page.goto(url, {
      waitUntil: "networkidle",
      timeout: 60_000,
    });

    await page.evaluate(async () => {
      const step = Math.max(400, Math.floor(innerHeight * 0.8));
      const maximum = document.documentElement.scrollHeight - innerHeight;

      for (let position = 0; position < maximum; position += step) {
        scrollTo(0, position);
        await new Promise((resolve) => setTimeout(resolve, 30));
      }

      scrollTo(0, maximum);
      await new Promise((resolve) => setTimeout(resolve, 300));
      scrollTo(0, 0);
    });

    const metrics = await page.evaluate(
      ({ expectedTitleValue, expectedDescriptionValue }) => {
        const jsonLdBlocks = Array.from(
          document.querySelectorAll('script[type="application/ld+json"]'),
        );
        const jsonErrors = [];
        const schemaTypes = [];

        for (const [index, block] of jsonLdBlocks.entries()) {
          try {
            const parsed = JSON.parse(block.textContent || "{}");
            const entities = Array.isArray(parsed?.["@graph"])
              ? parsed["@graph"]
              : [parsed];

            for (const entity of entities) {
              const types = Array.isArray(entity?.["@type"])
                ? entity["@type"]
                : [entity?.["@type"]];
              schemaTypes.push(...types.filter(Boolean));
            }
          } catch (error) {
            jsonErrors.push(`block ${index + 1}: ${error.message}`);
          }
        }

        const heroImage = document.querySelector(
          ".product-category-hero-image img",
        );
        const visibleImages = Array.from(
          document.querySelectorAll("main img"),
        ).filter((image) => {
          const rect = image.getBoundingClientRect();
          const style = getComputedStyle(image);
          return (
            rect.width > 0 &&
            rect.height > 0 &&
            style.display !== "none" &&
            style.visibility !== "hidden"
          );
        });

        return {
          title: document.title,
          titleMatches: document.title === expectedTitleValue,
          description:
            document
              .querySelector('meta[name="description"]')
              ?.getAttribute("content") || "",
          descriptionMatches:
            document
              .querySelector('meta[name="description"]')
              ?.getAttribute("content") === expectedDescriptionValue,
          canonical:
            document.querySelector('link[rel="canonical"]')?.href || "",
          h1: document.querySelector("h1")?.textContent?.trim() || "",
          h1Count: document.querySelectorAll("h1").length,
          guideCount: document.querySelectorAll(
            ".corrugated-mailer-guide",
          ).length,
          faqCount: document.querySelectorAll(
            ".corrugated-mailer-faq details",
          ).length,
          genericFaqCount: document.querySelectorAll("#home-faq-title").length,
          heroAlt: heroImage?.alt || "",
          horizontalOverflow:
            document.documentElement.scrollWidth >
            document.documentElement.clientWidth + 1,
          visibleBrokenImages: visibleImages.filter(
            (image) => !image.complete || image.naturalWidth === 0,
          ).length,
          visibleBrokenImageSources: visibleImages
            .filter((image) => !image.complete || image.naturalWidth === 0)
            .map((image) => image.currentSrc || image.src)
            .slice(0, 20),
          jsonLdCount: jsonLdBlocks.length,
          jsonErrors,
          schemaTypes: Array.from(new Set(schemaTypes)).sort(),
        };
      },
      {
        expectedTitleValue: expectedTitle,
        expectedDescriptionValue: expectedDescription,
      },
    );

    const failures = [];
    if (response?.status() !== 200) failures.push(`HTTP ${response?.status()}`);
    if (!metrics.titleMatches) failures.push("SEO title");
    if (!metrics.descriptionMatches) failures.push("meta description");
    if (metrics.h1 !== "Custom Corrugated Mailer Boxes" || metrics.h1Count !== 1) {
      failures.push("single canonical H1");
    }
    if (metrics.guideCount !== 1) failures.push("buyer guide");
    if (metrics.faqCount !== 6 || metrics.genericFaqCount !== 0) {
      failures.push("category-specific FAQ");
    }
    if (!metrics.heroAlt.toLowerCase().includes("corrugated mailer box")) {
      failures.push("hero alt text");
    }
    if (metrics.horizontalOverflow) failures.push("horizontal overflow");
    if (metrics.visibleBrokenImages > 0) failures.push("visible broken images");
    if (metrics.jsonErrors.length > 0) failures.push("invalid JSON-LD");
    for (const type of ["CollectionPage", "ItemList", "FAQPage"]) {
      if (!metrics.schemaTypes.includes(type)) failures.push(`${type} schema`);
    }
    if (runtimeErrors.length > 0) failures.push("browser runtime errors");

    if (screenshotDirectory) {
      await page.screenshot({
        path: path.join(
          screenshotDirectory,
          `corrugated-mailer-${viewport.name}.png`,
        ),
        fullPage: true,
        animations: "disabled",
      });
    }

    results.push({
      viewport,
      status: response?.status() || 0,
      failures,
      runtimeErrors,
      ...metrics,
    });
    await page.close();
  }
} finally {
  await browser.close();
}

console.log(JSON.stringify({ url, results }, null, 2));

if (results.some(({ failures }) => failures.length > 0)) {
  process.exitCode = 1;
}
