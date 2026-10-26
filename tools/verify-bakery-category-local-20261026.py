"""Check the rendered local category, its structured data and local assets."""
import concurrent.futures
import json
from pathlib import Path
import urllib.request
from urllib.parse import urlsplit, urldefrag
from lxml import html as lhtml

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'outputs/bakery-category-20261026'
OUT.mkdir(parents=True, exist_ok=True)
BASE = 'http://localhost/hopgiayvpn/'
URL = BASE + 'products/custom-bakery-bread-paper-bags/'
DATA = json.loads((ROOT / 'wp-content/themes/custom-box-theme/inc/bakery-bread-paper-bags-category.json').read_text(encoding='utf-8'))

def fetch(url):
    with urllib.request.urlopen(url, timeout=40) as response:
        return response.status, response.read()

status, html = fetch(URL)
(OUT / 'category.html').write_bytes(html)
soup = lhtml.fromstring(html)
errors = []
def check(condition, message):
    if not condition:
        errors.append(message)

check(status == 200, 'Category HTTP status')
check([h.text_content().strip() for h in soup.xpath('//h1')] == [DATA['name']], 'One correct H1')
title = soup.xpath('//title')[0].text_content()
check(title == DATA['title'], 'SEO title')
check(soup.xpath('//meta[@name="description"]')[0].get('content') == DATA['description'], 'Meta description')
robots = soup.xpath('//meta[@name="robots"]')[0].get('content')
canonical_nodes = soup.xpath('//link[@rel="canonical"]')
check((canonical_nodes and canonical_nodes[0].get('href') == URL) or ('noindex' in robots and not canonical_nodes), 'Canonical or existing local noindex suppression')
check(soup.xpath('//meta[@property="og:url"]')[0].get('content') == URL, 'Category social URL')
faqs = soup.xpath('//*[@id="bakery-faq"]//details')
check(len(faqs) == 8, 'Eight visible FAQ entries')
check(len(soup.xpath('//div[@class="vpn-kraft-workshop-photos"]/figure')) == 3, 'Three captioned workshop photos')
check(bool(soup.xpath('//button[@data-youtube-video-id="nD0iRaJHgLQ"]')), 'Existing factory video')
cards = soup.xpath('//a[@class="custom-product-card-link"]')
visible = [a.get('href') for a in cards]
expected = [BASE + 'product/' + slug + '/' for slug in DATA['products']]
check(visible == expected, 'Nine products in curated order')
for image in soup.xpath('//main//img'):
    check(bool(image.get('width')) and bool(image.get('height')), 'Image dimensions: ' + image.get('src', ''))
for a in soup.xpath('//main//a[starts-with(@href,"#")]'):
    check(bool(soup.xpath('//*[@id=$id]', id=a.get('href')[1:])), 'Anchor target: ' + a.get('href'))
nodes = []
for script in soup.xpath('//script[@type="application/ld+json"]'):
    value = json.loads(script.text)
    nodes.extend(value.get('@graph', [value]) if isinstance(value, dict) else value)
collections = [n for n in nodes if 'CollectionPage' in ([n.get('@type')] if isinstance(n.get('@type'), str) else n.get('@type', []))]
items = [n for n in nodes if n.get('@type') == 'ItemList' and n.get('@id') == URL + '#products']
check(len(collections) == 1, 'One CollectionPage schema node')
check(len(items) == 1 and [i['url'] for i in items[0]['itemListElement']] == visible, 'ItemList matches visible products')
local_urls = sorted({urldefrag(t.get(attr))[0] for selector, attr in [('//main//a[@href]', 'href'), ('//img[@src]', 'src'), ('//link[@rel="stylesheet"][@href]', 'href')] for t in soup.xpath(selector) if urlsplit(t.get(attr)).hostname == 'localhost'})
def asset_result(url):
    try:
        code, body = fetch(url)
        return {'url': url, 'status': code, 'bytes': len(body)}
    except Exception as error:
        return {'url': url, 'error': str(error)}
with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
    resources = list(pool.map(asset_result, local_urls))
for resource in resources:
    check(resource.get('status') == 200, 'Resource: ' + resource['url'])
# Sort order must be reflected in both the UI and the ItemList.
_, sorted_html = fetch(URL + '?orderby=date')
sorted_soup = lhtml.fromstring(sorted_html)
sorted_cards = [a.get('href') for a in sorted_soup.xpath('//a[@class="custom-product-card-link"]')]
sorted_graph = json.loads(sorted_soup.xpath('//script[@class="rank-math-schema"]')[0].text)['@graph']
sorted_items = next((n for n in sorted_graph if n.get('@id') == URL + '#products'), {})
check(sorted_cards == [i['url'] for i in sorted_items.get('itemListElement', [])], 'Sorted ItemList matches sorted UI')
# Incoming links: parent category, existing kraft landing and manufacturer page.
incoming = {}
for slug in ('products/paper-bags-with-logo/', 'products/custom-kraft-paper-bags/', 'custom-paper-bags-manufacturer/'):
    _, page_html = fetch(BASE + slug)
    page_soup = lhtml.fromstring(page_html)
    incoming[slug] = [a.text_content().strip() for a in page_soup.xpath('//a[@href]') if a.get('href') == URL]
    check(bool(incoming[slug]), 'Incoming category link: ' + slug)
result = {'url': URL, 'title': title, 'title_length': len(DATA['title']), 'description_length': len(DATA['description']),
          'products': len(cards), 'faqs': len(faqs), 'collection_schema': len(collections),
          'itemlist_matches': bool(items) and [i['url'] for i in items[0]['itemListElement']] == visible,
          'robots_local': robots, 'canonical_local': canonical_nodes[0].get('href') if canonical_nodes else None,
          'incoming_links': incoming, 'resources': resources, 'errors': errors}
(OUT / 'verification.json').write_text(json.dumps(result, indent=2), encoding='utf-8')
print(json.dumps({k: v for k, v in result.items() if k != 'resources'}, indent=2))
raise SystemExit(1 if errors else 0)
