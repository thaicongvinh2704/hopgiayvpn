import path from "node:path";
import { defineConfig } from "@playwright/test";
import {
  baseURL,
  chromeExecutable,
  repoRoot,
  requiredViewports,
} from "./site-fixtures.mjs";

const artifactPhase = (process.env.UX_ARTIFACT_PHASE || "")
  .trim()
  .replace(/[^a-z0-9-]+/gi, "-");
const artifactRoot = path.join(
  repoRoot,
  "artifacts",
  "mobile-ux-20260728",
  "playwright",
  ...(artifactPhase ? [artifactPhase] : []),
);
const requestedWorkers = Number.parseInt(
  process.env.UX_TEST_WORKERS || "1",
  10,
);

export default defineConfig({
  testDir: "./specs",
  testMatch: "**/*.spec.mjs",
  timeout: 60_000,
  expect: {
    timeout: 8_000,
  },
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 1 : 0,
  workers:
    Number.isFinite(requestedWorkers) && requestedWorkers > 0
      ? requestedWorkers
      : 1,
  outputDir: path.join(artifactRoot, "results"),
  reporter: [
    ["line"],
    [
      "html",
      {
        open: "never",
        outputFolder: path.join(artifactRoot, "report"),
      },
    ],
  ],
  use: {
    baseURL,
    headless: true,
    locale: "en-US",
    reducedMotion: "reduce",
    serviceWorkers: "block",
    ignoreHTTPSErrors: true,
    actionTimeout: 10_000,
    navigationTimeout: 45_000,
    screenshot: "only-on-failure",
    trace: "retain-on-failure",
    launchOptions: {
      executablePath: chromeExecutable,
      args: [
        "--disable-background-networking",
        "--disable-component-update",
        "--disable-default-apps",
        "--disable-extensions",
        "--disable-features=Translate,MediaRouter",
        "--no-default-browser-check",
        "--no-first-run",
      ],
    },
  },
  projects: requiredViewports.map((viewport) => ({
    name: viewport.key,
    use: {
      viewport: {
        width: viewport.width,
        height: viewport.height,
      },
      deviceScaleFactor: 1,
    },
  })),
});
