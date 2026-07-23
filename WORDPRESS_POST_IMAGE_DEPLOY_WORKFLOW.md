# WordPress Post + Image Deploy Workflow

Use this workflow when a post and its images are prepared locally and a normal Git pull must be enough to deliver all required files to hosting without manual database editing or manual Media Library uploads.

## Deployment Contract

This repository uses a Git-bundled, admin-triggered sync:

1. Git delivers the post-sync PHP file and the original image files.
2. The next authenticated WP Admin request runs the idempotent `admin_init` sync.
3. The sync copies missing bundled images into WordPress uploads, creates Media Library records and thumbnails, then updates the post.
4. The sync marks its version complete only after verifying the database state.

`git pull` updates files but cannot update the WordPress database by itself. Unless hosting has an explicit post-deploy PHP/WP-CLI hook, opening any WP Admin page once after the pull is the required database trigger.

## Git-Tracked Image Bundle

Every original image required by the post must be committed under:

```text
wp-content/themes/custom-box-theme/inc/product-sample-deploy-assets/uploads/YYYY/MM/
```

Commit only the original source image for each filename base. WordPress generates responsive thumbnails after copying the original into:

```text
wp-content/uploads/YYYY/MM/
```

Do not depend on untracked local files under `wp-content/uploads/`. A local upload returning `200` does not prove that the image is present in Git or registered in the live Media Library.

The attachment creator must use the bundled file as a fallback:

```php
$candidate_relative = 'YYYY/MM/' . $base . '.' . $extension;
$upload_path = trailingslashit($uploads['basedir']) . $candidate_relative;
$bundle_path = get_template_directory()
    . '/inc/product-sample-deploy-assets/uploads/'
    . $candidate_relative;

if (!file_exists($upload_path) && file_exists($bundle_path)) {
    if (!wp_mkdir_p(dirname($upload_path)) || !copy($bundle_path, $upload_path)) {
        continue;
    }
}
```

After the file exists in uploads, create the attachment with `wp_insert_attachment()`, generate metadata with `wp_generate_attachment_metadata()`, and save `_wp_attached_file`.

## Required Implementation Pattern

The deployable sync must live in the active theme, not only in a CLI tool.

For each post import:

- Add a post-sync file under `wp-content/themes/custom-box-theme/inc/`.
- Hook the sync with `admin_init`, guarded by `current_user_can('manage_options')`.
- Hook notices with `admin_notices`.
- Register the sync file, version option, expected version and target slug in `custom_box_post_sync_registry()` inside `wp-content/themes/custom-box-theme/inc/post-sync-loader.php`.
- Keep `post-sync-loader.php` included from `functions.php`; do not add individual post-sync files directly to the unconditional theme include list.
- The loader must load a new or incomplete pull-deploy sync on every normal WP Admin page, load the matching sync while its target post is being edited, support the explicit force-run query, and rotate completed sync health checks without loading every historical sync on every request.
- Find the target post by slug, with an exact-title fallback when useful.
- Find attachments by exact filename base, never by attachment ID.
- Copy a missing original from the Git-tracked bundle into uploads before creating its attachment.
- Update attachment alt text, title, caption and `post_parent`.
- Preserve `publish` and `private`; use `draft` for unpublished posts unless publishing was explicitly requested.
- Update the canonical excerpt, category, tags, Rank Math fields, featured image and inline figures.
- Use stable HTML comment markers before inline figures so reruns replace instead of duplicate.
- Add a detailed success notice only after the completion validator passes.
- Add a warning notice containing missing filenames, slots or validation failures.

CLI scripts in `tools/` are optional verification or repair helpers. They are not the only deploy mechanism unless the hosting pull process explicitly runs them.

## Runtime Loading and Admin Performance

Completed historical syncs must not all be parsed and validated on every WordPress Admin request.

The central post-sync loader provides four paths:

1. A missing or mismatched version option loads that sync immediately on any normal authenticated Admin request.
2. Editing a registered target post loads its matching sync so the validator can repair content or metadata.
3. `?custom_box_run_post_syncs=1` loads all registered syncs for an explicit audit or repair.
4. A rotating health audit loads at most one completed sync per interval.

Frontend, AJAX, REST and cron requests must not load post-sync files through this loader. Every new post sync must be added to the registry with the exact file, version, option and slug. The registry version must be updated whenever the sync version changes.

## Version and Retry Rules

Never use a version option as the only early-return condition.

Correct behavior:

```php
if (
    $version === get_option($version_option)
    && $post
    && custom_box_target_post_is_complete((int) $post->ID)
) {
    return;
}
```

After running the sync:

- Write the version option only when the completion validator returns `true`.
- If validation fails, delete the version option and retry on the next WP Admin request.
- Bump the version whenever content, metadata, image bundle or validation logic changes.
- Clear any stale success notice when validation fails.

This prevents a partial run from being permanently recorded as successful.

## Required Completion Validator

A sync is complete only when all of these checks pass against the saved WordPress database:

