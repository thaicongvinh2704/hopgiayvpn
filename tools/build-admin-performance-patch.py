"""Package only the reviewed theme files, with baseline/current hashes; no config or test data."""
from pathlib import Path
import hashlib
import json
import subprocess
import zipfile

root = Path(__file__).resolve().parents[1]
theme = 'wp-content/themes/custom-box-theme/'
names = [
    'functions.php', 'inc/admin-maintenance.php', 'inc/admin-product-sample-deploy.php',
    'inc/post-sync-loader.php', 'inc/search-indexing-health.php',
    'inc/custom-vial-box-product-sync.php', 'inc/corrugated-mailer-boxes-category.php',
    'inc/folding-cartons-vietnam-category.php', 'inc/rigid-box-manufacturer-vietnam-category.php',
    'inc/pizza-boxes-category.php', 'inc/halloween-packaging-category.php',
    'inc/christmas-packaging-category.php',
]
output = root / 'artifacts/admin-performance'
output.mkdir(parents=True, exist_ok=True)
package = output / 'admin-performance-patch-2026-10-07.zip'
manifest = {}
with zipfile.ZipFile(package, 'w', compression=zipfile.ZIP_DEFLATED) as archive:
    for name in names:
        path = theme + name
        data = (root / path).read_bytes()
        baseline = subprocess.run(['git', 'show', 'HEAD:' + path], cwd=root, capture_output=True)
        manifest[path] = {
            'sha256': hashlib.sha256(data).hexdigest(),
            'baseline_head_sha256': hashlib.sha256(baseline.stdout).hexdigest() if baseline.returncode == 0 else None,
        }
        info = zipfile.ZipInfo(path, date_time=(2026, 10, 7, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        archive.writestr(info, data)
    archive.write(root / 'docs/admin-performance.md', 'README-admin-performance.md')
with zipfile.ZipFile(package) as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == len(names) + 1
    for path in manifest:
        assert archive.read(path) == (root / path).read_bytes()
digest = hashlib.sha256(package.read_bytes()).hexdigest()
(output / 'manifest.json').write_text(json.dumps({
    'package': package.name, 'zip_sha256': digest, 'theme_files': manifest,
    'scope': 'Shared WordPress admin theme maintenance; not a plugin installer; not deployed to hosting',
}, indent=2) + '\n', encoding='utf-8')
print(json.dumps({'package': str(package), 'theme_files': len(names), 'bytes': package.stat().st_size, 'sha256': digest}))
