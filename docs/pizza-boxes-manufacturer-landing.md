# Pizza boxes manufacturer landing

Route: `/custom-pizza-boxes-manufacturer/`. The theme supplies this page without
requiring a WordPress Page record. Published products are read from the existing
`pizza-boxes` category. The quotation form uses the existing admin-post handler.

## Evidence and imagery

The page uses existing VPN photos of a printing operator, the assembly floor
and the packaging workshop. Source files and responsive encoding details are
recorded in `assets/images/pizza-box-landing/README.md`. Factory captions show
general packaging activity; pizza product concept images remain labelled.
Company name and address come from the site's shared business schema. Company
and location links let buyers inspect the published manufacturer details.
No new certification, customer review, test result or staff credential is claimed.

## Performance

Only this route loads the smaller shared stylesheet and icon fonts. The header,
footer, mobile navigation, search behavior and quotation handler are retained.
The loading overlay is omitted. FAQ uses native `details` elements and maps use
ordinary links. A small inline form script fetches fresh security tokens only
when the buyer interacts with the form; it adds no initial network request.

The hero uses an eager, preloaded, responsive WebP. Other factory and product
images load lazily, with dimensions reserved. Product images have explicit
responsive sizes. One product query is shared and attachment metadata is primed.

`pizza-landing-shell-manifest.json` fingerprints shared CSS, JavaScript and icon
definitions. If these files change, the page falls back to the full theme assets
until its bundles are rebuilt. Fingerprints normalize Windows/Linux line endings.
Normal production requests need no build tools.
Bundle files, photos and fonts should be deployed with the theme.

To rebuild locally, install FontTools/Brotli and make PurgeCSS available to the
Node builder (or set `PURGECSS_MODULE` to the installed module path). Lightning CSS
is supplied by the existing `tests/mobile-ux` development dependencies.

```text
python tools/build-pizza-landing-icon-fonts.py
node tools/build-pizza-landing-shell.mjs
```

Font Awesome Free 6.5.0 sources are in `assets/vendor/fontawesome`. Font subsets
retain the source copyright and license metadata. Fonts use SIL OFL 1.1; the
original CSS license notice is preserved in `pizza-landing-icons.css`.

## Validation

```text
cd tests/mobile-ux
node scripts/qa-pizza-boxes-manufacturer.mjs final
```

Reports and screenshots are written to
`artifacts/pizza-boxes-landing-20260928/final`. The 390 px run simulates 1.6 Mbps
download, 150 ms latency and a 4x slower CPU with an empty browser cache. Timing
includes the local WordPress server response; it is not a hosting or field score.
The checks do not submit quotation emails.

## Form checks

```text
php tests/pizza-boxes-quote-form.php success
php tests/pizza-boxes-quote-form.php minimal
php tests/pizza-boxes-quote-form.php mail-failure
php tests/pizza-boxes-quote-form.php missing
php tests/pizza-boxes-quote-form.php consent
php tests/pizza-boxes-quote-form.php invalid
php tests/pizza-boxes-quote-form.php spam
php tests/pizza-boxes-quote-form.php captcha-success
php tests/pizza-boxes-quote-form.php captcha-failure
cd tests/mobile-ux
node scripts/qa-pizza-boxes-quote-form.mjs
```

Run these integration checks against a local development database. They create
private test enquiries, inspect the saved fields and redirect states, and then
delete those exact records. Mail, Google verification and retry transports are
intercepted: these checks never send real email. Browser checks cover cached
expired fields, reCAPTCHA listener order, failure recovery and no-cache responses.

## Hosting deployment

1. Pull `main` and deploy the complete tracked theme assets.
2. Use **Product Sample Deploy** for the previously committed pizza products
   and category if they have not yet been imported on this hosting installation.
   This landing itself requires no database import or permalink flush.
3. Purge the page/CDN cache once so the new form script and assets are served.
   Leave `wp-admin/admin-post.php` and `wp-admin/admin-ajax.php` accessible to
   guests and excluded from page caching. Do not cache `quote_status` responses.
4. The theme derives HTTPS form/asset URLs from the hosting WordPress URLs.
   Keep `WP_HOME`/`WP_SITEURL` correct for the production domain. If reCAPTCHA is
   enabled, both keys must match that domain; do not copy localhost keys.
5. Email uses the site's existing `wp_mail` transport and is addressed to
   `sales.vpn@hopgiayvpn.com`. Configure the existing SMTP/hosting mail transport;
   ensure `WPMS_DO_NOT_SEND` is not enabled in production. No credentials are
   included in this commit. PHP/browser checks do not establish inbox delivery
   on hosting; that requires an authorized real submission after deployment.

The form validates consent on the server, saves each valid enquiry privately in
**Quote Requests**, then attempts email delivery. If email fails, the saved lead
remains available, retry is requested, and the buyer sees a received message
with a direct contact address. A database/save failure still shows an error.