- The target post exists with the expected slug and canonical excerpt.
- The post remains `draft`, or preserves an existing `publish`/`private` status.
- The featured image ID is non-zero.
- The featured attachment `_wp_attached_file` has the expected filename base.
- The stable inline marker count equals the expected inline-image count.
- The saved `<figure>` count equals the expected inline-image count.
- The saved `<img>` count equals the expected inline-image count.
- Every expected inline filename base appears in the saved content.
- No `IMAGE_SLOT_*` placeholder remains.
- The expected category slug is assigned.
- The exact expected tag-slug set is assigned, not merely a non-zero tag count.
- `rank_math_title`, `rank_math_description` and `rank_math_focus_keyword` exactly match the post specification.
- The missing-image and missing-slot lists are empty.

Checking only stable markers is insufficient. A Classic Editor save can leave markers while removing or altering the actual `<figure>/<img>` HTML.

## Canonical Draft and Classic Editor Safety

For unpublished posts, the sync should restore the canonical bundled content before inserting images. This lets the next admin request repair content if Classic Editor or a stale edit form removes figures or metadata.

For published or private posts, do not overwrite reviewed content automatically unless it is empty or still contains explicit image slots. Continue repairing safe metadata, attachments and figures only according to the post-specific policy.

## Filename Rule

The extension can differ between environments, but the filename base must stay stable.

```text
types-of-cosmetic-packaging-guide.png
types-of-cosmetic-packaging-guide.webp
```

Both map to:

```text
types-of-cosmetic-packaging-guide
```

Attachment lookup must confirm the exact base with `pathinfo()`. A broad SQL `LIKE` match alone can select a generated thumbnail or a similarly named file.

## Image Metadata Rule

Set image metadata automatically from a fixed map:

```text
filename base -> alt text -> title -> caption
```

Example:

```text
types-of-cosmetic-packaging-guide
Alt: Types of cosmetic packaging guide for beauty brands
Title: Types of Cosmetic Packaging Guide
Caption: A practical guide to cosmetic packaging types for skincare and beauty brands.
```

## Clean Image HTML

Use predictable figure markup:

```html
<!-- stable-post-image:slot_1 -->
<figure>
  <img src="IMAGE_URL" alt="ALT_TEXT" style="width:100%; height:auto;" loading="lazy" decoding="async">
  <figcaption>CAPTION</figcaption>
</figure>
```

Replace a span-wrapped slot before doing a plain string replacement, otherwise Classic Editor content can become invalid nested markup:

```php
if (preg_match($wrapped_slot_pattern, $content)) {
    $content = preg_replace($wrapped_slot_pattern, $figure, $content, 1);
} elseif (false !== strpos($content, $slot)) {
    $content = str_replace($slot, $figure, $content);
}
```

## Mandatory Local Repair Test

A normal successful import is not enough. Before pushing:

1. Run the sync and confirm the completion validator returns `true`.
2. Keep the current sync version stored.
3. Deliberately remove one saved inline `<figure>`.
4. Remove the featured image.
5. Remove all post tags.
6. Delete `rank_math_focus_keyword`.
7. Run the normal sync entry point again.
8. Confirm the validator changes from `false` back to `true`.
9. Confirm the same post ID is retained and no duplicate attachments or figures are created.

This test proves that the version guard does not hide broken state.

## Pull-Deploy Flow

```text
Local draft and image plan ready
-> Put original images in the Git-tracked theme bundle
-> Add the theme post-sync file and functions.php include
-> Run syntax, normal-sync and destructive-repair tests locally
-> Commit the sync, functions.php and every original bundled image
-> Push to the deployment branch
-> Pull the deployment branch on hosting
-> Open or refresh any authenticated WP Admin page once
-> Sync copies/registers images and updates the post database
-> Detailed success notice reports post ID, featured image, figure count, category, tag count and Rank Math state
-> Review the complete draft
-> Publish manually
```

## Pre-Push Checklist

Before pushing a post/image import:

- `php -l` passes for the post-sync file.
- `php -l` passes for `wp-content/themes/custom-box-theme/functions.php`.
- `git diff --cached --check` passes.
- `git diff --cached --name-only` includes the post-sync file, the post-sync loader registry change, `functions.php` only when the loader bootstrap changes, and every original bundled image.
- SHA-256 checks confirm bundled images match their intended local originals.
- Normal local sync confirms the expected post ID, status, featured image, figure count and zero remaining `IMAGE_SLOT_*`.
- Exact category, exact tag set and exact Rank Math fields are verified from the database.
- The mandatory destructive-repair test passes while the current version option is already stored.
- A second normal run does not create duplicate posts, attachments, markers or figures.
- No unrelated dirty-worktree files are staged.
- The commit does not rely on a `tools/` script as its only hosting mechanism.

## Post-Pull Verification

After pulling on hosting:

- Confirm a bundled image URL returns `200`.
- Confirm the copied uploads URL returns `200`.
- Open any WP Admin page to trigger `admin_init`.
- Require a detailed green success notice containing the actual post ID and verified counts.
- Reload the editor once after the notice.
- Confirm the first inline image appears at its planned slot, not merely somewhere in the content.
- Confirm the Featured Image, Categories and Tags boxes contain the expected values.
- Confirm Rank Math displays the expected focus keyword, SEO title and description.

HTTP `200` for an image file is necessary but not sufficient. The deployment is incomplete until the Media Library attachment, featured-image relationship, inline HTML, taxonomy terms and Rank Math metadata are all verified in the WordPress database.
