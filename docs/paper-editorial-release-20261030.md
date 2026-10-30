# Three approved paper bag guides — release v2

The owner approved the local v2 content and requested Git delivery on 30 October 2026. This release imports **three DRAFT posts and 14 images**. It does not publish the posts or change the website design.

## Deployment

Pull `main`, deploy `wp-content/mu-plugins/vpn-paper-editorial-20261030.php` and the **entire** sibling `vpn-paper-editorial-20261030/` directory. Open `/wp-admin/` while logged in as an existing administrator. The first administrator visit imports the fixed package into that installation's Media Library and posts database. No plugin activation, SQL transfer, Product Sample Deploy action or manual image upload is required.

| Guide | Slug | Featured + inline |
|---|---|---:|
| How to Choose a Paper Bag Supplier for Bulk Orders | `choose-paper-bag-supplier-bulk-orders` | 1 + 4 |
| SOS Paper Bags vs Baguette Bags: Which Fits Your Bakery? | `sos-paper-bags-vs-baguette-bags` | 1 + 3 |
| Paper Bag Sample Approval: What to Check Before Bulk Production | `paper-bag-sample-approval` | 1 + 4 |

Find them under **Posts → Drafts** after deployment. Hosting assigns its own post/media IDs; local IDs are not deployed. Title, excerpt, SEO title, meta description, focus keyword, image alt/title/caption and featured image are included. Rank Math is required and fields match the installed plugin mapping verified during local review. Categories resolve by slug and are created if absent, using their existing names. Author is the existing administrator executing the import; no expert account or credential is created.

Generated inline illustrations are identified as illustrations; two existing VPN workshop photographs retain their provenance captions. All 14 WebPs are bundled outside the ignored uploads directory. Obsolete diagrams, screenshots, local database exports and private runtime backups are excluded from the release.

## Preservation and recovery

The admin-only hook runs on `hopgiayvpn.com` / `www.hopgiayvpn.com`, with a database-session lock and a completion option. No anonymous request can import or choose a payload. All source HTML checksums and image checksums/dimensions are validated before mutation. HTML line endings are normalized so Git checkout settings do not invalidate checksums.

An unrelated existing slug is preserved and reported; the other guides can still import. Interrupted owned drafts resume only when their content/title/excerpt/source and existing SEO values remain compatible. Image asset markers reuse existing attachments. Completed owned articles are preserved, including later edits, publishing, unpublishing or trashing. Re-running does not restore the original content over owner changes.

If an import fails, an administrator notice identifies the affected article; the next administrator visit retries after five minutes. The completion marker is written only after all three results succeed. Removing the loader stops future attempts without deleting imported posts or images.

## Verification

Local v2: all three drafts, complete English content, Rank Math title/meta render, featured/inline images and captions, responsive desktop/mobile layout, scrollable tables and 23 local HTTP resources checked. No unresolved image markers or Windows paths remain in imported content.

Release lifecycle: **23 assertions** passed against real WordPress/media handling on isolated `vpn_chat_test:3311`, prefix `vpe30_`: fresh import, independent failure handling, interrupted resume, 14 images, repeated import, owner-edit preservation, anonymous rejection, request identity, administrator deploy hook and cross-session lock. The main local WordPress database was not used for these lifecycle fixtures.

Reproduce with the isolated test database available:

```powershell
C:/xampp/php/php.exe tests/paper-editorial-deploy-20261030.php --reset
C:/xampp/php/php.exe tests/paper-editorial-deploy-20261030.php --hooks
```

These test commands use only the fixed fixture database/tables on port 3311. Runtime uploads and reports stay local. Hosting deployment and post-publication search visibility have not been verified by this Git release.
