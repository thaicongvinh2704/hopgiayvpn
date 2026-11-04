"""Build explicit-format static assets. Never recompress featured photographs."""
from pathlib import Path
import json, hashlib, shutil
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'wp-content/themes/custom-box-theme/assets/images/performance-20261103'
OUT.mkdir(parents=True, exist_ok=True)
inventory = json.loads((ROOT/'outputs/performance-20261103/public-featured-inventory.json').read_text('utf-8'))
manifest = {'version': 1, 'featured': {}, 'workshop': {}}
for item in inventory['items']:
    source = Path(inventory['basedir'])/item['file']
    digest = hashlib.sha256(source.read_bytes()).hexdigest()
    filename = digest + source.suffix.lower()
    destination = OUT/filename
    if not destination.exists():
        shutil.copyfile(source, destination)
    manifest['featured'][item['file']] = filename

factory = ROOT/'wp-content/themes/custom-box-theme/assets/images/supplier-factory-20261015'
for name in ('offset-printing', 'paper-cutting', 'carton-storage'):
    filename = f'vpn-factory-{name}-20261009.webp'
    source = factory/filename
    manifest['workshop'][filename] = {}
    with Image.open(source) as im:
        for width in (480, 800):
            resized = im.resize((width, round(im.height*width/im.width)), Image.Resampling.LANCZOS)
            target = OUT/f'{source.stem}-{width}.webp'
            resized.save(target, 'WEBP', quality=92, method=6)
            # The original WebP is already compressed. A smaller dimension is
            # not automatically a smaller download; retain the original then.
            if target.stat().st_size >= source.stat().st_size:
                target.unlink()
                continue
            manifest['workshop'][filename][str(width)] = target.name

(OUT/'manifest.json').write_text(json.dumps(manifest, indent=2), 'utf-8')
print(json.dumps({'featured_mappings':len(manifest['featured']), 'unique_files':len(list(OUT.glob('*.*'))), 'total_bytes':sum(p.stat().st_size for p in OUT.iterdir() if p.is_file())}))
