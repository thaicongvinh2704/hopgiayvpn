import fs from "node:fs/promises";
import path from "node:path";
import sharp from "sharp";
import { repoRoot } from "../site-fixtures.mjs";

const imageRoot = path.join(
  repoRoot,
  "wp-content",
  "themes",
  "custom-box-theme",
  "assets",
  "images",
);

const factoryImages = [
  ["anh-nha-may-1.jpg", "anh-nha-may-1.webp"],
  ["anh-nha-may-2.png", "anh-nha-may-2.webp"],
  ["anh-nha-may-3.png", "anh-nha-may-3.webp"],
];

for (const [source, destination] of factoryImages) {
  await sharp(path.join(imageRoot, source))
    .resize({ width: 1_280, withoutEnlargement: true })
    .webp({ quality: 78, effort: 5 })
    .toFile(path.join(imageRoot, destination));
}

await sharp(path.join(imageRoot, "anh-nha-may-fly.png"))
  .resize({ width: 1_280, withoutEnlargement: true })
  .webp({ quality: 78, effort: 5 })
  .toFile(path.join(imageRoot, "anh-nha-may-fly.webp"));

await sharp(path.join(imageRoot, "logo-hop-giay-vpn-hcm.png"))
  .resize({ width: 320, withoutEnlargement: true })
  .webp({ quality: 82, alphaQuality: 90, effort: 5 })
  .toFile(path.join(imageRoot, "logo-hop-giay-vpn-hcm.webp"));

await sharp(path.join(imageRoot, "product-banner1.webp"))
  .resize({ width: 480, withoutEnlargement: true })
  .webp({ quality: 82, effort: 5 })
  .toFile(path.join(imageRoot, "product-banner1-mobile.webp"));

const clientLogoRoot = path.join(imageRoot, "client-logos");
const clientLogos = (await fs.readdir(clientLogoRoot)).filter((file) =>
  file.toLowerCase().endsWith(".png"),
);

for (const source of clientLogos) {
  const destination = `${path.parse(source).name}.webp`;
  await sharp(path.join(clientLogoRoot, source))
    .resize({
      width: 320,
      height: 320,
      fit: "inside",
      withoutEnlargement: true,
    })
    .webp({ quality: 82, alphaQuality: 90, effort: 5 })
    .toFile(path.join(clientLogoRoot, destination));
}

process.stdout.write(
  `Optimized ${factoryImages.length + clientLogos.length + 3} theme images.\n`,
);
