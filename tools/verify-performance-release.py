"""Offline integrity check for the deployable public image bundle. No WordPress/database needed."""
from pathlib import Path
import hashlib,json
ROOT=Path(__file__).resolve().parents[1]
ASSETS=ROOT/'wp-content/themes/custom-box-theme/assets/images/performance-20261103'
manifest=json.loads((ASSETS/'manifest.json').read_text('utf-8'))
errors=[]; names=set(manifest['featured'].values())
for relative,name in manifest['featured'].items():
    path=ASSETS/name
    if '..' in relative or Path(name).name!=name or not path.is_file():errors.append('Invalid or missing public image: '+relative);continue
    if hashlib.sha256(path.read_bytes()).hexdigest()!=path.stem:errors.append('Hash mismatch: '+name)
for original,variants in manifest['workshop'].items():
    source=ROOT/'wp-content/themes/custom-box-theme/assets/images/supplier-factory-20261015'/original
    for width,name in variants.items():
        names.add(name);path=ASSETS/name
        if not path.is_file() or int(width)>=1280:errors.append('Invalid responsive variant: '+name)
        elif path.stat().st_size>=source.stat().st_size:errors.append('Variant larger than original: '+name)
rules=(ASSETS/'.htaccess').read_text('utf-8')
if 'immutable' not in rules:errors.append('Missing content-addressed cache rules')
expected=names|{'manifest.json','.htaccess'}
if {p.name for p in ASSETS.iterdir() if p.is_file()}!=expected:errors.append('Unexpected files in public image bundle')
print(json.dumps({'public_image_mappings':len(manifest['featured']),'image_files':len(names),'bytes':sum((ASSETS/name).stat().st_size for name in names),'errors':errors},indent=2))
raise SystemExit(bool(errors))
