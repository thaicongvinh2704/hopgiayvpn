import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';
import { createHash } from 'node:crypto';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const require = createRequire(path.join(root, 'tests/mobile-ux/package.json'));
const { PurgeCSS } = require(process.env.PURGECSS_MODULE || 'purgecss');
const { transform } = require('lightningcss');
const config = require(path.join(root, 'tools/pizza-landing-purgecss.config.cjs'));
const results = await new PurgeCSS().purge(config);
if (results.length !== config.css.length) throw new Error('Both source stylesheets must be loaded before building.');
const combined = results.map(result => result.css).join('\n');
const output = transform({
  filename: 'pizza-landing-shell.css',
  code: Buffer.from(combined),
  minify: true,
}).code;
if (output.length < 1000) throw new Error('Refusing to emit an empty or incomplete shared stylesheet.');
const target = path.join(root, 'wp-content/themes/custom-box-theme/assets/css/pizza-landing-shell.css');
await fs.writeFile(target, output);
const theme = path.join(root, 'wp-content/themes/custom-box-theme');
const sources = {};
// Header/footer/state selectors are safelisted. Rebuild when their shared
// styles, behavior or icon definitions change, rather than on content edits.
for (const file of [...config.css, path.join(theme, 'assets/js/main.js'), path.join(theme, 'assets/vendor/fontawesome/css/all.min.css')]) {
  const relative = path.relative(theme, file).replaceAll('\\', '/');
  // Git checks out CRLF on Windows and LF on Linux hosting.
  const source = (await fs.readFile(file, 'utf8')).replace(/\r\n?/g, '\n');
  sources[relative] = createHash('sha256').update(source).digest('hex');
}
await fs.writeFile(target.replace('.css', '-manifest.json'), JSON.stringify({ sources }, null, 2));
console.log(`pizza-landing-shell.css: ${output.length.toLocaleString()} bytes`);
