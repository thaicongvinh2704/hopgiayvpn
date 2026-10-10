# Import Collapsible Rigid Boxes

This release contains the supplied article, SEO metadata and five 1600 × 900 WebP images (each below 100 KB). It creates one **draft blog post**, not a WooCommerce product.

## Hosting

1. Pull `main` and deploy the new files under `wp-content/mu-plugins/`. Deploy both `vpn-collapsible-article-import.php` and the entire `vpn-collapsible-article/` directory. No theme activation or plugin activation is needed.
2. Sign into WordPress as an administrator. Open **Tools → Article Import**, or `/wp-admin/tools.php?page=vpn-article-import`.
3. Click **Import Collapsible Rigid Boxes draft**. Follow the returned **Edit draft** or **Preview** link.
4. Check the article and publish it in the normal editor when ready. Pulling code alone does not create the database post.

The importer assigns one featured image, replaces four inline image slots with Media Library images, sets supplied image alt text and captions, assigns Custom Packaging and five tags, and saves Rank Math title, description and focus keyword. Names/slugs are looked up rather than relying on production database IDs. The source research notes are excluded; hypothetical CBM/cost inputs stay explicitly marked as examples.

The action requires administrator/upload permissions and a valid WordPress nonce. No public import endpoint or automatic-on-page-load database writes. SHA-256 checks validate the article and original images before importing. A completed second run returns the same post without overwriting edits. If an unrelated post already uses the slug, the importer stops. An interrupted empty draft can resume, reusing attachments; if someone edits it meanwhile, the importer stops for review. It never changes existing products or publishes automatically.

## Local

The same tool appears in the local WordPress administration when Apache/MySQL are running. Files alone are prepared in this release; local database import is not claimed. The bundled manifest and article remain the deployment source.

The rollback for code is removing these new importer files; this does not delete a created draft or its Media Library images. Those can be managed normally in WordPress. Do not bulk restore an older theme: this package does not modify About or other theme files.
