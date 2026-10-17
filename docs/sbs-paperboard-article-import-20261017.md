# SBS Paperboard article import — 17 October 2026

Imported the owner-provided HTML and five WebP assets into local WordPress as post ID **9000**, status **Published**. The local URL is `http://localhost/hopgiayvpn/sbs-paperboard/`.

The post uses the existing **Packaging Materials** category and all six supplied tags. Rank Math title, description and focus keyword match the supplied metadata. The featured image is attached as the thumbnail and the four inline markers are replaced with four lazy-loaded images and captions. All five supplied images are 1600×900 WebP files below 100 KB. The source article and the five extracted image files are preserved in `seo-content/sbs-paperboard-20261017/`.

The images are used as article visuals. Their captions describe the diagrams and sample checks without claiming that a pictured sample was tested or produced by VPN. The HTML does not claim that VPN stocks a specific mill grade or that the listed example values are VPN test results.

The connected WPVibe site read returned no existing `/sbs-paperboard/` post on 17 October 2026, so the deploy migration can create that URL. The only deploy files are the dedicated one-time migration `wp-content/mu-plugins/vpn-sbs-paperboard-article-import.php` and its fixed payload folder `wp-content/mu-plugins/vpn-sbs-paperboard-article/`. On the production domain, the migration verifies hashes and image dimensions, refuses to overwrite an unrelated slug, creates the post/media/category/tags/SEO fields, publishes once, then clears the LiteSpeed post and blog caches. It does not submit the URL to Google or Bing.

`tools/import-sbs-paperboard-local.php` is CLI-only and refuses non-local WordPress/DB hosts. `tools/build-sbs-paperboard-package.py` rebuilds the fixed deploy payload from the preserved source folder.

## Local verification

- Local PHP CLI import completed and a repeat run returned the same post ID, five attachments and public status without duplicating records.
- The rendered local URL returned HTTP 200, one H1, the title, footer, featured image, four inline figures and no unresolved image markers.
- Both PHP files passed `php -l`.

The local database is separate from hosting. The production post is created and set to Published only after the deploy files are pulled and a WordPress request runs the migration.
