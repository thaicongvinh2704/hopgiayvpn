# WordPress Post + Image Deploy Workflow

Use this workflow when a blog post is prepared locally, but the final post should be completed on the live hosting site without manual database editing.

## Best Method

Upload the images to the live WordPress Media Library first, then let theme code find those images and attach them to the post automatically.

This is faster and safer than moving images from local code because WordPress will handle:

- Media Library attachment records
- Upload paths
- Image thumbnails
- Image metadata
- File permissions on hosting

## What You Do Manually

1. Upload all required images to the live WordPress Media Library.
2. Keep the filenames the same as planned.
3. Deploy or pull the theme code on hosting.
4. Open WordPress admin once.
5. Check the draft post.
6. Click Publish when everything looks correct.

## What The Code Does Automatically

The theme migration code should:

- Find the target post by slug.
- Find each uploaded image by filename base.
- Update attachment alt text.
- Update attachment title.
- Update attachment caption if needed.
- Set the featured image.
- Insert images into the post content in the correct positions.
- Keep the post as draft.
- Show an admin notice if any image is missing.

## Required Implementation Pattern

The deployable sync must live in the active theme, not only in a CLI tool.

For each new post import:

- Add a post sync file under `wp-content/themes/custom-box-theme/inc/`.
- Hook the sync with `admin_init`, guarded by `current_user_can('manage_options')`.
- Add an admin notice with `admin_notices` for missing images, missing slots, or missing draft post.
- Include the new `inc/...-post-sync.php` file in `wp-content/themes/custom-box-theme/functions.php`.
- Keep the sync idempotent by using stable markers before inserted figures.
- Find the post by slug, with title fallback when useful.
- Find attachments by filename base, not attachment ID.
- Preserve `publish` and `private` statuses; only force `draft` for unpublished drafts/pending posts.
- Update Rank Math fields, excerpt, category, tags, featured image, and inline figures.
- Commit and push the theme sync file plus the `functions.php` include.

CLI scripts in `tools/` are optional helpers only. They can be used for local verification or one-off manual repair, but they do not run on hosting after a normal pull deploy unless the hosting process explicitly calls them. A post import is not complete if the only new code is under `tools/`.

## Filename Rule

The uploaded image extension can be different, such as `.png` locally and `.webp` on hosting, but the filename base should stay the same.

Example:

```text
types-of-cosmetic-packaging-guide.png
types-of-cosmetic-packaging-guide.webp
```

Both are OK if the attachment filename base is:

```text
types-of-cosmetic-packaging-guide
```

## Alt Text Rule

Alt text does not need to be entered manually in Media Library.

The code should set it automatically from a fixed map:

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

Images inserted into post content should use this format:

```html
<figure>
  <img src="IMAGE_URL" alt="ALT_TEXT" style="width:100%; height:auto;" loading="lazy" decoding="async">
</figure>
```

## Important Notes

- Do not rely on attachment IDs between local and live sites. IDs can be different.
- Find images by filename base instead.
- Do not publish automatically unless explicitly requested.
- Make the migration idempotent, so running it multiple times does not duplicate images.
- If an image is missing, skip that image and show which filename is missing.

## Recommended Flow

```text
Local draft/content ready
-> Upload images to live Media Library
-> Add theme post-sync file and include it in functions.php
-> Deploy or pull theme code on hosting
-> Open live WP admin once
-> Code syncs images + metadata + post content
-> Review draft
-> Publish manually
```

## Pre-Push Checklist

Before pushing a post/image import:

- `php -l` passes for the new theme sync file.
- `php -l` passes for `wp-content/themes/custom-box-theme/functions.php`.
- `git diff --cached --name-only` includes the theme sync file and `functions.php` include.
- Local verification confirms featured image, inline figure count, zero remaining `IMAGE_SLOT_*`, Rank Math metadata, category, and tags.
- The commit does not rely on a `tools/` script as the only deploy mechanism.
