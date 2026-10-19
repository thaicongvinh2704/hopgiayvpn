"""Build the fixed, owner-supplied four-product local source package."""
import hashlib
import html
import json
import re
from pathlib import Path
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "wp-content/themes/custom-box-theme/inc/product-content/four-products-20261019"
ASSETS = ROOT / "wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/2026/10/vpn-four-products-20261019"
products = [
    dict(code="BD", title="Custom Happy Birthday Gift Paper Bags",
         slug="custom-happy-birthday-gift-paper-bags", folder="birthday-gift-paper-bags",
         keyword="birthday gift paper bags", secondary=["birthday gift bags wholesale", "custom birthday bags", "birthday gift bag supplier"],
         seo_title="Birthday Gift Paper Bags | Custom Wholesale Orders | VPN",
         seo_description="Custom birthday gift paper bags with paper handles, your artwork and sizes. Order wholesale from VPN in Vietnam. Request a bulk production quote.",
         categories=["paper-bags-with-logo"], tags=["birthday-gift-bags", "custom-printed-paper-bags", "wholesale-paper-bags"],
         feature="Birthday artwork collection, paper loop handles, folded top and expandable sides",
         use="Gift retailers, party suppliers, occasion packaging distributors and retail collections",
         paper="White printing paper; grade and finish agreed with artwork and filled sample",
         shape="Rectangular gift bag with expandable sides and folded base",
         type="Custom birthday paper gift bag", color="Custom birthday artwork; colorful white-based reference",
         accessories="Gift tags, tissue and retail grouping quoted separately if requested",
         liner="Interior and any reinforcement confirmed in the approved specification",
         short_description="""<p>Custom birthday gift paper bags give gift shops, party suppliers and importers a coordinated occasion range with their own artwork and sizes. The reference collection pairs colorful birthday greetings with white paper loop handles, folded tops, side panels and folded bases. Two proportions illustrate how one collection can accommodate different gifts while keeping a recognizable retail identity.</p><p>VPN Packaging Factory develops the bag dimensions, paper, printing and handle construction around your intended contents. Send the gift size and weight, quantity by artwork and size, and delivery destination for a wholesale quotation. Our general planning minimum is 1,000 units, with the exact order requirements confirmed in the quote. Design support and custom sampling are available. Check a filled sample before approving carrying requirements, and specify any tissue, tag or mixed retail pack separately. These reference views guide the design discussion; final materials and dimensions follow the approved specification.</p>""",
         views=[("Two colorful birthday gift paper bags with white paper handles", "Coordinated birthday artwork in two reference proportions."),
                ("Open birthday gift paper bags showing top folds and white interiors", "Open view for reviewing top folds, handle position and loading space."),
                ("White paper loop handles and colorful birthday bag top edge", "Detail of the paper handles and folded top edge."),
                ("Birthday gift paper bags showing upright body and folded base", "Body and base reference for a coordinated birthday bag collection."),
                ("Top view of two birthday paper gift bags with open interiors", "Top view of the reference interiors and side panels."),
                ("Birthday gift bag reference with labeled paper handles and folds", "Annotated reference of visible handles, folds and side panels.")]),
    dict(code="BK", title="Custom Brown Kraft Bakery Paper Bags",
         slug="custom-brown-kraft-bakery-paper-bags", folder="brown-kraft-bakery-paper-bags",
         keyword="brown kraft bakery paper bags", secondary=["kraft bakery bags wholesale", "custom bakery paper bags", "bakery bag supplier"],
         seo_title="Brown Kraft Bakery Paper Bags | Custom Wholesale | VPN",
         seo_description="Brown kraft bakery paper bags made to your size and logo. Review paper options for pastries and bread. Ask VPN for a custom wholesale quotation.",
         categories=["paper-bags-with-logo", "bakery-packaging-boxes"], tags=["bakery-paper-bags", "kraft-paper-bags", "wholesale-paper-bags"],
         feature="Handleless open top, side gussets and folded base for bakery counter packing",
         use="Bakeries, cafe groups, food-service distributors and packaging importers",
         paper="Brown kraft paper; grease barrier and food-contact specification reviewed separately",
         shape="Open-top gusseted bakery bag with folded base", type="Custom kraft bakery paper bag",
         color="Natural brown reference; logo printing customized",
         accessories="Top fold, label or additional liner only when specified",
         liner="No grease barrier or direct food-contact grade assumed; confirm by intended use",
         short_description="""<p>Custom brown kraft bakery paper bags offer a practical open-top format for pastries, small loaves and bakery counter orders. The reference shows handleless bags with side gussets and a folded base, including an example display with croissants. Your bakery logo, dimensions and packing arrangement can be developed for a consistent supply across outlets or distributor customers.</p><p>Choose the specification around the real product: size, grease and moisture exposure, packing temperature and whether food contacts the paper directly. The images do not establish grease resistance or a food-contact grade. VPN Packaging Factory can review those requirements before a sample is approved. The general planning minimum is 1,000 units, with project minimums, price and packing confirmed in a written quotation. Send your email, artwork, quantity by size and destination to start. Design support and custom sampling are available, followed by production against the approved requirements.</p>""",
         views=[("Brown kraft bakery paper bags in upright and flat views with croissants", "Brown kraft bakery bag format with a reference croissant display."),
                ("Open brown kraft bakery bag showing side gussets and folded base", "Open bag showing the loading space and side expansion."),
                ("Brown kraft bakery paper bag edge and gusset close-up", "Paper appearance and side gusset detail; material grade is specified separately."),
                ("Upright kraft bakery bag beside a bag showing its folded bottom", "Side and folded-base views for construction review."),
                ("Interior top view of a brown kraft bakery paper bag", "Interior and opened base reference for checking product fit."),
                ("Kraft bakery bag with labeled open top side gusset and folded base", "Annotated construction reference; no food-contact or barrier rating is implied.")]),
    dict(code="WF", title="Custom Printed White Flat Paper Bags",
         slug="custom-printed-white-flat-paper-bags", folder="white-flat-paper-bags",
         keyword="white flat paper bags", secondary=["custom printed white paper bags", "white flat paper bags wholesale", "flat paper bag supplier"],
         seo_title="White Flat Paper Bags | Custom Printing Wholesale | VPN",
         seo_description="Custom white flat paper bags for lightweight retail packaging. Choose your size and logo printing. Request a wholesale manufacturing quote from VPN.",
         categories=["paper-bags-with-logo"], tags=["white-flat-paper-bags", "custom-printed-paper-bags", "wholesale-paper-bags"],
         feature="White handleless flat-style body with open mouth and folded bottom seam",
         use="Lightweight merchandise, stationery, wrapped accessories and suitable counter applications",
         paper="White paper; opacity, grade and any food-contact requirements confirmed by project",
         shape="Narrow flat-style handleless bag with folded bottom seam", type="Custom printed flat paper bag",
         color="White reference; custom logo and print colors",
         accessories="Top fold or labeling requirements agreed separately",
         liner="No barrier or food-contact performance specified by the reference images",
         short_description="""<p>Custom white flat paper bags provide a compact, handleless format for lightweight merchandise and retail counter handovers. The reference views show a plain white body, open mouth and folded bottom seam, with no carrying handles. This simple construction can be developed with your own logo, message and usable dimensions for stationery, cards, wrapped accessories or other suitable items.</p><p>VPN Packaging Factory reviews the bag around the actual contents, loading method and desired paper appearance. Specify opacity, a folded top or any food-contact requirement instead of assuming that every white paper has the same properties. For wholesale production, send your artwork, quantity by size and delivery destination. The general planning minimum is 1,000 units, subject to the selected specification and written quotation. Design support and made-to-request samples are available. Check the filled sample for fit, seam behavior and print placement before approving a larger run.</p>""",
         views=[("Stack of white flat paper bags without handles on a work surface", "Unprinted white flat bag reference, shown in a compact stack."),
                ("White flat paper bag with its open mouth facing the viewer", "Opening reference for reviewing usable dimensions and loading."),
                ("Close-up of white flat paper bag edge and upper opening", "Paper edge and opening detail; final grade is selected by project."),
                ("Front and reverse of white flat paper bags showing bottom seams", "Front and reverse panels with the folded bottom seam."),
                ("Interior view through the opening of a white flat paper bag", "Interior opening reference for checking the intended contents."),
                ("White flat paper bag stack with labeled mouth profile and bottom seam", "Annotated reference distinguishing the handleless flat bag construction.")]),
    dict(code="WM", title="Custom White Kraft Mailer Boxes",
         slug="custom-white-kraft-mailer-boxes", folder="white-kraft-mailer-box",
         keyword="white kraft mailer boxes", secondary=["white mailer boxes wholesale", "custom printed white mailer boxes", "white mailer box manufacturer"],
         seo_title="White Kraft Mailer Boxes | Custom Wholesale Supply | VPN",
         seo_description="White kraft mailer boxes with a white exterior and kraft interior. Customize size, print and inserts. Request a bulk manufacturing quote from VPN.",
         categories=["corrugated-mailer-boxes"], tags=["white-kraft-mailer-boxes", "custom-mailer-boxes", "wholesale-packaging-boxes"],
         feature="White exterior, kraft-colored interior, hinged lid and front tuck closure",
         use="Ecommerce brands, accessory sellers, gift programs and packaging importers",
         paper="White outer liner and kraft-colored interior; board construction and grade confirmed by project",
         shape="Rectangular folding mailer with connected lid", type="Custom hinged-lid mailer box",
         color="White outside and kraft-colored inside reference; custom print available",
         accessories="Fitted paper inserts, wrapping or labels quoted separately if required",
         liner="Interior liner, board thickness and protective arrangement confirmed by sample",
         short_description="""<p>Custom white kraft mailer boxes combine a plain white exterior with a kraft-colored interior for ecommerce parcels, accessory sets and gift packaging. The reference shows a shallow rectangular format, connected lid, folded side walls and a front tuck closure. Your brand artwork, internal dimensions and protective arrangement can be developed around the products being packed.</p><p>VPN Packaging Factory supplies custom packaging from Vietnam for brands, distributors and importers. Share the full packed contents, weight, quantity and destination so we can review board selection, printing and any insert. No fixed flute, load rating, adhesive seal or shipping performance is established by the images. The general planning minimum is 1,000 units, with price and order requirements confirmed in a written quotation. Design support and custom samples are available before production. Review a filled sample for assembly, closure, fit and product movement, and specify any extra shipping protection separately.</p>""",
         views=[("Closed white kraft mailer box with hinged lid and front tuck flap", "Closed reference with a white exterior and front tuck closure."),
                ("Open white mailer box revealing a kraft-colored interior and connected lid", "Open interior showing the connected lid and folded side panels."),
                ("Folded corner of a white mailer box with kraft-colored interior", "Corner and wall detail; board grade and thickness are specified separately."),
                ("Rear and side view of a closed white kraft mailer box", "Rear and side reference for the white outer panels."),
                ("Top view of an open white kraft mailer box and its empty interior", "Open top view for discussing internal layout and product clearance."),
                ("White kraft mailer reference labeled with lid side flaps and interior", "Annotated reference of visible lid, flaps and interior surfaces.")]),
]

