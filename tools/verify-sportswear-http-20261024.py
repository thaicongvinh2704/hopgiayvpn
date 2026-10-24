from pathlib import Path
from urllib.request import Request, urlopen
from concurrent.futures import ThreadPoolExecutor
from lxml import html
import json

root = Path(__file__).resolve().parents[1]
out = root / 'outputs/sportswear-ctr-20261024'
url = 'http://localhost/hopgiayvpn/product/custom-sportswear-packaging-box/'
data = json.loads((root/'wp-content/mu-plugins/vpn-sportswear-copy-20261024/release.json').read_text(encoding='utf-8'))
def fetch(u):
    with urlopen(Request(u, headers={'User-Agent':'VPN-local-QA/1.0'}), timeout=60) as r:
        return r.status, r.read()
status, raw = fetch(url)
(out/'local-after.html').write_bytes(raw)
s = html.fromstring(raw)
body = s.xpath('//div[contains(concat(" ",normalize-space(@class)," ")," product-content-body ")]')[0]
text = ' '.join(body.text_content().split())
(out/'local-after-text.txt').write_text(text, encoding='utf-8')
related = s.xpath('//section[contains(@class,"product-related-section")]//a[contains(@class,"product-related-card")]/@href')
gallery = s.xpath('//section[@id="manufacturing-workshop"]//img/@src')
gallery_alts = s.xpath('//section[@id="manufacturing-workshop"]//img/@alt')
schemas=[]
for script in s.xpath('//script[@type="application/ld+json"]'):
    value = json.loads(script.text_content()); schemas.extend(value.get('@graph',[value]))
products=[x for x in schemas if x.get('@type') == 'Product']
targets = sorted(set(body.xpath('.//a/@href') + related + gallery + s.xpath('//div[contains(@class,"product-gallery-main")]//img/@src')))
def target_check(u):
    try:
        st, b = fetch(u.split('#')[0]); return dict(url=u,status=st,bytes=len(b))
    except Exception as e:
        return dict(url=u,error=str(e))
with ThreadPoolExecutor(max_workers=3) as pool:
    targets_checked = list(pool.map(target_check, targets))
checks = dict(http_200=status==200, title=s.xpath('string(//title)')==data['title'],
    description=s.xpath('//meta[@name="description"]/@content')==[data['description']],
    local_noindex_preserved=any('noindex' in x for x in s.xpath('//meta[@name="robots"]/@content')),
    canonical_not_pointing_elsewhere=all(x == url for x in s.xpath('//link[@rel="canonical"]/@href')),
    one_h1=len(s.xpath('//h1'))==1,
    old_h1_retained=s.xpath('string(//h1)')=='CUSTOM SPORTSWEAR PACKAGING BOX',
    no_editorial_notes=all(x not in text for x in ['SEO keyword','generic cross-link','neighboring reference','Sportswear Box program']),
    no_placeholders='{{HOME}}' not in raw.decode('utf-8'),
    no_production_links_in_body='https://hopgiayvpn.com/' not in html.tostring(body).decode(),
    faq_four=len(body.xpath('.//h3'))==4,
    related_four=len(related)==4,
    factory_images_two=len(gallery)==2 and len(gallery_alts)==2 and all(gallery_alts),
    factory_css=bool(s.xpath('//link[contains(@href,"factory-gallery.css")]')),
    links_and_images_load=all(x.get('status')==200 for x in targets_checked),
    one_product_schema=len(products)==1,
    schema_description_matches=len(products)==1 and products[0].get('description')==data['description'])
result=dict(url=url,checks=checks,title=s.xpath('string(//title)'),description=s.xpath('//meta[@name="description"]/@content'),
    robots=s.xpath('//meta[@name="robots"]/@content'),canonical=s.xpath('//link[@rel="canonical"]/@href'),
    canonical_note='Local isolation intentionally sets blog_public=0; Rank Math omits canonical on this noindex preview.',
    related=related,workshop_images=gallery,
    body_headings=body.xpath('.//h2/text()|.//h3/text()'),targets=targets_checked,product_schema=products)
(out/'local-http-verification.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(result,ensure_ascii=False,indent=2))
assert all(checks.values()), checks
