import fs from "node:fs/promises";
import path from "node:path";
import { chromium } from "@playwright/test";
import { chromeExecutable } from "../site-fixtures.mjs";

const url = process.env.CHRISTMAS_PACKAGING_CATEGORY_URL || "http://localhost/hopgiayvpn/products/christmas-packaging/";
const screenshotDirectory = process.env.CATEGORY_QA_SCREENSHOT_DIR || "";
const expectedTitle = "Custom Christmas Packaging Boxes & Bags | Vietnam";
const expectedDescription = "Custom Christmas paper bags and gift boxes made in Vietnam. Compare structures, papers, printing, inserts, samples and seasonal production planning.";
const viewports = [{ name: "desktop", width: 1440, height: 900 }, { name: "mobile", width: 390, height: 844 }];

if (screenshotDirectory) await fs.mkdir(screenshotDirectory, { recursive: true });
const browser = await chromium.launch({ executablePath: chromeExecutable, headless: true, args: ["--disable-extensions", "--no-first-run"] });
const results = [];

try {
  for (const viewport of viewports) {
    const page = await browser.newPage({ viewport });
    const runtimeErrors = [];
    page.on("pageerror", (error) => runtimeErrors.push(error.message));
    const response = await page.goto(url, { waitUntil: "networkidle", timeout: 60_000 });
    await page.evaluate(async () => {
      const step = Math.max(400, Math.floor(innerHeight * 0.8));
      const maximum = document.documentElement.scrollHeight - innerHeight;
      for (let position = 0; position < maximum; position += step) {
        scrollTo(0, position);
        await new Promise((resolve) => setTimeout(resolve, 25));
      }
      scrollTo(0, maximum);
      await new Promise((resolve) => setTimeout(resolve, 300));
    });
    await page.locator(".christmas-factory-proof").scrollIntoViewIfNeeded();
    await page.waitForFunction(() => { const image = document.querySelector(".christmas-factory-proof img"); return image?.complete && image.naturalWidth > 0; });
    await page.evaluate(() => scrollTo(0, 0));

    const metrics = await page.evaluate(({ expectedTitle, expectedDescription }) => {
      const blocks = Array.from(document.querySelectorAll('script[type="application/ld+json"]'));
      const jsonErrors = [];
      const schemaTypes = [];
      for (const [index, block] of blocks.entries()) {
        try {
          const parsed = JSON.parse(block.textContent || "{}");
          for (const entity of (Array.isArray(parsed?.["@graph"]) ? parsed["@graph"] : [parsed])) {
            const types = Array.isArray(entity?.["@type"]) ? entity["@type"] : [entity?.["@type"]];
            schemaTypes.push(...types.filter(Boolean));
          }
        } catch (error) { jsonErrors.push(`block ${index + 1}: ${error.message}`); }
      }
      const images = Array.from(document.querySelectorAll("main img")).filter((image) => {
        const rect = image.getBoundingClientRect(); const style = getComputedStyle(image);
        return rect.width > 0 && rect.height > 0 && style.display !== "none" && style.visibility !== "hidden";
      });
      const secondaryCta = document.querySelector(".product-category-hero-actions .btn-outline");
      const factoryImage = document.querySelector(".christmas-factory-proof img");
      return {
        titleMatches: document.title === expectedTitle,
        descriptionMatches: document.querySelector('meta[name="description"]')?.getAttribute("content") === expectedDescription,
        h1: document.querySelector("h1")?.textContent?.trim() || "",
        h1Count: document.querySelectorAll("h1").length,
        productCount: document.querySelectorAll("[data-product-card]").length,
        proofPointCount: document.querySelectorAll(".product-category-hero-proof li").length,
        secondaryCtaHref: secondaryCta?.getAttribute("href") || "",
        guideCount: document.querySelectorAll(".christmas-packaging-guide").length,
        formatCardCount: document.querySelectorAll(".christmas-format-card").length,
        factoryProofCount: document.querySelectorAll(".christmas-factory-proof").length,
        factoryImageSource: factoryImage?.currentSrc || factoryImage?.src || "",
        faqCount: document.querySelectorAll(".christmas-packaging-faq details").length,
        genericFaqCount: document.querySelectorAll("#home-faq-title").length,
        internalGuideLinks: Array.from(document.querySelectorAll(".christmas-packaging-guide a[href]")).filter((link) => new URL(link.href).origin === location.origin).length,
        horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
        visibleBrokenImages: images.filter((image) => !image.complete || image.naturalWidth === 0).length,
        jsonLdCount: blocks.length,
        jsonErrors,
        schemaTypes: Array.from(new Set(schemaTypes)).sort(),
      };
    }, { expectedTitle, expectedDescription });

    const failures = [];
    if (response?.status() !== 200) failures.push(`HTTP ${response?.status()}`);
    if (!metrics.titleMatches) failures.push("SEO title");
    if (!metrics.descriptionMatches) failures.push("meta description");
    if (metrics.h1 !== "Custom Christmas Paper Bags & Gift Boxes" || metrics.h1Count !== 1) failures.push("single canonical H1");
    if (metrics.productCount !== 11) failures.push("11-product first-page grid");
    if (metrics.proofPointCount !== 3) failures.push("hero proof points");
    if (!metrics.secondaryCtaHref.endsWith("#christmas-buyer-guide")) failures.push("buyer-guide CTA");
    if (metrics.guideCount !== 1) failures.push("buyer guide");
    if (metrics.formatCardCount !== 9) failures.push("format reference gallery");
    if (metrics.factoryProofCount !== 1 || !metrics.factoryImageSource.includes("anh-nha-may-1")) failures.push("factory evidence");
    if (metrics.faqCount !== 8 || metrics.genericFaqCount !== 0) failures.push("category-specific FAQ");
    if (metrics.internalGuideLinks < 12) failures.push("internal guide links");
    if (metrics.horizontalOverflow) failures.push("horizontal overflow");
    if (metrics.visibleBrokenImages > 0) failures.push("visible broken images");
    if (metrics.jsonErrors.length > 0) failures.push("invalid JSON-LD");
    for (const type of ["CollectionPage", "ItemList", "FAQPage"]) if (!metrics.schemaTypes.includes(type)) failures.push(`${type} schema`);
    if (runtimeErrors.length > 0) failures.push("browser runtime errors");

    if (screenshotDirectory) await page.screenshot({ path: path.join(screenshotDirectory, `christmas-packaging-${viewport.name}.png`), fullPage: true, animations: "disabled" });
    results.push({ viewport, status: response?.status() || 0, failures, runtimeErrors, ...metrics });
    await page.close();
  }
} finally { await browser.close(); }

console.log(JSON.stringify({ url, results }, null, 2));
if (results.some(({ failures }) => failures.length > 0)) process.exitCode = 1;