for p in products:
    p["content_file"] = p["folder"] + ".html"
    if p["code"] == "WM":
        p["content_file"] = "white-kraft-mailer-box.html"
    body = (SOURCE / p["content_file"]).read_text(encoding="utf-8")
    text = html.unescape(re.sub(r"<[^>]*>|\{\{[^}]+\}\}", " ", body))
    p["wordcount"] = len(text.split())
    p["short_wordcount"] = len(re.sub(r"<[^>]*>", " ", p["short_description"]).split())
    assert 1500 <= p["wordcount"] <= 2000, (p["slug"], p["wordcount"])
    assert 120 <= p["short_wordcount"] <= 180, (p["slug"], p["short_wordcount"])
    assert p["keyword"].lower() in p["seo_title"].lower() and len(p["seo_title"]) <= 60
    assert p["keyword"].lower() in p["seo_description"].lower() and len(p["seo_description"]) <= 155
    assert not re.search(r"<h1\b", body, re.I)
    p["content_sha256"] = hashlib.sha256(body.encode()).hexdigest()
    p["images"] = []
    prefix = {"BD": "happy-birthday-gift-paper-bags", "BK": "brown-kraft-bakery-paper-bag", "WF": "white-flat-paper-bag", "WM": "white-kraft-mailer-box"}[p["code"]]
    files = sorted(ASSETS.glob(prefix + "-*.webp"))
    assert len(files) == 6
    for number, (f, (alt, caption)) in enumerate(zip(files, p.pop("views")), 1):
        im = Image.open(f)
        assert im.size == (450, 570) and f.stat().st_size < 100000
        p["images"].append(dict(file=f.name, source=f"images/{p['folder']}/{f.name}",
                                sha256=hashlib.sha256(f.read_bytes()).hexdigest(),
                                title=f"{p['title']} — View {number}", alt=alt, caption=caption))
    print(p["slug"], p["wordcount"], p["short_wordcount"], len(p["seo_title"]), len(p["seo_description"]))
(SOURCE / "manifest.json").write_text(json.dumps(dict(marker="four-products-20261019-v1", products=products), indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
