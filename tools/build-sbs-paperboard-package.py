#!/usr/bin/env python3
"""Build the fixed SBS article payload from the owner-supplied local source."""
from __future__ import annotations

import hashlib
import json
import shutil
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "seo-content" / "sbs-paperboard-20261017"
DEPLOY = ROOT / "wp-content" / "mu-plugins" / "vpn-sbs-paperboard-article"
ARTICLE = SOURCE / "article.html"

specs = [
    {
        "file": "sbs-paperboard-grades-thickness-fbb-comparison.webp",
        "title": "SBS Paperboard Grades and FBB Comparison",
        "alt": "SBS paperboard samples with printed folding cartons and material measuring tools",
        "caption": "Visual overview of paperboard samples, a folding carton and thickness-measuring tools.",
        "featured": True,
    },
    {
        "file": "sbs-c1s-vs-c2s-surface-comparison.webp",
        "title": "C1S and C2S Paperboard Surface Comparison",
        "alt": "Comparison of coated one side and coated two sides SBS paperboard samples",
        "caption": "C1S and C2S describe whether one or both faces are coated; confirm the exact grade with its supplier.",
        "slot": 1,
    },
    {
        "file": "sbs-paperboard-thickness-caliper-measurement.webp",
        "title": "SBS Paperboard Caliper Measurement",
        "alt": "Digital caliper checking the thickness of a white paperboard sample",
        "caption": "Caliper measures physical board thickness; GSM measures mass per area, so the two specifications are not interchangeable.",
        "slot": 2,
    },
    {
        "file": "sbs-vs-fbb-board-cross-section.webp",
        "title": "SBS and FBB Board Cross-Section Comparison",
        "alt": "SBS and FBB paperboard cut edges shown side by side",
        "caption": "Compare the named board construction with the supplier's data before approving a grade substitution.",
        "slot": 3,
    },
    {
        "file": "sbs-printed-carton-crease-quality-check.webp",
        "title": "Printed Folding Carton Crease Review",
        "alt": "Printed folding carton sample inspected for ink finish and crease quality",
        "caption": "Check print appearance and fold lines on a sample made with the intended board and finish.",
        "slot": 4,
    },
]

content = ARTICLE.read_text(encoding="utf-8").replace("\r\n", "\n").replace("\r", "\n")
for image in specs:
    source_file = SOURCE / "images" / image["file"]
    if not source_file.is_file():
        raise SystemExit(f"Missing supplied image: {source_file}")
    from PIL import Image

    with Image.open(source_file) as im:
        if im.format != "WEBP" or im.size != (1600, 900) or source_file.stat().st_size >= 100_000:
            raise SystemExit(f"Image preflight failed: {source_file.name} ({im.format}, {im.size}, {source_file.stat().st_size})")
    image.update({
        "bytes": source_file.stat().st_size,
        "width": 1600,
        "height": 900,
        "sha256": hashlib.sha256(source_file.read_bytes()).hexdigest(),
    })

for slot in range(1, 5):
    token = f"<!-- IMAGE_SLOT_{slot} -->"
    if content.count(token) != 1:
        raise SystemExit(f"Expected exactly one article image marker: {token}")
if "IMAGE_SLOT_" in content and any(content.count(f"<!-- IMAGE_SLOT_{i} -->") != 1 for i in range(1, 5)):
    raise SystemExit("Unexpected article image marker")

manifest = {
    "title": "SBS Paperboard: Grades, Thickness, Printing and FBB Comparison",
    "slug": "sbs-paperboard",
    "status": "publish",
    "excerpt": "Understand SBS paperboard grades, C1S and C2S coatings, thickness, GSM, printing options and FBB alternatives before ordering custom folding cartons.",
    "category": {"name": "Packaging Materials", "slug": "packaging-materials"},
    "tags": ["SBS Paperboard", "Folding Cartons", "FBB", "Paperboard Thickness", "C1S", "C2S"],
    "meta": {
        "rank_math_title": "SBS Paperboard: Grades, Thickness & FBB Comparison",
        "rank_math_description": "Learn what SBS paperboard is, compare C1S and C2S, understand thickness and GSM, and review SBS vs FBB before specifying custom folding cartons.",
        "rank_math_focus_keyword": "sbs paperboard",
    },
    "content_sha256": hashlib.sha256(content.encode("utf-8")).hexdigest(),
    "images": specs,
}

SOURCE.joinpath("manifest.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
DEPLOY.mkdir(parents=True, exist_ok=True)
shutil.copyfile(ARTICLE, DEPLOY / "article.html")
shutil.copyfile(SOURCE / "manifest.json", DEPLOY / "manifest.json")
deploy_images = DEPLOY / "images"
deploy_images.mkdir(parents=True, exist_ok=True)
for image in specs:
    shutil.copyfile(SOURCE / "images" / image["file"], deploy_images / image["file"])
print(json.dumps({"source": str(SOURCE), "deploy": str(DEPLOY), "article_sha256": manifest["content_sha256"], "images": [{"file": x["file"], "bytes": x["bytes"], "sha256": x["sha256"]} for x in specs]}, ensure_ascii=False, indent=2))
