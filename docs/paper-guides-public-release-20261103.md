# Six approved paper-bag buyer guides — public release

The owner approved Git delivery and explicitly requested **PUBLIC status on hosting**. The six articles are already public locally. This release carries their complete English content, 30 original WebP files, image metadata, excerpts and verified Rank Math field mappings. It does not change shared website design or other articles.

## Deployment

Pull `main` and deploy both:

- `wp-content/mu-plugins/vpn-paper-guides-20261103.php`
- The complete sibling directory `wp-content/mu-plugins/vpn-paper-guides-20261103/`

Then **open `/wp-admin/` once while logged in as an existing administrator** with permission to publish posts. This triggers the fixed import on `hopgiayvpn.com` / `www.hopgiayvpn.com`. Each new article is initially staged privately as a draft; it becomes **publish** after its body, five media records, category and SEO fields have passed readback. No plugin activation, SQL import, manual media upload or Product Sample Deploy action is needed.

| Article | Slug | Featured + inline |
|---|---|---:|
| How Many Paper Bags Fit in a Container? A Bulk Shipping Guide | `how-many-paper-bags-fit-in-a-container` | 1 + 4 |
| Paper Bag Quality Control: A Bulk-Order Inspection Checklist | `paper-bag-quality-control` | 1 + 4 |
| Bread Bags with Window: Specifications for Wholesale Orders | `bread-bags-with-window-buying-guide` | 1 + 4 |
| Greaseproof Bakery Bags: A Wholesale Buying Guide | `greaseproof-bakery-bags-buying-guide` | 1 + 4 |
| Custom Printed Baguette Bags: A Guide for Bakery Chains | `custom-printed-baguette-bags-buying-guide` | 1 + 4 |
| Custom Ribbon Handle Gift Bags: A Bulk Ordering Guide | `ribbon-handle-gift-bags-buying-guide` | 1 + 4 |

Hosting assigns its own post and media IDs. Uploaded images use the destination website's real Media Library URLs; article links use the canonical website URLs. Categories resolve by slug, or are created using their existing approved names. The existing administrator executing the import is the author; no account or expert biography is invented.

The package lives outside ignored `wp-content/uploads/`, so all original media files are delivered through Git. It contains no database dump, local post/media IDs, private GSC exports, local preview pages or image-generation instructions. Source HTML checksums normalize Git line-ending changes before comparison. Images are validated by checksum, size, dimensions and MIME type.

## Completion and preservation

- The admin hook has a database-session lock, a completion option and five-minute failure retry. Anonymous visitors cannot trigger imports or choose payloads.
- An unrelated existing slug is preserved and reported in an admin notice. The other articles continue. A conflicting existing article is never silently overwritten or duplicated with a suffixed slug.
- Interrupted owned drafts reuse their post and image markers. Edited partial copies or damaged media stop that article before publication and are reported.
- Completed local-review copies with matching source and media may be accepted without duplication. An untouched approved review draft can be promoted to public on its first release.
- After the release is accepted, later owner changes to text, SEO or status—including unpublishing/trash—are preserved. Repeated deployment does not force a later owner decision back to public.
- If prerequisites are missing (active Rank Math or a WebP image editor), an administrator sees the error. Completion is saved only after all six articles finish without errors. Removing the loader stops future attempts without deleting the posts or media.

## Verification

Real WordPress/media lifecycle tests run against **isolated `vpn_chat_test` on loopback port 3311, prefix `vpg03_`**. They cover a fresh public import, all 30 assets, publication only after content/images/SEO are saved, category/featured/SEO readback, slug conflicts, independent continuation, interrupted resume, original source-checksum adoption, repeat deployment, later edits/unpublishing/trash, invalid image metadata, missing SEO plugin, anonymous rejection, the actual administrator hook and a cross-session database lock.

**35 assertions passed:** 22 lifecycle checks and 13 hosting-style administrator-hook checks. No hosting changes were performed during testing.

```powershell
C:/xampp/php/php.exe -d extension=gd tests/paper-guides-deploy-20261103.php --reset
C:/xampp/php/php.exe -d extension=gd tests/paper-guides-deploy-20261103.php --hooks
```

The tests use only their fixed fixture tables and isolated uploads, block outbound HTTP/mail, and remove fixture articles/media afterward. Local content verification also confirmed all six existing public local URLs and their presence in the Blog listing. Hosting itself remains to be deployed by the owner; this Git push does not deploy it or submit URLs to search engines.
