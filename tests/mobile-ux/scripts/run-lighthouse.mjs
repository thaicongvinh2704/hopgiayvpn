import fs from "node:fs/promises";
import path from "node:path";
import lighthouse from "lighthouse";
import { launch } from "chrome-launcher";
import {
  chromeExecutable,
  makeURL,
  repoRoot,
  representativePages,
} from "../site-fixtures.mjs";

const phase = process.argv[2];

if (!["baseline", "after", "qc-pass-1", "qc-pass-2"].includes(phase)) {
  throw new Error(
    "Pass one output phase: baseline, after, qc-pass-1, or qc-pass-2.",
  );
}

const auditKeys = new Set(["home", "category", "product", "article"]);
const selectedPageKeys = new Set(
  (process.env.LIGHTHOUSE_PAGES || "")
    .split(",")
    .map((value) => value.trim())
    .filter(Boolean),
);
const fixtures = representativePages.filter(
  ({ key }) =>
    auditKeys.has(key) &&
    (!selectedPageKeys.size || selectedPageKeys.has(key)),
);
const outputRoot = path.join(
  repoRoot,
  "artifacts",
  "mobile-ux-20260728",
  phase,
  "lighthouse",
);
await fs.mkdir(outputRoot, { recursive: true });

const summaries = [];

for (const fixture of fixtures) {
  const renderedPath = fixture.localFallbackPath || fixture.path;
  const chrome = await launch({
    chromePath: chromeExecutable,
    chromeFlags: [
      "--headless=new",
      "--disable-gpu",
      "--disable-extensions",
      "--no-default-browser-check",
      "--no-first-run",
      "--incognito",
    ],
    logLevel: "silent",
  });

  try {
    const result = await lighthouse(makeURL(renderedPath), {
      port: chrome.port,
      logLevel: "error",
      output: ["json", "html"],
      onlyCategories: ["performance", "accessibility"],
      formFactor: "mobile",
      screenEmulation: {
        mobile: true,
        width: 390,
        height: 844,
        deviceScaleFactor: 1,
        disabled: false,
      },
      throttlingMethod: "simulate",
    });

    if (!result) {
      throw new Error(`Lighthouse returned no result for ${fixture.key}.`);
    }

    const [jsonReport, htmlReport] = result.report;
    const lhr = result.lhr;
    await Promise.all([
      fs.writeFile(
        path.join(outputRoot, `${fixture.key}.report.json`),
        jsonReport,
        "utf8",
      ),
      fs.writeFile(
        path.join(outputRoot, `${fixture.key}.report.html`),
        htmlReport,
        "utf8",
      ),
    ]);

    const summary = {
      phase,
      page: fixture.key,
      requestedPath: fixture.path,
      renderedPath,
      usedLocalFallback: renderedPath !== fixture.path,
      finalURL: lhr.finalDisplayedUrl,
      fetchTime: lhr.fetchTime,
      lighthouseVersion: lhr.lighthouseVersion,
      settings: {
        formFactor: lhr.configSettings.formFactor,
        throttlingMethod: lhr.configSettings.throttlingMethod,
        screenEmulation: lhr.configSettings.screenEmulation,
      },
      scores: {
        performance: Math.round(lhr.categories.performance.score * 100),
        accessibility: Math.round(lhr.categories.accessibility.score * 100),
      },
      metrics: {
        lcpMs: Math.round(
          lhr.audits["largest-contentful-paint"].numericValue,
        ),
        speedIndexMs: Math.round(lhr.audits["speed-index"].numericValue),
        tbtMs: Math.round(lhr.audits["total-blocking-time"].numericValue),
        cls:
          Math.round(
            lhr.audits["cumulative-layout-shift"].numericValue * 1_000,
          ) / 1_000,
      },
    };
    summaries.push(summary);
    process.stdout.write(
      `${phase}: ${fixture.key} performance ${summary.scores.performance}, ` +
        `accessibility ${summary.scores.accessibility}\n`,
    );
  } finally {
    await chrome.kill();
  }
}

await fs.writeFile(
  path.join(outputRoot, "summary.json"),
  `${JSON.stringify(summaries, null, 2)}\n`,
  "utf8",
);
