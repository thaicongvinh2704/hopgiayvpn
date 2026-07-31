# Release checkpoint visual QA

## Scope

- WordPress root: `C:\xampp\htdocs\hopgiayvpn`
- Local URL: `http://localhost/hopgiayvpn`
- Database: `hopgiayvpnmoi`
- Product scope preserved: 179 published products
- Batch scope: 17 batches
- Sample rule: first and last product in every batch (34 unique products)
- Viewports: desktop 1440 x 1000 and mobile 390 x 844
- Final regression browser: Microsoft Edge 150.0.4078.105, Windows headless mode
- Production host resolution was blocked during capture.

## Result

**PASS**

- Automated captures passed: 68/68
- Failed captures: 0
- Full-page screenshots: 68
- Initial-viewport screenshots: 68
- Contact sheets manually reviewed: 17/17
- HTTP failures: 0
- Horizontal-overflow failures: 0
- Pages without exactly one preserved H1: 0
- Heading-hierarchy failures: 0
- Broken-image failures: 0
- Missing quote-form failures: 0
- Missing CTA failures: 0
- Missing planned internal-link failures: 0
- Pages containing banned testimonial, shared FAQ, or `Request Free Sample`: 0
- Truncated full-page captures: 0

The layout, images, quote form, CTA controls and product content remained usable at both tested widths. Long current-page breadcrumb labels remain contained within the mobile viewport and do not create horizontal overflow.

After the first checkpoint, product ID 771 exposed a desktop whitespace problem: the short description was stored correctly but hidden in a lower disclosure, while the two-row hero Grid separated the heading from the feature/CTA summary. The template now renders the short description once in the hero and the desktop Grid uses an explicit content-first row. The complete 68-capture suite was rerun after this correction and passed.

## Changes during this checkpoint

- Product content changes: 0
- SEO strategy changes: 0
- Database content writes: 0
- UI issues found after the first checkpoint: 1
- UI issues fixed: 1
- Remaining UI issues: 0
- Product template/CSS corrections: short-description placement and desktop Grid flow only
- Production requests: 0
- Production writes: 0

## Evidence

- `visual-qa-plan.csv`
- `visual-qa-report.csv`
- `visual-qa-report.json`
- `contact-sheets/contact-sheet-manifest.json`
- `contact-sheets/BATCH-01-viewport-contact-sheet-v2.png` through `BATCH-17-viewport-contact-sheet-v2.png`
- `screenshots/BATCH-01/` through `screenshots/BATCH-17/`

The SQL backup itself is intentionally excluded from Git and release ZIP packages. Its path, size and SHA-256 are recorded in `backup-manifest.json`.
