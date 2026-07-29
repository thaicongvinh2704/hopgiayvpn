import path from "node:path";
import { fileURLToPath } from "node:url";

export const repoRoot = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  "../..",
);

export const baseURL =
  process.env.SITE_URL || "http://localhost/website-backup";

export const chromeExecutable =
  process.env.CHROME_PATH ||
  "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";

export const representativePages = [
  { key: "home", path: "/" },
  {
    key: "category",
    path: "/products/home-lifestyle-packaging/",
  },
  {
    key: "product",
    path: "/product/custom-corrugated-subscription-box/",
  },
  { key: "blog", path: "/blog/" },
  {
    key: "article",
    path: "/how-much-does-a-cardboard-box-weigh/",
    localFallbackPath: "/how-to-make-paper-bags-stronger/",
  },
  { key: "contact", path: "/contact/#quote" },
];

export const requiredViewports = [
  { key: "320x568", width: 320, height: 568 },
  { key: "360x800", width: 360, height: 800 },
  { key: "390x844", width: 390, height: 844 },
  { key: "412x915", width: 412, height: 915 },
  { key: "768x1024", width: 768, height: 1024 },
  { key: "1366x768", width: 1366, height: 768 },
  { key: "1440x900", width: 1440, height: 900 },
];

export function makeURL(pathname) {
  const normalizedBase = `${baseURL.replace(/\/$/, "")}/`;
  return new URL(pathname.replace(/^\/+/, ""), normalizedBase).toString();
}
