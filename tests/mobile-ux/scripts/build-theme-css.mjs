import fs from "node:fs/promises";
import path from "node:path";
import { transform } from "lightningcss";
import { repoRoot } from "../site-fixtures.mjs";

const cssRoot = path.join(
  repoRoot,
  "wp-content",
  "themes",
  "custom-box-theme",
  "assets",
  "css",
);
const sourceFiles = ["main.css", "responsive.css", "woocommerce.css"];

for (const sourceFile of sourceFiles) {
  const sourcePath = path.join(cssRoot, sourceFile);
  const destinationPath = path.join(
    cssRoot,
    sourceFile.replace(/\.css$/i, ".min.css"),
  );
  const source = await fs.readFile(sourcePath);
  const result = transform({
    filename: sourcePath,
    code: source,
    minify: true,
    sourceMap: false,
  });
  await fs.writeFile(destinationPath, result.code);
  process.stdout.write(
    `${sourceFile}: ${source.length} -> ${result.code.length} bytes\n`,
  );
}
