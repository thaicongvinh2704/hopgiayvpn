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
-> Deploy theme code
-> Open live WP admin once
-> Code syncs images + metadata + post content
-> Review draft
-> Publish manually
```
