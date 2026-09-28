"""Build small Font Awesome subsets for the pizza landing page's shared UI.

Requires fonttools and brotli. Original Font Awesome copyright and license
metadata are retained; glyph outlines and icon class names are unchanged.
"""
from pathlib import Path
import re
from fontTools import subset

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / 'wp-content/themes/custom-box-theme'
VENDOR = THEME / 'assets/vendor/fontawesome'
css = (VENDOR / 'css/all.min.css').read_text(encoding='utf-8')
used = set()
for extension in ('*.php', '*.js'):
    for source in THEME.rglob(extension):
        if any(part in {'vendor', 'post-content', 'product-content', '_antigravity-product-import-work'} for part in source.parts):
            continue
        used.update(re.findall(r'fa-[a-z0-9-]+', source.read_text(encoding='utf-8', errors='ignore')))

codes = set()
icon_rule = re.compile(r'(\.fa-[^{}]+)\{content:"\\([a-f0-9]+)"\}')
def retain_icon(match):
    classes = set(re.findall(r'fa-[a-z0-9-]+', match.group(1)))
    if not classes.intersection(used):
        return ''
    codes.add(int(match.group(2), 16))
    return match.group(0)

css = icon_rule.sub(retain_icon, css)
destination = THEME / 'assets/fonts'
destination.mkdir(exist_ok=True)
for filename in ('fa-solid-900', 'fa-regular-400', 'fa-brands-400'):
    options = subset.Options()
    options.flavor = 'woff2'
    options.name_IDs = ['*']
    options.name_languages = ['*']
    font = subset.load_font(str(VENDOR / f'webfonts/{filename}.woff2'), options)
    subsetter = subset.Subsetter(options=options)
    subsetter.populate(unicodes=codes)
    subsetter.subset(font)
    target = destination / f'pizza-{filename}.woff2'
    subset.save_font(font, str(target), options)
    css = css.replace(
        f'url(../webfonts/{filename}.woff2) format("woff2"),url(../webfonts/{filename}.ttf) format("truetype")',
        f'url(../fonts/pizza-{filename}.woff2) format("woff2")',
    )
    print(f'{target.name}: {target.stat().st_size:,} bytes')

output = THEME / 'assets/css/pizza-landing-icons.css'
output.write_text(css, encoding='utf-8')
print(f'{output.name}: {output.stat().st_size:,} bytes; {len(codes)} code points')
