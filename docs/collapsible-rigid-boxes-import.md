# Import Collapsible Rigid Boxes

This release contains the supplied article, SEO metadata and five 1600 × 900 WebP images (each below 100 KB). The owner approved **automatic publication after deploy**. It creates one published blog post, not a WooCommerce product.

## Hosting

1. Pull `main` and deploy the new files under `wp-content/mu-plugins/`. Deploy both `vpn-collapsible-article-import.php` and the entire `vpn-collapsible-article/` directory. No theme activation or plugin activation is needed.
2. The next uncached WordPress request automatically imports the images, content, terms and SEO, then publishes the completed article. No import button or editor publication is required. Requests served entirely from a static/CDN cache cannot run PHP; opening `/wp-admin/` after deploy guarantees a WordPress request if the deployment did not already make one.
3. View `https://hopgiayvpn.com/collapsible-rigid-boxes/` (assuming the site's existing root post permalinks). The post is public, with one featured and four inline images.
4. If deployment was incomplete or an unrelated slug exists, an administrator notice reports the error. The migration retries after five minutes. **Tools → Article Import** has a manual retry button for diagnosis/recovery.

The importer assigns one featured image, replaces four inline image slots with Media Library images, sets supplied image alt text and captions, assigns Custom Packaging and five tags, and saves Rank Math title, description and focus keyword. Names/slugs are looked up rather than relying on production database IDs. The source research notes are excluded; hypothetical CBM/cost inputs stay explicitly marked as examples.

The automatic path is a fixed, owner-approved deployment migration with no request-selected payload. It runs on `init` only until a successful publication marker is saved, only for hopgiayvpn.com/www or localhost/127.0.0.1, and uses an administrator author when the request is anonymous. It does not grant privileges to the visitor. A database lock prevents concurrent imports. The manual retry requires administrator/upload permissions and a valid WordPress nonce. SHA-256 checks validate normalized article text and original images before importing. The article stays draft until all five images and metadata are prepared. A completed deployment never overwrites later edits or republishes an article the owner subsequently unpublishes. An unrelated existing slug stops the import. An interrupted empty draft can resume, reusing attachments; a previously completed draft from the first importer version is promoted to publish without reimporting. It never changes existing products.

## Local

The same automatic migration runs on localhost/127.0.0.1 WordPress when Apache/MySQL are running. Files alone are prepared in this release; local database import is not claimed. The bundled manifest and article remain the deployment source.

The rollback for code is removing these new importer files; this does not delete a created draft or its Media Library images. Those can be managed normally in WordPress. Do not bulk restore an older theme: this package does not modify About or other theme files.
