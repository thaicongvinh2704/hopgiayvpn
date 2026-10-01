"""Create the scoped deploy archive and manifest; retain unrelated working edits."""
from pathlib import Path
import subprocess
import json
import hashlib
import zipfile
import shutil

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / 'artifacts/christmas-gift-boxes-20261001'
OUT.mkdir(parents=True, exist_ok=True)
INC = Path('wp-content/themes/custom-box-theme/inc')

# Only this release's loader registration and multi-product edit matching are
# carried into the release; other in-progress loader edits stay in the worktree.
loader = str(INC / 'post-sync-loader.php').replace('\\', '/')
base = subprocess.check_output(['git', 'show', 'HEAD:' + loader], cwd=ROOT).decode('utf-8')
work = (ROOT / loader).read_text(encoding='utf-8')
entry_start = work.index("        'inc/christmas-gift-boxes-20261001-product-sync.php' => array(")
entry_end = work.index("        'inc/advent-calendar", entry_start)
entry = work[entry_start:entry_end]
if 'inc/christmas-gift-boxes-20261001-product-sync.php' not in base:
    base = base.replace('    return array(\n', '    return array(\n' + entry, 1)
base = base.replace("if ($slug === $entry['slug'])", "if (in_array($slug, $entry['slugs'] ?? array($entry['slug']), true))")
base = base.replace("($requested_slug && $requested_slug === $entry['slug'])", "($requested_slug && in_array($requested_slug, $entry['slugs'] ?? array($entry['slug']), true))")
(OUT / 'post-sync-loader.scoped.php').write_text(base, encoding='utf-8')

paths = [
    INC / 'admin-product-sample-deploy.php', INC / 'post-sync-loader.php',
    INC / 'christmas-gift-boxes-20261001-support.php',
    INC / 'christmas-gift-boxes-20261001-product-sync.php',
    INC / 'product-sample-deploy-tools/import-christmas-gift-boxes-20261001.php',
    INC / 'product-sample-deploy-tools/verify-christmas-gift-boxes-20261001.php',
    INC / 'product-sample-deploy-tools/deploy-product-samples-all.php',
    Path('tools/build-product-sample-deploy-assets.php'), Path('tools/deploy-product-samples-all.php'),
    Path('tools/import-christmas-gift-boxes-20261001.php'), Path('tools/verify-christmas-gift-boxes-20261001.php'),
    Path('tools/prepare-christmas-gift-boxes-20261001.py'), Path('tools/register-christmas-gift-boxes-20261001.py'),
    Path('tools/build-christmas-gift-boxes-20261001-release.py'), Path('tools/test-christmas-gift-boxes-pull-sync.php'),
    Path('tests/mobile-ux/scripts/qa-christmas-gift-boxes-20261001.mjs'),
    Path('docs/christmas-gift-boxes-20261001-release.md'), Path('docs/christmas-gift-boxes-20261001-keyword-eeat-audit.md'),
]
paths += [p.relative_to(ROOT) for p in (ROOT / INC / 'product-content/christmas-gift-boxes-20261001').glob('*')]
paths += [p.relative_to(ROOT) for p in (ROOT / INC / 'product-sample-deploy-assets/uploads/2026/10').glob('*.webp')]
paths = sorted(set(p.as_posix() for p in paths))
manifest = []
with zipfile.ZipFile(OUT / 'christmas-gift-boxes-20261001-deploy.zip', 'w', zipfile.ZIP_DEFLATED) as archive:
    for path in paths:
        blob = base.encode('utf-8') if path == loader else (ROOT / path).read_bytes()
        archive.writestr(path, blob)
        manifest.append(dict(path=path, bytes=len(blob), sha256=hashlib.sha256(blob).hexdigest()))
(OUT / 'release-manifest.json').write_text(json.dumps(dict(release='2026-10-01-christmas-v2', files=manifest), indent=2), encoding='utf-8')
(OUT / 'commit-paths.txt').write_text('\n'.join(p for p in paths if p != loader)+'\n', encoding='utf-8')
for name in ['frontend-qa.json', 'mobile-top.png', 'mobile-description.png', 'mobile-factory.png', 'desktop.png']:
    shutil.copy2(ROOT / 'tmp/christmas-20261001' / name, OUT / name)
payload = json.loads((ROOT / INC / 'product-content/christmas-gift-boxes-20261001/products.json').read_text(encoding='utf-8'))
review = ''
for row in payload:
    review += '\n# ' + row['title'] + '\n\nKeyword: ' + row['keyword'] + '\nMeta: ' + row['seo_description'] + '\n\n' + row['short'] + '\n\n'
    review += (ROOT / INC / 'product-content/christmas-gift-boxes-20261001' / row['content_file']).read_text(encoding='utf-8')
(OUT / 'content-review.md').write_text(review, encoding='utf-8')
print('Release packaged:', len(paths), 'files; loader scoped to this release only.')
