# Pizza category release — 10 November 2026

Owner-approved local changes for `/products/pizza-boxes/`:

- Custom category introduction and a three-view transparent WebP gallery of one kraft pizza-box design. Dots, keyboard controls, automatic rotation, pointer-drag and touch scrolling; no Pause button. Design illustrations are labelled.
- Existing VPN workshop photographs, click-to-load factory video and company details. These show general packaging work, not pizza-specific performance or certification. Includes both business emails, phone, About and location links.
- Quote/details actions on product cards with matching colors and heights; shortened display title for the long fold-flat product, without database title/slug changes.
- Equal-sized images/title panels/actions; native disclosure for the detailed buyer guide; useful quote path to the existing pizza-manufacturer form.
- Category-scoped tablet header and two-column grid; narrow-card actions stack. The exact768px legacy menu conflict uses the normal navigation; phone drawer/search retained.

Validation: PHP syntax checks passed. Category, assets and representative control pages returned200 locally.24 widths from320 to1920px and3 landscape viewports had no page/title/action overflow; action targets44px. Mobile menu/search and both emails checked, gallery dots/keyboard tested, expanded table scrolls inside its container. Physical iOS/Safari hardware and drag/touch input were not directly tested. No enquiry submitted.

Deployment: pull this commit, deploy theme files and clear affected HTML/CSS/JS cache. No database import or permalink change is required. Then check the pizza category and quote destination on live desktop/mobile. Hosting deployment remains with the owner.

Only reviewed pizza changes are staged. Unrelated existing local edits in the shared archive and elsewhere remain untouched.
