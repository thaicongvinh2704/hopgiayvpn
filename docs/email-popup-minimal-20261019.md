# Minimal email popup — local review

Current owner-approved direction: simplify the mobile popup, remove the optional project-details box, replace the unsuitable mobile image and make the close icon less prominent. The owner subsequently authorized Git publication of this complete popup release. Hosting deployment remains with the owner.

The form now shows its invitation, one email field, one primary action and the existing contact-purpose/privacy notice. The decorative brand headline and initial “Not now” link are removed. The existing footer button remains available as “Continue browsing” after a successful receipt. The optional message remains an empty hidden input so cached frontend code can still read the expected field without an error. Inbox delivery, receipt validation, frequency limits and browser email autocomplete are unchanged; no new consent checkbox is introduced.

The close control is a quiet 20px glyph on mobile, without a circle or filled badge, with a 44px hit target. Native dialog focus starts on the title instead of highlighting the close button. Keyboard users still receive a focus outline when they reach the close control. Desktop keeps the existing portrait photograph and its two-column layout, with the simplified form centered in its column.

The mobile picture source is a new generated landscape photograph showing an unprinted kraft bag and a low closed box. It is decorative imagery, not evidence of an actual factory or completed order. The browser selects it only at mobile widths. The image was generated with the **built-in imagegen tool**, then technically resized and encoded as WebP without altering the scene.

Final saved asset: `C:/xampp/htdocs/hopgiayvpn-main/hopgiayvpn-main/wp-content/mu-plugins/vpn-email-quote-popup/packaging-banner-20261019.webp` (900 × 303, 29,226 bytes).

The generated PNG source is preserved at `outputs/email-popup-minimal-20261019/packaging-banner-source.png`; it is a review/source artifact, not a production dependency.

## Final generation prompt

> Use case: photorealistic-natural. Asset type: a minimal photographic banner for a packaging quotation popup on a manufacturer's website. Generate one wide horizontal image, panoramic aspect ratio 3:1. A single small unprinted brown kraft shopping bag with simple twisted-paper handles beside one low closed kraft cardboard gift box, on a clean, lightly worn wooden packing table. Real paper grain, slight folds and realistic imperfect edges. An understated workshop packing area very softly blurred behind, natural daylight, neutral warm beige and muted gray tones. Looks like a calm, believable phone photograph, not a CGI render or glossy studio advertisement. Frame the entire bag including handles and the entire box together as a modest group across the middle; leave a little quiet space above and at the far right for a discreet popup close icon. Simple composition, not crowded. The subject group must remain legible when displayed as a very small horizontal banner. No people, no hands, no text, no logos, no typography, no signage, no watermark, no decorative badges. Not an image of a webpage or popup; only the photographic asset.

## Verification and later release

PHP lint and scoped diff checks pass. The existing inbox frontend suite passes 106 checks, and the mobile UI suite passes 117 checks over 1440, 600, 430, 390, 360 and 320px widths, including the correct picture source, one visible email field, no details section, quiet close styling, focus, viewport bounds, retry, receipt confirmation, long email and a compact input viewport. The actual local landing page was reviewed at 390px, 320px and desktop using mocked inbox responses. No customer record or company email was created by these checks. Screenshots and machine-readable evidence are under `outputs/email-popup-minimal-20261019/`.

At 390×844, the isolated fixture entry is approximately 342×400px, compared with approximately 342×474px before this simplification. Production theme fonts may change text wrapping. Small-height viewports retain the existing compact mode and internal scrolling.

Deploy the updated popup PHP, CSS and new WebP together, then purge normal page/asset/CDN caches. The banner filename is versioned, and CSS uses filemtime versions. No SQL import or Product Sample Deploy is needed for this popup change. The source PNG, generated output evidence and private runtime are not deployment requirements.
