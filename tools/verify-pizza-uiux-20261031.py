from pathlib import Path
from urllib.request import urlopen
from urllib.parse import urljoin
from concurrent.futures import ThreadPoolExecutor
from lxml import html
import json
R=Path(__file__).resolve().parents[1];O=R/'outputs/pizza-uiux-20261031';url='http://localhost/hopgiayvpn/custom-pizza-boxes-manufacturer/'
O.mkdir(parents=True,exist_ok=True)
raw=urlopen(url,timeout=30).read();(O/'after.html').write_bytes(raw);d=html.fromstring(raw);main=d.xpath('//main')[0]
schema=[]
for s in d.xpath('//script[@type="application/ld+json"]/text()'):
 try:schema.append(json.loads(s))
 except ValueError:pass
graphs=[g for s in schema for g in s.get('@graph',[s])];faq=next(x for x in graphs if x.get('@type')=='FAQPage');image=next(x for x in graphs if x.get('@id','').endswith('#pizza-box-design'))
checks={'one_h1':len(d.xpath('//h1'))==1,'title_keyword':'Custom Pizza Box Manufacturer' in d.xpath('//title/text()')[0],
 'description_keyword':'pizza box manufacturer' in d.xpath('//meta[@name="description"]/@content')[0].lower(),
 'canonical_unchanged':d.xpath('//link[@rel="canonical"]/@href')==[url],
 'email_only_required':d.xpath('//form[contains(@class,"vpb-form")]//*[@required]/@name')==['email'],
 'email_autocomplete':bool(d.xpath('//input[@name="email"][@autocomplete="email"][@type="email"]')),
 'no_consent_checkbox':not d.xpath('//form[contains(@class,"vpb-form")]//input[@type="checkbox"]'),
 'optional_details':bool(d.xpath('//details[contains(@class,"vpb-form-details")]//input[@name="full_name"]')),
 'six_catalog_products':len(d.xpath('//article[contains(@class,"vpb-product-card")]'))==6,
 'three_workshop_photos':len(d.xpath('//*[@id="vpb-factory"]//img[@data-factory-image]'))==3,
 'lazy_video':not d.xpath('//*[@id="vpb-factory"]//iframe'),
 'faq_schema_matches':len(faq['mainEntity'])==len(d.xpath('//*[@id="vpb-faq"]//details')),
 'hero_schema_accurate':image['height']==800 and 'design reference' in image['caption'].lower(),
 'no_unresolved_slots':b'IMAGE_SLOT' not in raw,
 'working_quote_anchors':all(x.split('#')[-1] in d.xpath('//*[@id]/@id') for x in main.xpath('.//a/@href') if '#' in x and x.startswith('#'))}
urls=set()
for u in main.xpath('.//a/@href')+main.xpath('.//img/@src'):
 u=urljoin(url,u).split('#')[0]
 if u.startswith('http://localhost/'):urls.add(u)
for s in main.xpath('.//img/@srcset'):
 for u in s.split(','):
  urls.add(u.strip().split(' ')[0])
def check(u):
 try:
  with urlopen(u,timeout=30) as r:return {'url':u,'status':r.status}
 except Exception as e:return {'url':u,'error':str(e)}
with ThreadPoolExecutor(max_workers=4) as ex:resources=list(ex.map(check,sorted(urls)))
report={'checks':checks,'resources':resources,'no_form_submitted':True}
(O/'render-verification.json').write_text(json.dumps(report,indent=2),encoding='utf8')
print(json.dumps({'checks':checks,'resources':len(resources),'failed_resources':[x for x in resources if x.get('status')!=200]},indent=2))
if not all(checks.values()) or any(x.get('status')!=200 for x in resources):raise SystemExit(1)
