import { chromium } from "@playwright/test";
import {
  baseURL,
  chromeExecutable,
  representativePages,
  requiredViewports,
} from "../site-fixtures.mjs";
import {
  collectImageIssues,
  gotoFixture,
  installNetworkGuards,
  settleLazyMedia,
} from "../specs/helpers/site.mjs";

const requestedViewport = process.argv[2] || "390x844";
const viewport = requiredViewports.find(
  ({ key }) => key === requestedViewport,
);

if (!viewport) {
  throw new Error(
    `Unknown viewport "${requestedViewport}". Expected one of: ` +
      requiredViewports.map(({ key }) => key).join(", "),
  );
}

const browser = await chromium.launch({
  executablePath: chromeExecutable,
  headless: true,
  args: [
    "--disable-background-networking",
    "--disable-component-update",
    "--disable-default-apps",
    "--disable-extensions",
    "--no-default-browser-check",
    "--no-first-run",
  ],
});

const audit = {
  baseURL,
  generatedAt: new Date().toISOString(),
  pages: [],
  viewport,
};

try {
  for (const fixture of representativePages) {
    const page = await browser.newPage({
      locale: "en-US",
      reducedMotion: "reduce",
      viewport: {
        width: viewport.width,
        height: viewport.height,
      },
    });

    try {
      await installNetworkGuards(page);
      const navigation = await gotoFixture(page, fixture);
      await settleLazyMedia(page);
      const issues = await collectImageIssues(page);

      audit.pages.push({
        fixture: fixture.key,
        issues,
        navigation,
      });
    } finally {
      await page.close();
    }
  }
} finally {
  await browser.close();
}

const issueCount = audit.pages.reduce(
  (total, page) => total + page.issues.length,
  0,
);

audit.issueCount = issueCount;
console.log(JSON.stringify(audit, null, 2));
process.exitCode = issueCount > 0 ? 1 : 0;
